<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron_api_settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!is_admin()) {
            access_denied('Cron API Settings');
        }
    }

    public function index()
    {
        try {
            // Get search parameters
            $search_name = $this->input->get('search_name');
            $search_url = $this->input->get('search_url');
            $search_status = $this->input->get('search_status');

            // Check if database tables exist
            if (!$this->db->table_exists('tbl_external_apis')) {
                $data['error'] = 'Database table "tbl_external_apis" not found. Please run the SQL file to create the required tables.';
                $data['sql_file'] = 'src/application/database/create_tbl_cron_apis.sql';
                $data['title'] = 'Cron API Settings';
                $this->load->view('admin/cron_api_settings', $data);
                return;
            }

            // Build query with optional search filters
            $this->db->select('*')
                ->from('tbl_external_apis')
                ->order_by('created_at', 'DESC');

            // Apply search filters
            if (!empty($search_name)) {
                $this->db->like('name', $search_name);
            }
            if (!empty($search_url)) {
                $this->db->like('api_url', $search_url);
            }
            if (!empty($search_status)) {
                $this->db->where('is_active', $search_status === 'active' ? 1 : 0);
            }

            $allApis = $this->db->get()->result();

            $apisPerPage = 25;
            $apisPage = max(1, (int)($this->input->get('apis_page') ?? 1));
            $totalApis = is_array($allApis) ? count($allApis) : 0;
            $totalPages = $totalApis > 0 ? (int)ceil($totalApis / $apisPerPage) : 1;
            if ($apisPage > $totalPages) { $apisPage = $totalPages; }
            $apisOffset = ($apisPage - 1) * $apisPerPage;

            $data['cron_apis'] = $totalApis > 0 ? array_slice($allApis, $apisOffset, $apisPerPage) : [];
            $data['cron_apis_total'] = $totalApis;
            $data['cron_apis_page'] = $apisPage;
            $data['cron_apis_pages'] = $totalPages;
            $data['cron_apis_perpage'] = $apisPerPage;
            $data['cron_apis_start'] = $totalApis > 0 ? ($apisOffset + 1) : 0;
            $data['cron_apis_end'] = $totalApis > 0 ? min($apisOffset + $apisPerPage, $totalApis) : 0;

            // Pass search parameters to view for form population
            $data['search_filters'] = [
                'search_name' => $search_name,
                'search_url' => $search_url,
                'search_status' => $search_status,
            ];

            $data['title'] = 'Cron API Settings';

            $this->load->view('admin/cron_api_settings', $data);

        } catch (Exception $e) {
            $data['error'] = 'Error loading cron API settings: ' . $e->getMessage();
            $data['title'] = 'Cron API Settings';
            $this->load->view('admin/cron_api_settings', $data);
        }
    }

    public function add_cron_api()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $name = $this->input->post('name');
        $api_url = $this->input->post('api_url');
        $request_method = $this->input->post('request_method');
        $request_body = $this->input->post('request_body');
        $headers = $this->input->post('headers');
        $cron_schedule = $this->input->post('cron_schedule');
        $is_active = $this->input->post('is_active') == 'true' ? 1 : 0;

        if (!$name || !$api_url || !$cron_schedule) {
            echo json_encode(['success' => false, 'message' => 'Name, API URL, and Cron Schedule are required']);
            return;
        }

        $this->db->insert('tbl_external_apis', [
            'name' => $name,
            'api_url' => $api_url,
            'request_method' => $request_method ?: 'GET',
            'request_body' => $request_body,
            'headers' => $headers,
            'cron_schedule' => $cron_schedule,
            'is_active' => $is_active,
            'created_at' => date('Y-m-d H:i:s'),
            'last_run' => null,
            'next_run' => $this->calculate_next_run($cron_schedule)
        ]);

        echo json_encode(['success' => true, 'message' => 'Cron API added successfully']);
    }

    public function get_cron_apis()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->db->order_by('created_at', 'DESC');
        $apis = $this->db->get('tbl_external_apis')->result();

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($apis));
    }

    public function get_cron_api_logs()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $api_id = $this->input->get('api_id');

        // Check if tables exist first
        if (!$this->db->table_exists('tbl_external_api_logs') || !$this->db->table_exists('tbl_external_apis')) {
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'error' => 'Database tables not found. Please run the SQL file to create the required tables.',
                'sql_file' => 'src/application/database/create_tbl_cron_apis.sql'
            ]));
            return;
        }

        $this->db->select('
            logs.*,
            apis.name as api_name,
            apis.api_url as api_url,
            apis.is_active as is_active
        ')
        ->from('tbl_external_api_logs as logs')
        ->join('tbl_external_apis as apis', 'logs.external_api_id = apis.id', 'left')
        ->order_by('logs.created_at', 'DESC')
        ->limit(50);

        if ($api_id) {
            $this->db->where('logs.external_api_id', $api_id);
        }

        $logs = $this->db->get()->result();

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($logs));
    }

    public function update_cron_api()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id');
        $name = $this->input->post('name');
        $api_url = $this->input->post('api_url');
        $request_method = $this->input->post('request_method');
        $request_body = $this->input->post('request_body');
        $headers = $this->input->post('headers');
        $cron_schedule = $this->input->post('cron_schedule');
        $is_active = $this->input->post('is_active') == 'true' ? 1 : 0;

        if (!$id || !$name || !$api_url || !$cron_schedule) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            return;
        }

        $update_data = [
            'name' => $name,
            'api_url' => $api_url,
            'request_method' => $request_method ?: 'GET',
            'request_body' => $request_body,
            'headers' => $headers,
            'cron_schedule' => $cron_schedule,
            'is_active' => $is_active,
            'next_run' => $this->calculate_next_run($cron_schedule)
        ];

        $this->db->where('id', $id)->update('tbl_external_apis', $update_data);

        echo json_encode(['success' => true, 'message' => 'Cron API updated successfully']);
    }

    public function delete_cron_api()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id');

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID is required']);
            return;
        }

        $this->db->where('id', $id)->delete('tbl_external_apis');

        echo json_encode(['success' => true, 'message' => 'Cron API deleted successfully']);
    }

    public function execute_cron_api()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id');

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID is required']);
            return;
        }

        // Get API details
        $api = $this->db->where('id', $id)->get('tbl_external_apis')->row();

        if (!$api) {
            echo json_encode(['success' => false, 'message' => 'API not found']);
            return;
        }

        try {
            // Execute the API call
            $result = $this->execute_api_call($api);

            // Update last run time only for manual executions
            // next_run should only be updated when the scheduled cron runs
            $this->db->where('id', $id)->update('tbl_external_apis', [
                'last_run' => date('Y-m-d H:i:s')
            ]);

            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error executing API: ' . $e->getMessage(),
                'http_code' => 500
            ]);
        }
    }

    private function execute_api_call($api)
    {
        try {
            $ch = curl_init();

            if ($ch === false) {
                throw new Exception('Failed to initialize cURL');
            }

            curl_setopt($ch, CURLOPT_URL, $api->api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development/testing
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // For development/testing

            // Set request method and data
            if ($api->request_method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($api->request_body)) {
                    // Ensure request body is properly formatted JSON
                    $json_data = json_decode($api->request_body, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json_data));
                    } else {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $api->request_body);
                    }
                }
            } elseif ($api->request_method === 'PUT') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if (!empty($api->request_body)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $api->request_body);
                }
            } elseif ($api->request_method === 'DELETE') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            }

            // Set default headers for JSON APIs
            $default_headers = [
                'Content-Type: application/json',
                'Accept: application/json'
            ];

            // Set custom headers if provided
            if (!empty($api->headers)) {
                $custom_headers = json_decode($api->headers, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($custom_headers)) {
                    foreach ($custom_headers as $key => $value) {
                        $default_headers[] = "$key: $value";
                    }
                }
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $default_headers);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $curl_info = curl_getinfo($ch);

            curl_close($ch);

            // Log the API call with more details
            $this->log_cron_api_call($api->id, $http_code, $response, $error);

            if ($error) {
                return [
                    'success' => false,
                    'message' => 'API call failed: ' . $error,
                    'http_code' => $http_code,
                    'curl_info' => $curl_info
                ];
            }

            return [
                'success' => true,
                'message' => 'API call executed successfully',
                'http_code' => $http_code,
                'response' => $response,
                'curl_info' => $curl_info
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'cURL error: ' . $e->getMessage(),
                'http_code' => 500
            ];
        }
    }

    private function log_cron_api_call($api_id, $status_code, $response, $error = null)
    {
        $this->db->insert('tbl_external_api_logs', [
            'external_api_id' => $api_id,
            'status_code' => $status_code,
            'response_body' => $response,
            'error_message' => $error,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function calculate_next_run($cron_schedule)
    {
        // Simple cron schedule parser - you might want to use a more robust library
        // This is a basic implementation for common cron formats
        try {
            // For now, we'll use a simple approach - you can enhance this
            $parts = explode(' ', trim($cron_schedule));
            if (count($parts) >= 5) {
                // Calculate next run based on cron expression
                // This is a simplified calculation
                return date('Y-m-d H:i:s', strtotime('+1 hour')); // Default to 1 hour
            }
        } catch (Exception $e) {
            // Fallback
        }

        return date('Y-m-d H:i:s', strtotime('+1 hour'));
    }


}
