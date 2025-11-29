<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Internal_mail extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('internal_mail_model');
        $this->load->model('staff_model');
    }

    /**
     * Inbox - default view
     */
    public function index()
    {
        $this->inbox();
    }

    /**
     * Display inbox
     */
    public function inbox()
    {
        if (!staff_can('view', 'internal_mail')) {
            access_denied('Internal Mail');
        }

        $data['title'] = _l('internal_mail_inbox');
        $data['mailbox_type'] = 'inbox';
        $data['mails'] = $this->internal_mail_model->get_mailbox(get_staff_user_id(), 'inbox');
        $data['unread_count'] = $this->internal_mail_model->get_unread_count(get_staff_user_id());
        
        $this->load->view('admin/internal_mail/mailbox', $data);
    }

    /**
     * Display sent items
     */
    public function sent()
    {
        if (!staff_can('view', 'internal_mail')) {
            access_denied('Internal Mail');
        }

        $data['title'] = _l('internal_mail_sent');
        $data['mailbox_type'] = 'sent';
        $data['mails'] = $this->internal_mail_model->get_mailbox(get_staff_user_id(), 'sent');
        $data['unread_count'] = $this->internal_mail_model->get_unread_count(get_staff_user_id());
        
        $this->load->view('admin/internal_mail/mailbox', $data);
    }

    /**
     * Display drafts
     */
    public function drafts()
    {
        if (!staff_can('view', 'internal_mail')) {
            access_denied('Internal Mail');
        }

        $data['title'] = _l('internal_mail_drafts');
        $data['mailbox_type'] = 'drafts';
        $data['mails'] = $this->internal_mail_model->get_mailbox(get_staff_user_id(), 'drafts');
        $data['unread_count'] = $this->internal_mail_model->get_unread_count(get_staff_user_id());
        
        $this->load->view('admin/internal_mail/mailbox', $data);
    }

    /**
     * Display trash
     */
    public function trash()
    {
        if (!staff_can('view', 'internal_mail')) {
            access_denied('Internal Mail');
        }

        $data['title'] = _l('internal_mail_trash');
        $data['mailbox_type'] = 'trash';
        $data['mails'] = $this->internal_mail_model->get_mailbox(get_staff_user_id(), 'trash');
        $data['unread_count'] = $this->internal_mail_model->get_unread_count(get_staff_user_id());
        
        $this->load->view('admin/internal_mail/mailbox', $data);
    }

    /**
     * Compose new mail
     */
    public function compose($id = '')
    {
        if (!staff_can('create', 'internal_mail')) {
            access_denied('Internal Mail');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            
            // Handle file uploads
            $attachments = [];
            
            if (isset($_FILES['attachments']) && $_FILES['attachments']['name'][0] != '') {
                $upload_path = 'uploads/internal_mail/';
                
                // Create directory if not exists
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }

                $files_count = count($_FILES['attachments']['name']);
                
                for ($i = 0; $i < $files_count; $i++) {
                    if ($_FILES['attachments']['error'][$i] == 0) {
                        $file_name = time() . '_' . $_FILES['attachments']['name'][$i];
                        $file_path = $upload_path . $file_name;
                        
                        if (move_uploaded_file($_FILES['attachments']['tmp_name'][$i], $file_path)) {
                            $attachments[] = [
                                'file_name' => $file_name,
                                'original_file_name' => $_FILES['attachments']['name'][$i],
                                'file_type' => $_FILES['attachments']['type'][$i],
                                'file_size' => $_FILES['attachments']['size'][$i],
                            ];
                        }
                    }
                }
            }

            $mail_data = [
                'subject' => $this->input->post('subject'),
                'message' => $this->input->post('message', false),
                'priority' => $this->input->post('priority'),
                'to' => $this->input->post('to'),
                'cc' => $this->input->post('cc'),
                'bcc' => $this->input->post('bcc'),
                'thread_id' => $this->input->post('thread_id'),
                'attachments' => $attachments,
            ];

            // Check if it's a draft
            if ($this->input->post('save_draft')) {
                $mail_data['is_draft'] = 1;
            }

            $mail_id = $this->internal_mail_model->send($mail_data);

            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => true, 'mail_id' => $mail_id, 'message' => _l('internal_mail_draft_saved')]);
                return;
            }

            if ($mail_id) {
                if (isset($mail_data['is_draft'])) {
                    set_alert('success', _l('internal_mail_draft_saved'));
                    redirect(admin_url('internal_mail/drafts'));
                } else {
                    set_alert('success', _l('internal_mail_sent_successfully'));
                    redirect(admin_url('internal_mail/sent'));
                }
            } else {
                set_alert('danger', _l('internal_mail_send_failed'));
            }
        }

        // Get all active staff for recipient selection
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
        $data['title'] = _l('internal_mail_compose');
        $data['unread_count'] = $this->internal_mail_model->get_unread_count(get_staff_user_id());
        
        // Handle reply
        if ($this->input->get('reply_to_message_id')) {
            $reply_to_id = $this->input->get('reply_to_message_id');
            $original_mail = $this->internal_mail_model->get($reply_to_id);
            
            if ($original_mail) {
                $data['reply_to_mail'] = $original_mail;
                $data['thread_id'] = $original_mail->thread_id;
                
                // Set subject with Re: prefix
                $subject = $original_mail->subject;
                if (stripos($subject, 'Re:') !== 0) {
                    $subject = 'Re: ' . $subject;
                }
                $data['reply_subject'] = $subject;
                
                // Quote original message
                $sender_name = $original_mail->firstname . ' ' . $original_mail->lastname;
                $date = _dt($original_mail->date_sent);
                
                $data['reply_message'] = '<br><br><div style="border-left:3px solid #ccc;padding-left:12px;margin-top:12px;color:#666;">'
                    . '<p><strong>On ' . $date . ', ' . $sender_name . ' wrote:</strong></p>'
                    . $original_mail->message
                    . '</div>';
                
                // Set recipients (reply to sender)
                $data['reply_recipients'] = [
                    'to' => [$original_mail->from_staff_id],
                    'cc' => []
                ];
                
                // Reply All logic could be handled in frontend or here
                if ($this->input->get('reply_all')) {
                     // Add CCs...
                }
            }
        }
        
        // Handle forward
        if ($this->input->get('forward_message_id')) {
            $forward_id = $this->input->get('forward_message_id');
            $original_mail = $this->internal_mail_model->get($forward_id);
            
            if ($original_mail) {
                $data['forward_mail'] = $original_mail;
                // Don't set thread_id for forward - it creates a new thread
                
                // Set subject with Fwd: prefix
                $subject = $original_mail->subject;
                if (stripos($subject, 'Fwd:') !== 0) {
                    $subject = 'Fwd: ' . $subject;
                }
                $data['forward_subject'] = $subject;
                
                // Include original message
                $sender_name = $original_mail->firstname . ' ' . $original_mail->lastname;
                $date = _dt($original_mail->date_sent);
                
                $data['forward_message'] = '<br><br><div style="border-left:3px solid #ccc;padding-left:12px;margin-top:12px;color:#666;">'
                    . '<p><strong>---------- Forwarded message ---------</strong></p>'
                    . '<p><strong>From:</strong> ' . $sender_name . ' &lt;' . $original_mail->sender_email . '&gt;</p>'
                    . '<p><strong>Date:</strong> ' . $date . '</p>'
                    . '<p><strong>Subject:</strong> ' . $original_mail->subject . '</p>'
                    . '<br>'
                    . $original_mail->message
                    . '</div>';
                
                // For forward, recipients are empty (user needs to add them)
                $data['forward_recipients'] = [
                    'to' => [],
                    'cc' => []
                ];
            }
        }
        
        // Edit draft
        if ($id != '') {
            $data['mail'] = $this->internal_mail_model->get($id);
        }
        
        $this->load->view('admin/internal_mail/compose', $data);
    }

    /**
     * View thread
     */
    /**
     * View thread
     */
    public function view($token)
    {
        if (!staff_can('view', 'internal_mail')) {
            access_denied('Internal Mail');
        }

        $thread_id = $this->internal_mail_model->get_id_by_token($token);
        if (!$thread_id) {
            show_404();
        }

        // Mark thread as read
        $this->internal_mail_model->mark_thread_read($thread_id, get_staff_user_id());

        $messages = $this->internal_mail_model->get_thread_messages($thread_id, get_staff_user_id());

        if (!$messages) {
            show_404();
        }

        $data['messages'] = $messages;
        $data['thread_id'] = $thread_id;
        $data['token'] = $token;
        $data['subject'] = $messages[0]['subject']; // Subject of the first message
        $data['title'] = $data['subject'];
        $data['unread_count'] = $this->internal_mail_model->get_unread_count(get_staff_user_id());
        
        // Get navigation (prev/next threads)
        $current_folder = isset($messages[0]['system_folder']) ? $messages[0]['system_folder'] : 'inbox';
        $navigation = $this->internal_mail_model->get_thread_navigation($thread_id, get_staff_user_id(), $current_folder);
        $data['prev_token'] = $navigation['prev'];
        $data['next_token'] = $navigation['next'];
        
        $this->load->view('admin/internal_mail/view', $data);
    }

    /**
     * Delete thread (move to trash)

    public function delete($token)
    {
        if (!staff_can('delete', 'internal_mail')) {
            access_denied('Internal Mail');
        }

        $thread_id = $this->internal_mail_model->get_id_by_token($token);
        if (!$thread_id) {
            show_404();
        }

        $response = $this->internal_mail_model->move_thread($thread_id, 'trash', get_staff_user_id());

        if ($response) {
            set_alert('success', _l('internal_mail_moved_to_trash'));
        } else {
            set_alert('warning', _l('internal_mail_delete_failed'));
        }

        redirect(admin_url('internal_mail/inbox'));
    }

    /**
     * Mark thread as unread
     */
    public function mark_unread($token)
    {
        if (!staff_can('view', 'internal_mail')) {
            access_denied('Internal Mail');
        }

        $thread_id = $this->internal_mail_model->get_id_by_token($token);
        if (!$thread_id) {
            show_404();
        }

        $this->internal_mail_model->mark_thread_unread($thread_id, get_staff_user_id());
        set_alert('success', _l('internal_mail_marked_as_unread'));

        // Redirect back to inbox
        redirect(admin_url('internal_mail/inbox'));
    }

    /**
     * Move thread to folder
     */
    /**
     * Move thread to folder
     */
    public function move($token, $folder)
    {
        if (!staff_can('view', 'internal_mail')) {
            access_denied('Internal Mail');
        }
        
        // Validate folder
        $allowed_folders = ['inbox', 'archive', 'trash'];
        if (!in_array($folder, $allowed_folders)) {
            show_404();
        }

        $thread_id = $this->internal_mail_model->get_id_by_token($token);
        if (!$thread_id) {
            show_404();
        }

        $this->internal_mail_model->move_thread($thread_id, $folder, get_staff_user_id());
        set_alert('success', _l('internal_mail_moved_successfully'));
        
        // Redirect back to where we came from or inbox
        if (isset($_SERVER['HTTP_REFERER'])) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('internal_mail/inbox'));
        }
    }

    /**
     * Download attachment
     */
    public function download_attachment($attachment_id)
    {
        if (!staff_can('view', 'internal_mail')) {
            access_denied('Internal Mail');
        }

        $this->db->where('id', $attachment_id);
        $attachment = $this->db->get(db_prefix() . 'internal_mail_attachments')->row();

        if (!$attachment) {
            show_404();
        }

        // Verify user has access to this mail
        $mail = $this->internal_mail_model->get($attachment->mail_id);
        $staff_id = get_staff_user_id();
        
        $has_access = false;
        if ($mail->from_staff_id == $staff_id) {
            $has_access = true;
        } else {
            foreach ($mail->recipients as $recipient) {
                if ($recipient['staff_id'] == $staff_id) {
                    $has_access = true;
                    break;
                }
            }
        }

        if (!$has_access) {
            access_denied('Internal Mail Attachment');
        }

        $file_path = 'uploads/internal_mail/' . $attachment->file_name;

        if (file_exists($file_path)) {
            $this->load->helper('download');
            force_download($attachment->original_file_name, file_get_contents($file_path));
        } else {
            show_404();
        }
    }

    /**
     * Search mails
     */
    public function search()
    {
        if (!staff_can('view', 'internal_mail')) {
            access_denied('Internal Mail');
        }

        $keyword = $this->input->get('q');
        
        $data['title'] = _l('internal_mail_search_results');
        $data['mailbox_type'] = 'search';
        $data['keyword'] = $keyword;
        $data['mails'] = $this->internal_mail_model->search(get_staff_user_id(), $keyword);
        $data['unread_count'] = $this->internal_mail_model->get_unread_count(get_staff_user_id());
        
        $this->load->view('admin/internal_mail/mailbox', $data);
    }

    /**
     * Get unread count (AJAX)
     */
    public function get_unread_count()
    {
        $count = $this->internal_mail_model->get_unread_count(get_staff_user_id());
        echo json_encode(['count' => $count]);
    }
    
    /**
     * Search staff for autocomplete (AJAX)
     */
    public function search_staff()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $q = $this->input->post('q');
        
        // Get emp_code custom field ID
        $this->db->select('id');
        $this->db->where('slug', 'staff_emp_code');
        $emp_code_field = $this->db->get(db_prefix() . 'customfields')->row();
        
        // Build select with emp_code from custom fields
        $select_str = db_prefix() . 'staff.staffid, ' . db_prefix() . 'staff.firstname, ' . db_prefix() . 'staff.lastname, ' . db_prefix() . 'staff.email, ' . db_prefix() . 'staff.profile_image';
        
        if ($emp_code_field) {
            $select_str .= ',(SELECT ' . db_prefix() . 'customfieldsvalues.value FROM ' . db_prefix() . 'customfieldsvalues WHERE ' . db_prefix() . 'customfieldsvalues.relid=' . db_prefix() . 'staff.staffid AND ' . db_prefix() . 'customfieldsvalues.fieldid=' . $emp_code_field->id . ') as emp_code';
        }
        
        $this->db->select($select_str);
        
        // Search by name
        $this->db->group_start();
        $this->db->like('firstname', $q);
        $this->db->or_like('lastname', $q);
        $this->db->or_like('email', $q);
        $this->db->or_like("CONCAT(firstname, ' ', lastname)", $q);
        
        // If emp_code field exists, also search in custom fields
        if ($emp_code_field) {
            $this->db->or_where("(SELECT " . db_prefix() . "customfieldsvalues.value FROM " . db_prefix() . "customfieldsvalues WHERE " . db_prefix() . "customfieldsvalues.relid=" . db_prefix() . "staff.staffid AND " . db_prefix() . "customfieldsvalues.fieldid=" . $emp_code_field->id . ") LIKE '%" . $this->db->escape_like_str($q) . "%'", NULL, FALSE);
        }
        $this->db->group_end();
        
        $this->db->limit(50);
        $staff_members = $this->db->get(db_prefix() . 'staff')->result_array();

        $results = [];
        foreach ($staff_members as $staff) {
            $profile_image = staff_profile_image($staff['staffid'], ['staff-profile-image-small', 'img-circle', 'pull-left'], 'small', ['style' => 'width:30px;margin-right:10px;']);
            
            $emp_code_display = !empty($staff['emp_code']) ? ' (' . $staff['emp_code'] . ')' : '';
            $content = "<div class='staff-option-item'>" . $profile_image . "<div class='staff-option-details'><span>" . $staff['firstname'] . ' ' . $staff['lastname'] . $emp_code_display . "</span></div></div>";

            $results[] = [
                'staffid' => $staff['staffid'],
                'firstname' => $staff['firstname'],
                'lastname' => $staff['lastname'],
                'emp_code' => !empty($staff['emp_code']) ? $staff['emp_code'] : '',
                'content' => $content
            ];
        }

        echo json_encode($results);
    }
}
