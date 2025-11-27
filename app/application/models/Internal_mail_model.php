<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Internal_mail_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get mail messages
     * @param  string $id optional mail id
     * @param  array  $where perform where
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            $mail = $this->db->get(db_prefix() . 'internal_mail')->row();
            
            if ($mail) {
                // Get recipients
                $mail->recipients = $this->get_mail_recipients($id);
                // Get attachments
                $mail->attachments = $this->get_mail_attachments($id);
            }
            
            return $mail;
        }

        $this->db->where($where);
        return $this->db->get(db_prefix() . 'internal_mail')->result_array();
    }

    /**
     * Get inbox messages for current staff
     * @param  int $staff_id
     * @param  string $type (inbox, sent, drafts, trash)
     * @return array
     */
    public function get_mailbox($staff_id, $type = 'inbox')
    {
        $this->db->select('m.*, s.firstname, s.lastname, s.email as sender_email, 
                           mr.is_read, mr.recipient_type, mr.is_deleted, mr.read_date');
        $this->db->from(db_prefix() . 'internal_mail m');
        
        if ($type == 'sent') {
            $this->db->where('m.from_staff_id', $staff_id);
            $this->db->where('m.is_draft', 0);
        } elseif ($type == 'drafts') {
            $this->db->where('m.from_staff_id', $staff_id);
            $this->db->where('m.is_draft', 1);
        } else {
            // inbox or trash
            $this->db->join(db_prefix() . 'internal_mail_recipients mr', 'mr.mail_id = m.id');
            $this->db->where('mr.staff_id', $staff_id);
            
            if ($type == 'trash') {
                $this->db->where('mr.is_deleted', 1);
            } else {
                $this->db->where('mr.is_deleted', 0);
            }
        }
        
        $this->db->join(db_prefix() . 'staff s', 's.staffid = m.from_staff_id');
        $this->db->order_by('m.date_sent', 'DESC');
        
        return $this->db->get()->result_array();
    }

    /**
     * Send internal mail
     * @param  array $data
     * @return int|bool
     */
    public function send($data)
    {
        $mail_data = [
            'subject' => $data['subject'],
            'message' => $data['message'],
            'from_staff_id' => get_staff_user_id(),
            'date_sent' => date('Y-m-d H:i:s'),
            'is_draft' => isset($data['is_draft']) ? 1 : 0,
            'priority' => isset($data['priority']) ? $data['priority'] : 'normal',
            'has_attachments' => isset($data['attachments']) && count($data['attachments']) > 0 ? 1 : 0,
        ];

        $this->db->insert(db_prefix() . 'internal_mail', $mail_data);
        $mail_id = $this->db->insert_id();

        if ($mail_id) {
            // Add recipients (TO)
            if (isset($data['to']) && is_array($data['to'])) {
                foreach ($data['to'] as $staff_id) {
                    $this->add_recipient($mail_id, $staff_id, 'to');
                }
            }

            // Add CC recipients
            if (isset($data['cc']) && is_array($data['cc'])) {
                foreach ($data['cc'] as $staff_id) {
                    $this->add_recipient($mail_id, $staff_id, 'cc');
                }
            }

            // Add BCC recipients
            if (isset($data['bcc']) && is_array($data['bcc'])) {
                foreach ($data['bcc'] as $staff_id) {
                    $this->add_recipient($mail_id, $staff_id, 'bcc');
                }
            }

            // Handle attachments
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    $this->add_attachment($mail_id, $attachment);
                }
            }

            // Log activity
            if (!isset($data['is_draft'])) {
                log_activity('Internal Mail Sent [Subject: ' . $data['subject'] . ']');
            }

            return $mail_id;
        }

        return false;
    }

    /**
     * Add recipient to mail
     * @param int $mail_id
     * @param int $staff_id
     * @param string $type (to, cc, bcc)
     */
    private function add_recipient($mail_id, $staff_id, $type = 'to')
    {
        $recipient_data = [
            'mail_id' => $mail_id,
            'staff_id' => $staff_id,
            'recipient_type' => $type,
            'is_read' => 0,
        ];

        $this->db->insert(db_prefix() . 'internal_mail_recipients', $recipient_data);
    }

    /**
     * Add attachment to mail
     * @param int $mail_id
     * @param array $attachment_data
     */
    private function add_attachment($mail_id, $attachment_data)
    {
        $attachment = [
            'mail_id' => $mail_id,
            'file_name' => $attachment_data['file_name'],
            'original_file_name' => $attachment_data['original_file_name'],
            'file_type' => $attachment_data['file_type'],
            'file_size' => $attachment_data['file_size'],
            'date_added' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert(db_prefix() . 'internal_mail_attachments', $attachment);
    }

    /**
     * Get mail recipients
     * @param  int $mail_id
     * @return array
     */
    public function get_mail_recipients($mail_id)
    {
        $this->db->select('mr.*, s.firstname, s.lastname, s.email');
        $this->db->from(db_prefix() . 'internal_mail_recipients mr');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = mr.staff_id');
        $this->db->where('mr.mail_id', $mail_id);
        
        return $this->db->get()->result_array();
    }

    /**
     * Get mail attachments
     * @param  int $mail_id
     * @return array
     */
    public function get_mail_attachments($mail_id)
    {
        $this->db->where('mail_id', $mail_id);
        return $this->db->get(db_prefix() . 'internal_mail_attachments')->result_array();
    }

    /**
     * Mark mail as read
     * @param  int $mail_id
     * @param  int $staff_id
     * @return bool
     */
    public function mark_as_read($mail_id, $staff_id)
    {
        $this->db->where('mail_id', $mail_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->where('is_read', 0);
        
        $update_data = [
            'is_read' => 1,
            'read_date' => date('Y-m-d H:i:s'),
        ];
        
        $this->db->update(db_prefix() . 'internal_mail_recipients', $update_data);

        if ($this->db->affected_rows() > 0) {
            // Track read action
            $this->track_action($mail_id, $staff_id, 'read');
            return true;
        }

        return false;
    }

    /**
     * Delete mail (soft delete)
     * @param  int $mail_id
     * @param  int $staff_id
     * @return bool
     */
    public function delete($mail_id, $staff_id)
    {
        $this->db->where('mail_id', $mail_id);
        $this->db->where('staff_id', $staff_id);
        
        $update_data = [
            'is_deleted' => 1,
            'deleted_date' => date('Y-m-d H:i:s'),
        ];
        
        $this->db->update(db_prefix() . 'internal_mail_recipients', $update_data);

        if ($this->db->affected_rows() > 0) {
            $this->track_action($mail_id, $staff_id, 'deleted');
            log_activity('Internal Mail Deleted [ID: ' . $mail_id . ']');
            return true;
        }

        return false;
    }

    /**
     * Permanently delete mail
     * @param  int $mail_id
     * @return bool
     */
    public function permanent_delete($mail_id)
    {
        // Only allow if user is the sender or admin
        $mail = $this->get($mail_id);
        
        if (!$mail || (get_staff_user_id() != $mail->from_staff_id && !is_admin())) {
            return false;
        }

        $this->db->where('id', $mail_id);
        $this->db->delete(db_prefix() . 'internal_mail');

        if ($this->db->affected_rows() > 0) {
            // Delete files
            if ($mail->has_attachments) {
                $attachments = $this->get_mail_attachments($mail_id);
                foreach ($attachments as $attachment) {
                    $file_path = 'uploads/internal_mail/' . $attachment['file_name'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
            }

            log_activity('Internal Mail Permanently Deleted [ID: ' . $mail_id . ']');
            return true;
        }

        return false;
    }

    /**
     * Track mail action
     * @param  int $mail_id
     * @param  int $staff_id
     * @param  string $action
     */
    private function track_action($mail_id, $staff_id, $action)
    {
        $track_data = [
            'mail_id' => $mail_id,
            'staff_id' => $staff_id,
            'action' => $action,
            'action_date' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert(db_prefix() . 'internal_mail_tracking', $track_data);
    }

    /**
     * Get unread count for staff
     * @param  int $staff_id
     * @return int
     */
    public function get_unread_count($staff_id)
    {
        $this->db->where('staff_id', $staff_id);
        $this->db->where('is_read', 0);
        $this->db->where('is_deleted', 0);
        
        return $this->db->count_all_results(db_prefix() . 'internal_mail_recipients');
    }

    /**
     * Search mails
     * @param  int $staff_id
     * @param  string $keyword
     * @return array
     */
    public function search($staff_id, $keyword)
    {
        $this->db->select('m.*, s.firstname, s.lastname, mr.is_read, mr.recipient_type');
        $this->db->from(db_prefix() . 'internal_mail m');
        $this->db->join(db_prefix() . 'internal_mail_recipients mr', 'mr.mail_id = m.id');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = m.from_staff_id');
        
        $this->db->where('mr.staff_id', $staff_id);
        $this->db->where('mr.is_deleted', 0);
        
        $this->db->group_start();
        $this->db->like('m.subject', $keyword);
        $this->db->or_like('m.message', $keyword);
        $this->db->or_like('CONCAT(s.firstname, " ", s.lastname)', $keyword);
        $this->db->group_end();
        
        $this->db->order_by('m.date_sent', 'DESC');
        
        return $this->db->get()->result_array();
    }
}
