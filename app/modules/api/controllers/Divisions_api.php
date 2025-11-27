<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Divisions_api extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    private function validate_api_key($api_key)
    {
        if (!$api_key) return false;
        $this->db->where('api_key', $api_key);
        $user = $this->db->get('tbl_api_users')->row();
        return $user ? true : false;
    }

    public function get_divisions()
    {
        // Enhanced logging for production debugging
        $start_time = microtime(true);
        $request_id = uniqid('div_api_');

        log_message('info', "[$request_id] === DIVISIONS API REQUEST START ===");
        log_message('info', "[$request_id] Request Method: " . $this->input->method());
        log_message('info', "[$request_id] Request URI: " . $this->uri->uri_string());
        log_message('info', "[$request_id] Client IP: " . $this->input->ip_address());

        try {
            $method = $this->input->method();
            $api_key = $this->input->get_request_header('X-API-Key');

            log_message('info', "[$request_id] API Key provided: " . ($api_key ? 'YES (length: ' . strlen($api_key) . ')' : 'NO'));

            // Validate HTTP method
            if ($method !== 'get') {
                log_message('warning', "[$request_id] Invalid HTTP method: $method");
                $this->output->set_status_header(405);
                $error_response = ['error' => 'Method not allowed'];
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($error_response));
                log_message('info', "[$request_id] Response: 405 Method not allowed");
                return;
            }

            // Validate API key exists
            if (!$api_key) {
                log_message('warning', "[$request_id] No API key provided");
                $this->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'API key required']));
                log_message('info', "[$request_id] Response: 401 API key required");
                return;
            }

            // Validate API key in database
            log_message('info', "[$request_id] Validating API key...");
            if (!$this->validate_api_key($api_key)) {
                log_message('warning', "[$request_id] Invalid API key: " . substr($api_key, 0, 10) . "...");
                $this->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Invalid API key']));
                log_message('info', "[$request_id] Response: 401 Invalid API key");
                return;
            }
            log_message('info', "[$request_id] API key validation: SUCCESS");

            // Check if GET permission
            log_message('info', "[$request_id] Checking GET permissions...");
            if (!$this->check_permission($api_key, 'get')) {
                log_message('warning', "[$request_id] User does not have GET permission");
                $this->output
                    ->set_status_header(403)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Insufficient permissions']));
                log_message('info', "[$request_id] Response: 403 Insufficient permissions");
                return;
            }
            log_message('info', "[$request_id] Permission check: SUCCESS");

            // Check if divisions table exists
            log_message('info', "[$request_id] Checking if divisions table exists...");
            if (!$this->db->table_exists('tbldivisions')) {
                log_message('error', "[$request_id] CRITICAL: tbldivisions table does not exist!");
                $this->output
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Divisions table not found']));
                log_message('info', "[$request_id] Response: 500 Divisions table not found");
                return;
            }
            log_message('info', "[$request_id] Table check: SUCCESS");

            // Test database connection
            log_message('info', "[$request_id] Testing database query...");
            $divisions = $this->db->get('tbldivisions')->result_array();

            if ($divisions === false) {
                log_message('error', "[$request_id] Database query failed. Last error: " . $this->db->error()['message']);
                $this->output
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Database query failed']));
                log_message('info', "[$request_id] Response: 500 Database query failed");
                return;
            }

            $division_count = count($divisions);
            log_message('info', "[$request_id] Query successful. Found $division_count divisions");

            // Format response data to include relevant fields only
            $response_data = array_map(function($division) {
                return [
                    'divisionid' => (int)$division['divisionid'],
                    'name' => $division['name']
                ];
            }, $divisions);

            $response = json_encode([
                'success' => true,
                'message' => 'Divisions retrieved successfully',
                'divisions' => $response_data
            ]);

            $end_time = microtime(true);
            $execution_time = round(($end_time - $start_time) * 1000, 2);

            $this->output
                ->set_content_type('application/json')
                ->set_output($response);

            log_message('info', "[$request_id] Response: 200 SUCCESS (Execution time: {$execution_time}ms)");
            log_message('info', "[$request_id] === DIVISIONS API REQUEST END ===");

            // Log successful request
            log_api_request('/api/v1/get_divisions', $method, $api_key, [], $response_data, 200);

        } catch (Exception $e) {
            $end_time = microtime(true);
            $execution_time = round(($end_time - $start_time) * 1000, 2);

            log_message('error', "[$request_id] EXCEPTION in Divisions API: " . $e->getMessage());
            log_message('error', "[$request_id] File: " . $e->getFile() . " Line: " . $e->getLine());
            log_message('error', "[$request_id] Stack trace: " . $e->getTraceAsString());
            log_message('info', "[$request_id] Response: 500 Internal server error (Execution time: {$execution_time}ms)");

            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Internal server error']));
        }
    }

    private function check_permission($api_key, $perm)
    {
        $this->db->where('api_key', $api_key);
        $user = $this->db->get('tbl_api_users')->row();
        if (!$user) return false;
        return $user->{'perm_' . $perm} ? true : false;
    }
}
