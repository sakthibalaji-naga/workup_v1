<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Groups extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('group_model');

        if (!is_admin()) {
            access_denied('Groups');
        }
    }

    /* List all groups */
    public function index()
    {
        // Groups listing
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('groups');
        }

        $data['title'] = 'Groups';
        $this->load->view('admin/groups/manage', $data);
    }

    /* Edit or add new group */
    public function group($id = '')
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $message = '';

            // Get leader's department, division, sub_department
            $this->load->model('staff_model');
            $leader = $this->staff_model->get($data['leader_id']);

            if ($leader) {
                $data['division_id'] = (int) ($leader->divisionid ?? 0);

                $deptInfo = $this->group_model->get_leader_department_info($leader->staffid);
                $data['department_id'] = !empty($deptInfo['department_ids']) ? (int) $deptInfo['department_ids'][0] : 0;
                $data['sub_department_id'] = !empty($deptInfo['sub_department_ids']) ? (int) $deptInfo['sub_department_ids'][0] : null;
            }

            $data['leader_id'] = (int) $data['leader_id'];
            $data['division_id'] = (int) ($data['division_id'] ?? 0);
            $data['department_id'] = (int) ($data['department_id'] ?? 0);
            $data['sub_department_id'] = $data['sub_department_id'] !== null ? (int) $data['sub_department_id'] : null;
            unset($data['division'], $data['department'], $data['sub_department']);

            if (!$this->input->post('id')) {
                $id = $this->group_model->add($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('group'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->group_model->update($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('group'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
            die;
        }
    }

    /* Delete group from database */
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('groups'));
        }
        $response = $this->group_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('group_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('group')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('group_lowercase')));
        }
        redirect(admin_url('groups'));
    }

    public function get_staff()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->db->select('staffid, firstname, lastname');
        $this->db->from(db_prefix().'staff');
        $this->db->where('active', 1);
        if (get_option('access_tickets_to_none_staff_members') == 0) {
            $this->db->where('is_not_staff', 0);
        }
        $this->db->order_by('firstname', 'asc');
        $staff = $this->db->get()->result_array();

        $out = [];
        foreach ($staff as $s) {
            $out[] = [
                'value' => $s['staffid'],
                'label' => $s['firstname'] . ' ' . $s['lastname'],
            ];
        }

        echo json_encode($out);
        die;
    }

    public function staff_by_leader_department()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $leader_id = $this->input->post('leader_id');
        if (!$leader_id) {
            echo json_encode([]);
            die;
        }

        // Get current group id for edit mode
        $current_group_id = $this->input->post('group_id') ? (int)$this->input->post('group_id') : 0;

        // Get leader's department
        $this->db->select('departmentid');
        $this->db->from(db_prefix().'staff_departments');
        $this->db->where('staffid', (int)$leader_id);
        $dept_row = $this->db->get()->row();
        if (!$dept_row) {
            echo json_encode([]);
            die;
        }

        $department_id = $dept_row->departmentid;

        // Get staff in department except leader and exclude staff already in other groups (but allow if in current group)
        $this->db->select('st.staffid, st.firstname, st.lastname');
        if ($this->db->field_exists('employee_code', db_prefix() . 'staff')) {
            $this->db->select('st.employee_code');
        }
        $this->db->from(db_prefix().'staff as st');
        $this->db->join(db_prefix().'staff_departments sd', 'sd.staffid = st.staffid');
        $this->db->where('sd.departmentid', $department_id);
        $this->db->order_by('st.firstname', 'asc');
        $this->db->order_by('st.lastname', 'asc');
        $this->db->where('st.staffid !=', (int)$leader_id);
        $this->db->where('st.active', 1);
        if (get_option('access_tickets_to_none_staff_members') == 0) {
            $this->db->where('st.is_not_staff', 0);
        }
        // Exclude staff who are members of other groups, but allow if they are members of the current group
        if ($current_group_id > 0) {
            // For edit mode, allow members already in this group
            $this->db->where('NOT EXISTS (SELECT 1 FROM ' . db_prefix() . 'group_members gm WHERE gm.member_id = st.staffid AND gm.group_id != ' . $current_group_id . ')', null, false);
        } else {
            // For new group, exclude all who are in any group
            $this->db->where('NOT EXISTS (SELECT 1 FROM ' . db_prefix() . 'group_members gm WHERE gm.member_id = st.staffid)', null, false);
        }
        $rows = $this->db->get()->result_array();

        $out = [];
        foreach ($rows as $s) {
            $label = trim($s['firstname'] . ' ' . $s['lastname']);
            if (!empty($s['employee_code'])) {
                $label .= ' - ' . $s['employee_code'];
            }
            $out[] = [
                'value' => (int) $s['staffid'],
                'label' => $label,
            ];
        }

        echo json_encode($out);
        die;
    }

    public function get_group($id)
    {
        if ($this->input->is_ajax_request()) {
            $group = $this->group_model->get($id);
            echo json_encode($group);
        }
    }

    public function get_leader_details()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $leader_id = $this->input->post('leader_id');
        $data = [
            'division' => '',
            'department' => '',
            'sub_department' => ''
        ];

        if ($leader_id) {
            $this->load->model('staff_model');
            $leader = $this->staff_model->get($leader_id);

            if ($leader && $leader->divisionid) {
                $this->load->model('divisions_model');
                $division = $this->divisions_model->get($leader->divisionid);
                $data['division'] = $division->name ?? '';
            }

            if ($leader) {
                $deptInfo = $this->group_model->get_leader_department_info($leader->staffid);
                $data['department'] = !empty($deptInfo['departments']) ? implode(', ', $deptInfo['departments']) : '';
                $data['sub_department'] = !empty($deptInfo['sub_departments']) ? implode(', ', $deptInfo['sub_departments']) : '';
            }
        }

        echo json_encode($data);
        die;
    }
}
