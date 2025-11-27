<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Services_api extends CI_Controller
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

    public function get_services()
    {
        $method = $this->input->method();
        $api_key = $this->input->get_request_header('X-API-Key');
        $application_id = $this->input->get('application_id');
        $request_data = ['application_id' => $application_id];
        $status_code = 200;
        $response_data = null;

        if ($method !== 'get') {
            $status_code = 405;
            $response_data = ['error' => 'Method not allowed'];
            $this->output->set_status_header($status_code);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response_data));
            log_api_request('/api/v1/get_services', $method, $api_key, $request_data, $response_data, $status_code);
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
            log_api_request('/api/v1/get_services', $method, $api_key, $request_data, $response_data, $status_code);
            return;
        }

        // Check if application_id is provided
        if (!$application_id || !is_numeric($application_id)) {
            $status_code = 400;
            $response_data = ['error' => 'application_id parameter is required and must be numeric'];
            $this->output
                ->set_status_header($status_code)
                ->set_content_type('application/json')
                ->set_output(json_encode($response_data));
            log_api_request('/api/v1/get_services', $method, $api_key, $request_data, $response_data, $status_code);
            return;
        }

        // Verify application exists
        $this->db->where('id', $application_id);
        $application_exists = $this->db->get('tblapplications')->row();

        if (!$application_exists) {
            $status_code = 404;
            $response_data = ['error' => 'Application not found'];
            $this->output
                ->set_status_header($status_code)
                ->set_content_type('application/json')
                ->set_output(json_encode($response_data));
            log_api_request('/api/v1/get_services', $method, $api_key, $request_data, $response_data, $status_code);
            return;
        }

        // Get services for the specified application with related data
        $this->db->select('
            s.serviceid,
            s.name as service_name,
            s.responsible as responsible_id,
            CONCAT(st.firstname, " ", st.lastname) as responsible_name,
            s.divisionid,
            d.name as division_name,
            s.departmentid,
            dept.name as department_name,
            s.sub_department as sub_department_id,
            sub_dept.name as sub_department_name
        ');
        $this->db->from('tblservices s');
        $this->db->join('tblstaff st', 'st.staffid = s.responsible', 'left');
        $this->db->join('tbldivisions d', 'd.divisionid = s.divisionid', 'left');
        $this->db->join('tbldepartments dept', 'dept.departmentid = s.departmentid', 'left');
        $this->db->join('tbldepartments sub_dept', 'sub_dept.departmentid = s.sub_department', 'left');
        $this->db->where('s.applicationid', $application_id);
        $this->db->order_by('s.name', 'ASC');
        $services = $this->db->get()->result_array();

        // Format response data to include full object information
        $response_data_array = array_map(function($service) {
            return [
                'serviceid' => (int)$service['serviceid'],
                'name' => $service['service_name'],
                'responsible' => $service['responsible_id'] ? [
                    'id' => (int)$service['responsible_id'],
                    'name' => $service['responsible_name']
                ] : null,
                'division' => $service['divisionid'] ? [
                    'id' => (int)$service['divisionid'],
                    'name' => $service['division_name']
                ] : null,
                'department' => $service['departmentid'] ? [
                    'id' => (int)$service['departmentid'],
                    'name' => $service['department_name']
                ] : null,
                'sub_department' => $service['sub_department_id'] ? [
                    'id' => (int)$service['sub_department_id'],
                    'name' => $service['sub_department_name']
                ] : null
            ];
        }, $services);

        // Wrap in success response format
        $response_data = [
            'success' => true,
            'message' => 'Services retrieved successfully',
            'data' => $response_data_array
        ];

        $response = json_encode($response_data);

        $this->output
            ->set_content_type('application/json')
            ->set_output($response);

        // Log successful request
        log_api_request('/api/v1/get_services', $method, $api_key, $request_data, $response_data, $status_code);
    }

    private function check_permission($api_key, $perm)
    {
        $this->db->where('api_key', $api_key);
        $user = $this->db->get('tbl_api_users')->row();
        if (!$user) return false;
        return $user->{'perm_' . $perm} ? true : false;
    }
}
