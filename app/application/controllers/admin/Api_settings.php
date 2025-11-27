<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Api_settings extends AdminController
{
    /**
     * Runtime caches to avoid duplicate lookups while formatting API logs.
     */
    private $optionNameCache = [];
    private $staffNameCache = [];
    private $projectNameCache = [];

    public function __construct()
    {
        parent::__construct();

        if (!is_admin()) {
            access_denied('API Settings');
        }
    }

    public function index()
    {
        // Get search parameters
        $search_username = $this->input->get('search_username');
        $search_endpoint = $this->input->get('search_endpoint');
        $search_api_key = $this->input->get('search_api_key');
        $search_status = $this->input->get('search_status');

        // Build query with optional search filters
        $this->db->select('*')
            ->from('tbl_api_logs')
            ->order_by('created_at', 'DESC');

        // Apply search filters
        if (!empty($search_username)) {
            $this->db->like('username', $search_username);
        }
        if (!empty($search_endpoint)) {
            $this->db->like('endpoint', $search_endpoint);
        }
        if (!empty($search_api_key)) {
            $this->db->like('api_key', $search_api_key);
        }
        if (!empty($search_status)) {
            $this->db->where('status_code', $search_status);
        }

        $allLogs = $this->db->get()->result();

        // Add username field if missing (for backwards compatibility)
        foreach ($allLogs as &$log) {
            // Show actual response data if available, otherwise show a more descriptive message
            if (!empty($log->response_body)) {
                $log->formatted_response = $log->response_body;
            } else {
                $log->formatted_response = 'No response data available';
            }
            // Username should be pre-populated, fallback to N/A if missing
            if (!isset($log->username) || empty($log->username)) {
                $log->username = 'N/A';
            }
        }

        $logsPerPage = 25;
        $logsPage = max(1, (int)($this->input->get('logs_page') ?? 1));
        $totalLogs = is_array($allLogs) ? count($allLogs) : 0;
        $totalPages = $totalLogs > 0 ? (int)ceil($totalLogs / $logsPerPage) : 1;
        if ($logsPage > $totalPages) { $logsPage = $totalPages; }
        $logsOffset = ($logsPage - 1) * $logsPerPage;

        $data['api_logs'] = $totalLogs > 0 ? array_slice($allLogs, $logsOffset, $logsPerPage) : [];
        $data['api_logs_total'] = $totalLogs;
        $data['api_logs_page'] = $logsPage;
        $data['api_logs_pages'] = $totalPages;
        $data['api_logs_perpage'] = $logsPerPage;
        $data['api_logs_start'] = $totalLogs > 0 ? ($logsOffset + 1) : 0;
        $data['api_logs_end'] = $totalLogs > 0 ? min($logsOffset + $logsPerPage, $totalLogs) : 0;

        // Pass search parameters to view for form population
        $data['search_filters'] = [
            'search_username' => $search_username,
            'search_endpoint' => $search_endpoint,
            'search_api_key' => $search_api_key,
            'search_status' => $search_status,
        ];

        $data['title'] = 'API';

        $this->load->view('admin/api_settings', $data);
    }

    public function add_api_user()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $username = $this->input->post('username');
        $perm_get = $this->input->post('perm_get') == 'true' ? 1 : 0;
        $perm_post = $this->input->post('perm_post') == 'true' ? 1 : 0;

        if (!$username) {
            echo json_encode(['success' => false, 'message' => 'Username required']);
            return;
        }

        $api_key = $this->generate_api_key();

        $this->db->insert('tbl_api_users', [
            'username' => $username,
            'api_key' => $api_key,
            'perm_get' => $perm_get,
            'perm_post' => $perm_post
        ]);

        echo json_encode(['success' => true, 'username' => $username, 'api_key' => $api_key]);
    }

    private function generate_api_key()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    public function get_api_users()
    {
        log_message('debug', 'Api_settings get_api_users called');

        $this->db->order_by('created_at', 'DESC');
        $users = $this->db->get('tbl_api_users')->result();

        log_message('debug', 'Found ' . count($users) . ' user records');

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($users));
    }

    public function get_logs()
    {
        $this->db->order_by('created_at', 'DESC')->limit(50);
        $logs = $this->db->get('tbl_api_logs')->result();

        // Add username from API key lookup
        foreach ($logs as &$log) {
            // Set default values - show actual response data if available
            if (!empty($log->response_body)) {
                $log->formatted_response = $log->response_body;
            } else {
                $log->formatted_response = 'No response data available';
            }
            $log->username = 'N/A';

            // Lookup username if API key exists
            if (!empty($log->api_key)) {
                $this->db->where('api_key', $log->api_key);
                $user = $this->db->get('tbl_api_users')->row();
                if ($user && !empty($user->username)) {
                    $log->username = $user->username;
                }
            }
        }

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($logs));
    }

    private function format_api_response($response_body)
    {
        if (!$response_body) {
            return 'No response data';
        }

        $data = json_decode($response_body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $response_body; // Return raw if not JSON
        }

        // Check if this is ticket data by looking for ticket-specific fields
        if (isset($data['ticketid'])) {
            $data = $this->format_ticket_response($data);
        }

        return $this->encode_pretty_json($data);
    }

    private function encode_pretty_json($data)
    {
        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($encoded === false) {
            // Fallback to a basic encoding if the pretty print fails for any reason.
            $encoded = json_encode($data);
        }

        return $encoded;
    }

    private function format_ticket_response($ticket)
    {
        if (!is_array($ticket)) {
            return $ticket;
        }

        $formatted = $ticket;

        // Normalize text fields
        if (isset($formatted['subject'])) {
            $formatted['subject'] = $this->strip_html($formatted['subject']);
        }
        if (isset($formatted['message'])) {
            $formatted['message'] = $this->strip_html($formatted['message']);
        }

        // Replace option IDs with their display names
        $optionFields = [
            'status' => 'status',
            'priority' => 'priority',
            'department' => 'department',
            'service' => 'service',
            'divisionid' => 'division',
            'sub_department' => 'sub_department',
        ];

        foreach ($optionFields as $field => $type) {
            if (isset($formatted[$field]) && $formatted[$field] !== '' && $formatted[$field] !== null) {
                $formatted[$field] = $this->get_option_name($type, $formatted[$field]);
            }
        }

        // Rename fields after lookup for clarity
        if (isset($formatted['divisionid'])) {
            $formatted['division'] = $formatted['divisionid'];
            unset($formatted['divisionid']);
        }

        // Staff lookups for assigned/admin fields
        $staffFields = ['assigned', 'admin', 'adminreplying', 'staff_id_replying'];
        foreach ($staffFields as $field) {
            if (isset($formatted[$field]) && (string) $formatted[$field] !== '' && (string) $formatted[$field] !== '0') {
                $formatted[$field] = $this->get_staff_name($formatted[$field]);
            }
        }

        // Project lookup (replace ID with name and keep key intuitive)
        if (isset($formatted['project_id'])) {
            if ((string) $formatted['project_id'] !== '' && (string) $formatted['project_id'] !== '0') {
                $formatted['project'] = $this->get_project_name($formatted['project_id']);
            }
            unset($formatted['project_id']);
        }

        return $formatted;
    }

    private function get_option_name($type, $id)
    {
        // Define the mapping for different option types
        $tables = [
            'status' => 'tbltickets_status',
            'priority' => 'tbltickets_priorities',
            'department' => 'tbldepartments',
            'service' => 'tblservices',
            'division' => 'tbldivisions',
            'sub_department' => 'tbldepartments'  // sub_department is actually a department ID
        ];

        $name_fields = [
            'status' => 'name',
            'priority' => 'name',
            'department' => 'name',
            'service' => 'name',
            'division' => 'name',
            'sub_department' => 'name'
        ];

        $id_fields = [
            'status' => 'ticketstatusid',
            'priority' => 'priorityid',
            'department' => 'departmentid',
            'service' => 'serviceid',
            'division' => 'divisionid',
            'sub_department' => 'departmentid'
        ];

        if (!isset($tables[$type])) return $id;

        $cacheKey = $type . ':' . $id;
        if (isset($this->optionNameCache[$cacheKey])) {
            return $this->optionNameCache[$cacheKey];
        }

        $table = $tables[$type];
        $name_field = $name_fields[$type];
        $id_field = $id_fields[$type];

        try {
            $this->db->select($name_field)->from($table)->where($id_field, $id);
            $result = $this->db->get()->row();
            $value = $result ? $result->$name_field : $id;
            $this->optionNameCache[$cacheKey] = $value;
            return $value;
        } catch (Exception $e) {
            log_message('error', "get_option_name error - Type: $type, Table: $table, ID: $id, Error: " . $e->getMessage());
            return $id; // Return original ID if lookup fails
        }
    }

    private function get_staff_name($staff_id)
    {
        if ($staff_id === '' || $staff_id === null) {
            return $staff_id;
        }

        if (isset($this->staffNameCache[$staff_id])) {
            return $this->staffNameCache[$staff_id];
        }

        $this->db->select('CONCAT(firstname, " ", lastname) as full_name')
                 ->from('tblstaff')
                 ->where('staffid', $staff_id);
        $result = $this->db->get()->row();

        $value = $result ? $result->full_name : $staff_id;
        $this->staffNameCache[$staff_id] = $value;

        return $value;
    }

    private function get_project_name($project_id)
    {
        if ($project_id === '' || $project_id === null) {
            return $project_id;
        }

        if (isset($this->projectNameCache[$project_id])) {
            return $this->projectNameCache[$project_id];
        }

        $this->db->select('name')
                 ->from('tblprojects')
                 ->where('id', $project_id);
        $result = $this->db->get()->row();

        $value = $result ? $result->name : $project_id;
        $this->projectNameCache[$project_id] = $value;

        return $value;
    }

    private function strip_html($html)
    {
        return strip_tags($html);
    }

    public function get_filtered_logs()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Get search parameters from POST/GET
        $search_username = $this->input->post('search_username') ?: $this->input->get('search_username') ?: '';
        $search_endpoint = $this->input->post('search_endpoint') ?: $this->input->get('search_endpoint') ?: '';
        $search_api_key = $this->input->post('search_api_key') ?: $this->input->get('search_api_key') ?: '';
        $search_status = $this->input->post('search_status') ?: $this->input->get('search_status') ?: '';

        // Build query with optional search filters
        $this->db->select('*')
            ->from('tbl_api_logs')
            ->order_by('created_at', 'DESC');

        // Apply search filters
        if (!empty($search_username)) {
            $this->db->like('username', $search_username);
        }
        if (!empty($search_endpoint)) {
            $this->db->like('endpoint', $search_endpoint);
        }
        if (!empty($search_api_key)) {
            $this->db->like('api_key', $search_api_key);
        }
        if (!empty($search_status)) {
            $this->db->where('status_code', $search_status);
        }

        $logs = $this->db->get()->result();

        // Add essential fields for display
        foreach ($logs as &$log) {
            // Show actual response data if available, otherwise show a more descriptive message
            if (!empty($log->response_body)) {
                $log->formatted_response = $log->response_body;
            } else {
                $log->formatted_response = 'No response data available';
            }
            if (!isset($log->username) || empty($log->username)) {
                $log->username = 'N/A';
            }
        }

        // Pagination logic
        $logsPerPage = 25;
        $logsPage = max(1, (int)($this->input->post('page') ?: $this->input->get('logs_page') ?: 1));
        $totalLogs = count($logs);
        $totalPages = $totalLogs > 0 ? (int)ceil($totalLogs / $logsPerPage) : 1;
        if ($logsPage > $totalPages) { $logsPage = $totalPages; }
        $logsOffset = ($logsPage - 1) * $logsPerPage;

        $paginatedLogs = array_slice($logs, $logsOffset, $logsPerPage);

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode([
            'logs' => $paginatedLogs,
            'total' => $totalLogs,
            'pages' => $totalPages,
            'page' => $logsPage,
            'start' => $totalLogs > 0 ? ($logsOffset + 1) : 0,
            'end' => $totalLogs > 0 ? min($logsOffset + $logsPerPage, $totalLogs) : 0
        ]));
    }

    public function export_api_logs()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $format = $this->input->post('format') ?: 'csv';
        $searchUsername = $this->input->post('search_username');
        $searchEndpoint = $this->input->post('search_endpoint');
        $searchApiKey = $this->input->post('search_api_key');
        $searchStatus = $this->input->post('search_status');

        // Build query with same filters as index method
        $this->db->select('*')
            ->from('tbl_api_logs')
            ->order_by('created_at', 'DESC');

        // Apply search filters
        if (!empty($searchUsername)) {
            $this->db->like('username', $searchUsername);
        }
        if (!empty($searchEndpoint)) {
            $this->db->like('endpoint', $searchEndpoint);
        }
        if (!empty($searchApiKey)) {
            $this->db->like('api_key', $searchApiKey);
        }
        if (!empty($searchStatus)) {
            $this->db->where('status_code', $searchStatus);
        }

        $logs = $this->db->get()->result();

        // Add username field if missing (for backwards compatibility)
        foreach ($logs as &$log) {
            // Show actual response data if available, otherwise show a more descriptive message
            if (!empty($log->response_body)) {
                $log->formatted_response = $log->response_body;
            } else {
                $log->formatted_response = 'No response data available';
            }
            // Username should be pre-populated, fallback to N/A if missing
            if (!isset($log->username) || empty($log->username)) {
                $log->username = 'N/A';
            }
        }

        if (empty($logs)) {
            echo json_encode(['success' => false, 'message' => 'No data to export']);
            return;
        }

        require_once(APPPATH . 'libraries/Excel/Excel.php');
        $excel = new Excel();

        // CSV headers
        $headers = ['Date', 'Username', 'Endpoint', 'API Key', 'Status Code', 'Response Body'];

        $data = [];
        foreach ($logs as $log) {
            $data[] = [
                _dt($log->created_at),
                $log->username ?? 'N/A',
                $log->endpoint ?? '',
                $log->api_key ?? '',
                $log->status_code ?? '',
                $log->response_body ?? ''
            ];
        }

        $filename = 'api_logs_' . date('Y-m-d_H-i-s');

        if ($format === 'csv') {
            $excel->export_csv($data, $filename, $headers);
        } elseif ($format === 'xls') {
            $excel->export_xls($data, $filename, $headers);
        } elseif ($format === 'pdf') {
            $excel->export_pdf($data, $filename, $headers);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid format']);
            return;
        }

        echo json_encode(['success' => true, 'message' => 'Export completed']);
    }
}
