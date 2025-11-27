<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flow_builder extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('flow_builder_model');
    }

    public function index()
    {
        $data['title'] = _l('flow_builder');
        $data['flows'] = $this->flow_builder_model->get_all_flows();
        $this->load->view('admin/flow_builder/index', $data);
    }

    public function build($id = null)
    {
        $data['title'] = _l('flow_builder');
        $data['flow_id'] = $id;

        if ($id) {
            $data['flow'] = $this->flow_builder_model->get_flow($id);
            if (!$data['flow']) {
                show_404();
            }
        }

        $this->load->view('admin/flow_builder/builder', $data);
    }

    public function logs()
    {
        $data['title'] = _l('flow_execution_logs');
        $data['logs'] = $this->flow_builder_model->get_execution_logs();
        $data['flows'] = $this->flow_builder_model->get_all_flows();
        $this->load->view('admin/flow_builder/logs', $data);
    }

    public function test_api_connection()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $api_id = $this->input->post('external_api_id');
        if (!$api_id) {
            echo json_encode([
                'success' => false,
                'message' => 'API ID is required'
            ]);
            return;
        }

        // Use the existing external API functionality
        $result = $this->flow_builder_model->test_api_connection($api_id);

        echo json_encode($result);
    }

    // API endpoints for flow management
    public function save_flow()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $flow_data = $this->input->post();
        $flow_id = $this->flow_builder_model->save_flow($flow_data);

        echo json_encode([
            'success' => true,
            'flow_id' => $flow_id,
            'message' => _l('flow_saved_successfully')
        ]);
    }

    public function delete_flow($id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->flow_builder_model->delete_flow($id);

        echo json_encode([
            'success' => true,
            'message' => _l('flow_deleted_successfully')
        ]);
    }

    public function duplicate_flow($id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $new_flow_id = $this->flow_builder_model->duplicate_flow($id);

        echo json_encode([
            'success' => true,
            'flow_id' => $new_flow_id,
            'message' => _l('flow_duplicated_successfully')
        ]);
    }

    public function execute_flow($id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $result = $this->flow_builder_model->execute_flow($id);

        echo json_encode($result);
    }

    public function get_flow_components()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $components = [
            'triggers' => [
                [
                    'id' => 'api_trigger',
                    'name' => _l('api_response'),
                    'description' => 'Triggered by API response',
                    'icon' => 'fa fa-exchange',
                    'color' => '#007bff'
                ]
            ],
            'conditions' => [
                [
                    'id' => 'condition',
                    'name' => _l('set_condition'),
                    'description' => 'Set condition based on response',
                    'icon' => 'fa fa-code-fork',
                    'color' => '#28a745'
                ]
            ],
            'actions' => [
                [
                    'id' => 'staff_create',
                    'name' => _l('staff_create'),
                    'description' => 'Create new staff member',
                    'icon' => 'fa fa-user-plus',
                    'color' => '#17a2b8'
                ],
                [
                    'id' => 'staff_update',
                    'name' => _l('staff_update'),
                    'description' => 'Update existing staff member',
                    'icon' => 'fa fa-user-edit',
                    'color' => '#17a2b8'
                ],
                [
                    'id' => 'ticket_create',
                    'name' => _l('ticket_create'),
                    'description' => 'Create new ticket',
                    'icon' => 'fa fa-plus-circle',
                    'color' => '#ffc107'
                ],
                [
                    'id' => 'ticket_update',
                    'name' => _l('ticket_update'),
                    'description' => 'Update existing ticket',
                    'icon' => 'fa fa-edit',
                    'color' => '#ffc107'
                ],
                [
                    'id' => 'division_create',
                    'name' => _l('division_create'),
                    'description' => 'Create new division',
                    'icon' => 'fa fa-building',
                    'color' => '#6f42c1'
                ],
                [
                    'id' => 'division_update',
                    'name' => _l('division_update'),
                    'description' => 'Update existing division',
                    'icon' => 'fa fa-edit',
                    'color' => '#6f42c1'
                ],
                [
                    'id' => 'department_create',
                    'name' => _l('department_create'),
                    'description' => 'Create new department',
                    'icon' => 'fa fa-sitemap',
                    'color' => '#e83e8c'
                ],
                [
                    'id' => 'department_update',
                    'name' => _l('department_update'),
                    'description' => 'Update existing department',
                    'icon' => 'fa fa-edit',
                    'color' => '#e83e8c'
                ],
                [
                    'id' => 'sms_send',
                    'name' => _l('sms_send'),
                    'description' => 'Send SMS message',
                    'icon' => 'fa fa-sms',
                    'color' => '#20c997'
                ],
                [
                    'id' => 'whatsapp_send',
                    'name' => _l('whatsapp_send'),
                    'description' => 'Send WhatsApp message',
                    'icon' => 'fa fa-whatsapp',
                    'color' => '#25d366'
                ],
                [
                    'id' => 'email_send',
                    'name' => _l('email_send'),
                    'description' => 'Send email',
                    'icon' => 'fa fa-envelope',
                    'color' => '#dc3545'
                ]
            ]
        ];

        echo json_encode($components);
    }

    public function get_api_response_fields()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Load the database forge library if needed
        // $this->load->dbforge();

        // Check if the api_response_mappings table exists
        if (!$this->db->table_exists('tbl_api_response_mappings')) {
            // Table doesn't exist, run the SQL file
            $this->run_api_response_mappings_sql();
        }

        $api_id = $this->input->get('api_id');

        if ($api_id) {
            // Get fields for specific API
            $this->db->where('external_api_id', $api_id);
            $this->db->order_by('field_path', 'ASC');
            $mappings = $this->db->get('tbl_api_response_mappings')->result();

            $fields = [];
            foreach ($mappings as $mapping) {
                $fields[$mapping->field_path] = $mapping->field_name;
            }

            // If no specific mappings found, return fallback fields
            if (empty($fields)) {
                $fields = $this->get_fallback_api_fields();
            }
        } else {
            // No API ID specified, return fallback fields
            $fields = $this->get_fallback_api_fields();
        }

        echo json_encode($fields);
    }

    private function run_api_response_mappings_sql()
    {
        // Read and execute the SQL file
        $sql_file = APPPATH . 'database/create_tbl_api_response_mappings.sql';
        if (file_exists($sql_file)) {
            $sql = file_get_contents($sql_file);
            $this->db->query($sql);
        }
    }

    private function get_fallback_api_fields()
    {
        // Return common fields that might be in API responses
        return [
            'status' => 'Status',
            'message' => 'Message',
            'data' => 'Data',
            'user_id' => 'User ID',
            'ticket_id' => 'Ticket ID',
            'staff_id' => 'Staff ID',
            'division_id' => 'Division ID',
            'department_id' => 'Department ID',
            'response_code' => 'Response Code',
            'timestamp' => 'Timestamp'
        ];
    }

    public function get_staff_fields()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $fields = [
            'firstname' => 'First Name',
            'lastname' => 'Last Name',
            'email' => 'Email',
            'phonenumber' => 'Phone Number',
            'password' => 'Password',
            'facebook' => 'Facebook',
            'linkedin' => 'LinkedIn',
            'skype' => 'Skype',
            'default_language' => 'Default Language',
            'direction' => 'Direction',
            'hourly_rate' => 'Hourly Rate',
            'two_factor_auth_enabled' => 'Two Factor Auth Enabled',
            'email_signature' => 'Email Signature'
        ];

        echo json_encode($fields);
    }

    public function get_ticket_fields()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $fields = [
            'subject' => 'Subject',
            'message' => 'Message',
            'userid' => 'User ID',
            'contactid' => 'Contact ID',
            'department' => 'Department',
            'priority' => 'Priority',
            'service' => 'Service',
            'status' => 'Status',
            'assigned' => 'Assigned',
            'cc' => 'CC',
            'admin' => 'Admin',
            'project_id' => 'Project ID'
        ];

        echo json_encode($fields);
    }

    public function get_division_fields()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $fields = [
            'name' => 'Name',
            'description' => 'Description',
            'status' => 'Status'
        ];

        echo json_encode($fields);
    }

    public function get_department_fields()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $fields = [
            'name' => 'Name',
            'description' => 'Description',
            'divisionid' => 'Division ID',
            'parent_department' => 'Parent Department',
            'responsible_staff' => 'Responsible Staff',
            'status' => 'Status'
        ];

        echo json_encode($fields);
    }
}
