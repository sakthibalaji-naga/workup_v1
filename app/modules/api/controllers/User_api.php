<?php

defined('BASEPATH') or exit('No direct script access allowed');

class User_api extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper('general');
    }

    private function validate_api_key($api_key)
    {
        if (!$api_key) return false;
        $this->db->where('api_key', $api_key);
        $user = $this->db->get('tbl_api_users')->row();
        return $user ? true : false;
    }

    public function get_user_details()
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

        // Check if GET permission
        if (!$this->validate_api_key($api_key) || !$this->check_permission($api_key, 'get')) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Insufficient permissions']));
            return;
        }

        // Get userid parameter
        $userid = $this->input->get('userid');

        // Validate that userid parameter is provided
        if (!$userid || !is_numeric($userid)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'userid parameter is required and must be numeric']));
            return;
        }

        // Build query to find user by userid
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
            "" as sub_department_name
        ');
        $this->db->from('tblstaff s');
        $this->db->join('tblroles r', 'r.roleid = s.role', 'left');
        $this->db->join('tblstaff_departments sd', 'sd.staffid = s.staffid', 'left');
        $this->db->join('tbldepartments d', 'd.departmentid = sd.departmentid', 'left');
        $this->db->where('s.staffid', $userid);

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

        // Format response data
        $response_data = [
            'staffid' => (int)$user->staffid,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'fullname' => $user->firstname . ' ' . $user->lastname,
            'email' => $user->email,
            'phonenumber' => $user->phonenumber,
            'active' => (bool)$user->active,
            'datecreated' => $user->datecreated,
            'last_login' => $user->last_login,
            'role' => [
                'id' => $user->role_id ? (int)$user->role_id : null,
                'name' => $user->role_name
            ],
            'department' => $user->department_name,
            'sub_department' => $user->sub_department_name
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'User details retrieved successfully',
                'data' => $response_data
            ]));
    }

    private function check_permission($api_key, $perm)
    {
        $this->db->where('api_key', $api_key);
        $user = $this->db->get('tbl_api_users')->row();
        if (!$user) return false;
        return $user->{'perm_' . $perm} ? true : false;
    }
}
