<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Applications_api extends CI_Controller
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

    public function get_applications()
    {
        $method = $this->input->method();
        $api_key = $this->input->get_request_header('X-API-Key');
        $department_id = $this->input->get('department_id');
        $sub_department_id = $this->input->get('sub_department_id');

        if ($method !== 'get') {
            $this->output->set_status_header(405);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Method not allowed']));
            return;
        }

        // Check if GET permission
        if (!$this->check_permission($api_key, 'get')) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Insufficient permissions']));
            return;
        }

        // Check if department_id is provided
        if (!$department_id || !is_numeric($department_id)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'department_id parameter is required and must be numeric']));
            return;
        }

        // Verify department exists
        $this->db->where('departmentid', $department_id);
        $department_exists = $this->db->get('tbldepartments')->row();

        if (!$department_exists) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Department not found']));
            return;
        }

        // Get applications based on services for the department (matching ticket creation page logic)
        $this->db->select('DISTINCT(app.id), app.name, s.departmentid, s.sub_department');
        $this->db->from('tblservices s');
        $this->db->join('tblapplications app', 'app.id = s.applicationid', 'inner');
        $this->db->where('s.departmentid', $department_id);
        if ($sub_department_id && is_numeric($sub_department_id)) {
            $this->db->where('s.sub_department', $sub_department_id);
        }
        $this->db->order_by('app.name', 'ASC');
        $applications = $this->db->get()->result_array();

        // Format response data to include relevant fields only
        $response_data = array_map(function($application) {
            return [
                'id' => (int)$application['id'],
                'name' => $application['name'],
                'department_id' => (int)$application['departmentid'],
                'sub_department_id' => $application['sub_department'] ? (int)$application['sub_department'] : null
            ];
        }, $applications);

        // Wrap in success response format
        $response = json_encode([
            'success' => true,
            'message' => 'Applications retrieved successfully',
            'applications' => $response_data
        ]);

        $this->output
            ->set_content_type('application/json')
            ->set_output($response);

        // Log successful request
        log_api_request('/api/v1/get_applications', $method, $api_key, [
            'department_id' => $department_id,
            'sub_department_id' => $sub_department_id
        ], $response_data, 200);
    }

    private function check_permission($api_key, $perm)
    {
        $this->db->where('api_key', $api_key);
        $user = $this->db->get('tbl_api_users')->row();
        if (!$user) return false;
        return $user->{'perm_' . $perm} ? true : false;
    }
}
