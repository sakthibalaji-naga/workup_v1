<?php

defined('BASEPATH') or exit('No direct script access allowed');

class External_api_settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!is_admin()) {
            access_denied('External API Settings');
        }
    }

    public function index()
    {
        // Get search parameters
        $search_name = $this->input->get('search_name');
        $search_url = $this->input->get('search_url');
        $search_status = $this->input->get('search_status');

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

        $data['external_apis'] = $totalApis > 0 ? array_slice($allApis, $apisOffset, $apisPerPage) : [];
        $data['external_apis_total'] = $totalApis;
        $data['external_apis_page'] = $apisPage;
        $data['external_apis_pages'] = $totalPages;
        $data['external_apis_perpage'] = $apisPerPage;
        $data['external_apis_start'] = $totalApis > 0 ? ($apisOffset + 1) : 0;
        $data['external_apis_end'] = $totalApis > 0 ? min($apisOffset + $apisPerPage, $totalApis) : 0;

        // Pass search parameters to view for form population
        $data['search_filters'] = [
            'search_name' => $search_name,
            'search_url' => $search_url,
            'search_status' => $search_status,
        ];

        $data['title'] = 'External API Settings';

        $this->load->view('admin/external_api_settings', $data);
    }

    public function add_external_api()
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

        echo json_encode(['success' => true, 'message' => 'External API added successfully']);
    }

    public function get_external_apis()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->db->order_by('created_at', 'DESC');
        $apis = $this->db->get('tbl_external_apis')->result();

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($apis));
    }

    public function update_external_api()
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

        echo json_encode(['success' => true, 'message' => 'External API updated successfully']);
    }

    public function delete_external_api()
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

        echo json_encode(['success' => true, 'message' => 'External API deleted successfully']);
    }

    public function execute_external_api()
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

        // Execute the API call
        $result = $this->execute_api_call($api);

        // Parse and store API response fields for flow builder
        if ($result['success'] && isset($result['response'])) {
            $this->parse_and_store_api_response_fields($id, $result['response']);
        }

        // Update last run time
        $this->db->where('id', $id)->update('tbl_external_apis', [
            'last_run' => date('Y-m-d H:i:s'),
            'next_run' => $this->calculate_next_run($api->cron_schedule)
        ]);

        echo json_encode($result);
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
            $this->log_external_api_call($api->id, $http_code, $response, $error);

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

    private function log_external_api_call($api_id, $status_code, $response, $error = null)
    {
        // Get API details for logging
        $api = $this->db->where('id', $api_id)->get('tbl_external_apis')->row();

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

    private function parse_and_store_api_response_fields($api_id, $response)
    {
        try {
            if (!$this->db->table_exists('tbl_api_response_mappings')) {
                $sql_file = APPPATH . 'database/create_tbl_api_response_mappings.sql';
                if (file_exists($sql_file)) {
                    $sql = file_get_contents($sql_file);
                    $this->db->query($sql);
                }
            }
            $json_data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return; // Not valid JSON, skip parsing
            }

            // Clear existing mappings for this API
            $this->db->where('external_api_id', $api_id)->delete('tbl_api_response_mappings');

            // Parse the JSON structure recursively
            $mappings = $this->parse_json_structure($json_data);

            // Store the mappings
            foreach ($mappings as $mapping) {
                $this->db->insert('tbl_api_response_mappings', [
                    'external_api_id' => $api_id,
                    'field_path' => $mapping['path'],
                    'field_name' => $mapping['name'],
                    'field_type' => $mapping['type'],
                    'is_array_element' => $mapping['is_array_element'],
                    'array_index' => $mapping['array_index'],
                    'parent_path' => $mapping['parent_path'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (Exception $e) {
            // Log the error but don't interrupt the API execution
            error_log('Failed to parse API response fields: ' . $e->getMessage());
        }
    }

    private function parse_json_structure($data, $current_path = '', $parent_path = '', $depth = 0)
    {
        $mappings = [];
        $max_depth = 15; // Allow deeper parsing for complex structures

        if ($depth > $max_depth) {
            return $mappings;
        }

        // Handle objects by converting to array first
        if (is_object($data)) {
            $data = (array)$data;
        }

        if (is_array($data)) {
            // Check if it's an associative array (object-like) or indexed array
            $keys = array_keys($data);
            $is_associative = $keys !== range(0, count($data) - 1);

            if ($is_associative) {
                // Associative array - treat as object properties
                foreach ($data as $key => $value) {
                    $new_path = $current_path ? $current_path . '.' . $key : $key;

                    $mappings[] = [
                        'path' => $new_path,
                        'name' => $this->create_field_name($key),
                        'type' => $this->get_field_type($value),
                        'is_array_element' => 0,
                        'array_index' => null,
                        'parent_path' => $parent_path
                    ];

                    // Recursively parse nested structures (arrays/objects)
                    if (is_array($value) || is_object($value)) {
                        $nested_mappings = $this->parse_json_structure(
                            $value,
                            $new_path,
                            $current_path ?: '',
                            $depth + 1
                        );
                        $mappings = array_merge($mappings, $nested_mappings);
                    }
                }
            } else {
                // Indexed array
                $array_name = $this->create_field_name($this->extract_base_name($current_path) ?: 'Items');

                // Add the array itself
                $mappings[] = [
                    'path' => $current_path ?: 'root',
                    'name' => $array_name,
                    'type' => 'array',
                    'is_array_element' => 0,
                    'array_index' => null,
                    'parent_path' => $parent_path
                ];

                // Process array elements if they exist
                if (!empty($data)) {
                    $first_element = $data[0];

                    if (is_array($first_element) || is_object($first_element)) {
                        // Complex objects in array - parse the first one as template
                        $element_path = ($current_path ?: 'root') . '[0]';

                        $mappings[] = [
                            'path' => $element_path,
                            'name' => $array_name . ' Item',
                            'type' => 'object',
                            'is_array_element' => 1,
                            'array_index' => 0,
                            'parent_path' => $current_path ?: 'root'
                        ];

                        // Recursively parse the structure of the first array element
                        $nested_mappings = $this->parse_json_structure(
                            $first_element,
                            $element_path,
                            $current_path ?: 'root',
                            $depth + 1
                        );
                        $mappings = array_merge($mappings, $nested_mappings);
                    } elseif (is_scalar($first_element)) {
                        // Simple scalar array elements
                        $element_path = ($current_path ?: 'root') . '[0]';

                        $mappings[] = [
                            'path' => $element_path,
                            'name' => $array_name . ' Item',
                            'type' => $this->get_field_type($first_element),
                            'is_array_element' => 1,
                            'array_index' => 0,
                            'parent_path' => $current_path ?: 'root'
                        ];
                    }
                }
            }
        } else {
            // Scalar values - only add if this is a named field, not root
            if (!empty($current_path) && $current_path !== 'root') {
                $mappings[] = [
                    'path' => $current_path,
                    'name' => $this->create_field_name($this->extract_base_name($current_path)),
                    'type' => $this->get_field_type($data),
                    'is_array_element' => 0,
                    'array_index' => null,
                    'parent_path' => $parent_path
                ];
            }
        }

        return $mappings;
    }

    private function extract_base_name($path)
    {
        if (empty($path)) return '';

        // Extract the last part of the path
        $parts = explode('.', $path);
        $last_part = end($parts);

        // Remove array notation if present
        $last_part = preg_replace('/\[\d+\]$/', '', $last_part);

        return $last_part;
    }

    private function create_field_name($key, $parent_path = '')
    {
        // Convert camelCase, snake_case, and other formats to readable names
        $name = preg_replace('/(?<!^)[A-Z]/', ' $0', $key); // camelCase to spaces
        $name = str_replace(['_', '-'], ' ', $name); // Replace underscores and dashes
        $name = ucwords(strtolower($name)); // Capitalize words

        // If it's in an array or has a parent path, add context
        if ($parent_path) {
            $name = $parent_path . ' > ' . $name;
        }

        return $name;
    }

    private function get_field_type($value)
    {
        if (is_int($value) || is_float($value)) {
            return 'number';
        } elseif (is_bool($value)) {
            return 'boolean';
        } elseif (is_string($value)) {
            return 'string';
        } elseif (is_array($value)) {
            return 'array';
        } elseif (is_object($value)) {
            return 'object';
        } else {
            return 'string'; // Default fallback
        }
    }

    public function get_external_api_logs()
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
}
