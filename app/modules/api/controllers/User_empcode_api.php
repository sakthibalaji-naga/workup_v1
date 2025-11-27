<?php

defined('BASEPATH') or exit('No direct script access allowed');

class User_empcode_api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper('general');
    }

    private function validate_api_key(?string $api_key): bool
    {
        if (!$api_key) {
            return false;
        }

        $this->db->where('api_key', $api_key);
        $user = $this->db->get('tbl_api_users')->row();

        return $user ? true : false;
    }

    private function check_permission(?string $api_key, string $perm): bool
    {
        if (!$api_key) {
            return false;
        }

        $this->db->where('api_key', $api_key);
        $user = $this->db->get('tbl_api_users')->row();

        if (!$user) {
            return false;
        }

        return !empty($user->{'perm_' . $perm});
    }

    public function get_user_details_by_emp_code(): void
    {
        $method = $this->input->method();
        $api_key = $this->input->get_request_header('X-API-Key');

        if ($method !== 'get') {
            $this->output->set_status_header(405);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Method not allowed']));
            return;
        }

        if (!$this->validate_api_key($api_key) || !$this->check_permission($api_key, 'get')) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Insufficient permissions']));
            return;
        }

        $emp_code_param = $this->input->get('emp_code');
        if ($emp_code_param === null) {
            $emp_code_param = $this->input->get('empcode');
        }

        $emp_code = is_string($emp_code_param) ? trim($emp_code_param) : '';

        if ($emp_code === '') {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'emp_code parameter is required']));
            return;
        }

        try {
            $empCodeField = $this->db
                ->select('id')
                ->where(['slug' => 'staff_emp_code', 'fieldto' => 'staff'])
                ->get(db_prefix() . 'customfields')
                ->row();
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Failed to look up employee code field', 'details' => $e->getMessage()]));
            return;
        }

        if (!$empCodeField) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Employee code field is not configured']));
            return;
        }

        $this->db->select('
            s.staffid,
            s.firstname,
            s.lastname,
            s.email,
            s.phonenumber,
            s.active,
            s.datecreated,
            s.last_login,
            s.role as role_id,
            r.name as role_name,
            d.name as department_name,
            "" as sub_department_name,
            cfv.value as staff_emp_code
        ');
        $this->db->from(db_prefix() . 'staff s');
        $this->db->join(db_prefix() . 'roles r', 'r.roleid = s.role', 'left');
        $this->db->join(db_prefix() . 'staff_departments sd', 'sd.staffid = s.staffid', 'left');
        $this->db->join(db_prefix() . 'departments d', 'd.departmentid = sd.departmentid', 'left');
        $this->db->join(
            db_prefix() . 'customfieldsvalues cfv',
            'cfv.relid = s.staffid AND cfv.fieldto = "staff" AND cfv.fieldid = ' . (int) $empCodeField->id,
            'left'
        );
        $this->db->where('cfv.value', $emp_code);
        $this->db->limit(1);

        try {
            $user = $this->db->get()->row();
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Database query failed', 'details' => $e->getMessage()]));
            return;
        }

        if (!$user) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'User not found']));
            return;
        }

        $response_data = [
            'staffid' => (int) $user->staffid,
            'emp_code' => $user->staff_emp_code,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'fullname' => trim($user->firstname . ' ' . $user->lastname),
            'email' => $user->email,
            'phonenumber' => $user->phonenumber,
            'active' => (bool) $user->active,
            'datecreated' => $user->datecreated,
            'last_login' => $user->last_login,
            'role' => [
                'id' => $user->role_id ? (int) $user->role_id : null,
                'name' => $user->role_name,
            ],
            'department' => $user->department_name,
            'sub_department' => $user->sub_department_name,
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'User details retrieved successfully',
                'data' => $response_data,
            ]));

        // Log successful request
        log_api_request('/api/v1/get_user_details_by_emp_code', $method, $api_key, ['emp_code' => $emp_code], $response_data, 200);
    }
}
