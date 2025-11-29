<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Internal_mail_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get mail message by ID
     * @param  int $id
     * @return object
     */
    public function get($id)
    {
        $this->db->select('m.*, t.subject as thread_subject, s.firstname, s.lastname, s.email as sender_email, s.profile_image');
        $this->db->from(db_prefix() . 'internal_mail m');
        $this->db->join(db_prefix() . 'internal_mail_threads t', 't.id = m.thread_id');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = m.from_staff_id');
        $this->db->where('m.id', $id);
        
        $mail = $this->db->get()->row();

        if ($mail) {
            $mail->attachments = $this->get_mail_attachments($id);
            $mail->recipients = $this->get_mail_recipients($id);
        }

        return $mail;
    }

    /**
     * Get thread messages
     * @param  int $thread_id
     * @param  int $staff_id (to check permissions/visibility)
     * @return array
     */
    public function get_thread_messages($thread_id, $staff_id)
    {
        // Get all messages in thread that are visible to user (in their message_folders)
        // OR if they are the sender.
        // Actually, we should check `tblinternal_mail_message_folders` for this user.
        
        // First, get the custom field IDs for emp_code and division
        $this->db->select('id');
        $this->db->where('slug', 'staff_emp_code');
        $emp_code_field = $this->db->get(db_prefix() . 'customfields')->row();
        
        $this->db->select('id');
        $this->db->where('slug', 'staff_division');
        $division_field = $this->db->get(db_prefix() . 'customfields')->row();
        
        // Build the select string with custom field subqueries
        $select_str = 'm.*, s.firstname, s.lastname, s.email as sender_email, s.profile_image, mf.is_read, mf.is_starred, mf.system_folder';
        
        if ($emp_code_field) {
            $select_str .= ',(SELECT ' . db_prefix() . 'customfieldsvalues.value FROM ' . db_prefix() . 'customfieldsvalues WHERE ' . db_prefix() . 'customfieldsvalues.relid=s.staffid AND ' . db_prefix() . 'customfieldsvalues.fieldid=' . $emp_code_field->id . ') as emp_code';
        } else {
            $select_str .= ',s.staffid as emp_code';
        }
        
        if ($division_field) {
            $select_str .= ',(SELECT ' . db_prefix() . 'customfieldsvalues.value FROM ' . db_prefix() . 'customfieldsvalues WHERE ' . db_prefix() . 'customfieldsvalues.relid=s.staffid AND ' . db_prefix() . 'customfieldsvalues.fieldid=' . $division_field->id . ') as division_name';
        }
        
        $this->db->select($select_str);
        $this->db->from(db_prefix() . 'internal_mail m');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = m.from_staff_id');
        $this->db->join(db_prefix() . 'internal_mail_message_folders mf', 'mf.message_id = m.id');
        
        $this->db->where('m.thread_id', $thread_id);
        $this->db->where('mf.user_id', $staff_id);
        $this->db->where('mf.system_folder !=', 'trash'); // Don't show trash in conversation view? Or show with warning?
        // Usually show everything unless permanently deleted.
        // Let's just filter out if it's not in any folder (which shouldn't happen unless deleted).
        
        $this->db->order_by('m.date_sent', 'DESC');
        
        $messages = $this->db->get()->result_array();
        
        foreach ($messages as &$msg) {
            $msg['attachments'] = $this->get_mail_attachments($msg['id']);
            $msg['recipients'] = $this->get_mail_recipients($msg['id']);
        }
        
        return $messages;
    }

    /**
     * Get mailbox (threads)
     * @param  int $staff_id
     * @param  string $folder
     * @param  int $limit
     * @param  int $offset
     * @return array
     */
    public function get_mailbox($staff_id, $folder = 'inbox', $limit = 50, $offset = 0)
    {
        // Get threads that have messages in this folder
        $this->db->select('t.*, t.token, MAX(m.date_sent) as last_message_date, COUNT(m.id) as total_messages, SUM(CASE WHEN mf.is_read = 0 THEN 1 ELSE 0 END) as unread_messages');
        $this->db->from(db_prefix() . 'internal_mail_message_folders mf');
        $this->db->join(db_prefix() . 'internal_mail m', 'm.id = mf.message_id');
        $this->db->join(db_prefix() . 'internal_mail_threads t', 't.id = m.thread_id');
        
        $this->db->where('mf.user_id', $staff_id);
        $this->db->where('mf.system_folder', $folder);
        
        $this->db->group_by('t.id');
        $this->db->order_by('last_message_date', 'DESC');
        
        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }
        
        $threads = $this->db->get()->result_array();
        
        // For each thread, get the latest message details (sender, subject snippet)
        foreach ($threads as &$thread) {
            // Get latest message in this thread for this user
            $this->db->select('m.subject, m.message, m.from_staff_id, s.firstname, s.lastname');
            $this->db->from(db_prefix() . 'internal_mail m');
            $this->db->join(db_prefix() . 'staff s', 's.staffid = m.from_staff_id');
            $this->db->where('m.thread_id', $thread['id']);
            $this->db->order_by('m.date_sent', 'DESC');
            $this->db->limit(1);
            $latest = $this->db->get()->row_array();
            
            if ($latest) {
                $thread['latest_subject'] = $latest['subject'];
                $thread['latest_message'] = $latest['message'];
                $thread['sender_fullname'] = $latest['firstname'] . ' ' . $latest['lastname'];
                $thread['from_staff_id'] = $latest['from_staff_id'];
            }
        }
        
        return $threads;
    }

    /**
     * Get thread ID by token
     * @param string $token
     * @return int|bool
     */
    public function get_id_by_token($token)
    {
        $this->db->select('id');
        $this->db->where('token', $token);
        $row = $this->db->get(db_prefix() . 'internal_mail_threads')->row();
        return $row ? $row->id : false;
    }

    /**
     * Send internal mail
     * @param  array $data
     * @return int|bool
     */
    public function send($data)
    {
        $this->db->trans_start();

        $date_sent = date('Y-m-d H:i:s');
        $from_staff_id = get_staff_user_id();
        $is_draft = isset($data['is_draft']) ? 1 : 0;
        
        // 1. Handle Threading
        $thread_id = null;
        if (isset($data['thread_id']) && !empty($data['thread_id'])) {
            $thread_id = $data['thread_id'];
            // Update thread last_message_at
            $this->db->where('id', $thread_id);
            $this->db->update(db_prefix() . 'internal_mail_threads', ['last_message_at' => $date_sent]);
        } else {
            // Create new thread
            $this->db->insert(db_prefix() . 'internal_mail_threads', [
                'subject' => $data['subject'],
                'created_at' => $date_sent,
                'last_message_at' => $date_sent,
                'token' => bin2hex(random_bytes(16)) // Generate unique token
            ]);
            $thread_id = $this->db->insert_id();
        }

        // 2. Insert Message
        $mail_data = [
            'thread_id' => $thread_id,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'body_text' => strip_tags($data['message']), // Simple plain text version
            'from_staff_id' => $from_staff_id,
            'date_sent' => $date_sent,
            'is_draft' => $is_draft,
            'priority' => isset($data['priority']) ? $data['priority'] : 'normal',
            'has_attachments' => isset($data['attachments']) && count($data['attachments']) > 0 ? 1 : 0,
        ];

        $this->db->insert(db_prefix() . 'internal_mail', $mail_data);
        $mail_id = $this->db->insert_id();

        if (!$mail_id) {
            $this->db->trans_rollback();
            return false;
        }

        // 3. Handle Recipients & Folders
        $recipients = [];
        if (isset($data['to'])) $recipients = array_merge($recipients, $this->prepare_recipients($data['to'], 'to'));
        if (isset($data['cc'])) $recipients = array_merge($recipients, $this->prepare_recipients($data['cc'], 'cc'));
        if (isset($data['bcc'])) $recipients = array_merge($recipients, $this->prepare_recipients($data['bcc'], 'bcc'));

        // Add to tblinternal_mail_recipients (for history)
        foreach ($recipients as $recipient) {
            $this->db->insert(db_prefix() . 'internal_mail_recipients', [
                'mail_id' => $mail_id,
                'staff_id' => $recipient['staff_id'],
                'recipient_type' => $recipient['type'],
                'is_read' => 0
            ]);

            // Add to tblinternal_mail_message_folders (Inbox for recipient)
            if (!$is_draft) {
                $this->db->insert(db_prefix() . 'internal_mail_message_folders', [
                    'user_id' => $recipient['staff_id'],
                    'message_id' => $mail_id,
                    'system_folder' => 'inbox',
                    'is_read' => 0
                ]);
            }
        }

        // Add to Sender's Folder (Sent or Drafts)
        $this->db->insert(db_prefix() . 'internal_mail_message_folders', [
            'user_id' => $from_staff_id,
            'message_id' => $mail_id,
            'system_folder' => $is_draft ? 'drafts' : 'sent',
            'is_read' => 1
        ]);

        // 4. Handle Attachments
        if (isset($data['attachments']) && is_array($data['attachments'])) {
            foreach ($data['attachments'] as $attachment) {
                $this->add_attachment($mail_id, $attachment);
            }
        }

        // 5. Audit Log
        $this->log_audit($from_staff_id, $is_draft ? 'draft_saved' : 'mail_sent', 'mail', $mail_id, ['subject' => $data['subject']]);

        $this->db->trans_complete();

        return $this->db->trans_status() ? $mail_id : false;
    }

    private function prepare_recipients($ids, $type)
    {
        $result = [];
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $result[] = ['staff_id' => $id, 'type' => $type];
            }
        }
        return $result;
    }

    /**
     * Add attachment to mail
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
     */
    public function get_mail_attachments($mail_id)
    {
        $this->db->where('mail_id', $mail_id);
        return $this->db->get(db_prefix() . 'internal_mail_attachments')->result_array();
    }

    /**
     * Mark thread as read
     */
    public function mark_thread_read($thread_id, $staff_id)
    {
        // Find all messages in this thread for this user and mark as read
        // We need to join to find messages in the thread
        
        $sql = "UPDATE " . db_prefix() . "internal_mail_message_folders mf
                JOIN " . db_prefix() . "internal_mail m ON m.id = mf.message_id
                SET mf.is_read = 1
                WHERE m.thread_id = ? AND mf.user_id = ? AND mf.is_read = 0";
                
        $this->db->query($sql, [$thread_id, $staff_id]);
        
        return $this->db->affected_rows() > 0;
    }

    /**
     * Mark thread as unread
     */
    public function mark_thread_unread($thread_id, $staff_id)
    {
        // Find all messages in this thread for this user and mark as unread
        $sql = "UPDATE " . db_prefix() . "internal_mail_message_folders mf
                JOIN " . db_prefix() . "internal_mail m ON m.id = mf.message_id
                SET mf.is_read = 0
                WHERE m.thread_id = ? AND mf.user_id = ?";
                
        $this->db->query($sql, [$thread_id, $staff_id]);
        
        return $this->db->affected_rows() > 0;
    }

    /**
     * Get navigation info for a thread (prev/next threads)
     * @param int $thread_id
     * @param int $staff_id  
     * @param string $folder
     * @return array ['prev' => token|null, 'next' => token|null]
     */
    public function get_thread_navigation($thread_id, $staff_id, $folder = 'inbox')
    {
        // Get all thread IDs and tokens in this folder, ordered by last_message_date DESC
        $this->db->select('t.id, t.token, MAX(m.date_sent) as last_message_date');
        $this->db->from(db_prefix() . 'internal_mail_message_folders mf');
        $this->db->join(db_prefix() . 'internal_mail m', 'm.id = mf.message_id');
        $this->db->join(db_prefix() . 'internal_mail_threads t', 't.id = m.thread_id');
        
        $this->db->where('mf.user_id', $staff_id);
        $this->db->where('mf.system_folder', $folder);
        
        $this->db->group_by('t.id');
        $this->db->order_by('last_message_date', 'DESC');
        
        $threads = $this->db->get()->result_array();
        
        // Find current thread position
        $current_index = null;
        foreach ($threads as $index => $thread) {
            if ($thread['id'] == $thread_id) {
                $current_index = $index;
                break;
            }
        }
        
        $result = ['prev' => null, 'next' => null];
        
        if ($current_index !== null) {
            // Previous is the one before in the list (newer)
            if ($current_index > 0) {
                $result['prev'] = $threads[$current_index - 1]['token'];
            }
            
            // Next is the one after in the list (older)
            if ($current_index < count($threads) - 1) {
                $result['next'] = $threads[$current_index + 1]['token'];
            }
        }
        
        return $result;
    }

    /**
     * Move thread to folder
     */
    public function move_thread($thread_id, $folder, $staff_id)
    {
        // Move all messages in this thread for this user to the new folder
        $sql = "UPDATE " . db_prefix() . "internal_mail_message_folders mf
                JOIN " . db_prefix() . "internal_mail m ON m.id = mf.message_id
                SET mf.system_folder = ?
                WHERE m.thread_id = ? AND mf.user_id = ?";
                
        $this->db->query($sql, [$folder, $thread_id, $staff_id]);
        
        return $this->db->affected_rows() > 0;
    }

    /**
     * Get unread count
     */
    public function get_unread_count($staff_id)
    {
        $this->db->where('user_id', $staff_id);
        $this->db->where('is_read', 0);
        $this->db->where('system_folder', 'inbox'); // Only count inbox? Or all folders? Usually Inbox.
        
        return $this->db->count_all_results(db_prefix() . 'internal_mail_message_folders');
    }

    /**
     * Search mails
     */
    public function search($staff_id, $keyword)
    {
        $this->db->select('m.*, t.subject as thread_subject, s.firstname, s.lastname');
        $this->db->from(db_prefix() . 'internal_mail_message_folders mf');
        $this->db->join(db_prefix() . 'internal_mail m', 'm.id = mf.message_id');
        $this->db->join(db_prefix() . 'internal_mail_threads t', 't.id = m.thread_id');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = m.from_staff_id');
        
        $this->db->where('mf.user_id', $staff_id);
        
        $this->db->group_start();
        $this->db->like('m.subject', $keyword);
        $this->db->or_like('m.message', $keyword);
        $this->db->or_like('CONCAT(s.firstname, " ", s.lastname)', $keyword);
        $this->db->group_end();
        
        $this->db->group_by('t.id'); // Group by thread
        $this->db->order_by('m.date_sent', 'DESC');
        
        return $this->db->get()->result_array();
    }

    /**
     * Log audit
     */
    private function log_audit($user_id, $action, $entity_type, $entity_id, $metadata = [])
    {
        $this->db->insert(db_prefix() . 'internal_mail_audit_logs', [
            'user_id' => $user_id,
            'action' => $action,
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'metadata_json' => json_encode($metadata),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
