<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Departments_api extends CI_Controller
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

    public function get_departments()
    {
        $method = $this->input->method();
        $api_key = $this->input->get_request_header('X-API-Key');
        $division_id = $this->input->get('division_id');

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

        // Check if division_id is provided
        if (!$division_id || !is_numeric($division_id)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'division_id parameter is required and must be numeric']));
            return;
        }

        // Verify division exists
        $this->db->where('divisionid', $division_id);
        $division_exists = $this->db->get('tbldivisions')->row();

        if (!$division_exists) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Division not found']));
            return;
        }

        // Get departments for the specified division using the junction table
        // Only show parent departments (those that don't have a parent themselves)
        $this->db->select('d.departmentid, d.name, d.parent_department');
        $this->db->from('tbldepartments d');
        $this->db->join('tbldepartment_divisions dd', 'dd.departmentid = d.departmentid');
        $this->db->where('dd.divisionid', $division_id);
        $this->db->where('(d.parent_department IS NULL OR d.parent_department = 0)');
        $this->db->order_by('d.name', 'ASC');
        $departments = $this->db->get()->result_array();

        // Get all department IDs for sub-department lookup
        $department_ids = array_column($departments, 'departmentid');

        // Get sub-departments for these departments
        $this->db->select('departmentid, name, parent_department');
        $this->db->from('tbldepartments');
        $this->db->where('parent_department !=', 0);
        $this->db->where_in('parent_department', $department_ids);
        $this->db->order_by('name', 'ASC');
        $all_sub_departments = $this->db->get()->result_array();

        // Group sub-departments by parent department
        $sub_departments_by_parent = [];
        foreach ($all_sub_departments as $sub) {
            if (!isset($sub_departments_by_parent[$sub['parent_department']])) {
                $sub_departments_by_parent[$sub['parent_department']] = [];
            }
            $sub_departments_by_parent[$sub['parent_department']][] = [
                'id' => (int)$sub['departmentid'],
                'name' => $sub['name']
            ];
        }

        // Format response data
        $response_data = [];
        foreach ($departments as $department) {
            $dept_data = [
                'departmentid' => (int)$department['departmentid'],
                'name' => $department['name'],
                'has_sub_departments' => !empty($sub_departments_by_parent[$department['departmentid']])
            ];

            // Add sub-departments list if any exist
            if (!empty($sub_departments_by_parent[$department['departmentid']])) {
                $dept_data['sub_departments'] = $sub_departments_by_parent[$department['departmentid']];
            }

            $response_data[] = $dept_data;
        }

        $response = json_encode([
            'success' => true,
            'message' => 'Departments retrieved successfully',
            'departments' => $response_data
        ]);

        $this->output
            ->set_content_type('application/json')
            ->set_output($response);

        // Log successful request
        log_api_request('/api/v1/get_departments', $method, $api_key, ['division_id' => $division_id], $response_data, 200);
    }

    private function check_permission($api_key, $perm)
    {
        $this->db->where('api_key', $api_key);
        $user = $this->db->get('tbl_api_users')->row();
        if (!$user) return false;
        return $user->{'perm_' . $perm} ? true : false;
    }
}
