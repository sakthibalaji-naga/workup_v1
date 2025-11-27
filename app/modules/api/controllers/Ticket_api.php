<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ticket_api extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('tickets_model');
        $this->load->helper('general');
        $this->load->helper('files');
        $this->load->library('user_agent');
    }

    private function validate_api_key($api_key)
    {
        if (!$api_key) return false;
        $this->db->where('api_key', $api_key);
        $user = $this->db->get('tbl_api_users')->row();
        return $user ? true : false;
    }

    public function index()
    {
        $api_key = $this->input->get_request_header('X-API-Key');

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['message' => 'Ticket API Version 1']));
    }

    public function create_ticket()
    {
        $method = $this->input->method();
        $api_key = $this->input->get_request_header('X-API-Key');

        // Collect request data at start for logging
        $subject = $this->input->post('subject') ?: ($this->input->post('data') ? json_decode($this->input->post('data'), true)['subject'] : null);
        $message = $this->input->post('message') ?: ($this->input->post('data') ? json_decode($this->input->post('data'), true)['message'] : null);
        $request_body = $this->input->raw_input_stream;
        $json_data = json_decode($request_body, true);

        $request_data = [
            'subject' => $subject,
            'message' => $message,
            'request_body' => $request_body,
            'json_data' => $json_data
        ];
        $status_code = 200;
        $response_data = null;

        if ($method !== 'post') {
            $status_code = 405;
            $response_data = ['error' => 'Method not allowed'];
            $this->output->set_status_header($status_code);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response_data));
            log_api_request('/api/v1/create_ticket', $method, $api_key, $request_data, $response_data, $status_code);
            return;
        }

        // Check if POST permission
        if (!$this->check_permission($api_key, 'post')) {
            $status_code = 403;
            $response_data = ['error' => 'Insufficient permissions'];
            $this->output
                ->set_status_header($status_code)
                ->set_content_type('application/json')
                ->set_output(json_encode($response_data));
            log_api_request('/api/v1/create_ticket', $method, $api_key, $request_data, $response_data, $status_code);
            return;
        }

        // Get data from POST parameters or JSON body
        $subject = $this->input->post('subject') ?: ($this->input->post('data') ? json_decode($this->input->post('data'), true)['subject'] : null);
        $message = $this->input->post('message') ?: ($this->input->post('data') ? json_decode($this->input->post('data'), true)['message'] : null);

        // Also check JSON body if not POST parameters
        if (!$subject || !$message) {
            $request_body = $this->input->raw_input_stream;
            $json_data = json_decode($request_body, true);
            if ($json_data) {
                $subject = $subject ?: $json_data['subject'];
                $message = $message ?: $json_data['message'];
            }
        }

        if (!$subject || !$message) {
            $status_code = 400;
            $response_data = ['error' => 'Subject and message required'];
            $this->output
                ->set_status_header($status_code)
                ->set_content_type('application/json')
                ->set_output(json_encode($response_data));
            log_api_request('/api/v1/create_ticket', $method, $api_key, $request_data, $response_data, $status_code);
            return;
        }

        // All ticket creation fields from parameters
        $priority = $this->input->post('priority') ?: ($json_data['priority'] ?? get_option('email_piping_default_priority') ?: 2);
        $department = $this->input->post('department') ?: ($json_data['department'] ?? 0);
        $service = $this->input->post('service') ?: ($json_data['service'] ?? 0);
        $assigned = $this->input->post('assigned') ?: ($json_data['assigned'] ?? 0);
        $contactid = $this->input->post('contactid') ?: ($json_data['contactid'] ?? 0);
        $userid = $this->input->post('userid') ?: ($json_data['userid'] ?? 0);
        $admin = $this->input->post('admin') ?: ($json_data['admin'] ?? null);
        $sub_department = $this->input->post('sub_department') ?: ($json_data['sub_department'] ?? null);
        $divisionid = $this->input->post('divisionid') ?: ($json_data['divisionid'] ?? null);
        $project_id = $this->input->post('project_id') ?: ($json_data['project_id'] ?? 0);
        $application_id = $this->input->post('application_id') ?: ($json_data['application_id'] ?? null);
        $tags = $this->input->post('tags') ?: ($json_data['tags'] ?? '');

        // Generate ticket number using the proper method
        $year = date('y');
        $count = $this->db->where('YEAR(date)', date('Y'))->count_all_results('tbltickets');
        $sequence = $count + 1;
        $ticket_number = $year . str_pad($sequence, 5, '0', STR_PAD_LEFT);

        // Set assigned admin and user/contact info based on input
        $userid_assigned = (int)$userid;
        $contactid_assigned = (int)$contactid;

        if (!$userid_assigned && !$contactid_assigned) {
            // No user/contact specified, treat as customer creating ticket
            $userid_assigned = 0;
            $contactid_assigned = 0;
        }

        // Handle assigned staff member
        $assigned_staff = (int)$assigned;

        // Prepare ticket data for insertion with all fields
        $insert_data = [
            'subject' => trim($subject),
            'message' => trim($message),
            'ticket_number' => $ticket_number,
            'date' => date('Y-m-d H:i:s'),
            'ticketkey' => app_generate_hash(),
            'status' => 1, // Open status
            'userid' => $userid_assigned,
            'contactid' => $contactid_assigned,
            'admin' => (int)$admin, // Admin ID - owner of the ticket
            'assigned' => $assigned_staff,
            'priority' => (int)$priority,
            'service' => (int)$service,
            'department' => (int)$department,
            'sub_department' => $sub_department,
            'divisionid' => $divisionid,
            'project_id' => (int)$project_id,
            'application_id' => $application_id
        ];

        // Insert ticket
        $this->db->insert('tbltickets', $insert_data);
        $ticketid = $this->db->insert_id();

        if (!$ticketid) {
            $status_code = 500;
            $response_data = ['error' => 'Failed to create ticket'];
            $this->output
                ->set_status_header($status_code)
                ->set_content_type('application/json')
                ->set_output(json_encode($response_data));
            log_api_request('/api/v1/create_ticket', $method, $api_key, $request_data, $response_data, $status_code);
            return;
        }

        // Handle attachments/images
        $attachments = $this->handle_api_attachments($ticketid);

        $response_data = [
            'success' => true,
            'message' => 'Ticket created successfully',
            'ticket_number' => $ticket_number,
            'ticket_id' => $ticketid,
            'attachments_uploaded' => count($attachments),
            'attachments' => $attachments
        ];

        $response = json_encode($response_data);

        $this->output
            ->set_content_type('application/json')
            ->set_output($response);

        // Log successful ticket creation
        log_api_request('/api/v1/create_ticket', $method, $api_key, $request_data, $response_data, $status_code);
    }

    public function get_ticket($ticket_number)
    {
        $method = $this->input->method();
        $api_key = $this->input->get_request_header('X-API-Key');
        $request_data = ['ticket_number' => $ticket_number];
        $status_code = 200;
        $response_data = null;

        if ($method !== 'get') {
            $status_code = 405;
            $response_data = ['error' => 'Method not allowed'];
            $this->output->set_status_header($status_code);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response_data));
            log_api_request('/api/v1/ticket/{ticket_number}', $method, $api_key, $request_data, $response_data, $status_code);
            return;
        }

        // Check if GET permission
        if (!$this->check_permission($api_key, 'get')) {
            $status_code = 403;
            $response_data = ['error' => 'Insufficient permissions'];
            $this->output
                ->set_status_header($status_code)
                ->set_content_type('application/json')
                ->set_output(json_encode($response_data));
            log_api_request('/api/v1/ticket/{ticket_number}', $method, $api_key, $request_data, $response_data, $status_code);
            return;
        }

        // Use correct table and get ticket data
        $this->db->where('ticket_number', $ticket_number);
        $ticket = $this->db->get('tbltickets')->row();

        if (!$ticket) {
            $status_code = 404;
            $response_data = ['error' => 'Ticket not found'];
            $this->output
                ->set_status_header($status_code)
                ->set_content_type('application/json')
                ->set_output(json_encode($response_data));
            log_api_request('/api/v1/ticket/{ticket_number}', $method, $api_key, $request_data, $response_data, $status_code);
            return;
        }

        // Convert to array
        $ticket_data = (array) $ticket;

        // Fields to exclude from API response
        $exclude_fields = [
            'adminreplying',
            'userid',
            'contactid',
            'merged_ticket_id',
            'email',
            'name',
            'clientread',
            'cc'
        ];

        // Remove unwanted fields
        $filtered = [];
        foreach ($ticket_data as $field => $value) {
            if (!in_array($field, $exclude_fields)) {
                if ($field === 'message') {
                    // Strip HTML tags from message
                    $filtered[$field] = strip_tags($value);
                } else {
                    $filtered[$field] = $value;
                }
            }
        }

        $response_data = [
            'success' => true,
            'message' => 'Ticket retrieved successfully',
            'ticket' => $filtered
        ];

        $response = json_encode($response_data);

        $this->output
            ->set_content_type('application/json')
            ->set_output($response);

        // Log successful ticket retrieval
        log_api_request('/api/v1/ticket/{ticket_number}', $method, $api_key, $request_data, $response_data, $status_code);
    }

    private function check_permission($api_key, $perm)
    {
        $this->db->where('api_key', $api_key);
        $user = $this->db->get('tbl_api_users')->row();
        if (!$user) return false;
        return $user->{'perm_' . $perm} ? true : false;
    }

    private function handle_api_attachments($ticket_id)
    {
        $attachments = [];

        // Handle uploaded files
        if (!empty($_FILES)) {
            $path = FCPATH . 'uploads/ticket_attachments/' . $ticket_id . '/';

            // Create directory if it doesn't exist
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
                // Create index.html for security
                file_put_contents($path . 'index.html', '');
            }

            // Get allowed extensions
            $allowed_extensions = get_option('ticket_attachments_file_extensions');
            if (!$allowed_extensions) {
                // Default extensions if not configured
                $allowed_extensions = '.jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar';
            }
            $allowed_extensions = array_map(function ($ext) {
                return strtolower(trim($ext));
            }, explode(',', $allowed_extensions));

            // First check for Swagger-style named fields
            $attachment_fields = ['attachments', 'file1', 'file2', 'file3'];
            foreach ($attachment_fields as $field_name) {
                if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
                    $this->process_single_file($_FILES[$field_name], $path, $ticket_id, $allowed_extensions, $attachments);
                }
            }

            // Then process any other file fields that might be present
            foreach ($_FILES as $key => $file) {
                // Skip if we already processed this field
                if (in_array($key, $attachment_fields)) {
                    continue;
                }

                // Handle both single files and arrays
                if (is_array($file['name'])) {
                    // Multiple files with same name (array format)
                    foreach ($file['name'] as $index => $filename) {
                        if ($file['error'][$index] === UPLOAD_ERR_OK) {
                            $this->process_single_file($file, $path, $ticket_id, $allowed_extensions, $attachments, $index);
                        }
                    }
                } else {
                    // Single file
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $this->process_single_file($file, $path, $ticket_id, $allowed_extensions, $attachments);
                    }
                }
            }
        }

        return $attachments;
    }

    private function process_single_file($file_data, $path, $ticket_id, $allowed_extensions, &$attachments, $index = null)
    {
        if ($index !== null) {
            // From array upload
            $filename = $file_data['name'][$index];
            $tmp_name = $file_data['tmp_name'][$index];
            $error = $file_data['error'][$index];
        } else {
            // Single file upload
            $filename = $file_data['name'];
            $tmp_name = $file_data['tmp_name'];
            $error = $file_data['error'];
        }

        // Skip if no filename
        if (empty($filename)) {
            return;
        }

        // Check for upload errors
        if ($error !== UPLOAD_ERR_OK) {
            return;
        }

        // Validate file extension
        $filenameparts = explode('.', $filename);
        $extension = end($filenameparts);
        $extension = strtolower('.' . $extension);

        if (!in_array($extension, $allowed_extensions)) {
            return;
        }

        // Clean filename
        $clean_filename = implode(array_slice($filenameparts, 0, -1));
        $clean_filename = trim(preg_replace('/[^a-zA-Z0-9-_ ]/', '', $clean_filename));
        if (!$clean_filename) {
            $clean_filename = 'attachment';
        }

        $full_filename = $clean_filename . $extension; // Include the dot in extension

        // Generate unique filename using Perfex helper
        $unique_filename = unique_filename($path, $full_filename);

        // Additional check: if file already exists, make it unique
        $counter = 1;
        $original_filename = $unique_filename;
        while (file_exists($path . $unique_filename)) {
            $filenameparts = explode('.', $original_filename);
            if (count($filenameparts) > 1) {
                $extension_part = '.' . end($filenameparts);
                $name_part = implode('.', array_slice($filenameparts, 0, -1));
                $unique_filename = $name_part . '_' . $counter . $extension_part;
            } else {
                $unique_filename = $original_filename . '_' . $counter;
            }
            $counter++;
            if ($counter > 100) { // Prevent infinite loop
                $unique_filename = $original_filename . '_' . time();
                break;
            }
        }

        // Move uploaded file
        if (move_uploaded_file($tmp_name, $path . $unique_filename)) {
            // Insert into database
            $this->db->insert('tblticket_attachments', [
                'ticketid' => $ticket_id,
                'file_name' => $unique_filename,
                'filetype' => get_mime_by_extension($unique_filename),
                'dateadded' => date('Y-m-d H:i:s')
            ]);

            $attachments[] = [
                'file_name' => $unique_filename,
                'original_name' => $filename,
                'filetype' => get_mime_by_extension($unique_filename)
            ];
        }
    }
}
