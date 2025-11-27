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
                'attachments' => $attachments,
            ];

            // Check if it's a draft
            if ($this->input->post('save_draft')) {
                $mail_data['is_draft'] = 1;
            }

            $mail_id = $this->internal_mail_model->send($mail_data);

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
        
        if ($id != '') {
            $data['mail'] = $this->internal_mail_model->get($id);
        }
        
        $this->load->view('admin/internal_mail/compose', $data);
    }

    /**
     * View mail
     */
    public function view($id)
    {
        if (!staff_can('view', 'internal_mail')) {
            access_denied('Internal Mail');
        }

        $mail = $this->internal_mail_model->get($id);

        if (!$mail) {
            show_404();
        }

        // Mark as read if recipient
        $staff_id = get_staff_user_id();
        $is_recipient = false;
        
        foreach ($mail->recipients as $recipient) {
            if ($recipient['staff_id'] == $staff_id) {
                $is_recipient = true;
                break;
            }
        }

        if ($is_recipient) {
            $this->internal_mail_model->mark_as_read($id, $staff_id);
        }

        $data['mail'] = $mail;
        $data['title'] = $mail->subject;
        $data['unread_count'] = $this->internal_mail_model->get_unread_count(get_staff_user_id());
        
        $this->load->view('admin/internal_mail/view', $data);
    }

    /**
     * Delete mail
     */
    public function delete($id)
    {
        if (!staff_can('delete', 'internal_mail')) {
            access_denied('Internal Mail');
        }

        $response = $this->internal_mail_model->delete($id, get_staff_user_id());

        if ($response) {
            set_alert('success', _l('internal_mail_deleted'));
        } else {
            set_alert('warning', _l('internal_mail_delete_failed'));
        }

        redirect(admin_url('internal_mail/inbox'));
    }

    /**
     * Permanently delete mail
     */
    public function permanent_delete($id)
    {
        if (!is_admin() && !staff_can('delete', 'internal_mail')) {
            access_denied('Internal Mail');
        }

        $response = $this->internal_mail_model->permanent_delete($id);

        if ($response) {
            set_alert('success', _l('internal_mail_permanently_deleted'));
        } else {
            set_alert('warning', _l('internal_mail_delete_failed'));
        }

        redirect(admin_url('internal_mail/trash'));
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
}
