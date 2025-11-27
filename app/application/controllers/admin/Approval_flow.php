<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read Staff_model $staff_model
 * @property-read Approval_flow_model $approval_flow_model
 */
class Approval_flow extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('approval_flow_model');
        $this->load->model('divisions_model');
    }

    /* List all approval flows */
    public function index()
    {
        close_setup_menu();

        $data['title'] = _l('approval_flows');
        $this->load->view('admin/approval_flow/manage', $data);
    }

    public function table()
    {
        $filter = $this->input->get('filter') ?: 'all';
        $this->app->get_table_data('approval_flow', ['filter' => $filter]);
    }

    /* Create new approval flow */
    public function create()
    {
        if (!staff_can('create', 'approval_flow')) {
            access_denied('approval_flow');
        }

        $data = [];
        $data['title'] = _l('new_approval_flow');
        $data['members'] = $this->staff_model->get('', ['active' => 1]);
        $data['staffDirectory'] = $this->format_staff_directory($data['members']);
        $data['approval_flow_modal_standalone'] = false;
        $this->load->view('admin/approval_flow/approval_flow_create', $data);
    }

    /* Add new approval flow or update existing */
    public function approval_flow($id = '')
    {
        if (!staff_can('edit', 'approval_flow') && !staff_can('create', 'approval_flow')) {
            ajax_access_denied();
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            if ($id == '') {
                if (!staff_can('create', 'approval_flow')) {
                    header('HTTP/1.0 400 Bad Request');
                    echo json_encode([
                        'success' => false,
                        'message' => _l('access_denied'),
                    ]);
                    die;
                }
                $id = $this->approval_flow_model->add($data);
                $message = _l('added_successfully', _l('approval_flow'));
            } else {
                if (!staff_can('edit', 'approval_flow')) {
                    header('HTTP/1.0 400 Bad Request');
                    echo json_encode([
                        'success' => false,
                        'message' => _l('access_denied'),
                    ]);
                    die;
                }
                $success = $this->approval_flow_model->update($data, $id);
                $message = _l('updated_successfully', _l('approval_flow'));
            }

            echo json_encode([
                'success' => $id ? true : false,
                'id' => $id,
                'message' => $message,
            ]);
            die;
        }

        $data = [];
        $data['members'] = $this->staff_model->get('', ['active' => 1]);
        $data['staffDirectory'] = $this->format_staff_directory($data['members']);

        if ($id == '') {
            $title = _l('add_new', _l('approval_flow'));
        } else {
            $data['approval_flow'] = $this->approval_flow_model->get($id);
            $title = _l('edit', _l('approval_flow')) . ' ' . $data['approval_flow']->name;
        }

        $data['id'] = $id;
        $data['title'] = $title;
        $data['approval_flow_modal_standalone'] = ! $this->input->is_ajax_request();

        // Check if approval flow is in use for UI modification
        $active_tasks_using_flow = [];
        if ($id != '') {
            $active_tasks_using_flow = $this->approval_flow_model->get_active_tasks_using_flow($id);
            $data['approval_flow_in_use'] = !empty($active_tasks_using_flow);
            $data['active_tasks_using_flow'] = $active_tasks_using_flow;
        } else {
            $data['approval_flow_in_use'] = false;
            $data['active_tasks_using_flow'] = [];
        }

        $this->load->view('admin/approval_flow/approval_flow', $data);
    }

    /* Delete approval flow */
    public function delete($id)
    {
        if (!staff_can('delete', 'approval_flow')) {
            access_denied('approval_flow');
        }

        $success = $this->approval_flow_model->delete($id);
        $message = '';

        if ($success) {
            $message = _l('deleted', _l('approval_flow'));
            set_alert('success', $message);
        } else {
            set_alert('warning', _l('problem_deleting', _l('approval_flow_lowercase')));
        }

        redirect(admin_url('approval_flow'));
    }

    /* Change approval flow status */
    public function change_status($id)
    {
        if (!staff_can('edit', 'approval_flow')) {
            ajax_access_denied();
        }

        // Handle CSRF for AJAX requests
        if ($this->input->is_ajax_request()) {
            // CSRF token is automatically handled by CodeIgniter for AJAX
        }

        $success = $this->approval_flow_model->change_status($id);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? _l('updated_successfully', _l('approval_flow')) : _l('problem_updating', _l('approval_flow_lowercase'))
        ]);
        die;
    }

    /**
     * Get approval steps for a flow via AJAX
     */
    public function get_steps($flow_id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $steps = $this->approval_flow_model->get_approval_steps($flow_id);
        $staff_ids = array_column($steps, 'staff_id');

        echo json_encode([
            'success' => true,
            'staff_ids' => $staff_ids,
            'steps' => $steps
        ]);
        die;
    }

    /**
     * Prepare staff directory data with avatar, division, and emp code metadata for UI rendering.
     *
     * @param array $members
     *
     * @return array<int, array<string, mixed>>
     */
    private function format_staff_directory($members)
    {
        $directory = [];

        $divisionMap = [];
        $divisions = $this->divisions_model->get();
        if (is_array($divisions)) {
            foreach ($divisions as $division) {
                if (!isset($division['divisionid'])) {
                    continue;
                }
                $divisionMap[(int) $division['divisionid']] = $division['name'];
            }
        }

        foreach ($members as $member) {
            $staffId = isset($member['staffid']) ? (int) $member['staffid'] : 0;
            if ($staffId === 0) {
                continue;
            }

            $firstname = $member['firstname'] ?? '';
            $lastname  = $member['lastname'] ?? '';
            $fullName  = trim($firstname . ' ' . $lastname);

            $initials = '';
            if ($firstname !== '') {
                $initials .= mb_substr($firstname, 0, 1, 'UTF-8');
            }
            if ($lastname !== '') {
                $initials .= mb_substr($lastname, 0, 1, 'UTF-8');
            }
            if ($initials === '' && $fullName !== '') {
                $initials = mb_substr($fullName, 0, 2, 'UTF-8');
            }
            if ($initials === '') {
                $initials = 'ST';
            }
            $initials = strtoupper($initials);

            $divisionId   = isset($member['divisionid']) && $member['divisionid'] !== '' ? (int) $member['divisionid'] : null;
            $divisionName = $divisionId && isset($divisionMap[$divisionId]) ? $divisionMap[$divisionId] : '';

            $directory[] = [
                'id'            => $staffId,
                'fullname'      => $fullName !== '' ? $fullName : _l('staff_member'),
                'initials'      => $initials,
                'division_id'   => $divisionId,
                'division_name' => $divisionName,
                'emp_code'      => isset($member['staff_emp_code']) ? (string) $member['staff_emp_code'] : '',
                'avatar'        => staff_profile_image_url($staffId, 'small'),
            ];
        }

        return $directory;
    }
}
