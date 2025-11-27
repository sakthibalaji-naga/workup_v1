<?php

use app\services\MergeTickets;

defined('BASEPATH') or exit('No direct script access allowed');

class Tickets_model extends App_Model
{
    private $ticketHasCreatorColumn = null;
    private $piping = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function ticket_count($status = null)
    {
        $where = 'AND merged_ticket_id is NULL';
        if (!is_admin()) {
            $this->load->model('departments_model');
            $staff_deparments_ids = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
            if (get_option('staff_access_only_assigned_departments') == 1) {
                $departments_ids = [];
                if (count($staff_deparments_ids) == 0) {
                    $departments = $this->departments_model->get();
                    foreach ($departments as $department) {
                        array_push($departments_ids, $department['departmentid']);
                    }
                } else {
                    $departments_ids = $staff_deparments_ids;
                }
                if (count($departments_ids) > 0) {
                    $tickets = db_prefix() . 'tickets';
                    $deptWhere = 'department IN (SELECT departmentid FROM ' . db_prefix() . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")';
                    $assignedWhere = $tickets . '.assigned = ' . get_staff_user_id();
                    $creatorWhere  = $tickets . '.admin = ' . get_staff_user_id();
                    $clauses = [$deptWhere, $assignedWhere, $creatorWhere];
                    // pending reassignments visible too (if table exists)
                    $reassign = db_prefix() . 'ticket_reassignments';
                    if ($this->db->table_exists($reassign)) {
                        $pendingWhere = 'EXISTS (SELECT 1 FROM ' . $reassign . ' tr WHERE tr.ticketid = ' . $tickets . '.ticketid AND tr.status = "pending" AND tr.to_assigned = ' . get_staff_user_id() . ')';
                        $clauses[] = $pendingWhere;
                    }
                    // handler relation (if table exists)
                    $handlersTable = db_prefix() . 'ticket_handlers';
                    if ($this->db->table_exists($handlersTable)) {
                        $handlerWhere  = 'EXISTS (SELECT 1 FROM ' . $handlersTable . ' th WHERE th.ticketid = ' . $tickets . '.ticketid AND th.staffid = ' . get_staff_user_id() . ')';
                        $clauses[] = $handlerWhere;
                    }
                    $where = 'AND (' . implode(' OR ', $clauses) . ')';
                }
            }
        }
        $_where = '';
        if (!is_null($status)) {
            if ($where == '') {
                $_where = 'status=' . $status;
            } else {
                $_where = 'status=' . $status . ' ' . $where;
            }
        }

        return total_rows(db_prefix() . 'tickets', $_where);
    }

    public function insert_piped_ticket($data)
    {
        $data = hooks()->apply_filters('piped_ticket_data', $data);

        $this->piping = true;
        $attachments  = $data['attachments'];
        $subject      = $data['subject'];
        // Prevent insert ticket to database if mail delivery error happen
        // This will stop createing a thousand tickets
        $system_blocked_subjects = [
            'Mail delivery failed',
            'failure notice',
            'Returned mail: see transcript for details',
            'Undelivered Mail Returned to Sender',
        ];

        $subject_blocked = false;

        foreach ($system_blocked_subjects as $sb) {
            if (strpos('x' . $subject, $sb) !== false) {
                $subject_blocked = true;

                break;
            }
        }

        if ($subject_blocked == true) {
            return;
        }

        $message = $data['body'];
        $name    = $data['fromname'];

        $email   = $data['email'];
        $to      = $data['to'];
        $cc      = $data['cc'] ?? [];
        $subject = $subject;
        $message = $message;

        $this->load->model('spam_filters_model');
        $mailstatus = $this->spam_filters_model->check($email, $subject, $message, 'tickets');

        // No spam found
        if (!$mailstatus) {
            $pos = strpos($subject, '[Ticket ID: ');
            if ($pos === false) {
            } else {
                $tid = substr($subject, $pos + 12);
                $tid = substr($tid, 0, strpos($tid, ']'));
                $this->db->where('ticketid', $tid);
                $data = $this->db->get(db_prefix() . 'tickets')->row();
                $tid  = $data->ticketid;
            }
            $to            = trim($to);
            $toemails      = explode(',', $to);
            $department_id = false;
            $userid        = false;
            foreach ($toemails as $toemail) {
                if (!$department_id) {
                    $this->db->where('email', trim($toemail));
                    $data = $this->db->get(db_prefix() . 'departments')->row();
                    if ($data) {
                        $department_id = $data->departmentid;
                        $to            = $data->email;
                    }
                }
            }
            if (!$department_id) {
                $mailstatus = 'Department Not Found';
            } else {
                if ($to == $email) {
                    $mailstatus = 'Blocked Potential Email Loop';
                } else {
                    $message = trim($message);
                    $this->db->where('active', 1);
                    $this->db->where('email', $email);
                    $result = $this->db->get(db_prefix() . 'staff')->row();
                    if ($result) {
                        if ($tid) {
                            $data            = [];
                            $data['message'] = $message;
                            $data['status']  = get_option('default_ticket_reply_status');

                            if (!$data['status']) {
                                $data['status'] = 3; // Answered
                            }

                            if ($userid == false) {
                                $data['name']  = $name;
                                $data['email'] = $email;
                            }

                            if (count($cc) > 0) {
                                $data['cc'] = $cc;
                            }

                            $reply_id = $this->add_reply($data, $tid, $result->staffid, $attachments);
                            if ($reply_id) {
                                $mailstatus = 'Ticket Reply Imported Successfully';
                            }
                        } else {
                            $mailstatus = 'Ticket ID Not Found';
                        }
                    } else {
                        $this->db->where('email', $email);
                        $result = $this->db->get(db_prefix() . 'contacts')->row();
                        if ($result) {
                            $userid    = $result->userid;
                            $contactid = $result->id;
                        }
                        if ($userid == false && get_option('email_piping_only_registered') == '1') {
                            $mailstatus = 'Unregistered Email Address';
                        } else {
                            $filterdate = date('Y-m-d H:i:s', strtotime('-15 minutes'));
                            $query      = 'SELECT count(*) as total FROM ' . db_prefix() . 'tickets WHERE date > "' . $filterdate . '" AND (email="' . $this->db->escape($email) . '"';
                            if ($userid) {
                                $query .= ' OR userid=' . (int) $userid;
                            }
                            $query .= ')';
                            $result = $this->db->query($query)->row();
                            if (10 < $result->total) {
                                $mailstatus = 'Exceeded Limit of 10 Tickets within 15 Minutes';
                            } else {
                                if (isset($tid)) {
                                    $data            = [];
                                    $data['message'] = $message;
                                    $data['status']  = 1;
                                    if ($userid == false) {
                                        $data['name']  = $name;
                                        $data['email'] = $email;
                                    } else {
                                        $data['userid']    = $userid;
                                        $data['contactid'] = $contactid;

                                        $this->db->where('ticketid', $tid);
                                        $this->db->group_start();
                                        $this->db->where('userid', $userid);

                                        // Allow CC'ed user to reply to the ticket
                                        $this->db->or_like('cc', $email);
                                        $this->db->group_end();
                                        $t = $this->db->get(db_prefix() . 'tickets')->row();
                                        if (!$t) {
                                            $abuse = true;
                                        }
                                    }
                                    if (!isset($abuse)) {
                                        if (count($cc) > 0) {
                                            $data['cc'] = $cc;
                                        }
                                        $reply_id = $this->add_reply($data, $tid, null, $attachments);
                                        if ($reply_id) {
                                            // Dont change this line
                                            $mailstatus = 'Ticket Reply Imported Successfully';
                                        }
                                    } else {
                                        $mailstatus = 'Ticket ID Not Found For User';
                                    }
                                } else {
                                    if (get_option('email_piping_only_registered') == 1 && !$userid) {
                                        $mailstatus = 'Blocked Ticket Opening from Unregistered User';
                                    } else {
                                        if (get_option('email_piping_only_replies') == '1') {
                                            $mailstatus = 'Only Replies Allowed by Email';
                                        } else {
                                            $data               = [];
                                            $data['department'] = $department_id;
                                            $data['subject']    = $subject;
                                            $data['message']    = $message;
                                            $data['contactid']  = $contactid;
                                            $data['priority']   = get_option('email_piping_default_priority');
                                            if ($userid == false) {
                                                $data['name']  = $name;
                                                $data['email'] = $email;
                                            } else {
                                                $data['userid'] = $userid;
                                            }
                                            $tid = $this->add($data, null, $attachments);
                                            if ($tid && count($cc) > 0) {
                                                // A customer opens a ticket by mail to "support@example".com, with one or many 'Cc'
                                                // Remember those 'Cc'.
                                                $this->db->where('ticketid', $tid);
                                                $this->db->update('tickets', ['cc' => implode(',', $cc)]);
                                            }
                                            // Dont change this line
                                            $mailstatus = 'Ticket Imported Successfully';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($mailstatus == '') {
            $mailstatus = 'Ticket Import Failed';
        }
        $this->db->insert(db_prefix() . 'tickets_pipe_log', [
            'date'     => date('Y-m-d H:i:s'),
            'email_to' => $to,
            'name'     => $name ?: 'Unknown',
            'email'    => $email ?: 'N/A',
            'subject'  => $subject ?: 'N/A',
            'message'  => $message,
            'status'   => $mailstatus,
        ]);

        return $mailstatus;
    }

    private function process_pipe_attachments($attachments, $ticket_id, $reply_id = '')
    {
        if (!empty($attachments)) {
            $ticket_attachments = [];
            $allowed_extensions = array_map(function ($ext) {
                return strtolower(trim($ext));
            }, explode(',', get_option('ticket_attachments_file_extensions')));

            $path = FCPATH . 'uploads/ticket_attachments' . '/' . $ticket_id . '/';

            foreach ($attachments as $attachment) {
                $filename      = $attachment['filename'];
                $filenameparts = explode('.', $filename);
                $extension     = end($filenameparts);
                $extension     = strtolower($extension);
                if (in_array('.' . $extension, $allowed_extensions)) {
                    $filename = implode(array_slice($filenameparts, 0, 0 - 1));
                    $filename = trim(preg_replace('/[^a-zA-Z0-9-_ ]/', '', $filename));

                    if (!$filename) {
                        $filename = 'attachment';
                    }

                    if (!file_exists($path)) {
                        mkdir($path, 0755);
                        $fp = fopen($path . 'index.html', 'w');
                        fclose($fp);
                    }

                    $filename = unique_filename($path, $filename . '.' . $extension);
                    file_put_contents($path . $filename, $attachment['data']);

                    array_push($ticket_attachments, [
                        'file_name' => $filename,
                        'filetype'  => get_mime_by_extension($filename),
                    ]);
                }
            }

            $this->insert_ticket_attachments_to_database($ticket_attachments, $ticket_id, $reply_id);
        }
    }

    public function get($id = '', $where = [])
    {
        $this->db->select('*,' . db_prefix() . 'tickets.userid,' . db_prefix() . 'tickets.name as from_name,' . db_prefix() . 'tickets.email as ticket_email, ' . db_prefix() . 'departments.name as department_name, ' . db_prefix() . 'tickets_priorities.name as priority_name, statuscolor, ' . db_prefix() . 'tickets.admin, ' . db_prefix() . 'services.name as service_name, service, ' . db_prefix() . 'tickets_status.name as status_name,' . db_prefix() . 'tickets.ticketid, ' . db_prefix() . 'contacts.firstname as user_firstname, ' . db_prefix() . 'contacts.lastname as user_lastname,' . db_prefix() . 'staff.firstname as staff_firstname, ' . db_prefix() . 'staff.lastname as staff_lastname,lastreply,message,' . db_prefix() . 'tickets.status,subject,department,priority,' . db_prefix() . 'tickets.sub_department,' . db_prefix() . 'tickets.divisionid,' . db_prefix() . 'tickets.assigned,' . db_prefix() . 'contacts.email,adminread,clientread,date,ticket_number');
        $this->db->join(db_prefix() . 'departments', db_prefix() . 'departments.departmentid = ' . db_prefix() . 'tickets.department', 'left');
        $this->db->join(db_prefix() . 'tickets_status', db_prefix() . 'tickets_status.ticketstatusid = ' . db_prefix() . 'tickets.status', 'left');
        $this->db->join(db_prefix() . 'services', db_prefix() . 'services.serviceid = ' . db_prefix() . 'tickets.service', 'left');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'tickets.userid', 'left');
        $this->db->join(db_prefix() . 'contacts', db_prefix() . 'contacts.id = ' . db_prefix() . 'tickets.contactid', 'left');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'tickets.admin', 'left');
        $this->db->join(db_prefix() . 'tickets_priorities', db_prefix() . 'tickets_priorities.priorityid = ' . db_prefix() . 'tickets.priority', 'left');
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'tickets.ticketid', $id);

            return $this->db->get(db_prefix() . 'tickets')->row();
        }
        $this->db->order_by('lastreply', 'asc');

        if (is_client_logged_in()) {
            $this->db->where(db_prefix() . 'tickets.merged_ticket_id IS NULL', null, false);
        }

        return $this->db->get(db_prefix() . 'tickets')->result_array();
    }

    /**
     * Get ticket by id and all data
     * @param  mixed  $id     ticket id
     * @param  mixed $userid Optional - Tickets from USER ID
     * @return object
     */
    public function get_ticket_by_id($id, $userid = '')
    {
        $this->db->select('*, ' . db_prefix() . 'tickets.userid, ' . db_prefix() . 'tickets.name as from_name, ' . db_prefix() . 'tickets.email as ticket_email, ' . db_prefix() . 'departments.name as department_name, ' . db_prefix() . 'tickets_priorities.name as priority_name, statuscolor, ' . db_prefix() . 'tickets.admin, ' . db_prefix() . 'services.name as service_name, service, ' . db_prefix() . 'tickets_status.name as status_name, ' . db_prefix() . 'tickets.ticketid, ' . db_prefix() . 'contacts.firstname as user_firstname, ' . db_prefix() . 'contacts.lastname as user_lastname, ' . db_prefix() . 'staff.firstname as staff_firstname, ' . db_prefix() . 'staff.lastname as staff_lastname, lastreply, message, ' . db_prefix() . 'tickets.status, subject, department, priority, ' . db_prefix() . 'tickets.sub_department, ' . db_prefix() . 'tickets.divisionid, ' . db_prefix() . 'contacts.email, adminread, clientread, date,ticket_number,' . db_prefix() . 'tickets_priorities.duration_value as priority_duration_value,' . db_prefix() . 'tickets_priorities.duration_unit as priority_duration_unit');
        if ($this->ticket_has_creator_column()) {
            $this->db->select(db_prefix() . 'tickets.created_by');
        }
        $this->db->from(db_prefix() . 'tickets');
        $this->db->join(db_prefix() . 'departments', db_prefix() . 'departments.departmentid = ' . db_prefix() . 'tickets.department', 'left');
        $this->db->join(db_prefix() . 'tickets_status', db_prefix() . 'tickets_status.ticketstatusid = ' . db_prefix() . 'tickets.status', 'left');
        $this->db->join(db_prefix() . 'services', db_prefix() . 'services.serviceid = ' . db_prefix() . 'tickets.service', 'left');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'tickets.userid', 'left');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'tickets.admin', 'left');
        $this->db->join(db_prefix() . 'contacts', db_prefix() . 'contacts.id = ' . db_prefix() . 'tickets.contactid', 'left');
        $this->db->join(db_prefix() . 'tickets_priorities', db_prefix() . 'tickets_priorities.priorityid = ' . db_prefix() . 'tickets.priority', 'left');

        if (strlen($id) === 32) {
            $this->db->where(db_prefix() . 'tickets.ticketkey', $id);
        } else {
            $this->db->where(db_prefix() . 'tickets.ticketid', $id);
        }

        if (is_numeric($userid)) {
            $this->db->where(db_prefix() . 'tickets.userid', $userid);
        }

        $ticket = $this->db->get()->row();
        if ($ticket) {
            $ticket->submitter = $ticket->contactid != 0 ?
            ($ticket->user_firstname . ' ' . $ticket->user_lastname) :
            $ticket->from_name;

            if (!($ticket->admin == null || $ticket->admin == 0)) {
                $ticket->opened_by = $ticket->staff_firstname . ' ' . $ticket->staff_lastname;
            }

            $ticket->attachments = $this->get_ticket_attachments($ticket->ticketid);
        }


        return $ticket;
    }

    /**
     * Insert ticket attachments to database
     * @param  array  $attachments array of attachment
     * @param  mixed  $ticketid
     * @param  boolean $replyid If is from reply
     */
    public function insert_ticket_attachments_to_database($attachments, $ticketid, $replyid = false)
    {
        foreach ($attachments as $attachment) {
            $attachment['ticketid']  = $ticketid;
            $attachment['dateadded'] = date('Y-m-d H:i:s');
            if ($replyid !== false && is_int($replyid)) {
                $attachment['replyid'] = $replyid;
            }
            $this->db->insert(db_prefix() . 'ticket_attachments', $attachment);
        }
    }

    /**
     * Get ticket attachments from database
     * @param  mixed $id      ticket id
     * @param  mixed $replyid Optional - reply id if is from from reply
     * @return array
     */
    public function get_ticket_attachments($id, $replyid = '')
    {
        $this->db->where('ticketid', $id);
        $this->db->where('replyid', is_numeric($replyid) ? $replyid : null);

        return $this->db->get('ticket_attachments')->result_array();
    }

    /**
     * Add new reply to ticket
     * @param mixed $data  reply $_POST data
     * @param mixed $id    ticket id
     * @param boolean $admin staff id if is staff making reply
     */
    public function add_reply($data, $id, $admin = null, $pipe_attachments = false)
    {
        if (isset($data['assign_to_current_user'])) {
            $assigned = get_staff_user_id();
            unset($data['assign_to_current_user']);
        }

        $unsetters = [
            'note_description',
            'department',
            'priority',
            'subject',
            'assigned',
            'project_id',
            'service',
            'status_top',
            'attachments',
            'DataTables_Table_0_length',
            'DataTables_Table_1_length',
            'custom_fields',
        ];

        foreach ($unsetters as $unset) {
            if (isset($data[$unset])) {
                unset($data[$unset]);
            }
        }

        if ($admin !== null) {
            $data['admin'] = $admin;
            $status        = $data['status'];
        } else {
            $status = 1;
        }

        if (isset($data['status'])) {
            unset($data['status']);
        }

        $cc = '';
        if (isset($data['cc'])) {
            $cc = $data['cc'];
            unset($data['cc']);
        }

        // if ticket is merged
        $ticket           = $this->get($id);
        $data['ticketid'] = ($ticket && $ticket->merged_ticket_id != null) ? $ticket->merged_ticket_id : $id;
        $data['date']     = date('Y-m-d H:i:s');
        $data['message']  = trim($data['message']);

        if ($this->piping == true) {
            // $data['message'] = preg_replace('/\v+/u', '<br>', $data['message']);
        }

        $is_html_stripped = $this->piping === true;

        // admin can have html
        if (!$is_html_stripped && 
            $admin == null && 
            hooks()->apply_filters('ticket_message_without_html_for_non_admin', true)
        ) {
            $data['message'] = _strip_tags($data['message']);
            $data['message'] = nl2br_save_html($data['message']);
        }

        if (!isset($data['userid'])) {
            $data['userid'] = 0;
        }

        // $data['message'] = remove_emojis($data['message']);
        $data            = hooks()->apply_filters('before_ticket_reply_add', $data, $id, $admin);

        $this->db->insert(db_prefix() . 'ticket_replies', $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            /**
             * When a ticket is in status "In progress" and the customer reply to the ticket
             * it changes the status to "Open" which is not normal.
             *
             * The ticket should keep the status "In progress"
             */
            $this->db->select('status');
            $this->db->where('ticketid', $id);
            $old_ticket_status = $this->db->get(db_prefix() . 'tickets')->row()->status;

            $newStatus = hooks()->apply_filters(
                'ticket_reply_status',
                ($old_ticket_status == 2 && $admin == null ? $old_ticket_status : $status),
                ['ticket_id' => $id, 'reply_id' => $insert_id, 'admin' => $admin, 'old_status' => $old_ticket_status]
            );

            if (isset($assigned)) {
                $this->db->where('ticketid', $id);
                $this->db->update(db_prefix() . 'tickets', [
                    'assigned' => $assigned,
                ]);
            }

            if ($pipe_attachments != false) {
                $this->process_pipe_attachments($pipe_attachments, $id, $insert_id);
            } else {
                $attachments = handle_ticket_attachments($id);
                if ($attachments) {
                    $this->insert_ticket_attachments_to_database($attachments, $id, $insert_id);
                }
            }

            $_attachments = $this->get_ticket_attachments($id, $insert_id);

            log_activity('New Ticket Reply [ReplyID: ' . $insert_id . ']');

            $this->db->where('ticketid', $id);
            $this->db->update(db_prefix() . 'tickets', [
                'lastreply'  => date('Y-m-d H:i:s'),
                'status'     => $newStatus,
                'adminread'  => 0,
                'clientread' => 0,
            ]);

            if ($old_ticket_status != $newStatus) {
                hooks()->do_action('after_ticket_status_changed', [
                    'id'     => $id,
                    'status' => $newStatus,
                ]);
            }

            $ticket    = $this->get_ticket_by_id($id);
            $userid    = $ticket->userid;
            $isContact = false;
            if ($ticket->userid != 0 && $ticket->contactid != 0) {
                $email     = $this->clients_model->get_contact($ticket->contactid)->email;
                $isContact = true;
            } else {
                $email = $ticket->ticket_email;
            }
            if ($admin == null) {
                $this->load->model('departments_model');
                $this->load->model('staff_model');

                $notifiedUsers = [];
                $staff         = $this->getStaffMembersForTicketNotification($ticket->department, $ticket->assigned);
                foreach ($staff as $staff_key => $member) {
                    send_mail_template('ticket_new_reply_to_staff', $ticket, $member, $_attachments);
                    if (get_option('receive_notification_on_new_ticket_replies') == 1) {
                        $notified = add_notification([
                            'description'     => 'not_new_ticket_reply',
                            'touserid'        => $member['staffid'],
                            'fromcompany'     => 1,
                            'fromuserid'      => 0,
                            'link'            => 'tickets/ticket/' . $id,
                            'additional_data' => serialize([
                                $ticket->subject,
                            ]),
                        ]);
                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            } else {
                $this->update_staff_replying($id);

                $total_staff_replies = total_rows(db_prefix() . 'ticket_replies', ['admin is NOT NULL', 'ticketid' => $ticket->ticketid]);
                if (
                    $ticket->assigned == 0 &&
                    get_option('automatically_assign_ticket_to_first_staff_responding') == '1' &&
                    $total_staff_replies == 1
                ) {
                    $this->db->where('ticketid', $id);
                    $this->db->update(db_prefix() . 'tickets', ['assigned' => $admin]);
                }

                $sendEmail = true;
                if ($isContact && total_rows(db_prefix() . 'contacts', ['ticket_emails' => 1, 'id' => $ticket->contactid]) == 0) {
                    $sendEmail = false;
                }
                if ($sendEmail) {
                    send_mail_template('ticket_new_reply_to_customer', $ticket, $email, $_attachments, $cc);
                }
            }

            if ($cc) {
                // imported reply
                if (is_array($cc)) {
                    if ($ticket->cc) {
                        $currentCC = explode(',', $ticket->cc);
                        $cc        = array_unique([$cc, $currentCC]);
                    }
                    $cc = implode(',', $cc);
                }
                $this->db->where('ticketid', $id);
                $this->db->update('tickets', ['cc' => $cc]);
            }
            hooks()->do_action('after_ticket_reply_added', [
                'data'    => $data,
                'id'      => $id,
                'admin'   => $admin,
                'replyid' => $insert_id,
            ]);

            // Log reply addition
            $this->add_ticket_log($id, 'reply_added', [
                'reply_id' => $insert_id,
                'replied_by' => $admin ?? 'customer',
                'message_length' => strlen($data['message']),
            ]);

            return $insert_id;
        }

        return false;
    }

    /**
     *  Delete ticket reply
     * @param   mixed $ticket_id    ticket id
     * @param   mixed $reply_id     reply id
     * @return  boolean
     */
    public function delete_ticket_reply($ticket_id, $reply_id)
    {
        hooks()->do_action('before_delete_ticket_reply', ['ticket_id' => $ticket_id, 'reply_id' => $reply_id]);

        $this->db->where('id', $reply_id);
        $this->db->delete(db_prefix() . 'ticket_replies');

        if ($this->db->affected_rows() > 0) {
            // Get the reply attachments by passing the reply_id to get_ticket_attachments method
            $attachments = $this->get_ticket_attachments($ticket_id, $reply_id);
            if (count($attachments) > 0) {
                foreach ($attachments as $attachment) {
                    $this->delete_ticket_attachment($attachment['id']);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Remove ticket attachment by id
     * @param  mixed $id attachment id
     * @return boolean
     */
    public function delete_ticket_attachment($id)
    {
        $deleted = false;
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'ticket_attachments')->row();
        if ($attachment) {
            if (unlink(get_upload_path_by_type('ticket') . $attachment->ticketid . '/' . $attachment->file_name)) {
                $this->db->where('id', $attachment->id);
                $this->db->delete(db_prefix() . 'ticket_attachments');
                $deleted = true;
            }
            // Check if no attachments left, so we can delete the folder also
            $other_attachments = list_files(get_upload_path_by_type('ticket') . $attachment->ticketid);
            if (count($other_attachments) == 0) {
                delete_dir(get_upload_path_by_type('ticket') . $attachment->ticketid);
            }
        }

        return $deleted;
    } 

    /**
     * Get ticket attachment by id
     * @param  mixed $id attachment id
     * @return mixed
     */
    public function get_ticket_attachment($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'ticket_attachments')->row();
    }

    /**
     * This functions is used when staff open client ticket
     * @param  mixed $userid client id
     * @param  mixed $id     ticketid
     * @return array
     */
    public function get_user_other_tickets($userid, $id)
    {
        $this->db->select(db_prefix() . 'departments.name as department_name, ' . db_prefix() . 'services.name as service_name,' . db_prefix() . 'tickets_status.name as status_name,' . db_prefix() . 'staff.firstname as staff_firstname, ' . db_prefix() . 'clients.lastname as staff_lastname,ticketid,subject,firstname,lastname,lastreply');
        $this->db->from(db_prefix() . 'tickets');
        $this->db->join(db_prefix() . 'departments', db_prefix() . 'departments.departmentid = ' . db_prefix() . 'tickets.department', 'left');
        $this->db->join(db_prefix() . 'tickets_status', db_prefix() . 'tickets_status.ticketstatusid = ' . db_prefix() . 'tickets.status', 'left');
        $this->db->join(db_prefix() . 'services', db_prefix() . 'services.serviceid = ' . db_prefix() . 'tickets.service', 'left');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'tickets.userid', 'left');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'tickets.admin', 'left');
        $this->db->where(db_prefix() . 'tickets.userid', $userid);
        $this->db->where(db_prefix() . 'tickets.ticketid !=', $id);
        $tickets = $this->db->get()->result_array();
        $i       = 0;
        foreach ($tickets as $ticket) {
            $tickets[$i]['submitter'] = $ticket['firstname'] . ' ' . $ticket['lastname'];
            unset($ticket['firstname']);
            unset($ticket['lastname']);
            $i++;
        }

        return $tickets;
    }

    /**
     * Get all ticket replies
     * @param  mixed  $id     ticketid
     * @param  mixed $userid specific client id
     * @return array
     */
    public function get_ticket_replies($id)
    {
        $ticket_replies_order = get_option('ticket_replies_order');
        // backward compatibility for the action hook
        $ticket_replies_order = hooks()->apply_filters('ticket_replies_order', $ticket_replies_order);

        $this->db->select(db_prefix() . 'ticket_replies.id,' . db_prefix() . 'ticket_replies.name as from_name,' . db_prefix() . 'ticket_replies.email as reply_email, ' . db_prefix() . 'ticket_replies.admin, ' . db_prefix() . 'ticket_replies.userid,' . db_prefix() . 'staff.firstname as staff_firstname, ' . db_prefix() . 'staff.lastname as staff_lastname,' . db_prefix() . 'contacts.firstname as user_firstname,' . db_prefix() . 'contacts.lastname as user_lastname,message,date,contactid');
        $this->db->from(db_prefix() . 'ticket_replies');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'ticket_replies.userid', 'left');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'ticket_replies.admin', 'left');
        $this->db->join(db_prefix() . 'contacts', db_prefix() . 'contacts.id = ' . db_prefix() . 'ticket_replies.contactid', 'left');
        $this->db->where('ticketid', $id);
        $this->db->order_by('date', $ticket_replies_order);
        $replies = $this->db->get()->result_array();
        $i       = 0;
        foreach ($replies as $reply) {
            if ($reply['admin'] !== null || $reply['admin'] != 0) {
                // staff reply
                $replies[$i]['submitter'] = $reply['staff_firstname'] . ' ' . $reply['staff_lastname'];
            } else {
                if ($reply['contactid'] != 0) {
                    $replies[$i]['submitter'] = $reply['user_firstname'] . ' ' . $reply['user_lastname'];
                } else {
                    $replies[$i]['submitter'] = $reply['from_name'];
                }
            }
            unset($replies[$i]['staff_firstname']);
            unset($replies[$i]['staff_lastname']);
            unset($replies[$i]['user_firstname']);
            unset($replies[$i]['user_lastname']);
            $replies[$i]['attachments'] = $this->get_ticket_attachments($id, $reply['id']);
            $i++;
        }

        return $replies;
    }

    /**
     * Add new ticket to database
     * @param mixed $data  ticket $_POST data
     * @param mixed $admin If admin adding the ticket passed staff id
     */
    public function add($data, $admin = null, $pipe_attachments = false)
    {
        if ($admin !== null) {
            $data['admin'] = $admin;
            unset($data['ticket_client_search']);
        }

        if (isset($data['assigned']) && $data['assigned'] == '') {
            $data['assigned'] = 0;
        }

        if (isset($data['project_id']) && $data['project_id'] == '') {
            $data['project_id'] = 0;
        }

        if ($admin == null) {
            if (isset($data['email'])) {
                $data['userid']    = 0;
                $data['contactid'] = 0;
            } else {
                // Opened from customer portal otherwise is passed from pipe or admin area
                if (!isset($data['userid']) && !isset($data['contactid'])) {
                    $data['userid']    = get_client_user_id();
                    $data['contactid'] = get_contact_user_id();
                }
            }
            $data['status'] = 1;
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        // CC is only from admin area
        $cc = '';
        if (isset($data['cc'])) {
            $cc = $data['cc'];
            unset($data['cc']);
        }

        $data['date']      = date('Y-m-d H:i:s');
        $data['ticketkey'] = app_generate_hash();
        $data['status']    = 1;
        $data['message']   = trim($data['message']);
        $data['subject']   = trim($data['subject']);
        // if ($this->piping == true) {
        //     $data['message'] = preg_replace('/\v+/u', '<br>', $data['message']);
        // }

        $is_html_stripped = $this->piping === true;
      
        // Admin can have html
        if (!$is_html_stripped && 
            $admin == null && 
            hooks()->apply_filters('ticket_message_without_html_for_non_admin', true)
        ) {
            $data['message'] = _strip_tags($data['message']);
            $data['subject'] = _strip_tags($data['subject']);
            $data['message'] = nl2br_save_html($data['message']);
        }

        if (!isset($data['userid'])) {
            $data['userid'] = 0;
        }

        if (isset($data['priority']) && $data['priority'] == '' || !isset($data['priority'])) {
            $data['priority'] = 0;
        }

        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }

        // $data['message'] = remove_emojis($data['message']);

        // Normalize sub_department: store NULL if empty/non-numeric
        if (isset($data['sub_department'])) {
            if ($data['sub_department'] === '' || !is_numeric($data['sub_department'])) {
                $data['sub_department'] = null;
            } else {
                $data['sub_department'] = (int) $data['sub_department'];
            }
        }
        // Normalize divisionid
        if (isset($data['divisionid'])) {
            if ($data['divisionid'] === '' || !is_numeric($data['divisionid'])) {
                $data['divisionid'] = null;
            } else {
                $data['divisionid'] = (int) $data['divisionid'];
            }
        }
        if (isset($data['application_id']) && $data['application_id'] == '') {
            $data['application_id'] = null;
        }

        // Generate ticket number
        $year = date('y');
        $count = $this->db->where('YEAR(date)', date('Y'))->count_all_results(db_prefix() . 'tickets');
        $sequence = $count + 1;
        $data['ticket_number'] = $year . str_pad($sequence, 5, '0', STR_PAD_LEFT);

        $data            = hooks()->apply_filters('before_ticket_created', $data, $admin);

        $this->db->insert(db_prefix() . 'tickets', $data);
        $ticketid = $this->db->insert_id();
        if ($ticketid) {
            handle_tags_save($tags, $ticketid, 'ticket');

            if (isset($custom_fields)) {
                handle_custom_fields_post($ticketid, $custom_fields);
            }

            if (isset($data['assigned']) && $data['assigned'] != 0) {
                if ($data['assigned'] != get_staff_user_id()) {
                    $notified = add_notification([
                        'description'     => 'not_ticket_assigned_to_you',
                        'touserid'        => $data['assigned'],
                        'fromcompany'     => 1,
                        'fromuserid'      => 0,
                        'link'            => 'tickets/ticket/' . $ticketid,
                        'additional_data' => serialize([
                            $data['subject'],
                        ]),
                    ]);

                    if ($notified) {
                        pusher_trigger_notification([$data['assigned']]);
                    }

                    send_mail_template('ticket_assigned_to_staff', get_staff($data['assigned'])->email, $data['assigned'], $ticketid, $data['userid'], $data['contactid']);
                }
            }
            if ($pipe_attachments != false) {
                $this->process_pipe_attachments($pipe_attachments, $ticketid);
            } else {
                $attachments = handle_ticket_attachments($ticketid);
                if ($attachments) {
                    $this->insert_ticket_attachments_to_database($attachments, $ticketid);
                }
            }

            $_attachments = $this->get_ticket_attachments($ticketid);


            $isContact = false;
            if (isset($data['userid']) && $data['userid'] != false) {
                $email     = $this->clients_model->get_contact($data['contactid'])->email;
                $isContact = true;
            } else {
                $email = $data['email'];
            }

            $template = 'ticket_created_to_customer';
            if ($admin == null) {
                $template      = 'ticket_autoresponse';
                $notifiedUsers = [];
                $staffToNotify = $this->getStaffMembersForTicketNotification($data['department'], $data['assigned'] ?? 0);
                foreach ($staffToNotify as $member) {
                    send_mail_template('ticket_created_to_staff', $ticketid, $data['userid'], $data['contactid'], $member, $_attachments);
                    if (get_option('receive_notification_on_new_ticket') == 1) {
                        $notified = add_notification([
                            'description'     => 'not_new_ticket_created',
                            'touserid'        => $member['staffid'],
                            'fromcompany'     => 1,
                            'fromuserid'      => 0,
                            'link'            => 'tickets/ticket/' . $ticketid,
                            'additional_data' => serialize([
                                $data['subject'],
                            ]),
                        ]);
                        if ($notified) {
                            $notifiedUsers[] = $member['staffid'];
                        }
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            } else {
                if ($cc) {
                    $this->db->where('ticketid', $ticketid);
                    $this->db->update('tickets', ['cc' => is_array($cc) ? implode(',', $cc) : $cc]);
                }
            }

            $sendEmail = true;

            if ($isContact && total_rows(db_prefix() . 'contacts', ['ticket_emails' => 1, 'id' => $data['contactid']]) == 0) {
                $sendEmail = false;
            }

            if ($sendEmail) {
                $ticket = $this->get_ticket_by_id($ticketid);
                // $admin == null ? [] : $_attachments - Admin opened ticket from admin area add the attachments to the email
                send_mail_template($template, $ticket, $email, $admin == null ? [] : $_attachments, $cc);
            }

            hooks()->do_action('ticket_created', $ticketid);
            log_activity('New Ticket Created [ID: ' . $ticketid . ']');

            // Log ticket creation
            $this->add_ticket_log($ticketid, 'ticket_created', [
                'subject' => $data['subject'],
                'department' => $data['department'],
                'priority' => $data['priority'],
                'service' => $data['service'] ?? null,
                'assigned' => $data['assigned'] ?? 0,
                'created_by' => $admin ?? get_staff_user_id(),
            ]);

            // Send SMS notification for new ticket created
            $this->load->library('sms/App_sms');
            $creator_id = $admin ?? get_staff_user_id();
            $creator = $this->staff_model->get($creator_id);

            // Prepare merge fields for SMS
            $merge_fields = [];
            $ticket_data = $this->get_ticket_by_id($ticketid);
            $merge_fields['{ticket_id}'] = $ticketid;
            $merge_fields['{ticket_number}'] = $ticket_data->ticket_number ?? $ticketid;
            $merge_fields['{ticket_subject}'] = $data['subject'];
            if ($creator) {
                $merge_fields['{staff_firstname}'] = $creator->firstname;
                $merge_fields['{staff_lastname}'] = $creator->lastname;
            }

            // Send SMS to assigned staff if available, otherwise to creator or a default
            $sms_recipient = null;
            if (!empty($data['assigned']) && $data['assigned'] != $creator_id) {
                $assigned_staff = $this->staff_model->get($data['assigned']);
                if ($assigned_staff && !empty($assigned_staff->phonenumber)) {
                    $sms_recipient = $assigned_staff->phonenumber;
                }
            }
            // If no assigned or no phone, send to creator if they have phone
            if (!$sms_recipient && $creator && !empty($creator->phonenumber)) {
                $sms_recipient = $creator->phonenumber;
            }

            if ($sms_recipient) {
                $sms_recipient_type = (!empty($data['assigned']) && $data['assigned'] == $creator_id) ? 'creator' : 'assignee';
                $sms_sent = $this->app_sms->trigger(SMS_TRIGGER_NEW_TICKET_CREATED, $sms_recipient, $merge_fields);

                // Log SMS notification
                $this->add_ticket_log($ticketid, 'sms_notification_sent', [
                    'trigger' => _l('sms_trigger_new_ticket_created', 'custom_lang'),
                    'recipient' => $sms_recipient,
                    'recipient_type' => _l('sms_recipient_type_' . $sms_recipient_type, 'custom_lang'),
                    'sent_by' => $creator_id,
                    'sms_sent' => $sms_sent ? _l('ticket_log_sms_sent_status', 'custom_lang') : _l('failed', 'custom_lang'),
                ]);
            }

            return $ticketid;
        }

        return false;
    }

    /**
     * Get latest 5 client tickets
     * @param  integer $limit  Optional limit tickets
     * @param  mixed $userid client id
     * @return array
     */
    public function get_client_latests_ticket($limit = 5, $userid = '')
    {
        $this->db->select(db_prefix() . 'tickets.userid, ticketstatusid, statuscolor, ' . db_prefix() . 'tickets_status.name as status_name,' . db_prefix() . 'tickets.ticketid, subject, date');
        $this->db->from(db_prefix() . 'tickets');
        $this->db->join(db_prefix() . 'tickets_status', db_prefix() . 'tickets_status.ticketstatusid = ' . db_prefix() . 'tickets.status', 'left');
        if (is_numeric($userid)) {
            $this->db->where(db_prefix() . 'tickets.userid', $userid);
        } else {
            $this->db->where(db_prefix() . 'tickets.userid', get_client_user_id());
        }
        $this->db->limit($limit);
        $this->db->where(db_prefix() . 'tickets.merged_ticket_id IS NULL', null, false);

        return $this->db->get()->result_array();
    }

    /**
     * Delete ticket from database and all connections
     * @param  mixed $ticketid ticketid
     * @return boolean
     */
    public function delete($ticketid)
    {
        $affectedRows = 0;
        hooks()->do_action('before_ticket_deleted', $ticketid);
        // final delete ticket
        $this->db->where('ticketid', $ticketid);
        $this->db->delete(db_prefix() . 'tickets');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;

            $this->db->where('merged_ticket_id', $ticketid);
            $this->db->set('merged_ticket_id', null);
            $this->db->update(db_prefix() . 'tickets');

            $this->db->where('ticketid', $ticketid);
            $attachments = $this->db->get(db_prefix() . 'ticket_attachments')->result_array();
            if (count($attachments) > 0) {
                if (is_dir(get_upload_path_by_type('ticket') . $ticketid)) {
                    if (delete_dir(get_upload_path_by_type('ticket') . $ticketid)) {
                        foreach ($attachments as $attachment) {
                            $this->db->where('id', $attachment['id']);
                            $this->db->delete(db_prefix() . 'ticket_attachments');
                            if ($this->db->affected_rows() > 0) {
                                $affectedRows++;
                            }
                        }
                    }
                }
            }

            $this->db->where('relid', $ticketid);
            $this->db->where('fieldto', 'tickets');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            // Delete replies
            $this->db->where('ticketid', $ticketid);
            $this->db->delete(db_prefix() . 'ticket_replies');

            $this->db->where('rel_id', $ticketid);
            $this->db->where('rel_type', 'ticket');
            $this->db->delete(db_prefix() . 'notes');

            $this->db->where('rel_id', $ticketid);
            $this->db->where('rel_type', 'ticket');
            $this->db->delete(db_prefix() . 'taggables');

            $this->db->where('rel_type', 'ticket');
            $this->db->where('rel_id', $ticketid);
            $this->db->delete(db_prefix() . 'reminders');

            // Get related tasks
            $this->db->where('rel_type', 'ticket');
            $this->db->where('rel_id', $ticketid);
            $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id']);
            }
        }
        if ($affectedRows > 0) {
            log_activity('Ticket Deleted [ID: ' . $ticketid . ']');

            hooks()->do_action('after_ticket_deleted', $ticketid);

            return true;
        }

        return false;
    }

    /**
     * Update ticket data / admin use
     * @param  mixed $data ticket $_POST data
     * @return boolean
     */
    public function update_single_ticket_settings($data)
    {
        if (!isset($data['ticketid']) || !is_numeric($data['ticketid'])) {
            return false;
        }
        $affectedRows = 0;
        $data         = hooks()->apply_filters('before_ticket_settings_updated', $data);

        $ticketBeforeUpdate = $this->get_ticket_by_id($data['ticketid']);

        if (isset($data['merge_ticket_ids'])) {
            $tickets = explode(',', $data['merge_ticket_ids']);
            if ($this->merge($data['ticketid'], $ticketBeforeUpdate->status, $tickets)) {
                $affectedRows++;
            }
            unset($data['merge_ticket_ids']);
        }

        if (isset($data['custom_fields']) && count($data['custom_fields']) > 0) {
            if (handle_custom_fields_post($data['ticketid'], $data['custom_fields'])) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }

        if (handle_tags_save($tags, $data['ticketid'], 'ticket')) {
            $affectedRows++;
        }

        if (isset($data['priority']) && $data['priority'] == '' || !isset($data['priority'])) {
            $data['priority'] = 0;
        }

        // Normalize assigned; if not provided, keep original assignment
        if (isset($data['assigned'])) {
            if ($data['assigned'] === '' || $data['assigned'] === null) {
                $data['assigned'] = 0;
            } else {
                $data['assigned'] = (int) $data['assigned'];
            }
        } else {
            $data['assigned'] = (int) $ticketBeforeUpdate->assigned;
        }

        if (isset($data['project_id']) && $data['project_id'] == '') {
            $data['project_id'] = 0;
        }

        if (isset($data['contactid']) && $data['contactid'] != '') {
            $data['name']  = null;
            $data['email'] = null;
        }

        // Normalize custom additions
        if (isset($data['divisionid'])) {
            if ($data['divisionid'] === '' || !is_numeric($data['divisionid'])) {
                $data['divisionid'] = null;
            } else {
                $data['divisionid'] = (int) $data['divisionid'];
            }
        }
        if (isset($data['sub_department'])) {
            if ($data['sub_department'] === '' || !is_numeric($data['sub_department'])) {
                $data['sub_department'] = null;
            } else {
                $data['sub_department'] = (int) $data['sub_department'];
            }
        }
        if (isset($data['department'])) {
            if ($data['department'] === '' || !is_numeric($data['department'])) {
                $data['department'] = 0;
            } else {
                $data['department'] = (int) $data['department'];
            }
        }

        // Only update columns that actually exist to avoid SQL errors
        $ticketId   = (int) $data['ticketid'];
        $table      = db_prefix() . 'tickets';
        $fields     = array_flip($this->db->list_fields($table));
        $updateData = [];
        foreach ($data as $k => $v) {
            if ($k === 'ticketid') { continue; }
            if (isset($fields[$k])) {
                $updateData[$k] = $v;
            }
        }

        $affected = 0;
        if (!empty($updateData)) {
            $this->db->where('ticketid', $ticketId);
            $this->db->update($table, $updateData);
            $affected = $this->db->affected_rows();
        }

        // If nothing was reported changed but critical values differ, force an explicit update
        if ($affected === 0 && !empty($updateData)) {
            $forceData = [];
            if (isset($updateData['assigned']) && (int)$ticketBeforeUpdate->assigned !== (int)$updateData['assigned']) {
                $forceData['assigned'] = (int)$updateData['assigned'];
            }
            if (isset($updateData['sub_department'])) {
                $prevSub = isset($ticketBeforeUpdate->sub_department) ? (int)$ticketBeforeUpdate->sub_department : null;
                $newSub  = $updateData['sub_department'] === null ? null : (int)$updateData['sub_department'];
                if ($prevSub !== $newSub) {
                    $forceData['sub_department'] = $updateData['sub_department'];
                }
            }
            if (!empty($forceData)) {
                $this->db->where('ticketid', $ticketId)->update($table, $forceData);
                $affected = $this->db->affected_rows();
            }
        }
        if ($affected > 0) {
            hooks()->do_action(
                'ticket_settings_updated',
                [
                    'ticket_id'       => $ticketId,
                    'original_ticket' => $ticketBeforeUpdate,
                    'data'            => $updateData,
                ]
            );
            $affectedRows++;
        }
        // Treat no-change updates as successful, to avoid UI showing failure when fields remain the same
        $noChangeSuccess = ($affected === 0);

        $sendAssignedEmail = false;

        $current_assigned = $ticketBeforeUpdate->assigned;
        if ($current_assigned != 0) {
            if ($current_assigned != $data['assigned']) {
                if ($data['assigned'] != 0 && $data['assigned'] != get_staff_user_id()) {
                    $sendAssignedEmail = true;
                    $notified          = add_notification([
                        'description'     => 'not_ticket_reassigned_to_you',
                        'touserid'        => $data['assigned'],
                        'fromcompany'     => 1,
                        'fromuserid'      => 0,
                        'link'            => 'tickets/ticket/' . $data['ticketid'],
                        'additional_data' => serialize([
                            $data['subject'],
                        ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$data['assigned']]);
                    }
                }
            }
        } else {
            if ($data['assigned'] != 0 && $data['assigned'] != get_staff_user_id()) {
                $sendAssignedEmail = true;
                $notified          = add_notification([
                    'description'     => 'not_ticket_assigned_to_you',
                    'touserid'        => $data['assigned'],
                    'fromcompany'     => 1,
                    'fromuserid'      => 0,
                    'link'            => 'tickets/ticket/' . $data['ticketid'],
                    'additional_data' => serialize([
                        $data['subject'],
                    ]),
                ]);

                if ($notified) {
                    pusher_trigger_notification([$data['assigned']]);
                }
            }
        }
        if ($sendAssignedEmail === true) {
            $this->db->where('staffid', $data['assigned']);
            $assignedStaff = $this->db->get(db_prefix() . 'staff')->row();

            send_mail_template('ticket_assigned_to_staff', $assignedStaff->email, $data['assigned'], $data['ticketid'], $data['userid'], $data['contactid']);


        }
        if ($affectedRows > 0 || $noChangeSuccess) {
            // Compute only changed fields for logging
            $changes = [];
            $fieldsToCompare = ['assigned', 'sub_department', 'divisionid', 'department', 'priority', 'service', 'project_id', 'contactid', 'subject', 'name', 'email', 'approx_resolution_time'];
            foreach ($fieldsToCompare as $field) {
                $oldValue = (isset($ticketBeforeUpdate->{$field}) ? $ticketBeforeUpdate->{$field} : null);
                $newValue = (isset($updateData[$field]) ? $updateData[$field] : $oldValue);
                if ($oldValue !== $newValue) {
                    $changes[$field] = ['old_value' => $oldValue, 'new_value' => $newValue];
                }
            }
            if (!empty($changes)) {
                add_ticket_log($data['ticketid'], 'ticket_settings_updated', $changes);
            }
            log_activity('Ticket Updated [ID: ' . $data['ticketid'] . ']');

            return true;
        }

        return false;
    }

    // --- Reassignment Approval Workflow ---
    private function ensure_reassign_table()
    {
        $table = db_prefix() . 'ticket_reassignments';
        if (!$this->db->table_exists($table)) {
            // Create table on the fly if missing (align with migrations)
            $this->db->query('CREATE TABLE IF NOT EXISTS `'.$table.'` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `ticketid` INT(11) NOT NULL,
                    `divisionid` INT(11) NULL,
                    `department` INT(11) NULL,
                    `sub_department` INT(11) NULL,
                    `service` INT(11) NULL,
                    `from_assigned` INT(11) NULL,
                    `to_assigned` INT(11) NOT NULL,
                    `status` VARCHAR(20) NOT NULL DEFAULT "pending",
                    `created_by` INT(11) NOT NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `expires_at` DATETIME NULL,
                    `decision_by` INT(11) NULL,
                    `decision_at` DATETIME NULL,
                    `decision_remarks` TEXT NULL,
                    PRIMARY KEY (`id`),
                    KEY `ticketid` (`ticketid`),
                    KEY `to_assigned` (`to_assigned`),
                    KEY `status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        } else {
            // Check for missing columns and add them if needed
            $columns = array_map('strtolower', $this->db->list_fields($table));

            $required = [
                'expires_at' => 'ALTER TABLE `'.$table.'` ADD `expires_at` DATETIME NULL AFTER `created_at`',
                'decision_by' => 'ALTER TABLE `'.$table.'` ADD `decision_by` INT(11) NULL AFTER `expires_at`',
                'decision_at' => 'ALTER TABLE `'.$table.'` ADD `decision_at` DATETIME NULL AFTER `decision_by`',
                'decision_remarks' => 'ALTER TABLE `'.$table.'` ADD `decision_remarks` TEXT NULL AFTER `decision_at`',
            ];

            foreach ($required as $column => $sql) {
                if (! in_array($column, $columns, true)) {
                    $this->db->query($sql);
                }
            }
        }
        return true;
    }

    /**
     * Check if a close request has expired
     */
    private function is_close_request_expired($request)
    {
        if (!$request || empty($request->expires_at) || $request->expires_at === '0000-00-00 00:00:00') {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        return $now > $request->expires_at;
    }

    /**
     * Auto-approve an expired close request
     */
    private function auto_approve_expired_close_request($request)
    {
        if (!$request) {
            return;
        }

        $this->db
            ->where('id', (int) $request->id)
            ->update(db_prefix() . 'ticket_close_requests', [
                'status' => 'approved',
                'responded_by' => 0, // System user
                'responded_at' => date('Y-m-d H:i:s'),
            ]);

        // Log the auto-approval
        $this->add_ticket_log((int) $request->ticketid, 'close_request_auto_approved', [
            'reason' => 'Auto-approved after expiry',
            'expires_at' => $request->expires_at,
        ]);

        // Update the ticket status to close it
        $targetCloseStatus = isset($request->target_status) && (int) $request->target_status > 0
            ? (int) $request->target_status
            : $this->get_close_status_id();
        if ($targetCloseStatus) {
            $this->db
                ->where('ticketid', (int) $request->ticketid)
                ->update(db_prefix() . 'tickets', ['status' => $targetCloseStatus]);
        }
    }

    /**
     * Get the ID of the first available close status
     */
    private function get_close_status_id()
    {
        $statuses = $this->get_ticket_status();
        foreach ($statuses as $status) {
            $name = isset($status['name']) ? $status['name'] : (isset($status->name) ? $status->name : '');
            $normalized = function_exists('mb_strtolower') ? mb_strtolower($name) : strtolower($name);
            if (strpos($normalized, 'close') !== false) {
                return isset($status['ticketstatusid']) ? (int) $status['ticketstatusid'] : (int) $status->ticketstatusid;
            }
        }
        return null; // No close status found
    }

    // Duplicate function removed - ensure_reassign_table() was defined earlier

    public function approve_reassign_request($requestId, $approverId)
    {
        $this->ensure_reassign_table();
        $req = $this->db->where('id', (int)$requestId)->get(db_prefix().'ticket_reassignments')->row();
        if (!$req) { return 'Request not found'; }
        if ($req->status !== 'pending') { return 'Request is not pending'; }
        if ((int)$req->to_assigned !== (int)$approverId && !is_admin()) { return 'Not allowed'; }

        // Apply updates to ticket
        $update = [ 'assigned' => (int)$req->to_assigned ];
        if (!is_null($req->divisionid))     { $update['divisionid'] = (int)$req->divisionid; }
        if (!is_null($req->department))     { $update['department'] = (int)$req->department; }
        if (!is_null($req->sub_department)) { $update['sub_department'] = (int)$req->sub_department; }
        if (!is_null($req->service))        { $update['service'] = (int)$req->service; }

        // Only update columns that exist
        $fields = array_flip($this->db->list_fields(db_prefix().'tickets'));
        $filtered = [];
        foreach ($update as $k=>$v){ if (isset($fields[$k])) { $filtered[$k] = $v; } }
        if (!empty($filtered)) {
            $this->db->where('ticketid', (int)$req->ticketid)->update(db_prefix().'tickets', $filtered);
        }
        // Reset handlers when reassignment is approved
        $this->set_ticket_handlers((int)$req->ticketid, []);
        // Mark request approved
        $this->db->where('id', (int)$requestId)->update(db_prefix().'ticket_reassignments', [ 'status' => 'approved', 'decision_by' => (int)$approverId, 'decision_at' => date('Y-m-d H:i:s') ]);

        add_ticket_log((int)$req->ticketid, 'ticket_reassigned', [ 'from' => $req->from_assigned, 'to' => $req->to_assigned ]);
        return true;
    }

    public function reject_reassign_request($requestId, $approverId, $remarks = '')
    {
        $this->ensure_reassign_table();
        $req = $this->db->where('id', (int)$requestId)->get(db_prefix().'ticket_reassignments')->row();
        if (!$req) { return 'Request not found'; }
        if ($req->status !== 'pending') { return 'Request is not pending'; }
        if ((int)$req->to_assigned !== (int)$approverId && !is_admin()) { return 'Not allowed'; }
        $this->db->where('id', (int)$requestId)->update(db_prefix().'ticket_reassignments', [ 'status' => 'rejected', 'decision_remarks' => $remarks, 'decision_by' => (int)$approverId, 'decision_at' => date('Y-m-d H:i:s') ]);
        return true;
    }

    public function get_pending_reassign($ticketId)
    {
        $table = db_prefix() . 'ticket_reassignments';
        if (!$this->db->table_exists($table)) {
            return null;
        }

        $pending = $this->db->where(['ticketid' => (int) $ticketId, 'status' => 'pending'])
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get($table)
            ->row();

        if (!$pending) {
            return null;
        }

        $limitYears = (int) get_option('ticket_reassign_pending_expiry_years');
        if ($limitYears < 0) {
            $limitYears = 0;
        }

        if ($limitYears <= 0) {
            return $pending;
        }

        $expectedExpiry = $this->calculate_reassign_expiry($pending->created_at ?? null, $limitYears);
        if ($expectedExpiry && ($pending->expires_at ?? null) !== $expectedExpiry) {
            $this->db->where('id', (int) $pending->id)->update($table, ['expires_at' => $expectedExpiry]);
            $pending->expires_at = $expectedExpiry;
        }

        if (!empty($pending->expires_at) && $pending->expires_at !== '0000-00-00 00:00:00') {
            $now = date('Y-m-d H:i:s');
            if ($now > $pending->expires_at) {
                $this->db->where('id', (int) $pending->id)->update($table, [
                    'status'          => 'expired',
                    'decision_at'     => $now,
                    'decision_remarks'=> 'Reassignment request automatically expired after exceeding the allowed timeframe.',
                ]);
                return null;
            }
        }

        return $pending;
    }

    private function calculate_reassign_expiry($createdAt, $limitYears)
    {
        $limitYears = (int) $limitYears;
        if ($limitYears <= 0) {
            return null;
        }

        if (empty($createdAt) || $createdAt === '0000-00-00 00:00:00') {
            $createdAt = date('Y-m-d H:i:s');
        }

        try {
            $date = new DateTime($createdAt);
        } catch (\Exception $e) {
            return null;
        }

        $date->modify('+' . $limitYears . ' year' . ($limitYears === 1 ? '' : 's'));

        return $date->format('Y-m-d H:i:s');
    }

    public function create_reassign_request($data)
    {
        $this->ensure_reassign_table();

        $ticketid = (int) ($data['ticketid'] ?? 0);
        $to_assigned = (int) ($data['to_assigned'] ?? 0);
        $from_assigned = (int) ($data['from_assigned'] ?? 0);
        $divisionid = isset($data['divisionid']) && !empty($data['divisionid']) ? (int) $data['divisionid'] : null;
        $department = isset($data['department']) && !empty($data['department']) ? (int) $data['department'] : null;
        $sub_department = isset($data['sub_department']) && !empty($data['sub_department']) ? (int) $data['sub_department'] : null;
        $service = isset($data['service']) && !empty($data['service']) ? (int) $data['service'] : null;
        $application_id = isset($data['application_id']) && !empty($data['application_id']) ? (int) $data['application_id'] : null;

        // Debug logging
        log_message('debug', 'create_reassign_request: ticketid=' . $ticketid . ', to_assigned=' . $to_assigned . ', from_assigned=' . $from_assigned);

        // Validate ticket exists
        $ticket = $this->get_ticket_by_id($ticketid);
        if (!$ticket) {
            log_message('error', 'create_reassign_request: Ticket not found - ticketid=' . $ticketid);
            return 'Ticket not found';
        }

        // Validate target user exists and is active
        if ($to_assigned > 0) {
            $this->db->where('staffid', $to_assigned);
            $this->db->where('active', 1);
            if (!$this->db->get(db_prefix() . 'staff')->row()) {
                log_message('error', 'create_reassign_request: Target assignee not found or inactive - staffid=' . $to_assigned);
                return 'Target assignee not found or inactive';
            }
        }

        // Check for existing pending reassignment for this ticket
        $existing = $this->get_pending_reassign($ticketid);
        if ($existing) {
            log_message('debug', 'create_reassign_request: Existing pending reassignment found - ticketid=' . $ticketid);
            return 'A reassignment request is already pending for this ticket';
        }

        $created_by = get_staff_user_id();
        $created_at = date('Y-m-d H:i:s');

        // Calculate expiry if configured
        $expiry_days = (int) get_option('ticket_reassign_pending_expiry_years');
        if ($expiry_days < 0) {
            $expiry_days = 0;
        }
        $expires_at = null;
        if ($expiry_days > 0) {
            $expires_at = $this->calculate_reassign_expiry($created_at, $expiry_days);
        }

        $insert_data = [
            'ticketid' => $ticketid,
            'from_assigned' => $from_assigned,
            'to_assigned' => $to_assigned,
            'divisionid' => $divisionid,
            'department' => $department,
            'sub_department' => $sub_department,
            'service' => $service,
            'status' => 'pending',
            'created_by' => $created_by,
            'created_at' => $created_at,
            'expires_at' => $expires_at,
        ];

        $this->db->insert(db_prefix() . 'ticket_reassignments', $insert_data);

        if ($this->db->affected_rows() > 0) {
            $request_id = $this->db->insert_id();

            // Send SMS notification to target assignee about pending reassignment request
            $this->db->where('staffid', $to_assigned);
            $assignedStaff = $this->db->get(db_prefix() . 'staff')->row();

            if ($assignedStaff && !empty($assignedStaff->phonenumber)) {
                $this->load->library('sms/App_sms');

                // Get the requester's name
                $requester = $this->staff_model->get($created_by);
                $requester_name = $requester ? ($requester->firstname . ' ' . $requester->lastname) : '';

                // Get ticket details
                $ticket = $this->get_ticket_by_id($ticketid);

                $merge_fields = [
                    '{ticket_id}' => $ticketid,
                    '{ticket_number}' => $ticket->ticket_number ?? $ticketid,
                    '{requested_by_name}' => $requester_name,
                    '{staff_firstname}' => $assignedStaff->firstname,
                    '{staff_lastname}' => $assignedStaff->lastname,
                    '{company_name}' => get_option('companyname')
                ];

                $sms_sent = $this->app_sms->trigger(SMS_TRIGGER_TICKET_REASSIGNMENT_REQUEST, $assignedStaff->phonenumber, $merge_fields);

                // Log SMS notification
                $this->add_ticket_log($ticketid, 'sms_notification_sent', [
                    'trigger' => SMS_TRIGGER_TICKET_REASSIGNMENT_REQUEST,
                    'recipient' => $assignedStaff->phonenumber,
                    'recipient_type' => _l('sms_recipient_type_staff', 'custom_lang'),
                    'sent_by' => $created_by,
                    'sms_sent' => $sms_sent ? _l('yes', 'custom_lang') : _l('no', 'custom_lang'),
                    'reassignment_pending' => true,
                ]);
            }

            // Log the creation
            $this->add_ticket_log($ticketid, 'reassign_request_created', [
                'request_id' => $request_id,
                'from_assigned' => $from_assigned,
                'to_assigned' => $to_assigned,
                'divisionid' => $divisionid,
                'department' => $department,
                'sub_department' => $sub_department,
                'service' => $service,
                'application_id' => $application_id,
                'expires_at' => $expires_at,
            ]);

            return true;
        }

        return 'Failed to create reassignment request';
    }

    public function get_reassign_request($requestId)
    {
        $this->ensure_reassign_table();
        return $this->db->where('id', (int)$requestId)->get(db_prefix().'ticket_reassignments')->row();
    }
    /**
     * C<ha></ha>nge ticket status
     * @param  mixed $id     ticketid
     * @param  mixed $status status id
     * @return array
     */
    public function change_ticket_status($id, $status)
    {
        // Normalize inputs
        $id     = (int) $id;
        $status = (int) $status;

        // Validate ticket exists
        $ticket     = $this->get_ticket_by_id($id);
        $old_status = $ticket ? (int) $ticket->status : null;
        if (!$ticket) {
            return [
                'alert'   => 'danger',
                'message' => _l('ticket_not_found'),
            ];
        }

        // Validate status exists (avoid FK/invalid value issues)
        $statusExists = $this->db
            ->where('ticketstatusid', $status)
            ->count_all_results(db_prefix() . 'tickets_status') > 0;
        if (!$statusExists) {
            return [
                'alert'   => 'danger',
                'message' => _l('ticket_status_changed_fail'),
            ];
        }

        $currentStaff = (int) get_staff_user_id();
        $isAssignee   = $ticket && isset($ticket->assigned) && (int) $ticket->assigned === $currentStaff;
        $isHandler    = $ticket ? $this->is_ticket_handler((int)$ticket->ticketid, $currentStaff) : false;
        $creatorId    = $ticket ? $this->get_ticket_creator_staff_id($ticket) : 0;

        // Require approval for handlers and assignees (except admins and creators)
        $requiresApproval = $ticket
            && $this->is_close_status($status)
            && ($isAssignee || $isHandler)
            && $creatorId > 0
            && $creatorId !== $currentStaff
            && ! is_admin();

        if ($requiresApproval) {
            $approvalOutcome = $this->create_close_approval_request($ticket, $currentStaff, $old_status, $status, $creatorId);

            if (is_array($approvalOutcome) && ($approvalOutcome['success'] ?? false)) {
                return [
                    'alert'   => $approvalOutcome['alert'] ?? 'success',
                    'message' => $approvalOutcome['message'] ?? _l('ticket_close_request_sent'),
                ];
            }

            return [
                'alert'   => $approvalOutcome['alert'] ?? 'warning',
                'message' => $approvalOutcome['message'] ?? _l('ticket_close_request_failed'),
            ];
        }

        // Perform update with db_debug off to prevent 500s
        $prevDebug           = $this->db->db_debug;
        $this->db->db_debug  = false;
        $this->db->where('ticketid', $id);
        $this->db->update(db_prefix() . 'tickets', ['status' => $status]);
        $error = $this->db->error();
        $this->db->db_debug = $prevDebug;

        $alert   = 'warning';
        $message = _l('ticket_status_changed_fail');

        if (isset($error['code']) && (int) $error['code'] !== 0) {
            log_message('error', 'Tickets_model::change_ticket_status DB error [' . $error['code'] . ']: ' . ($error['message'] ?? ''));
        } elseif ($this->db->affected_rows() > 0 || (int) $old_status === $status) {
            // Consider no-op as success for UX (status already set)
            // Get status names for logging
            $old_status_name = '';
            $new_status_name = '';
            if ($old_status) {
                $old_status_data = $this->get_ticket_status($old_status);
                $old_status_name = $old_status_data ? $old_status_data->name : 'Unknown';
            }
            if ($status) {
                $new_status_data = $this->get_ticket_status($status);
                $new_status_name = $new_status_data ? $new_status_data->name : 'Unknown';
            }

            $this->add_ticket_log($id, 'status_change', [
                'old_status' => $old_status_name,
                'new_status' => $new_status_name,
            ]);
            $alert   = 'success';
            $message = _l('ticket_status_changed_successfully');

            if ($ticket && $this->is_close_status($status)) {
                $this->notify_ticket_participants_on_closure($ticket, $currentStaff);
            }

            // Send SMS for waiting for close status
            if ($ticket && $this->is_waiting_for_close_status($status) && !empty($ticket->assigned) && $ticket->assigned != 0) {
                $this->load->library('sms/App_sms');
                $this->load->model('staff_model');
                $marked_by = $this->staff_model->get($currentStaff);
                $assignee = $this->staff_model->get($ticket->assigned);

                if ($assignee && !empty($assignee->phonenumber)) {
                    $merge_fields = [
                        '{ticket_id}' => $id,
                        '{ticket_number}' => $ticket->ticket_number ?? $id,
                        '{ticket_subject}' => $ticket->subject ?? '',
                        '{marked_by_name}' => $marked_by ? trim(($marked_by->firstname ?? '') . ' ' . ($marked_by->lastname ?? '')) : '',
                        '{staff_firstname}' => $assignee->firstname ?? '',
                        '{staff_lastname}' => $assignee->lastname ?? '',
                        '{company_name}' => get_option('companyname'),
                    ];

                    $sms_sent = $this->app_sms->trigger(SMS_TRIGGER_TICKET_WAITING_FOR_CLOSE, $assignee->phonenumber, $merge_fields);

                    $this->add_ticket_log($id, 'sms_notification_sent', [
                        'trigger' => _l('sms_trigger_ticket_waiting_for_close', 'custom_lang'),
                        'recipient' => $assignee->phonenumber,
                        'recipient_type' => _l('sms_recipient_type_staff', 'custom_lang'),
                        'sent_by' => $currentStaff,
                        'sms_sent' => $sms_sent ? _l('yes', 'custom_lang') : _l('no', 'custom_lang'),
                    ]);
                }
            }

            hooks()->do_action('after_ticket_status_changed', [
                'id'     => $id,
                'status' => $status,
            ]);
        }
        return [
            'alert'   => $alert,
            'message' => $message,
        ];
    }


    private function ensure_close_request_table()
    {
        $table = db_prefix() . 'ticket_close_requests';
        if (! $this->db->table_exists($table)) {
            $this->db->query('CREATE TABLE IF NOT EXISTS `'.$table.'` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `ticketid` INT(11) NOT NULL,
                    `requested_by` INT(11) NOT NULL,
                    `approver_id` INT(11) DEFAULT NULL,
                    `previous_status` INT(11) DEFAULT NULL,
                    `target_status` INT(11) DEFAULT NULL,
                    `status` VARCHAR(30) NOT NULL DEFAULT "pending",
                    `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `expires_at` DATETIME DEFAULT NULL,
                    `responded_by` INT(11) DEFAULT NULL,
                    `responded_at` DATETIME DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `ticketid` (`ticketid`),
                    KEY `status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        } else {
            $columns = array_map('strtolower', $this->db->list_fields($table));

            $required = [
                'approver_id'   => 'ALTER TABLE `'.$table.'` ADD `approver_id` INT(11) NULL AFTER `requested_by`',
                'previous_status' => 'ALTER TABLE `'.$table.'` ADD `previous_status` INT(11) NULL AFTER `approver_id`',
                'target_status' => 'ALTER TABLE `'.$table.'` ADD `target_status` INT(11) NULL AFTER `previous_status`',
                'requested_at'  => 'ALTER TABLE `'.$table.'` ADD `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `status`',
                'expires_at'    => 'ALTER TABLE `'.$table.'` ADD `expires_at` DATETIME NULL AFTER `requested_at`',
                'responded_by'  => 'ALTER TABLE `'.$table.'` ADD `responded_by` INT(11) NULL AFTER `expires_at`',
                'responded_at'  => 'ALTER TABLE `'.$table.'` ADD `responded_at` DATETIME NULL AFTER `responded_by`',
            ];

            foreach ($required as $column => $sql) {
                if (! in_array($column, $columns, true)) {
                    $this->db->query($sql);
                }
            }
        }

        return true;
    }

    public function create_close_approval_request($ticket, $requestedBy, $previousStatus, $targetStatus, $approverId)
    {
        $this->ensure_close_request_table();

        $table = db_prefix() . 'ticket_close_requests';
        $existing = $this->db
            ->where(['ticketid' => (int) $ticket->ticketid, 'status' => 'pending'])
            ->order_by('requested_at', 'DESC')
            ->get($table)
            ->row();

        if ($existing) {
            return [
                'success' => true,
                'created' => false,
                'alert'   => 'info',
                'message' => _l('ticket_close_request_already_pending'),
                'request_id' => (int) $existing->id,
            ];
        }

        // Calculate expiry time if enabled
        $now = date('Y-m-d H:i:s');
        $expiryHours = (int) get_option('ticket_close_pending_expiry_hours');
        if ($expiryHours < 0) {
            $expiryHours = 0;
        }
        $expiry = null;
        if ($expiryHours > 0) {
            $expiry = date('Y-m-d H:i:s', strtotime($now . ' +' . $expiryHours . ' hours'));
        }

        $insert = [
            'ticketid'        => (int) $ticket->ticketid,
            'requested_by'    => (int) $requestedBy,
            'approver_id'     => $approverId ? (int) $approverId : null,
            'previous_status' => $previousStatus !== null ? (int) $previousStatus : null,
            'target_status'  => $targetStatus !== null ? (int) $targetStatus : null,
            'status'          => 'pending',
            'requested_at'    => $now,
            'expires_at'      => $expiry,
        ];

        $this->db->insert($table, $insert);

        if ($this->db->affected_rows() > 0) {
            $requestId = $this->db->insert_id();
            $this->add_ticket_log((int) $ticket->ticketid, 'close_request_created', [
                'requested_by'    => (int) $requestedBy,
                'previous_status' => $previousStatus,
                'target_status'   => $targetStatus,
                'approver_id'     => $approverId ? (int) $approverId : null,
                'expires_at'      => $expiry,
            ]);

            if ($approverId > 0) {
                add_notification([
                    'description'     => 'ticket_close_approval_request',
                    'touserid'        => (int) $approverId,
                    'fromcompany'     => 1,
                    'fromuserid'      => 0,
                    'link'            => 'tickets/ticket/' . (int) $ticket->ticketid,
                    'additional_data' => serialize([$ticket->subject ?? '']),
                ]);
                pusher_trigger_notification([(int) $approverId]);
            }

            return [
                'success'    => true,
                'created'    => true,
                'alert'      => 'success',
                'message'    => _l('ticket_close_request_sent'),
                'request_id' => (int) $requestId,
            ];
        }

        log_message('error', 'Failed to create ticket close approval request for ticket ID ' . (int) $ticket->ticketid);

        return [
            'success' => false,
            'created' => false,
            'alert'   => 'warning',
            'message' => _l('ticket_close_request_failed'),
        ];
    }

    public function get_pending_close_request($ticketId)
    {
        $table = db_prefix() . 'ticket_close_requests';
        if (!$this->db->table_exists($table)) {
            return null;
        }

        $pending = $this->db
            ->where(['ticketid' => (int) $ticketId, 'status' => 'pending'])
            ->order_by('requested_at', 'DESC')
            ->get($table)
            ->row();

        if (!$pending) {
            return null;
        }

        // Check if the request has expired and auto-approve if needed
        if ($this->is_close_request_expired($pending)) {
            $this->auto_approve_expired_close_request($pending);
            // Return null since it's been auto-approved and no longer pending
            return null;
        }

        return $pending;
    }

    public function resolve_close_approval_request($ticketId, $action, $staffId)
    {
        $this->ensure_close_request_table();
        $table = db_prefix() . 'ticket_close_requests';
        $request = $this->db
            ->where(['ticketid' => (int) $ticketId, 'status' => 'pending'])
            ->order_by('requested_at', 'DESC')
            ->get($table)
            ->row();

        if (!$request) {
            return _l('ticket_close_request_not_found');
        }

        $ticket = $this->get_ticket_by_id((int) $ticketId);

        $approverId = (int) ($request->approver_id ?? 0);
        if ($approverId === 0 && $ticket) {
            $approverId = $this->get_ticket_creator_staff_id($ticket);
        }
        if (!defined('ENVIRONMENT') || ENVIRONMENT !== 'production') {
            log_message('debug', 'close_request_check ticket=' . (int) $ticketId . ' request=' . (int) ($request->id ?? 0) . ' approver=' . $approverId . ' staff=' . (int) $staffId . ' is_admin=' . (is_admin() ? 'yes' : 'no'));
        }

        if (!is_admin() && $approverId > 0 && (int) $staffId !== $approverId) {
            return _l('ticket_close_request_not_authorized');
        }

        $decision = strtolower($action) === 'approve' ? 'approve' : 'reopen';

        $this->db
            ->where('id', (int) $request->id)
            ->update($table, [
                'status'       => $decision === 'approve' ? 'approved' : 'declined',
                'responded_by' => (int) $staffId,
                'responded_at' => date('Y-m-d H:i:s'),
            ]);

        $logType = $decision === 'approve' ? 'close_request_approved' : 'close_request_reopened';
        $this->add_ticket_log((int) $ticketId, $logType, [
            'requested_by' => (int) $request->requested_by,
        ]);

        $previousStatusId = $ticket ? (int) $ticket->status : null;
        $updatedStatusId  = null;

        if ($decision === 'approve') {
            $targetStatusId = isset($request->target_status) && (int) $request->target_status > 0
                ? (int) $request->target_status
                : null;

            if (!$targetStatusId && $ticket && $this->is_close_status((int) $ticket->status)) {
                $targetStatusId = (int) $ticket->status;
            }

            if (!$targetStatusId) {
                $closeFallback = $this->get_close_status_id();
                if ($closeFallback) {
                    $targetStatusId = (int) $closeFallback;
                }
            }

            if ($targetStatusId) {
                $this->db
                    ->where('ticketid', (int) $ticketId)
                    ->update(db_prefix() . 'tickets', ['status' => $targetStatusId]);

                if ($this->db->affected_rows() > 0 || $previousStatusId !== $targetStatusId) {
                    $updatedStatusId = $targetStatusId;
                }
            }
        } else {
            $targetStatusId = $request->previous_status ? (int) $request->previous_status : $this->get_default_ticket_status_id();
            if ($targetStatusId) {
                $this->db
                    ->where('ticketid', (int) $ticketId)
                    ->update(db_prefix() . 'tickets', ['status' => $targetStatusId]);

                if ($this->db->affected_rows() > 0 || $previousStatusId !== $targetStatusId) {
                    $updatedStatusId = $targetStatusId;
                }
            }
        }

        if ($updatedStatusId !== null) {
            $oldStatusName = '';
            $newStatusName = '';

            if ($previousStatusId) {
                $oldStatusData = $this->get_ticket_status($previousStatusId);
                $oldStatusName = $oldStatusData && isset($oldStatusData->name) ? $oldStatusData->name : '';
            }

            if ($updatedStatusId) {
                $newStatusData = $this->get_ticket_status($updatedStatusId);
                $newStatusName = $newStatusData && isset($newStatusData->name) ? $newStatusData->name : '';
            }

            $this->add_ticket_log((int) $ticketId, 'status_change', [
                'old_status' => $oldStatusName,
                'new_status' => $newStatusName,
            ]);
        }

        $ticket = $this->get_ticket_by_id((int) $ticketId);

        if ($ticket) {
            $event = $decision === 'approve' ? 'closed' : 'declined';
            $this->notify_ticket_participants_on_closure($ticket, (int) $staffId, $event);
        }

        if ((int) $request->requested_by > 0) {
            $description = $decision === 'approve' ? 'ticket_close_request_approved' : 'ticket_close_request_reopened';
            $notified = add_notification([
                'description' => $description,
                'touserid'    => (int) $request->requested_by,
                'fromcompany' => 1,
                'fromuserid'  => 0,
                'link'        => 'tickets/ticket/' . (int) $ticketId,
            ]);
            if ($notified) {
                pusher_trigger_notification([(int) $request->requested_by]);
            }
        }

        return true;
    }

    private function ensure_reopen_request_table()
    {
        $table = db_prefix() . 'ticket_reopen_requests';
        if (! $this->db->table_exists($table)) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `{$table}` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `ticketid` INT(11) NOT NULL,
                    `requested_by` INT(11) NOT NULL,
                    `assignee_id` INT(11) DEFAULT NULL,
                    `from_status` INT(11) DEFAULT NULL,
                    `target_status` INT(11) DEFAULT NULL,
                    `status` VARCHAR(30) NOT NULL DEFAULT 'pending',
                    `requested_at` DATETIME NOT NULL,
                    `responded_by` INT(11) DEFAULT NULL,
                    `responded_at` DATETIME DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `ticketid` (`ticketid`),
                    KEY `status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }
        return true;
    }

    public function create_reopen_request($ticket, $requestedBy)
    {
        $this->ensure_reopen_request_table();
        if (!$ticket) {
            return _l('ticket_not_found');
        }

        $table = db_prefix() . 'ticket_reopen_requests';
        $existing = $this->db
            ->where(['ticketid' => (int) $ticket->ticketid, 'status' => 'pending'])
            ->order_by('requested_at', 'DESC')
            ->get($table)
            ->row();

        if ($existing) {
            return [
                'success' => false,
                'alert'   => 'info',
                'message' => _l('ticket_reopen_request_already_pending'),
                'request_id' => (int) $existing->id,
            ];
        }

        $assigneeId   = isset($ticket->assigned) ? (int) $ticket->assigned : 0;
        $targetStatus = $this->determine_reopen_target_status($ticket);

        $insert = [
            'ticketid'      => (int) $ticket->ticketid,
            'requested_by'  => (int) $requestedBy,
            'assignee_id'   => $assigneeId > 0 ? $assigneeId : null,
            'from_status'   => isset($ticket->status) ? (int) $ticket->status : null,
            'target_status' => $targetStatus ?: null,
            'status'        => 'pending',
            'requested_at'  => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($table, $insert);

        if ($this->db->affected_rows() > 0) {
            $requestId = $this->db->insert_id();
            $this->add_ticket_log((int) $ticket->ticketid, 'reopen_request_created', [
                'requested_by' => (int) $requestedBy,
                'assignee_id'  => $assigneeId,
            ]);

            if ($assigneeId > 0) {
                $notified = add_notification([
                    'description'     => 'ticket_reopen_request_assignee',
                    'touserid'        => (int) $assigneeId,
                    'fromcompany'     => 1,
                    'fromuserid'      => 0,
                    'link'            => 'tickets/ticket/' . (int) $ticket->ticketid,
                    'additional_data' => serialize([$ticket->subject ?? '', '#' . (int) $ticket->ticketid]),
                ]);
                if ($notified) {
                    pusher_trigger_notification([(int) $assigneeId]);
                }

                // Send SMS notification to assignee for reopen request
                $this->load->library('sms/App_sms');
                $this->load->model('staff_model');

                $assignee = $this->staff_model->get($assigneeId);

                if ($assignee && !empty($assignee->phonenumber)) {
                    $merge_fields = [
                        '{ticket_id}' => (int) $ticket->ticketid,
                        '{ticket_number}' => $ticket->ticket_number ?? (int) $ticket->ticketid,
                        '{requester_name}' => $ticket->submitter ?? '',
                        '{staff_firstname}' => $assignee->firstname,
                        '{staff_lastname}' => $assignee->lastname,
                        '{company_name}' => get_option('companyname')
                    ];

                    $sms_sent = $this->app_sms->trigger(SMS_TRIGGER_TICKET_REOPEN_REQUEST, $assignee->phonenumber, $merge_fields);

                    // Log SMS notification
                    $this->add_ticket_log((int) $ticket->ticketid, 'sms_notification_sent', [
                        'trigger' => _l('sms_trigger_ticket_reopen_request', 'custom_lang'),
                        'recipient' => $assignee->phonenumber,
                        'recipient_type' => _l('sms_recipient_type_staff', 'custom_lang'),
                        'sent_by' => (int) $requestedBy,
                        'sms_sent' => $sms_sent ? _l('yes', 'custom_lang') : _l('no', 'custom_lang'),
                    ]);
                }
            }

            return [
                'success'    => true,
                'alert'      => 'success',
                'message'    => _l('ticket_reopen_request_sent'),
                'request_id' => (int) $requestId,
            ];
        }

        log_message('error', 'Failed to create ticket reopen request for ticket ID ' . (int) $ticket->ticketid);

        return [
            'success' => false,
            'alert'   => 'danger',
            'message' => _l('ticket_reopen_request_failed'),
        ];
    }

    public function get_pending_reopen_request($ticketId)
    {
        $table = db_prefix() . 'ticket_reopen_requests';
        if (!$this->db->table_exists($table)) {
            return null;
        }
        return $this->db
            ->where(['ticketid' => (int) $ticketId, 'status' => 'pending'])
            ->order_by('requested_at', 'DESC')
            ->get($table)
            ->row();
    }

    public function resolve_reopen_request($ticketId, $decision, $staffId)
    {
        $this->ensure_reopen_request_table();
        $table = db_prefix() . 'ticket_reopen_requests';
        $request = $this->db
            ->where(['ticketid' => (int) $ticketId, 'status' => 'pending'])
            ->order_by('requested_at', 'DESC')
            ->get($table)
            ->row();

        if (!$request) {
            return _l('ticket_reopen_request_not_found');
        }

        $ticket = $this->get_ticket_by_id((int) $ticketId);
        if (!$ticket) {
            return _l('ticket_not_found');
        }

        $assigneeId = isset($ticket->assigned) ? (int) $ticket->assigned : 0;
        if (!is_admin() && $assigneeId !== (int) $staffId) {
            return _l('ticket_reopen_request_not_authorized');
        }

        $decisionKey = $decision === 'approve' ? 'approved' : 'declined';

        $this->db
            ->where('id', (int) $request->id)
            ->update($table, [
                'status'       => $decisionKey,
                'responded_by' => (int) $staffId,
                'responded_at' => date('Y-m-d H:i:s'),
            ]);

        $logType = $decision === 'approve' ? 'reopen_request_approved' : 'reopen_request_declined';
        $this->add_ticket_log((int) $ticketId, $logType, [
            'requested_by' => (int) $request->requested_by,
        ]);

        if ($decision === 'approve') {
            $targetStatus = $request->target_status ? (int) $request->target_status : $this->get_default_ticket_status_id();
            if ($targetStatus) {
                $this->db
                    ->where('ticketid', (int) $ticketId)
                    ->update(db_prefix() . 'tickets', ['status' => $targetStatus]);
            }
            $ticket = $this->get_ticket_by_id((int) $ticketId);
            if ($ticket) {
                $this->notify_ticket_participants_on_closure($ticket, (int) $staffId, 'reopened');
            }
        }

        if ((int) $request->requested_by > 0) {
            $description = $decision === 'approve' ? 'ticket_reopen_request_approved' : 'ticket_reopen_request_declined';
            $notified = add_notification([
                'description' => $description,
                'touserid'    => (int) $request->requested_by,
                'fromcompany' => 1,
                'fromuserid'  => 0,
                'link'        => 'tickets/ticket/' . (int) $ticketId,
            ]);
            if ($notified) {
                pusher_trigger_notification([(int) $request->requested_by]);
            }
        }

        return true;
    }

    private function determine_reopen_target_status($ticket)
    {
        $target = null;
        $closeTable = db_prefix() . 'ticket_close_requests';
        if ($this->db->table_exists($closeTable)) {
            $row = $this->db
                ->where('ticketid', (int) $ticket->ticketid)
                ->where('status', 'approved')
                ->order_by('responded_at', 'DESC')
                ->limit(1)
                ->get($closeTable)
                ->row();
            if ($row && isset($row->previous_status) && (int) $row->previous_status > 0) {
                $target = (int) $row->previous_status;
            }
        }
        if (!$target) {
            $target = $this->get_default_ticket_status_id();
        }
        return $target;
    }

    private function get_default_ticket_status_id()
    {
        $status = $this->db
            ->where('isdefault', 1)
            ->order_by('statusorder', 'asc')
            ->get(db_prefix() . 'tickets_status')
            ->row();
        if ($status) {
            return (int) $status->ticketstatusid;
        }
        $fallback = $this->db
            ->order_by('statusorder', 'asc')
            ->limit(1)
            ->get(db_prefix() . 'tickets_status')
            ->row();
        return $fallback ? (int) $fallback->ticketstatusid : null;
    }

    private function notify_ticket_participants_on_closure($ticket, $triggeredBy, $event = 'closed')
    {
        if (! $ticket) {
            return;
        }

        $participants = $this->gather_ticket_participant_staff_ids($ticket);
        if (empty($participants)) {
            return;
        }

        $triggeredBy = (int) $triggeredBy;
        $notifiedIds = [];
        if ($event === 'declined') {
            $eventKey = 'ticket_close_request_declined_broadcast';
        } elseif ($event === 'reopened') {
            $eventKey = 'ticket_reopened_broadcast';
        } else {
            $eventKey = 'ticket_closed_broadcast';
        }
        foreach ($participants as $staffId) {
            $staffId = (int) $staffId;
            if ($staffId === 0 || $staffId === $triggeredBy) {
                continue;
            }

            $notified = add_notification([
                'description'     => $eventKey,
                'touserid'        => $staffId,
                'fromcompany'     => 1,
                'fromuserid'      => 0,
                'link'            => 'tickets/ticket/' . (int) $ticket->ticketid,
                'additional_data' => serialize([
                    $ticket->subject ?? '',
                    '#' . (int) $ticket->ticketid,
                ]),
            ]);

            if ($notified) {
                $notifiedIds[] = $staffId;
            }
        }

        if (! empty($notifiedIds)) {
            pusher_trigger_notification($notifiedIds);
        }
    }

    private function gather_ticket_participant_staff_ids($ticket)
    {
        if (! $ticket) {
            return [];
        }

        $staffIds = [];
        if (isset($ticket->assigned) && (int) $ticket->assigned > 0) {
            $staffIds[] = (int) $ticket->assigned;
        }
        if (isset($ticket->admin) && (int) $ticket->admin > 0) {
            $staffIds[] = (int) $ticket->admin;
        }

        $handlers = $this->get_ticket_handlers((int) $ticket->ticketid, true);
        if (is_array($handlers) && ! empty($handlers)) {
            foreach ($handlers as $handler) {
                if (is_array($handler) && isset($handler['staffid'])) {
                    $staffIds[] = (int) $handler['staffid'];
                } elseif (is_object($handler) && isset($handler->staffid)) {
                    $staffIds[] = (int) $handler->staffid;
                }
            }
        }

        $staffIds = array_values(array_unique(array_filter($staffIds)));

        return $staffIds;
    }

    private function ticket_has_creator_column()
    {
        if ($this->ticketHasCreatorColumn === null) {
            try {
                $fields = array_map('strtolower', $this->db->list_fields(db_prefix() . 'tickets'));
                $this->ticketHasCreatorColumn = in_array('created_by', $fields, true);
            } catch (\Throwable $e) {
                $this->ticketHasCreatorColumn = false;
            }
        }
        return (bool) $this->ticketHasCreatorColumn;
    }

    public function get_ticket_creator_staff_id($ticket)
    {
        if (!$ticket) {
            return 0;
        }
        if ($this->ticket_has_creator_column() && isset($ticket->created_by) && (int) $ticket->created_by > 0) {
            return (int) $ticket->created_by;
        }
        return (int) ($ticket->admin ?? 0);
    }

    public function is_close_status($statusId)
    {
        if (!$statusId) {
            return false;
        }
        $statusRow = $this->get_ticket_status($statusId);
        if (!$statusRow || !isset($statusRow->name)) {
            return false;
        }
        $name = $statusRow->name;
        $normalized = function_exists('mb_strtolower') ? mb_strtolower($name) : strtolower($name);

        return strpos($normalized, 'close') !== false;
    }

    private function is_waiting_for_close_status($statusId)
    {
        if (!$statusId) {
            return false;
        }
        $statusRow = $this->get_ticket_status($statusId);
        if (!$statusRow || !isset($statusRow->name)) {
            return false;
        }
        $name = trim($statusRow->name);
        $normalized = function_exists('mb_strtolower') ? mb_strtolower($name) : strtolower($name);

        return $normalized === 'waiting for close';
    }
    public function get_ticket_handlers(int $ticketId, bool $withDetails = true)
    {
        if (!$this->db->table_exists(db_prefix() . 'ticket_handlers')) {
            // Try to create the table on the fly (migration may not have run yet)
            if (!$this->ensure_ticket_handlers_table()) {
                return [];
            }
        }
        if ($withDetails) {
            // First, let's get basic ticket handlers data without complex joins
            $handlerRows = $this->db->select('staffid')->from(db_prefix() . 'ticket_handlers')->where('ticketid', $ticketId)->get()->result_array();

            if (empty($handlerRows)) {
                return [];
            }

            $staffIds = array_column($handlerRows, 'staffid');
            if (empty($staffIds)) {
                return [];
            }

            // Now get staff details for these users
            $this->db->select('staffid, firstname, lastname');
            $this->db->from(db_prefix() . 'staff');
            $this->db->where_in('staffid', $staffIds);
            $staffRows = $this->db->get()->result_array();

            // Create a lookup for staff names by staffid
            $staffLookup = [];
            foreach ($staffRows as $staff) {
                $staffLookup[(int) $staff['staffid']] = $staff;
            }

            // We'll skip the custom field join for now since it may be causing issues
            // Build result array
            $result = [];
            foreach ($staffIds as $staffId) {
                $staffId = (int) $staffId;
                $staff = $staffLookup[$staffId] ?? null;

                $name = '';
                if ($staff) {
                    $name = trim(($staff['firstname'] ?? '') . ' ' . ($staff['lastname'] ?? ''));
                }

                if ($name === '') {
                    $name = '#' . $staffId;
                }

                $result[] = [
                    'staffid'  => $staffId,
                    'name'     => $name,
                    'emp_code' => '', // Skip for now to avoid complexity
                ];
            }

            return $result;
        }
        $ids = $this->db->select('staffid')->from(db_prefix() . 'ticket_handlers')->where('ticketid', $ticketId)->get()->result_array();
        return array_map('intval', array_column($ids, 'staffid'));
    }

    /**
     * Collect all staff members that should automatically follow a ticket-derived task.
     *
     * @param  int        $ticketId
     * @param  null|object $ticket Optional pre-fetched ticket record to avoid duplicate queries.
     * @return array<int>
     */
    public function get_ticket_staff_followers(int $ticketId, $ticket = null): array
    {
        $ticketId = (int) $ticketId;
        if ($ticket === null) {
            if ($ticketId <= 0) {
                return [];
            }
            $ticket = $this->get_ticket_by_id($ticketId);
        }

        if (!$ticket) {
            return [];
        }

        $staffIds  = [];
        $maybePush = static function ($value) use (&$staffIds) {
            $value = (int) $value;
            if ($value > 0) {
                $staffIds[] = $value;
            }
        };

        $maybePush($ticket->assigned ?? 0);
        $maybePush($ticket->admin ?? 0);

        if ($this->ticket_has_creator_column() && isset($ticket->created_by)) {
            $maybePush($ticket->created_by);
        }

        $targetTicketId = $ticketId > 0 ? $ticketId : (int) ($ticket->ticketid ?? 0);
        if ($targetTicketId > 0) {
            $handlers = $this->get_ticket_handlers($targetTicketId, false);
            if (!empty($handlers)) {
                foreach ($handlers as $handlerId) {
                    $maybePush($handlerId);
                }
            }
        }

        $departmentIds = [];
        if (!empty($ticket->department)) {
            $departmentIds[] = (int) $ticket->department;
        }
        if (!empty($ticket->sub_department)) {
            $departmentIds[] = (int) $ticket->sub_department;
        }

        if (!empty($ticket->service)) {
            $service = $this->db->select('responsible, departmentid, sub_department')
                ->from(db_prefix() . 'services')
                ->where('serviceid', (int) $ticket->service)
                ->get()
                ->row();

            if ($service) {
                $maybePush($service->responsible ?? 0);
                if (!empty($service->departmentid)) {
                    $departmentIds[] = (int) $service->departmentid;
                }
                if (!empty($service->sub_department)) {
                    $departmentIds[] = (int) $service->sub_department;
                }
            }
        }

        $departmentIds = array_values(array_unique(array_filter(array_map('intval', $departmentIds))));
        if (!empty($departmentIds) && $this->db->field_exists('responsible_staff', db_prefix() . 'departments')) {
            $responsibles = $this->db->select('responsible_staff')
                ->from(db_prefix() . 'departments')
                ->where_in('departmentid', $departmentIds)
                ->get()
                ->result_array();

            foreach ($responsibles as $row) {
                if (isset($row['responsible_staff'])) {
                    $maybePush($row['responsible_staff']);
                }
            }
        }

        $staffIds = array_values(array_unique(array_filter(array_map('intval', $staffIds))));

        return $staffIds;
    }

    public function is_ticket_handler(int $ticketId, int $staffId): bool
    {
        if (!$this->db->table_exists(db_prefix() . 'ticket_handlers')) {
            if (!$this->ensure_ticket_handlers_table()) {
                return false;
            }
        }
        return $this->db->where(['ticketid' => $ticketId, 'staffid' => $staffId])->count_all_results(db_prefix() . 'ticket_handlers') > 0;
    }

    public function set_ticket_handlers(int $ticketId, array $staffIds): bool
    {
        if (!$this->db->table_exists(db_prefix() . 'ticket_handlers')) {
            if (!$this->ensure_ticket_handlers_table()) {
                return false;
            }
        }
        $staffIds = array_values(array_unique(array_filter(array_map('intval', $staffIds))));

        // Get current handlers before update
        $currentHandlers = $this->get_ticket_handlers($ticketId, false);

        $this->db->trans_begin();
        $this->db->where('ticketid', $ticketId)->delete(db_prefix() . 'ticket_handlers');
        foreach ($staffIds as $sid) {
            $this->db->insert(db_prefix() . 'ticket_handlers', [
                'ticketid'   => $ticketId,
                'staffid'    => $sid,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();

    // Log handler assignment changes
    $this->add_ticket_log($ticketId, 'handlers_updated', [
        'previous_handlers' => $currentHandlers,
        'new_handlers' => $staffIds,
    ]);

    // Send SMS notification for new handlers
    $newHandlers = array_diff($staffIds, $currentHandlers);
    if (!empty($newHandlers)) {
        $this->load->library('sms/App_sms');
        $ticket = $this->get_ticket_by_id($ticketId);

        foreach ($newHandlers as $staffId) {
            $this->db->where('staffid', $staffId);
            $staff = $this->db->get(db_prefix() . 'staff')->row();

            if ($staff && !empty($staff->phonenumber)) {
                // Calculate due date from priority
                $dueDate = '';
                if (isset($ticket->priority) && $ticket->priority > 0 && isset($ticket->duration_value) && isset($ticket->duration_unit)) {
                    $interval = '';
                    switch ($ticket->duration_unit) {
                        case 'days':
                            $interval = '+' . $ticket->duration_value . ' days';
                            break;
                        case 'hours':
                            $interval = '+' . $ticket->duration_value . ' hours';
                            break;
                        case 'minutes':
                            $interval = '+' . $ticket->duration_value . ' minutes';
                            break;
                    }
                    if ($interval) {
                        $dueDate = date('m/d/Y', strtotime($interval));
                    }
                }

                // Prepare merge fields for SMS
                $merge_fields = [
                    '{ticket_id}' => $ticketId,
                    '{ticket_number}' => $ticket->ticket_number ?? $ticketId,
                    '{ticket_subject}' => $ticket->subject ?? '',
                    '{priority}' => $ticket->priority_name ?? '',
                    '{due_date}' => $dueDate,
                ];

                $sms_sent = $this->app_sms->trigger(SMS_TRIGGER_HANDLER_ASSIGNED, $staff->phonenumber, $merge_fields);

                // Log SMS notification
                $this->add_ticket_log($ticketId, 'sms_notification_sent', [
                    'trigger' => _l('sms_trigger_handler_assigned', 'custom_lang'),
                    'recipient' => $staff->phonenumber,
                    'recipient_type' => _l('sms_recipient_type_staff', 'custom_lang'),
                    'sent_by' => get_staff_user_id(),
                    'sms_sent' => $sms_sent ? _l('yes', 'custom_lang') : _l('no', 'custom_lang'),
                    'handler_staff' => $staffId,
                ]);
            }
        }
    }

    return true;
    }

    /**
     * Ensure the ticket handlers table exists; create it if missing.
     * Mirrors migration 333_create_ticket_handlers_table.
     */
    private function ensure_ticket_handlers_table(): bool
    {
        $table = db_prefix() . 'ticket_handlers';
        if ($this->db->table_exists($table)) {
            return true;
        }
        if (!class_exists('CI_DB_forge', false)) {
            $this->load->dbforge();
        }
        if (!isset($this->dbforge)) {
            // dbforge could not be loaded
            return false;
        }
        $this->dbforge->add_field([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ticketid' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'staffid' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key(['ticketid', 'staffid']);
        try {
            $created = $this->dbforge->create_table($table, true);
            return (bool)$created;
        } catch (\Throwable $e) {
            log_message('error', 'ensure_ticket_handlers_table failed: ' . $e->getMessage());
            return false;
        }
    }
    // Priorities

    /**
     * Get ticket priority by id
     * @param  mixed $id priority id
     * @return mixed     if id passed return object else array
     */
    public function get_priority($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('priorityid', $id);

            return $this->db->get(db_prefix() . 'tickets_priorities')->row();
        }

        // Include new columns if they exist
        $select = 'priorityid, name';
        if ($this->db->field_exists('color', db_prefix() . 'tickets_priorities')) {
            $select .= ', color';
        }
        if ($this->db->field_exists('duration_value', db_prefix() . 'tickets_priorities')) {
            $select .= ', duration_value';
        }
        if ($this->db->field_exists('duration_unit', db_prefix() . 'tickets_priorities')) {
            $select .= ', duration_unit';
        }
        $this->db->select($select);

        return $this->db->get(db_prefix() . 'tickets_priorities')->result_array();
    }

    /**
     * Add new ticket priority
     * @param array $data ticket priority data
     */
    public function add_priority($data)
    {
        // Ensure new columns exist
        if (!$this->db->field_exists('color', db_prefix() . 'tickets_priorities')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'tickets_priorities` ADD COLUMN `color` VARCHAR(7) DEFAULT "#6c757d" AFTER `name`');
        }
        if (!$this->db->field_exists('duration_value', db_prefix() . 'tickets_priorities')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'tickets_priorities` ADD COLUMN `duration_value` INT(11) DEFAULT NULL AFTER `color`');
        }
        if (!$this->db->field_exists('duration_unit', db_prefix() . 'tickets_priorities')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'tickets_priorities` ADD COLUMN `duration_unit` VARCHAR(10) DEFAULT NULL AFTER `duration_value`');
        }
        // Set defaults
        if (!isset($data['color']) || empty($data['color'])) {
            $data['color'] = '#6c757d';
        }
        if (isset($data['duration_value']) && ($data['duration_value'] === '' || $data['duration_value'] === null)) {
            $data['duration_value'] = null;
        }
        if (isset($data['duration_unit']) && ($data['duration_unit'] === '' || $data['duration_unit'] === null)) {
            $data['duration_unit'] = null;
        }
        $this->db->insert(db_prefix() . 'tickets_priorities', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Ticket Priority Added [ID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update ticket priority
     * @param  array $data ticket priority $_POST data
     * @param  mixed $id   ticket priority id
     * @return boolean
     */
    public function update_priority($data, $id)
    {
        // Ensure new columns exist
        if (!$this->db->field_exists('color', db_prefix() . 'tickets_priorities')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'tickets_priorities` ADD COLUMN `color` VARCHAR(7) DEFAULT "#6c757d" AFTER `name`');
        }
        if (!$this->db->field_exists('duration_value', db_prefix() . 'tickets_priorities')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'tickets_priorities` ADD COLUMN `duration_value` INT(11) DEFAULT NULL AFTER `color`');
        }
        if (!$this->db->field_exists('duration_unit', db_prefix() . 'tickets_priorities')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'tickets_priorities` ADD COLUMN `duration_unit` VARCHAR(10) DEFAULT NULL AFTER `duration_value`');
        }
        // Set defaults
        if (!isset($data['color']) || empty($data['color'])) {
            $data['color'] = '#6c757d';
        }
        if (isset($data['duration_value']) && ($data['duration_value'] === '' || $data['duration_value'] === null)) {
            $data['duration_value'] = null;
        }
        if (isset($data['duration_unit']) && ($data['duration_unit'] === '' || $data['duration_unit'] === null)) {
            $data['duration_unit'] = null;
        }
        $this->db->where('priorityid', $id);
        $this->db->update(db_prefix() . 'tickets_priorities', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Ticket Priority Updated [ID: ' . $id . ' Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete ticket priorit
     * @param  mixed $id ticket priority id
     * @return mixed
     */
    public function delete_priority($id)
    {
        $current = $this->get($id);
        // Check if the priority id is used in tickets table
        if (is_reference_in_table('priority', db_prefix() . 'tickets', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $this->db->where('priorityid', $id);
        $this->db->delete(db_prefix() . 'tickets_priorities');
        if ($this->db->affected_rows() > 0) {
            if (get_option('email_piping_default_priority') == $id) {
                update_option('email_piping_default_priority', '');
            }
            log_activity('Ticket Priority Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    // Predefined replies

    /**
     * Get predefined reply  by id
     * @param  mixed $id predefined reply id
     * @return mixed if id passed return object else array
     */
    public function get_predefined_reply($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'tickets_predefined_replies')->row();
        }

        return $this->db->get(db_prefix() . 'tickets_predefined_replies')->result_array();
    }

    /**
     * Add new predefined reply
     * @param array $data predefined reply $_POST data
     */
    public function add_predefined_reply($data)
    {
        $this->db->insert(db_prefix() . 'tickets_predefined_replies', $data);
        $insertid = $this->db->insert_id();
        log_activity('New Predefined Reply Added [ID: ' . $insertid . ', ' . $data['name'] . ']');

        return $insertid;
    }

    /**
     * Update predefined reply
     * @param  array $data predefined $_POST data
     * @param  mixed $id   predefined reply id
     * @return boolean
     */
    public function update_predefined_reply($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'tickets_predefined_replies', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Predefined Reply Updated [ID: ' . $id . ', ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete predefined reply
     * @param  mixed $id predefined reply id
     * @return boolean
     */
    public function delete_predefined_reply($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'tickets_predefined_replies');
        if ($this->db->affected_rows() > 0) {
            log_activity('Predefined Reply Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    // Ticket statuses

    /**
     * Get ticket status by id
     * @param  mixed $id status id
     * @return mixed     if id passed return object else array
     */
    public function get_ticket_status($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('ticketstatusid', $id);

            return $this->db->get(db_prefix() . 'tickets_status')->row();
        }
        $this->db->order_by('statusorder', 'asc');

        return $this->db->get(db_prefix() . 'tickets_status')->result_array();
    }

    /**
     * Add new ticket status
     * @param array ticket status $_POST data
     * @return mixed
     */
    public function add_ticket_status($data)
    {
        $this->db->insert(db_prefix() . 'tickets_status', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Ticket Status Added [ID: ' . $insert_id . ', ' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update ticket status
     * @param  array $data ticket status $_POST data
     * @param  mixed $id   ticket status id
     * @return boolean
     */
    public function update_ticket_status($data, $id)
    {
        $this->db->where('ticketstatusid', $id);
        $this->db->update(db_prefix() . 'tickets_status', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Ticket Status Updated [ID: ' . $id . ' Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete ticket status
     * @param  mixed $id ticket status id
     * @return mixed
     */
    public function delete_ticket_status($id)
    {
        $current = $this->get_ticket_status($id);
        // Default statuses cant be deleted
        if ($current->isdefault == 1) {
            return [
                'default' => true,
            ];
        // Not default check if if used in table
        } elseif (is_reference_in_table('status', db_prefix() . 'tickets', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $this->db->where('ticketstatusid', $id);
        $this->db->delete(db_prefix() . 'tickets_status');
        if ($this->db->affected_rows() > 0) {
            log_activity('Ticket Status Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    // Ticket services
    public function get_service($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('serviceid', $id);

            return $this->db->get(db_prefix() . 'services')->row();
        }

        $this->db->select('*'); // Select only basic columns that exist
        $this->db->order_by('name', 'asc');
        $rows = $this->db->get(db_prefix() . 'services')->result_array();
        // Enhance rows with option attributes for selects
        foreach ($rows as &$row) {
            $attrs = [];
            if (isset($row['departmentid'])) {
                $attrs['data-departmentid'] = (string) $row['departmentid'];
            }
            if (isset($row['sub_department'])) {
                // Provide both underscore and hyphen attribute for robust access
                $attrs['data-sub_department'] = (string) $row['sub_department'];
                $attrs['data-sub-department'] = (string) $row['sub_department'];
            }
            if (isset($row['divisionid'])) {
                $attrs['data-divisionid'] = (string) $row['divisionid'];
            }
            // Expose responsible user so UI can auto-assign
            if (isset($row['responsible'])) {
                $attrs['data-responsible'] = (string) $row['responsible'];
            }
            if (isset($row['applicationid'])) { // Add applicationid to attrs
                $attrs['data-applicationid'] = (string) $row['applicationid'];
            }
            if (!empty($attrs)) {
                $row['option_attributes'] = $attrs;
            }
        }
        unset($row);
        return $rows;
    }

    public function add_service($data)
    {
        $this->db->insert(db_prefix() . 'services', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Ticket Service Added [ID: ' . $insert_id . '.' . $data['name'] . ']');
        }

        return $insert_id;
    }

    public function update_service($data, $id)
    {
        $this->db->where('serviceid', $id);
        $this->db->update(db_prefix() . 'services', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Ticket Service Updated [ID: ' . $id . ' Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    public function delete_service($id)
    {
        if (is_reference_in_table('service', db_prefix() . 'tickets', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $this->db->where('serviceid', $id);
        $this->db->delete(db_prefix() . 'services');
        if ($this->db->affected_rows() > 0) {
            log_activity('Ticket Service Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /* Toggle service active/inactive status */
    public function toggle_service_status($id, $status)
    {
        // Check if active column exists
        if (!$this->db->field_exists('active', db_prefix() . 'services')) {
            return false;
        }

        $this->db->where('serviceid', $id);
        $this->db->update(db_prefix() . 'services', ['active' => $status]);

        if ($this->db->affected_rows() > 0) {
            log_activity('Service Status Updated [ID: ' . $id . ', Status: ' . ($status ? 'Active' : 'Inactive') . ']');
            return true;
        }

        return false;
    }

    /* Check if service has linked tickets */
    public function has_linked_tickets($service_id)
    {
        return total_rows(db_prefix() . 'tickets', ['service' => $service_id]) > 0;
    }

    /* Get linked tickets for a service */
    public function get_linked_tickets($service_id)
    {
        $this->db->select('ticketid, ticket_number, subject, status');
        $this->db->from(db_prefix() . 'tickets');
        $this->db->where('service', $service_id);
        $this->db->limit(10); // Limit to prevent too many results
        return $this->db->get()->result_array();
    }

    /**
     * @return array
     * Used in home dashboard page
     * Displays weekly ticket openings statistics (chart)
     */
    public function get_weekly_tickets_opening_statistics()
    {
        $departments_ids = [];
        if (!is_admin()) {
            if (get_option('staff_access_only_assigned_departments') == 1) {
                $this->load->model('departments_model');
                $staff_deparments_ids = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                $departments_ids      = [];
                if (count($staff_deparments_ids) == 0) {
                    $departments = $this->departments_model->get();
                    foreach ($departments as $department) {
                        array_push($departments_ids, $department['departmentid']);
                    }
                } else {
                    $departments_ids = $staff_deparments_ids;
                }
            }
        }

        $chart = [
            'labels'   => get_weekdays(),
            'datasets' => [
                [
                    'label'           => _l('home_weekend_ticket_opening_statistics'),
                    'backgroundColor' => 'rgba(197, 61, 169, 0.5)',
                    'borderColor'     => '#c53da9',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => [
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                    ],
                ],
            ],
        ];

        $monday = new DateTime(date('Y-m-d', strtotime('monday this week')));
        $sunday = new DateTime(date('Y-m-d', strtotime('sunday this week')));

        $thisWeekDays = get_weekdays_between_dates($monday, $sunday);

        $byDepartments = count($departments_ids) > 0;
        if (isset($thisWeekDays[1])) {
            $i = 0;
            foreach ($thisWeekDays[1] as $weekDate) {
                $this->db->like('DATE(date)', $weekDate, 'after');
                $this->db->where(db_prefix() . 'tickets.merged_ticket_id IS NULL', null, false);
                if ($byDepartments) {
                    $this->db->where('department IN (SELECT departmentid FROM ' . db_prefix() . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")');
                }
                $chart['datasets'][0]['data'][$i] = $this->db->count_all_results(db_prefix() . 'tickets');

                $i++;
            }
        }

        return $chart;
    }

    public function get_tickets_assignes_disctinct()
    {
        return $this->db->query('SELECT DISTINCT(assigned) as assigned FROM ' . db_prefix() . 'tickets WHERE assigned != 0 AND merged_ticket_id IS NULL')->result_array();
    }

    /**
     * Check for previous tickets opened by this email/contact and link to the contact
     * @param  string $email      email to check for
     * @param  mixed $contact_id the contact id to transfer the tickets
     * @return boolean
     */
    public function transfer_email_tickets_to_contact($email, $contact_id)
    {
        // Some users don't want to fill the email
        if (empty($email)) {
            return false;
        }

        $customer_id = get_user_id_by_contact_id($contact_id);

        $this->db->where('userid', 0)
            ->where('contactid', 0)
            ->where('admin IS NULL')
            ->where('email', $email);

        $this->db->update(db_prefix() . 'tickets', [
            'email'     => null,
            'name'      => null,
            'userid'    => $customer_id,
            'contactid' => $contact_id,
        ]);

        $this->db->where('userid', 0)
            ->where('contactid', 0)
            ->where('admin IS NULL')
            ->where('email', $email);

        $this->db->update(db_prefix() . 'ticket_replies', [
            'email'     => null,
            'name'      => null,
            'userid'    => $customer_id,
            'contactid' => $contact_id,
        ]);

        return true;
    }

    /**
     * Check whether the given ticketid is already merged into another primary ticket
     *
     * @param  int  $id
     *
     * @return boolean
     */
    public function is_merged($id)
    {
        return total_rows('tickets', "ticketid={$id} and merged_ticket_id IS NOT NULL") > 0;
    }

    /**
     * @param $primary_ticket_id
     * @param $status
     * @param  array  $ids
     *
     * @return bool
     */
    public function merge($primary_ticket_id, $status, array $ids)
    {
        if ($this->is_merged($primary_ticket_id)) {
            return false;
        }

        if (($index = array_search($primary_ticket_id, $ids)) !== false) {
            unset($ids[$index]);
        }

        if (count($ids) == 0) {
            return false;
        }

        return (new MergeTickets($primary_ticket_id, $ids))
            ->markPrimaryTicketAs($status)
            ->merge();
    }

    /**
     * @param array $tickets id's of tickets to check
     * @return array
     */
    public function get_already_merged_tickets($tickets)
    {
        if (count($tickets) === 0) {
            return [];
        }

        $alreadyMerged = [];
        foreach ($tickets as $ticketId) {
            if ($this->is_merged((int) $ticketId)) {
                $alreadyMerged[] = $ticketId;
            }
        }

        return $alreadyMerged;
    }

    /**
     * @param $primaryTicketId
     * @return array
     */
    public function get_merged_tickets_by_primary_id($primaryTicketId)
    {
        return $this->db->where('merged_ticket_id', $primaryTicketId)->get(db_prefix() . 'tickets')->result_array();
    }

    public function update_staff_replying($ticketId, $userId = '')
    {
        $ticket = $this->get($ticketId);

        if ($userId === '') {
            return $this->db->where('ticketid', $ticketId)
                ->set('staff_id_replying', null)
                ->update(db_prefix() . 'tickets');
        }

        if ($ticket->staff_id_replying !== $userId && !is_null($ticket->staff_id_replying)) {
            return false;
        }

        if ($ticket->staff_id_replying === $userId) {
            return true;
        }

        return $this->db->where('ticketid', $ticketId)
            ->set('staff_id_replying', $userId)
            ->update(db_prefix() . 'tickets');
    }

    public function get_staff_replying($ticketId)
    {
        $this->db->select('ticketid,staff_id_replying');
        $this->db->where('ticketid', $ticketId);

        return $this->db->get(db_prefix() . 'tickets')->row();
    }

    private function getStaffMembersForTicketNotification($department, $assignedStaff = 0)
    {
        $this->load->model('departments_model');
        $this->load->model('staff_model');

        $staffToNotify = [];
        if ($assignedStaff != 0 && get_option('staff_related_ticket_notification_to_assignee_only') == 1) {
            $member = $this->staff_model->get($assignedStaff, ['active' => 1]);
            if ($member) {
                $staffToNotify[] = (array) $member;
            }
        } else {
            $staff = $this->staff_model->get('', ['active' => 1]);
            foreach ($staff as $member) {
                if (get_option('access_tickets_to_none_staff_members') == 0 && !is_staff_member($member['staffid'])) {
                    continue;
                }
                $staff_departments = $this->departments_model->get_staff_departments($member['staffid'], true);
                if (in_array($department, $staff_departments)) {
                    $staffToNotify[] = $member;
                }
            }
        }

        return $staffToNotify;
    }

    /**
     * Add ticket log entry
     * @param int $ticketid Ticket ID
     * @param string $log_type Type of log (e.g., 'status_change', 'ticket_settings_updated')
     * @param array $log_details Additional details for the log
     */
    public function add_ticket_log($ticketid, $log_type, $log_details = [])
    {
        if (!$this->db->table_exists(db_prefix() . 'ticket_logs')) {
            return; // Table doesn't exist, skip logging
        }

        $data = [
            'ticketid' => $ticketid,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => get_staff_user_id(),
            'user_type' => 'staff',
            'log_type' => $log_type,
            'log_details' => json_encode($log_details),
        ];

        $this->db->insert(db_prefix() . 'ticket_logs', $data);
    }

    public function get_ticket_log($id)
    {
        $this->db->where('ticketid', $id);
        $this->db->order_by('timestamp', 'asc');
        return $this->db->get(db_prefix() . 'ticket_logs')->result_array();
    }

    // SLA functionality

    public function save_sla_entries($ticketId, $slaTexts, $uploadedFiles, $slaEntryKeys)
    {
        log_message('debug', 'save_sla_entries called with ticketId: ' . $ticketId . ', texts: ' . count($slaTexts) . ', keys: ' . json_encode($slaEntryKeys));
        $this->ensure_sla_tables();

        $entriesSaved = 0;
        foreach ($slaTexts as $key => $text) {
            if (trim($text) === '') continue; // Skip empty entries

            log_message('debug', 'Processing SLA text key: ' . $key . ', text length: ' . strlen($text));
            $insertData = [
                'ticket_id' => $ticketId,
                'entry_key' => $slaEntryKeys[$key] ?? $key,
                'sla_text' => $text,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            log_message('debug', 'Insert data: ' . json_encode($insertData));

            $insertId = $this->db->insert(db_prefix() . 'sla_entries', $insertData);

            if ($insertId) {
                $entryId = $this->db->insert_id();
                log_message('debug', 'SLA entry inserted successfully, ID: ' . $entryId);
                $entriesSaved++;

                // Handle attachments if any - organize by entry_key from slaEntryKeys
                $entryKey = $slaEntryKeys[$key] ?? $key;
                log_message('debug', 'Checking attachments for entryKey: ' . $entryKey . ', available keys: ' . json_encode(array_keys($uploadedFiles)));
                if (isset($uploadedFiles[$entryKey]) && is_array($uploadedFiles[$entryKey])) {
                    log_message('debug', 'Found ' . count($uploadedFiles[$entryKey]) . ' attachments for entryKey: ' . $entryKey);
                    foreach ($uploadedFiles[$entryKey] as $file) {
                        // Use the new file structure from controller
            $attachData = [
                            'sla_entry_id' => $entryId,
                            'file_name' => $file['filename'],
                            'file_path' => 'ticket_sla_attachments/' . $ticketId . '/' . $file['filename'],
                            'created_at' => date('Y-m-d H:i:s'),
                        ];
                        log_message('debug', 'Inserting attachment: ' . json_encode($attachData));
                        $insertResult = $this->db->insert(db_prefix() . 'sla_attachments', $attachData);
                        if ($insertResult) {
                            $attachmentId = $this->db->insert_id();
                            log_message('debug', 'Attachment inserted successfully, ID: ' . $attachmentId);
                        } else {
                            log_message('error', 'Failed to insert attachment: ' . $this->db->error()['message']);
                        }
                    }
                } else {
                    log_message('debug', 'No attachments found for entryKey: ' . $entryKey);
                }
            } else {
                log_message('error', 'Failed to insert SLA entry. DB Error: ' . json_encode($this->db->error()));
            }
        }

        log_message('debug', 'SLA entries saved count: ' . $entriesSaved);
        return $entriesSaved > 0;
    }

    public function get_sla_entries($ticketId)
    {
        $this->ensure_sla_tables();

    $this->db->select('se.*, sa.id as attachment_id, sa.file_name, sa.file_path');
        $this->db->from(db_prefix() . 'sla_entries se');
        $this->db->join(db_prefix() . 'sla_attachments sa', 'sa.sla_entry_id = se.id', 'left');
        $this->db->where('se.ticket_id', $ticketId);
        $this->db->order_by('se.created_at', 'desc');

        $results = $this->db->get()->result_array();

        $entries = [];
        foreach ($results as $row) {
            $entryId = $row['id'];
            if (!isset($entries[$entryId])) {
                $entries[$entryId] = [
                    'id' => $entryId,
                    'sla_text' => $row['sla_text'],
                    'created_at' => $row['created_at'],
                    'attachments' => []
                ];
            }
            if (!empty($row['file_name'])) {
                $entries[$entryId]['attachments'][] = [
                    'id' => $row['attachment_id'],
                    'file_name' => $row['file_name'],
                    'file_path' => $row['file_path']
                ];
            }
        }

        return array_values($entries);
    }

    public function delete_sla_entry($entryId)
    {
        $this->ensure_sla_tables();

        // First get attachments to delete files
        $attachments = $this->db->select('file_path')->where('sla_entry_id', $entryId)->get(db_prefix() . 'sla_attachments')->result_array();

        foreach ($attachments as $attachment) {
            if (file_exists($attachment['file_path'])) {
                unlink($attachment['file_path']);
            }
        }

        // Delete attachments from database
        $this->db->where('sla_entry_id', $entryId)->delete(db_prefix() . 'sla_attachments');

        // Delete the entry
        return $this->db->where('id', $entryId)->delete(db_prefix() . 'sla_entries');
    }

    private function ensure_sla_tables()
    {
        // Create sla_entries table if not exists
        $table_entries = db_prefix() . 'sla_entries';
        if (!$this->db->table_exists($table_entries)) {
            $sql = "CREATE TABLE IF NOT EXISTS `$table_entries` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `ticket_id` INT(11) NOT NULL,
                `entry_key` INT(11) NOT NULL,
                `sla_text` TEXT NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `ticket_id` (`ticket_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $result = $this->db->query($sql);
            log_message('debug', 'Created sla_entries table: ' . ($result ? 'success' : 'failed') . ' SQL: ' . $sql);
            if (!$result) {
                log_message('error', 'DB Error creating sla_entries: ' . $this->db->error()['message']);
            }
        }

        // Create sla_attachments table if not exists
        $table_attachments = db_prefix() . 'sla_attachments';
        if (!$this->db->table_exists($table_attachments)) {
            $sql = "CREATE TABLE IF NOT EXISTS `$table_attachments` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `sla_entry_id` INT(11) NOT NULL,
                `file_name` VARCHAR(255) NOT NULL,
                `file_path` VARCHAR(500) NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `sla_entry_id` (`sla_entry_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $result = $this->db->query($sql);
            log_message('debug', 'Created sla_attachments table: ' . ($result ? 'success' : 'failed') . ' SQL: ' . $sql);
            if (!$result) {
                log_message('error', 'DB Error creating sla_attachments: ' . $this->db->error()['message']);
            }
        }
    }
}
