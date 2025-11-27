<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Tickets_model $tickets_model
 */
class Tickets extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (get_option('access_tickets_to_none_staff_members') == 0 && ! is_staff_member()) {
            redirect(admin_url());
        }
        $this->load->model('tickets_model');
    }

    /**
     * Return sub departments (children) for selected department (AJAX)
     */
    public function sub_departments()
    {
        if ($this->input->is_ajax_request()) {
            $parentId = (int) $this->input->post('parent_id');
            $children = [];
            if ($parentId > 0) {
                // Get sub-departments that belong to this parent and have associated services
                $this->db->distinct();
                $this->db->select('d.departmentid, d.name');
                $this->db->from(db_prefix() . 'departments d');
                $this->db->join(db_prefix() . 'services s', 's.sub_department = d.departmentid', 'inner');
                $this->db->where('d.parent_department', $parentId);
                $children = $this->db->get()->result_array();
            }
            echo json_encode($children);
            die;
        }
        show_404();
    }

    // AJAX: Return active staff for given department or subordinates
    public function staff_by_department()
    {
        if ($this->input->is_ajax_request()) {
            $subordinatesOnly = (int) $this->input->post('subordinates_only') === 1;
            $managerId = (int) $this->input->post('manager_id');
            // If subordinates_only and ticket_id is provided, override manager_id with ticket's assignee
            if ($subordinatesOnly && $this->input->post('ticket_id')) {
                $ticketId = (int) $this->input->post('ticket_id');
                $ticketRow = $this->db->select('assigned')->from(db_prefix() . 'tickets')->where('ticketid', $ticketId)->get()->row();
                if ($ticketRow && $ticketRow->assigned) {
                    $managerId = (int) $ticketRow->assigned;
                }
            }
            $dep = (int) $this->input->post('department');
            $includeChildren = (int) $this->input->post('include_children') === 1;
            $out = [];
            if (($dep > 0 && !$subordinatesOnly) || ($subordinatesOnly && $managerId > 0)) {
                // Try to locate staff_emp_code custom field ID
                $empCodeField = $this->db->select('id')->where(['slug' => 'staff_emp_code', 'fieldto' => 'staff'])->get(db_prefix() . 'customfields')->row();

                if ($subordinatesOnly) {
                    // Get direct subordinates of managerId based on reporting_manager field
                    $this->db->select(db_prefix() . 'staff.staffid, firstname, lastname, reporting_manager');
                    $this->db->select(db_prefix() . 'staff_departments.departmentid as deptid');
                    $this->db->select('d.name as dept_name');
                    $this->db->select('d.parent_department');
                    if ($empCodeField) {
                        $this->db->select('cfv.value as staff_emp_code');
                    }
                    $this->db->from(db_prefix() . 'staff');
                    $this->db->join(db_prefix() . 'staff_departments', db_prefix() . 'staff_departments.staffid = ' . db_prefix() . 'staff.staffid', 'inner');
                    $this->db->join(db_prefix() . 'departments d', 'd.departmentid = ' . db_prefix() . 'staff_departments.departmentid', 'left');
                    if ($empCodeField) {
                        $this->db->join(db_prefix() . 'customfieldsvalues cfv', 'cfv.relid = ' . db_prefix() . 'staff.staffid AND cfv.fieldto = "staff" AND cfv.fieldid = ' . $empCodeField->id, 'left');
                    }
                    $this->db->where(db_prefix() . 'staff.reporting_manager', $managerId);
                    $this->db->where(db_prefix() . 'staff.active', 1);
                    $staff = $this->db->get()->result_array();
                } else {
                    // Build dept IDs: include child departments when requested
                    $deptIds = [$dep];
                    if ($includeChildren) {
                        $children = $this->db->select('departmentid')->where('parent_department', $dep)->get(db_prefix() . 'departments')->result_array();
                        foreach ($children as $c) { $deptIds[] = (int) $c['departmentid']; }
                    }

                    // Try to locate staff_emp_code custom field ID
                    $empCodeField = $this->db->select('id')->where(['slug' => 'staff_emp_code', 'fieldto' => 'staff'])->get(db_prefix() . 'customfields')->row();

                    $this->db->select(db_prefix() . 'staff.staffid, firstname, lastname');
                    $this->db->select(db_prefix() . 'staff_departments.departmentid as deptid');
                    $this->db->select('d.name as dept_name');
                    if ($empCodeField) {
                        $this->db->select('cfv.value as staff_emp_code');
                    }
                    $this->db->from(db_prefix() . 'staff');
                    $this->db->join(db_prefix() . 'staff_departments', db_prefix() . 'staff_departments.staffid = ' . db_prefix() . 'staff.staffid', 'inner');
                    $this->db->join(db_prefix() . 'departments d', 'd.departmentid = ' . db_prefix() . 'staff_departments.departmentid', 'left');
                    if ($empCodeField) {
                        $this->db->join(db_prefix() . 'customfieldsvalues cfv', 'cfv.relid = ' . db_prefix() . 'staff.staffid AND cfv.fieldto = "staff" AND cfv.fieldid = ' . $empCodeField->id, 'left');
                    }
                    $this->db->where_in(db_prefix() . 'staff_departments.departmentid', $deptIds);
                    $this->db->where(db_prefix() . 'staff.active', 1);
                    $staff = $this->db->get()->result_array();
                }
                foreach ($staff as $s) {
                    $out[] = [
                        'id' => (int) $s['staffid'],
                        'name' => trim($s['firstname'] . ' ' . $s['lastname']),
                        'emp_code' => isset($s['staff_emp_code']) ? (string) $s['staff_emp_code'] : '',
                        'deptid' => isset($s['deptid']) ? (int)$s['deptid'] : null,
                        'dept_name' => (isset($s['parent_department']) && intval($s['parent_department']) > 0) ? $s['dept_name'] : '',
                        'sub_department' => $s['dept_name'] ?? '',
                    ];
                }
            }
            echo json_encode($out);
            die;
        }
        show_404();
    }

    // AJAX: Return top-level departments by division
    public function departments_by_division()
    {
        if ($this->input->is_ajax_request()) {
            $divisionId = (int) $this->input->post('divisionid');
            $out        = [];
            if ($divisionId > 0) {
                // Get departments that belong to this division and have associated services
                $this->db->distinct();
                $this->db->select('d.departmentid, d.name');
                $this->db->from(db_prefix() . 'departments d');
                $this->db->join(db_prefix() . 'department_divisions dd', 'dd.departmentid = d.departmentid', 'inner');
                $this->db->join(db_prefix() . 'services s', 's.divisionid = dd.divisionid AND s.departmentid = d.departmentid', 'inner');
                $this->db->where('dd.divisionid', $divisionId);
                $this->db->where_in('d.parent_department', [0, NULL]);
                $this->db->order_by('d.name', 'asc');
                $rows = $this->db->get()->result_array();
                foreach ($rows as $r) {
                    $out[] = [
                        'departmentid' => (int) $r['departmentid'],
                        'name'         => $r['name'],
                    ];
                }
            }
            echo json_encode($out);
            die;
        }
        show_404();
    }

    // AJAX: Return applications by department/sub-department
    public function applications_by_department()
    {
        if ($this->input->is_ajax_request()) {
            $departmentId = (int) $this->input->post('department_id');
            $subDepartmentId = (int) $this->input->post('sub_department_id');
            $out = [];

            if ($departmentId > 0) {
                $this->db->distinct();
                $this->db->select('app.id, app.name, app.position');
                $this->db->from(db_prefix() . 'services s');
                $this->db->join(db_prefix() . 'applications app', 'app.id = s.applicationid', 'inner');
                $this->db->where('s.departmentid', $departmentId);
                if ($subDepartmentId > 0) {
                $this->db->where('s.sub_department', $subDepartmentId);
                }
                $this->db->order_by('CASE WHEN app.position = 0 THEN 999999 ELSE app.position END', 'ASC', false);
                $rows = $this->db->get()->result_array();
                foreach ($rows as $r) {
                    $out[] = [
                        'id' => (int) $r['id'],
                        'name' => $r['name'],
                        'position' => isset($r['position']) ? (int) $r['position'] : 0,
                    ];
                }
            }
            echo json_encode($out);
            die;
        }
        show_404();
    }

    // Return service info (department/sub/division) for mapping in UI
    public function service_info($id)
    {
        if (!is_admin() && get_option('access_tickets_to_none_staff_members') == 0) {
            access_denied('Ticket Services');
        }
        if ($this->input->is_ajax_request() || $this->input->is_cli_request()) {
            if (!is_numeric($id)) {
                echo json_encode(['success' => false]);
                die;
            }
            $row = $this->tickets_model->get_service($id);
            if ($row) {
                echo json_encode([
                    'success'        => true,
                    'serviceid'      => (int) $row->serviceid,
                    'divisionid'     => isset($row->divisionid) ? (int) $row->divisionid : null,
                    'departmentid'   => isset($row->departmentid) ? (int) $row->departmentid : null,
                    'sub_department' => isset($row->sub_department) ? (int) $row->sub_department : null,
                    'responsible'    => isset($row->responsible) ? (int) $row->responsible : null,
                ]);
                die;
            }
            echo json_encode(['success' => false]);
            die;
        }
        show_404();
    }

    // Return department info such as responsible_staff (HOD)
    public function department_info($id)
    {
        if ($this->input->is_ajax_request() || $this->input->is_cli_request()) {
            if (!is_numeric($id)) {
                echo json_encode(['success' => false]);
                die;
            }
            $this->load->model('departments_model');
            $row = $this->departments_model->get((int) $id);
            if ($row) {
                $responsible = null;
                if ($this->db->field_exists('responsible_staff', db_prefix().'departments')) {
                    $responsible = isset($row->responsible_staff) ? (int) $row->responsible_staff : null;
                }
                echo json_encode([
                    'success'            => true,
                    'departmentid'       => (int) $row->departmentid,
                    'responsible_staff'  => $responsible,
                ]);
                die;
            }
            echo json_encode(['success' => false]);
            die;
        }
        show_404();
    }

    // Return staff info (full name and emp code) by staff id
    public function staff_info($id)
    {
        if ($this->input->is_ajax_request() || $this->input->is_cli_request()) {
            if (!is_numeric($id)) {
                echo json_encode(['success' => false]);
                die;
            }

            // Try to locate staff_emp_code custom field ID
            $empCodeField = $this->db->select('id')->where(['slug' => 'staff_emp_code', 'fieldto' => 'staff'])->get(db_prefix().'customfields')->row();

            $this->db->select(db_prefix().'staff.staffid, firstname, lastname');
            $this->db->select(db_prefix() . 'staff_departments.departmentid as deptid');
            $this->db->select('d.name as dept_name');
            $this->db->select('d.parent_department');
            if ($empCodeField) {
                $this->db->select('cfv.value as staff_emp_code');
            }
            $this->db->from(db_prefix().'staff');
            $this->db->join(db_prefix() . 'staff_departments', db_prefix() . 'staff_departments.staffid = ' . db_prefix() . 'staff.staffid', 'left');
            $this->db->join(db_prefix() . 'departments d', 'd.departmentid = ' . db_prefix() . 'staff_departments.departmentid', 'left');
            if ($empCodeField) {
                $this->db->join(
                    db_prefix().'customfieldsvalues cfv',
                    'cfv.relid = '.db_prefix().'staff.staffid AND cfv.fieldto = "staff" AND cfv.fieldid = '.$empCodeField->id,
                    'left'
                );
            }
            $this->db->where(db_prefix().'staff.staffid', (int) $id);
            $row = $this->db->get()->row_array();

            if ($row) {
                // Get sub department name if parent_department exists
                $sub_dept_name = '';
                if (!empty($row['parent_department'])) {
                    $parent = $this->db->select('name')->where('departmentid', $row['parent_department'])->get(db_prefix() . 'departments')->row();
                    if ($parent) {
                        $sub_dept_name = $parent->name;
                    }
                }

                echo json_encode([
                    'success'      => true,
                    'id'           => (int) $row['staffid'],
                    'name'         => trim(($row['firstname'] ?? '').' '.($row['lastname'] ?? '')),
                    'emp_code'     => isset($row['staff_emp_code']) ? (string) $row['staff_emp_code'] : '',
                    'department'   => $sub_dept_name,  // Parent department name
                    'sub_department' => isset($row['dept_name']) ? $row['dept_name'] : '',  // Current department name
                    'department_id' => isset($row['deptid']) ? (int)$row['deptid'] : null,
                ]);
                die;
            }

            echo json_encode(['success' => false]);
            die;
        }
        show_404();
    }

    // AJAX: Return active staff for selected department or sub-department (responsible user list)
    public function service_responsible_staff()
    {
        if (!is_admin()) {
            access_denied('Ticket Services');
        }
        if ($this->input->is_ajax_request()) {
            $departmentId   = (int) $this->input->post('department_id');
            $subDepartmentId = (int) $this->input->post('sub_department_id');
            $staffType = $this->input->post('staff_type') ?: 'department';

            $targetDepartmentId = $subDepartmentId ?: $departmentId;
            $out = [];

            if ($staffType === 'all') {
                // Return all active staff without department filter
                $this->db->select(db_prefix() . 'staff.staffid, CONCAT(' . db_prefix() . 'staff.firstname, " ", ' . db_prefix() . 'staff.lastname) as name');
                $this->db->from(db_prefix() . 'staff');
                $this->db->where(db_prefix() . 'staff.active', 1);
                if (get_option('access_tickets_to_none_staff_members') == 0) {
                    $this->db->where(db_prefix() . 'staff.is_not_staff', 0);
                }
                $this->db->order_by('name', 'ASC');
                $out = $this->db->get()->result_array();
            } elseif ($targetDepartmentId > 0) {
                // Return staff filtered by department (existing behavior)
                $this->db->select(db_prefix() . 'staff.staffid, CONCAT(' . db_prefix() . 'staff.firstname, " ", ' . db_prefix() . 'staff.lastname) as name');
                $this->db->from(db_prefix() . 'staff');
                $this->db->join(db_prefix() . 'staff_departments', db_prefix() . 'staff_departments.staffid = ' . db_prefix() . 'staff.staffid', 'left');
                $this->db->where(db_prefix() . 'staff_departments.departmentid', $targetDepartmentId);
                $this->db->where(db_prefix() . 'staff.active', 1);
                if (get_option('access_tickets_to_none_staff_members') == 0) {
                    $this->db->where(db_prefix() . 'staff.is_not_staff', 0);
                }
                $this->db->order_by('name', 'ASC');
                $out = $this->db->get()->result_array();
            }
            echo json_encode($out);
            die;
        }
        show_404();
    }

    public function index($status = '', $userid = '')
    {
        close_setup_menu();

        if (! is_numeric($status)) {
            $status = '';
        }

        $this->load->helper('table');
        $data['table'] = App_table::find('tickets');

        if ($this->input->is_ajax_request()) {
            if (! $this->input->post('via_ticket')) {
                $tableParams = [
                    'status' => $status,
                    'userid' => $userid,
                ];
            } else {
                // request for othes tickets when single ticket is opened
                $tableParams = [
                    'userid'     => $this->input->post('via_ticket_userid'),
                    'via_ticket' => $this->input->post('via_ticket'),
                ];

                if ($tableParams['userid'] == 0) {
                    unset($tableParams['userid']);
                    $tableParams['by_email'] = $this->input->post('via_ticket_email');
                }
            }
            $data['table']->output($tableParams);
        }

        $data['chosen_ticket_status']              = $status;
        $data['weekly_tickets_opening_statistics'] = json_encode($this->tickets_model->get_weekly_tickets_opening_statistics());
        $data['title']                             = _l('support_tickets');
        $this->load->model('departments_model');
        $data['statuses']             = $this->tickets_model->get_ticket_status();
        $data['staff_deparments_ids'] = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
        $data['departments']          = $this->departments_model->get();
        $data['priorities']           = $this->tickets_model->get_priority();
        $data['services']             = $this->tickets_model->get_service();
        $data['ticket_assignees']     = $this->tickets_model->get_tickets_assignes_disctinct();
        $data['bodyclass']            = 'tickets-page';
        add_admin_tickets_js_assets();
        $data['default_tickets_list_statuses'] = hooks()->apply_filters('default_tickets_list_statuses', [1, 2, 4]);
        $this->load->view('admin/tickets/list', $data);
    }

    public function add($userid = false)
    {
        if ($this->input->post()) {
            $data            = $this->input->post();
            $data['message'] = html_purify($this->input->post('message', false));
            $id              = $this->tickets_model->add($data, get_staff_user_id());
            if ($id) {
                // Get ticket with ticket number
                $ticket = $this->tickets_model->get_ticket_by_id($id);
                $ticket_number = $ticket->ticket_number ?? $id;
                set_alert('success', _l('new_ticket_added_successfully', $ticket_number));
                redirect(admin_url('tickets/ticket/' . $ticket_number));
            }
        }
        if ($userid !== false) {
            $data['userid'] = $userid;
            $data['client'] = $this->clients_model->get($userid);
        }
    // Load necessary models
    $this->load->model('knowledge_base_model');
    $this->load->model('departments_model');
    $this->load->model('divisions_model');
    $this->load->model('application_model');

    $data['applications']       = $this->application_model->get();
    $data['departments']        = $this->departments_model->get();
    // Only load divisions that have associated services
    $all_divisions = $this->divisions_model->get();
    $divisions_with_services = [];
    if (!empty($all_divisions)) {
        // Get division IDs that have services
        $this->db->distinct();
        $this->db->select('divisionid');
        $this->db->from(db_prefix() . 'services');
        $this->db->where('divisionid IS NOT NULL');
        $service_division_ids = $this->db->get()->result_array();
        $service_division_ids = array_column($service_division_ids, 'divisionid');

        // Filter divisions to only include those with services
        foreach ($all_divisions as $division) {
            if (in_array($division['divisionid'], $service_division_ids)) {
                $divisions_with_services[] = $division;
            }
        }
    }
    $data['divisions']          = $divisions_with_services;
    $data['predefined_replies'] = $this->tickets_model->get_predefined_reply();
        $priorities = $this->tickets_model->get_priority();
        // Debug: Check what we have
        log_message('debug', 'Priorities data: ' . json_encode($priorities));

        // Enhance priorities with option attributes for JS calculation
        foreach ($priorities as &$priority) {
            $attrs = [];
            if (isset($priority['duration_value'])) {
                $attrs['data-duration-value'] = $priority['duration_value'];
            }
            if (isset($priority['duration_unit'])) {
                $attrs['data-duration-unit'] = $priority['duration_unit'];
            }
            if (!empty($attrs)) {
                $priority['option_attributes'] = $attrs;
            }
        }
        unset($priority);
        $data['priorities']         = $priorities;
        $data['services']           = $this->tickets_model->get_service();
        $whereStaff                 = ['active' => 1];
        if (get_option('access_tickets_to_none_staff_members') == 0) {
            $whereStaff['is_not_staff'] = 0;
        }
        $data['staff']     = $this->staff_model->get('', $whereStaff);
        $data['articles']  = $this->knowledge_base_model->get();
        $data['bodyclass'] = 'ticket';
        $data['title']     = _l('new_ticket');

        if ($this->input->get('project_id') && $this->input->get('project_id') > 0) {
            // request from project area to create new ticket
            $data['project_id'] = $this->input->get('project_id');
            $data['userid']     = get_client_id_by_project_id($data['project_id']);
            if (total_rows(db_prefix() . 'contacts', ['active' => 1, 'userid' => $data['userid']]) == 1) {
                $contact = $this->clients_model->get_contacts($data['userid']);
                if (isset($contact[0])) {
                    $data['contact'] = $contact[0];
                }
            }
        } elseif ($this->input->get('contact_id') && $this->input->get('contact_id') > 0 && $this->input->get('userid')) {
            $contact_id = $this->input->get('contact_id');
            if (total_rows(db_prefix() . 'contacts', ['active' => 1, 'id' => $contact_id]) == 1) {
                $contact = $this->clients_model->get_contact($contact_id);
                if ($contact) {
                    $data['contact'] = (array) $contact;
                }
            }
        }
        add_admin_tickets_js_assets();
        $this->load->view('admin/tickets/add', $data);
    }

    public function delete($ticketid)
    {
        if (! $ticketid) {
            redirect(admin_url('tickets'));
        }

        if (! can_staff_delete_ticket()) {
            access_denied('delete ticket');
        }

        $response = $this->tickets_model->delete($ticketid);

        if ($response == true) {
            set_alert('success', _l('deleted', _l('ticket')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('ticket_lowercase')));
        }

        // ensure if deleted from single ticket page, user is redirected to index
        if (str_contains(previous_url(), 'ticket/' . $ticketid)) {
            redirect(admin_url('tickets'));

            return;
        }
        redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
    }

    public function delete_attachment($id)
    {
        if (is_admin() || (! is_admin() && get_option('allow_non_admin_staff_to_delete_ticket_attachments') == '1')) {
            if (get_option('staff_access_only_assigned_departments') == 1 && ! is_admin()) {
                $attachment = $this->tickets_model->get_ticket_attachment($id);
                $ticket     = $this->tickets_model->get_ticket_by_id($attachment->ticketid);

                $this->load->model('departments_model');
                $staff_departments = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                if (! in_array($ticket->department, $staff_departments)) {
                    set_alert('danger', _l('ticket_access_by_department_denied'));
                    redirect(admin_url('access_denied'));
                }
            }

            $this->tickets_model->delete_ticket_attachment($id);
        }

        redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
    }

    public function update_staff_replying($ticketId, $userId = '')
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode(['success' => $this->tickets_model->update_staff_replying($ticketId, $userId)]);

            exit;
        }
    }

    public function check_staff_replying($ticketId)
    {
        if ($this->input->is_ajax_request()) {
            $ticket            = $this->tickets_model->get_staff_replying($ticketId);
            $isAnotherReplying = $ticket->staff_id_replying !== null && $ticket->staff_id_replying !== get_staff_user_id();
            echo json_encode([
                'is_other_staff_replying' => $isAnotherReplying,
                'message'                 => $isAnotherReplying ? e(_l('staff_is_currently_replying', get_staff_full_name($ticket->staff_id_replying))) : '',
            ]);

            exit;
        }
    }

    public function ticket($id)
    {
        if (! $id) {
            redirect(admin_url('tickets/add'));
        }

        // Allow fetching by ticket_number instead of ticketid
        if (strlen($id) == 7 && preg_match('/^\d{7}$/', $id)) {
            $this->db->select('ticketid');
            $this->db->where('ticket_number', $id);
            $ticket_row = $this->db->get(db_prefix() . 'tickets')->row();
            if ($ticket_row) {
                $id = $ticket_row->ticketid;
            } else {
                // Not found as ticket_number, treat as ticketid (if it's exactly a ticketid)
                if ($this->db->where('ticketid', $id)->count_all_results(db_prefix() . 'tickets') == 0) {
                    blank_page(_l('ticket_not_found'));
                }
            }
        } elseif (!is_numeric($id)) {
            // Invalid format
            blank_page(_l('ticket_not_found'));
        }

        $data['ticket']         = $this->tickets_model->get_ticket_by_id($id);
        $data['merged_tickets'] = $this->tickets_model->get_merged_tickets_by_primary_id($id);

        if (! $data['ticket']) {
            blank_page(_l('ticket_not_found'));
        }

        // Load pending reassignment (if any) to reflect access and banner/UI state
        $pending_reassignment = $this->tickets_model->get_pending_reassign((int)$id);

        if (get_option('staff_access_only_assigned_departments') == 1) {
            if (! is_admin()) {
                $this->load->model('departments_model');
                $staff_departments = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                if (! in_array($data['ticket']->department, $staff_departments)) {
                    // Allow access for ticket assignee, ticket creator, registered handlers, or pending target
                    $isOwner     = (int)$data['ticket']->assigned === (int)get_staff_user_id();
                    $isCreator   = (int)$data['ticket']->admin === (int)get_staff_user_id();
                    $isHandler   = false;
                    if (method_exists($this->tickets_model, 'is_ticket_handler')) {
                        $isHandler = $this->tickets_model->is_ticket_handler((int)$id, (int)get_staff_user_id());
                    }
                    $isPendingTarget = $pending_reassignment && (int)$pending_reassignment->to_assigned === (int)get_staff_user_id();
                    if (!($isOwner || $isCreator || $isHandler || $isPendingTarget)) {
                        set_alert('danger', _l('ticket_access_by_department_denied'));
                        redirect(admin_url('access_denied'));
                    }
                }
            }
        }

        // Automatic assignment logic
        $current_user_id = get_staff_user_id();
        $is_owner = (int)$data['ticket']->assigned === (int)$current_user_id;
        $is_creator = (int)$data['ticket']->admin === (int)$current_user_id;
        $can_reassign = is_admin() || $is_owner || $is_creator;

        // Assign to me automatically only when ticket is unassigned
        // Avoid overriding a deliberate assignee chosen on creation
        if ($can_reassign && !$is_owner && !$pending_reassignment && (int)$data['ticket']->assigned === 0) {
            $this->db->where('ticketid', $id)->update(db_prefix().'tickets', ['assigned' => $current_user_id]);
            log_activity('Ticket assigned to me automatically [ID: ' . $id . ']');
            // Reload ticket data after assignment
            $data['ticket'] = $this->tickets_model->get_ticket_by_id($id);
        }

        $ticketIsClosed   = $this->tickets_model->is_close_status((int)($data['ticket']->status ?? 0));
        $ticketCreatorId  = $this->tickets_model->get_ticket_creator_staff_id($data['ticket']);
        $data['ticket_is_closed']          = $ticketIsClosed;
        $data['ticket_creator_id']         = $ticketCreatorId;
        $data['reopen_request']            = $ticketIsClosed ? $this->tickets_model->get_pending_reopen_request($id) : null;
        $data['can_request_reopen']        = $ticketIsClosed && empty($data['reopen_request']) && (int)$ticketCreatorId === (int)$current_user_id;
        $assigneeId                        = (int)($data['ticket']->assigned ?? 0);
        $data['can_handle_reopen_request'] = $ticketIsClosed && !empty($data['reopen_request']) && (is_admin() || ($assigneeId > 0 && $assigneeId === (int)$current_user_id));

        if ($this->input->post()) {
            // If reassignment is pending and current user is target, block edits (view-only)
            if ($pending_reassignment && (int)$pending_reassignment->to_assigned === (int) get_staff_user_id()) {
                set_alert('danger', 'Reassignment is pending your approval. You have view-only access.');
                $ticket = $this->tickets_model->get_ticket_by_id($id);
                $ticket_number = $ticket->ticket_number ?? $id;
                redirect(admin_url('tickets/ticket/' . $ticket_number));
            }
            $returnToTicketList = false;
            $data               = $this->input->post();

            if (isset($data['ticket_add_response_and_back_to_list'])) {
                $returnToTicketList = true;
                unset($data['ticket_add_response_and_back_to_list']);
            }

            $data['message'] = html_purify($this->input->post('message', false));
            $replyid         = $this->tickets_model->add_reply($data, $id, get_staff_user_id());

            if ($replyid) {
                $ticket = $this->tickets_model->get_ticket_by_id($id);
                $ticket_number = $ticket->ticket_number ?: $id;
                set_alert('success', _l('replied_to_ticket_successfully', $ticket_number));
            }
            if (! $returnToTicketList) {
                $ticket_number = $ticket->ticket_number ?: $id;
                redirect(admin_url('tickets/ticket/' . $ticket_number));
            } else {
                set_ticket_open(0, $id);
                redirect(admin_url('tickets'));
            }
        }
    // Load necessary models
    $this->load->model('knowledge_base_model');
    $this->load->model('departments_model');
    $this->load->model('divisions_model');

        $data['statuses']                       = $this->tickets_model->get_ticket_status();
        $data['statuses']['callback_translate'] = 'ticket_status_translate';

    $data['departments']        = $this->departments_model->get();
    $data['divisions']          = $this->divisions_model->get();
        $data['predefined_replies'] = $this->tickets_model->get_predefined_reply();
        $data['priorities']         = $this->tickets_model->get_priority();
        $data['services']           = $this->tickets_model->get_service();
        $whereStaff                 = ['active' => 1];
        if (get_option('access_tickets_to_none_staff_members') == 0) {
            $whereStaff['is_not_staff'] = 0;
        }
        $data['staff']          = $this->staff_model->get('', $whereStaff);
        $data['articles']       = $this->knowledge_base_model->get();
        $data['ticket_replies'] = $this->tickets_model->get_ticket_replies($id);
        $allLogs = $this->tickets_model->get_ticket_log($id);
        $logsPerPage = 15;
        $logsPage    = max(1, (int)($this->input->get('logs_page') ?? 1));
        $totalLogs   = is_array($allLogs) ? count($allLogs) : 0;
        $totalPages  = $totalLogs > 0 ? (int)ceil($totalLogs / $logsPerPage) : 1;
        if ($logsPage > $totalPages) { $logsPage = $totalPages; }
        $logsOffset = ($logsPage - 1) * $logsPerPage;
        $data['ticket_logs']         = $totalLogs > 0 ? array_slice($allLogs, $logsOffset, $logsPerPage) : [];
        $data['ticket_logs_total']   = $totalLogs;
        $data['ticket_logs_page']    = $logsPage;
        $data['ticket_logs_pages']   = $totalPages;
        $data['ticket_logs_perpage'] = $logsPerPage;
        $data['ticket_logs_start']   = $totalLogs > 0 ? ($logsOffset + 1) : 0;
        $data['ticket_logs_end']     = $totalLogs > 0 ? min($logsOffset + $logsPerPage, $totalLogs) : 0;

        $statusLookup = [];
        $statusSource = $this->tickets_model->get_ticket_status();
        if (is_array($statusSource)) {
            foreach ($statusSource as $statusRow) {
                if (is_array($statusRow) && isset($statusRow['ticketstatusid'])) {
                    $statusLookup[(int)$statusRow['ticketstatusid']] = $statusRow['name'] ?? ('#' . $statusRow['ticketstatusid']);
                } elseif (is_object($statusRow) && isset($statusRow->ticketstatusid)) {
                    $statusLookup[(int)$statusRow->ticketstatusid] = $statusRow->name ?? ('#' . $statusRow->ticketstatusid);
                }
            }
        }
        $data['ticket_status_lookup'] = $statusLookup;

        $data['bodyclass']      = 'top-tabs ticket single-ticket';
        $data['title']          = $data['ticket']->subject;
        $data['sender_blocked'] = total_rows(db_prefix() . 'contacts', [
            'active' => 1, 'userid' => $data['ticket']->userid, 'id' => $data['ticket']->contactid,
        ]) == 0;
        $data['ticket']->ticket_notes = $this->misc_model->get_notes($id, 'ticket');
        // Pass pending reassignment to the view for UI changes
        $data['pending_reassignment'] = $pending_reassignment;
        $data['close_request'] = $this->tickets_model->get_pending_close_request($id);
        add_admin_tickets_js_assets();
        $this->load->view('admin/tickets/single', $data);
    }

    // Create a reassignment request requiring target user approval
    public function reassign_request()
    {
        // Allow both AJAX and non-AJAX requests for debugging
        $isAjax = $this->input->is_ajax_request();
        $post = $this->input->post();
        $ticketId = (int)($post['ticketid'] ?? 0);
        $toAssigned = (int)($post['to_assigned'] ?? 0);
        if ($ticketId <= 0 || $toAssigned <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ticket or assignee']);
            die;
        }
        $ticket = $this->tickets_model->get_ticket_by_id($ticketId);
        if (!$ticket) { echo json_encode(['success' => false, 'message' => 'Ticket not found']); die; }
        // Ensure only admin, current assignee, or ticket creator can initiate reassign
        $isOwner   = (int)$ticket->assigned === (int)get_staff_user_id();
        $isCreator = (int)$ticket->admin === (int)get_staff_user_id();
        if (!is_admin() && !$isOwner && !$isCreator) { echo json_encode(['success' => false, 'message' => 'Not allowed']); die; }
        // Delegate to model
        $ok = $this->tickets_model->create_reassign_request([
            'ticketid'      => $ticketId,
            'from_assigned' => (int)$ticket->assigned,
            'to_assigned'   => $toAssigned,
            'divisionid'    => $post['divisionid'] !== '' ? (int)$post['divisionid'] : null,
            'department'    => $post['department'] !== '' ? (int)$post['department'] : null,
            'sub_department'=> $post['sub_department'] !== '' ? (int)$post['sub_department'] : null,
            'application_id'=> $post['application_id'] !== '' ? (int)$post['application_id'] : null,
            'service'       => $post['service'] !== '' ? (int)$post['service'] : null,
        ]);
        echo json_encode(['success' => $ok === true, 'message' => $ok === true ? '' : ($ok ?: 'Failed')]);
        die;
    }

    // Accept reassignment (target user only)
    public function reassign_accept($requestId)
    {
        $requestId = (int)$requestId;
        $ok = $this->tickets_model->approve_reassign_request($requestId, get_staff_user_id());
        if ($ok === true) { set_alert('success', 'Reassignment accepted.'); }
        else { set_alert('danger', is_string($ok) ? $ok : 'Failed to accept reassignment'); }
        $req = $this->tickets_model->get_reassign_request($requestId);
        $tid = $req ? (int)$req->ticketid : 0;
        $ticket_number = '';
        if ($tid > 0) {
            $ticket = $this->tickets_model->get_ticket_by_id($tid);
            $ticket_number = $ticket->ticket_number ?? $tid;
        }
        redirect(admin_url('tickets/ticket/' . ($ticket_number ?: '')));
    }

    // Reject reassignment (target user only)
    public function reassign_reject($requestId)
    {
        $requestId = (int)$requestId;
        $remarks = $this->input->post('remarks');
        $ok = $this->tickets_model->reject_reassign_request($requestId, get_staff_user_id(), $remarks);
        if ($ok === true) { set_alert('success', 'Reassignment declined.'); }
        else { set_alert('danger', is_string($ok) ? $ok : 'Failed to decline reassignment'); }
        $req = $this->tickets_model->get_reassign_request($requestId);
        $tid = $req ? (int)$req->ticketid : 0;
        $ticket_number = '';
        if ($tid > 0) {
            $ticket = $this->tickets_model->get_ticket_by_id($tid);
            $ticket_number = $ticket->ticket_number ?? $tid;
        }
        redirect(admin_url('tickets/ticket/' . ($ticket_number ?: '')));
    }

    // Cancel reassignment (requester only)
    public function reassign_cancel($ticketId)
    {
        $ticketId = (int) $ticketId;
        if ($ticketId <= 0) {
            show_404();
        }

        $ticket = $this->tickets_model->get_ticket_by_id($ticketId);
        if (!$ticket) {
            set_alert('danger', 'Ticket not found');
            redirect(admin_url('tickets'));
        }

        $ticket_number = $ticket->ticket_number ?? $ticketId;

        $currentUser = (int) get_staff_user_id();
        $pendingRequest = $this->tickets_model->get_pending_reassign($ticketId);

        if (!$pendingRequest) {
            set_alert('danger', 'No pending reassignment request found for this ticket');
            redirect(admin_url('tickets/ticket/' . $ticket_number));
        }

        // Only the original requester can cancel
        if ((int) $pendingRequest->created_by !== $currentUser) {
            set_alert('danger', 'You are not authorized to cancel this reassignment request');
            redirect(admin_url('tickets/ticket/' . $ticket_number));
        }

        // Cancel the reassignment request
        $this->db->where('id', (int) $pendingRequest->id)
                 ->update(db_prefix() . 'ticket_reassignments', [
                     'status' => 'cancelled',
                     'decision_by' => $currentUser,
                     'decision_at' => date('Y-m-d H:i:s'),
                     'decision_remarks' => 'Cancelled by requester'
                 ]);

        if ($this->db->affected_rows() > 0) {
            $this->tickets_model->add_ticket_log($ticketId, 'reassign_request_cancelled', [
                'cancelled_by' => $currentUser,
                'request_id' => (int) $pendingRequest->id
            ]);
            set_alert('success', 'Reassignment request cancelled successfully');
        } else {
            set_alert('danger', 'Failed to cancel reassignment request');
        }

        redirect(admin_url('tickets/ticket/' . $ticket_number));
    }

    public function close_request_action($ticketId, $decision)
    {
        $ticketId = (int) $ticketId;
        $decision = strtolower($decision);

        if (!in_array($decision, ['approve', 'reopen'], true)) {
            show_404();
        }

        $ticket = $this->tickets_model->get_ticket_by_id($ticketId);
        if (!$ticket) {
            set_alert('danger', _l('ticket_not_found'));
            redirect(admin_url('tickets'));
        }

        $ticket_number = $ticket->ticket_number ?? $ticketId;

        $currentUser    = (int) get_staff_user_id();
        $pendingRequest = $this->tickets_model->get_pending_close_request($ticketId);
        if (!$pendingRequest) {
            set_alert('danger', _l('ticket_close_request_not_found'));
            redirect(admin_url('tickets/ticket/' . $ticket_number));
        }

        $creatorId = $this->tickets_model->get_ticket_creator_staff_id($ticket);

        $approverId = (int) ($pendingRequest->approver_id ?? 0);
        if ($approverId === 0) {
            $approverId = $creatorId;
        }

        if (!is_admin() && $approverId > 0 && $approverId !== $currentUser) {
            set_alert('danger', _l('ticket_close_request_not_authorized'));
            redirect(admin_url('tickets/ticket/' . $ticket_number));
        }

        $result = $this->tickets_model->resolve_close_approval_request($ticketId, $decision, $currentUser);

        if ($result === true) {
            $messageKey = $decision === 'approve' ? 'ticket_close_request_approved' : 'ticket_close_request_reopened';
            set_alert('success', _l($messageKey));
        } else {
            set_alert('danger', is_string($result) ? $result : _l('ticket_close_request_failed'));
        }

        redirect(admin_url('tickets/ticket/' . $ticket_number));
    }

    public function reopen_request($ticketId)
    {
        $ticketId = (int) $ticketId;
        if ($ticketId <= 0) {
            show_404();
        }

        $ticket = $this->tickets_model->get_ticket_by_id($ticketId);
        if (!$ticket) {
            set_alert('danger', _l('ticket_not_found'));
            redirect(admin_url('tickets'));
        }

        $ticket_number = $ticket->ticket_number ?? $ticketId;

        $currentUser = (int) get_staff_user_id();
        $creatorId   = $this->tickets_model->get_ticket_creator_staff_id($ticket);

        if (!$this->tickets_model->is_close_status((int) ($ticket->status ?? 0))) {
            set_alert('info', _l('ticket_reopen_request_not_allowed'));
            redirect(admin_url('tickets/ticket/' . $ticket_number));
        }

        if (!is_admin() && $creatorId !== $currentUser) {
            set_alert('danger', _l('ticket_reopen_request_not_authorized'));
            redirect(admin_url('tickets/ticket/' . $ticket_number));
        }

        $result = $this->tickets_model->create_reopen_request($ticket, $currentUser);

        if (is_array($result) && ($result['success'] ?? false)) {
            set_alert($result['alert'] ?? 'success', $result['message'] ?? _l('ticket_reopen_request_sent'));
        } else {
            $alert   = 'danger';
            $message = _l('ticket_reopen_request_failed');
            if (is_array($result)) {
                $alert   = $result['alert'] ?? $alert;
                $message = $result['message'] ?? $message;
            } elseif (is_string($result)) {
                $message = $result;
            }
            set_alert($alert, $message);
        }

        redirect(admin_url('tickets/ticket/' . $ticket_number));
    }

    public function reopen_request_action($ticketId, $decision)
    {
        $ticketId = (int) $ticketId;
        $decision = strtolower($decision);

        if (!in_array($decision, ['approve', 'decline'], true)) {
            show_404();
        }

        $ticket = $this->tickets_model->get_ticket_by_id($ticketId);
        if (!$ticket) {
            set_alert('danger', _l('ticket_not_found'));
            redirect(admin_url('tickets'));
        }

        $ticket_number = $ticket->ticket_number ?? $ticketId;

        $currentUser = (int) get_staff_user_id();
        $assigneeId  = (int) ($ticket->assigned ?? 0);

        if (!is_admin() && $assigneeId !== $currentUser) {
            set_alert('danger', _l('ticket_reopen_request_not_authorized'));
            redirect(admin_url('tickets/ticket/' . $ticket_number));
        }

        $result = $this->tickets_model->resolve_reopen_request($ticketId, $decision, $currentUser);

        if ($result === true) {
            $messageKey = $decision === 'approve' ? 'ticket_reopen_request_approved' : 'ticket_reopen_request_declined';
            set_alert('success', _l($messageKey));
        } else {
            set_alert('danger', is_string($result) ? $result : _l('ticket_reopen_request_failed'));
        }

        redirect(admin_url('tickets/ticket/' . $ticket_number));
    }
    public function edit_message()
    {
        if (! can_staff_edit_ticket_message()) {
            access_denied();
        }

        if ($this->input->post()) {
            $data         = $this->input->post();
            $data['data'] = html_purify($this->input->post('data', false));

            if ($data['type'] == 'reply') {
                $this->db->where('id', $data['id']);
                $this->db->update(db_prefix() . 'ticket_replies', [
                    'message' => $data['data'],
                ]);
            } elseif ($data['type'] == 'ticket') {
                $this->db->where('ticketid', $data['id']);
                $this->db->update(db_prefix() . 'tickets', [
                    'message' => $data['data'],
                ]);
            }
            if ($this->db->affected_rows() > 0) {
                set_alert('success', _l('ticket_message_updated_successfully'));
            }
            redirect(admin_url('tickets/ticket/' . $data['main_ticket']));
        }
    }

    public function delete_ticket_reply($ticket_id, $reply_id)
    {
        if (! $reply_id) {
            redirect(admin_url('tickets'));
        }

        if (! can_staff_delete_ticket_reply()) {
            access_denied('delete ticket');
        }

        $response = $this->tickets_model->delete_ticket_reply($ticket_id, $reply_id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('ticket_reply')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('ticket_reply')));
        }
        redirect(admin_url('tickets/ticket/' . $ticket_id));
    }

    public function change_status_ajax($id, $status)
    {
        // Always return JSON
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id     = (int) $id;
            $status = (int) $status;

            // If there is a pending reassignment for this ticket where current user is the target,
            // do not allow status changes (view-only access)
            $pending = $this->tickets_model->get_pending_reassign($id);
            if ($pending && (int) $pending->to_assigned === (int) get_staff_user_id()) {
                echo json_encode(['alert' => 'danger', 'message' => 'Reassignment pending. View-only access.']);
                return;
            }

            $resp = $this->tickets_model->change_ticket_status($id, $status);
            echo json_encode($resp);
            return;
        } catch (\Throwable $e) {
            log_message('error', 'Tickets::change_status_ajax exception: ' . $e->getMessage());
            echo json_encode(['alert' => 'danger', 'message' => _l('something_went_wrong')]);
            return;
        }
    }

    public function update_single_ticket_settings()
    {
        if ($this->input->post()) {
            $tid = (int)$this->input->post('ticketid');
            $pending = $this->tickets_model->get_pending_reassign($tid);
            if ($pending && (int)$pending->to_assigned === (int)get_staff_user_id()) {
                echo json_encode(['success' => false, 'message' => 'Reassignment pending. View-only access.']);
                exit();
            }
            // Only admin, current ticket owner, or ticket creator can merge tickets
            $mergeInput = $this->input->post('merge_ticket_ids');
            if (!empty($mergeInput)) {
                $ticketRow = $tid ? $this->tickets_model->get_ticket_by_id($tid) : null;
                $isOwnerMerge = $ticketRow && (int)$ticketRow->assigned === (int)get_staff_user_id();
                $isCreatorMerge = $ticketRow && (int)$ticketRow->admin === (int)get_staff_user_id();
                if (!is_admin() && !$isOwnerMerge && !$isCreatorMerge) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Not allowed to merge tickets',
                    ]);
                    exit();
                }
            }
            if ($this->input->post('merge_ticket_ids') !== 0) {
                $ticketsToMerge = explode(',', $this->input->post('merge_ticket_ids') ?: '');

                $alreadyMergedTickets = $this->tickets_model->get_already_merged_tickets($ticketsToMerge);
                if (count($alreadyMergedTickets) > 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => _l('cannot_merge_tickets_with_ids', implode(',', $alreadyMergedTickets)),
                    ]);

                    exit();
                }
            }

            $success = $this->tickets_model->update_single_ticket_settings($this->input->post());
            if ($success) {
                if (get_option('staff_access_only_assigned_departments') == 1) {
                    $ticket = $this->tickets_model->get_ticket_by_id($this->input->post('ticketid'));
                    $this->load->model('departments_model');
                    $staff_departments = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                    if (! in_array($ticket->department, $staff_departments) && ! is_admin()) {
                        set_alert('success', _l('ticket_settings_updated_successfully_and_reassigned', $ticket->department_name));
                        echo json_encode([
                            'success'               => $success,
                            'department_reassigned' => true,
                        ]);

                        exit();
                    }
                }
                set_alert('success', _l('ticket_settings_updated_successfully'));
            }
            echo json_encode([
                'success' => $success,
            ]);

            exit();
        }
    }

    // Priorities
    // Get all ticket priorities
    public function priorities()
    {
        if (! is_admin()) {
            access_denied('Ticket Priorities');
        }
        $data['priorities'] = $this->tickets_model->get_priority();
        $data['title']      = _l('ticket_priorities');
        $this->load->view('admin/tickets/priorities/manage', $data);
    }

    // Add new priority od update existing
    public function priority()
    {
        if (! is_admin()) {
            access_denied('Ticket Priorities');
        }
        if ($this->input->post()) {
            if (! $this->input->post('id')) {
                $id = $this->tickets_model->add_priority($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('ticket_priority')));
                }
            } else {
                $data = $this->input->post();
                $id   = $data['id'];
                unset($data['id']);
                $success = $this->tickets_model->update_priority($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('ticket_priority')));
                }
            }

            exit;
        }
    }

    // Delete ticket priority
    public function delete_priority($id)
    {
        if (! is_admin()) {
            access_denied('Ticket Priorities');
        }
        if (! $id) {
            redirect(admin_url('tickets/priorities'));
        }
        $response = $this->tickets_model->delete_priority($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('ticket_priority_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('ticket_priority')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('ticket_priority_lowercase')));
        }
        redirect(admin_url('tickets/priorities'));
    }

    // List all ticket predefined replies
    public function predefined_replies()
    {
        if (! is_admin()) {
            access_denied('Predefined Replies');
        }
        if ($this->input->is_ajax_request()) {
            $aColumns = [
                'name',
            ];
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'tickets_predefined_replies';
            $result       = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], [
                'id',
            ]);
            $output  = $result['output'];
            $rResult = $result['rResult'];

            foreach ($rResult as $aRow) {
                $row = [];

                for ($i = 0; $i < count($aColumns); $i++) {
                    $_data = $aRow[$aColumns[$i]];
                    if ($aColumns[$i] == 'name') {
                        $_data = '<a href="' . admin_url('tickets/predefined_reply/' . $aRow['id']) . '" class="tw-font-medium">' . e($_data) . '</a>';
                    }
                    $row[] = $_data;
                }

                $options = '<div class="tw-flex tw-items-center tw-space-x-2">';
                $options .= '<a href="' . admin_url('tickets/predefined_reply/' . $aRow['id']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
                    <i class="fa-regular fa-pen-to-square fa-lg"></i>
                </a>';

                $options .= '<a href="' . admin_url('tickets/delete_predefined_reply/' . $aRow['id']) . '"
                class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
                    <i class="fa-regular fa-trash-can fa-lg"></i>
                </a>';
                $options .= '</div>';
                $row[]              = $options;
                $output['aaData'][] = $row;
            }
            echo json_encode($output);

            exit();
        }
        $data['title'] = _l('predefined_replies');
        $this->load->view('admin/tickets/predefined_replies/manage', $data);
    }

    public function get_predefined_reply_ajax($id)
    {
        echo json_encode($this->tickets_model->get_predefined_reply($id));
    }

    public function ticket_change_data()
    {
        if ($this->input->is_ajax_request()) {
            $contact_id = $this->input->post('contact_id');
            echo json_encode([
                'contact_data'          => $this->clients_model->get_contact($contact_id),
                'customer_has_projects' => customer_has_projects(get_user_id_by_contact_id($contact_id)),
            ]);
        }
    }

    // Add new reply or edit existing
    public function predefined_reply($id = '')
    {
        if (! is_admin() && get_option('staff_members_save_tickets_predefined_replies') == '0') {
            access_denied('Predefined Reply');
        }
        if ($this->input->post()) {
            $data              = $this->input->post();
            $data['message']   = html_purify($this->input->post('message', false));
            $ticketAreaRequest = isset($data['ticket_area']);

            if (isset($data['ticket_area'])) {
                unset($data['ticket_area']);
            }

            if ($id == '') {
                $id = $this->tickets_model->add_predefined_reply($data);
                if (! $ticketAreaRequest) {
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('predefined_reply')));
                        redirect(admin_url('tickets/predefined_reply/' . $id));
                    }
                } else {
                    echo json_encode(['success' => $id ? true : false, 'id' => $id]);

                    exit;
                }
            } else {
                $success = $this->tickets_model->update_predefined_reply($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('predefined_reply')));
                }
                redirect(admin_url('tickets/predefined_reply/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('predefined_reply'));
        } else {
            $predefined_reply         = $this->tickets_model->get_predefined_reply($id);
            $data['predefined_reply'] = $predefined_reply;
            $title                    = _l('edit', _l('predefined_reply')) . ' ' . $predefined_reply->name;
        }
        $data['title'] = $title;
        $this->load->view('admin/tickets/predefined_replies/reply', $data);
    }

    // Delete ticket reply from database
    public function delete_predefined_reply($id)
    {
        if (! is_admin()) {
            access_denied('Delete Predefined Reply');
        }
        if (! $id) {
            redirect(admin_url('tickets/predefined_replies'));
        }
        $response = $this->tickets_model->delete_predefined_reply($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('predefined_reply')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('predefined_reply_lowercase')));
        }
        redirect(admin_url('tickets/predefined_replies'));
    }

    // Ticket statuses
    // Get all ticket statuses
    public function statuses()
    {
        if (! is_admin()) {
            access_denied('Ticket Statuses');
        }
        $data['statuses'] = $this->tickets_model->get_ticket_status();
        $data['title']    = 'Ticket statuses';
        $this->load->view('admin/tickets/tickets_statuses/manage', $data);
    }

    // Add new or edit existing status
    public function status()
    {
        if (! is_admin()) {
            access_denied('Ticket Statuses');
        }
        if ($this->input->post()) {
            if (! $this->input->post('id')) {
                $id = $this->tickets_model->add_ticket_status($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('ticket_status')));
                }
            } else {
                $data = $this->input->post();
                $id   = $data['id'];
                unset($data['id']);
                $success = $this->tickets_model->update_ticket_status($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('ticket_status')));
                }
            }

            exit;
        }
    }

    // Delete ticket status from database
    public function delete_ticket_status($id)
    {
        if (! is_admin()) {
            access_denied('Ticket Statuses');
        }
        if (! $id) {
            redirect(admin_url('tickets/statuses'));
        }
        $response = $this->tickets_model->delete_ticket_status($id);
        if (is_array($response) && isset($response['default'])) {
            set_alert('warning', _l('cant_delete_default', _l('ticket_status_lowercase')));
        } elseif (is_array($response) && isset($response['referenced'])) {
            set_alert('danger', _l('is_referenced', _l('ticket_status_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('ticket_status')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('ticket_status_lowercase')));
        }
        redirect(admin_url('tickets/statuses'));
    }

    // List all ticket services
    public function services()
    {
        if (! is_admin()) {
            access_denied('Ticket Services');
        }
        if ($this->input->is_ajax_request()) {
            $aColumns = [
                db_prefix() . 'services.serviceid',
                db_prefix() . 'services.name',
            ];
            $sIndexColumn = 'serviceid';
            $sTable       = db_prefix() . 'services';

            // Joins to fetch related display fields
            $join = [
                'LEFT JOIN ' . db_prefix() . "divisions d ON d.divisionid = " . db_prefix() . 'services.divisionid',
                'LEFT JOIN ' . db_prefix() . "departments dep ON dep.departmentid = " . db_prefix() . 'services.departmentid',
                'LEFT JOIN ' . db_prefix() . "departments dep2 ON dep2.departmentid = " . db_prefix() . 'services.sub_department',
                'LEFT JOIN ' . db_prefix() . "staff st ON st.staffid = " . db_prefix() . 'services.responsible',
                'LEFT JOIN ' . db_prefix() . "applications app ON app.id = " . db_prefix() . 'services.applicationid',
            ];

            // Additional columns to select for output building
            $additionalSelect = [
                db_prefix() . 'services.serviceid as serviceid',
                db_prefix() . 'services.divisionid as divisionid',
                db_prefix() . 'services.departmentid as departmentid',
                db_prefix() . 'services.sub_department as sub_department',
                db_prefix() . 'services.responsible as responsible',
                db_prefix() . 'services.applicationid as applicationid',
                db_prefix() . 'services.staff_type as staff_type',
                'app.name as application_name',
                'd.name as division_name',
                'dep.name as department_name',
                'dep2.name as sub_department_name',
                "CONCAT(st.firstname, ' ', st.lastname) as responsible_name",
            ];

            // Add active column to select
            $additionalSelect[] = db_prefix() . 'services.active as active';

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], $additionalSelect);
            $output  = $result['output'];
            $rResult = $result['rResult'];

            foreach ($rResult as $aRow) {
                $row = [];

                // ID
                $serviceId = (int) $aRow['serviceid'];
                $row[]     = (string) $serviceId;

                // Name (clickable)
                $serviceNameRaw  = $aRow[db_prefix() . 'services.name'] ?? ($aRow['name'] ?? '');
                $serviceNameRaw  = (string) $serviceNameRaw;
                $serviceNameAttr = htmlspecialchars($serviceNameRaw, ENT_QUOTES, 'UTF-8');

                $serviceNameJson = json_encode(
                    $serviceNameRaw,
                    JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
                );
                if ($serviceNameJson === false) {
                    $serviceNameJson = json_encode('');
                }
                $serviceNameJsArg = htmlspecialchars($serviceNameJson, ENT_QUOTES, 'UTF-8');

                $_name = '<a href="#" class="tw-font-medium"'
                    . ' onclick="edit_service(this,' . $serviceId . ');return false"'
                    . ' data-name="' . $serviceNameAttr . '"'
                    . ' data-divisionid="' . (!empty($aRow['divisionid']) ? (int) $aRow['divisionid'] : '') . '"'
                    . ' data-departmentid="' . (!empty($aRow['departmentid']) ? (int) $aRow['departmentid'] : '') . '"'
                    . ' data-sub_department="' . (!empty($aRow['sub_department']) ? (int) $aRow['sub_department'] : '') . '"'
                    . ' data-responsible="' . (!empty($aRow['responsible']) ? (int) $aRow['responsible'] : '') . '"'
                    . ' data-staff_type="' . (!empty($aRow['staff_type']) ? $aRow['staff_type'] : 'department') . '">'
                    . $serviceNameAttr . '</a>';
                $row[] = $_name;

                // Application
                $row[] = $aRow['application_name'] ?? '';

                // Division
                $row[] = $aRow['division_name'] ?? '';

                // Department
                $row[] = $aRow['department_name'] ?? '';

                // Sub Department
                $row[] = $aRow['sub_department_name'] ?? '';

                // Responsible User
                $row[] = $aRow['responsible_name'] ?? '';

                // Active Status
                $active = isset($aRow['active']) ? (int) $aRow['active'] : 1; // Default to active if column doesn't exist
                $status_label = $active ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>';
                $row[] = $status_label;

                // Options with toggle buttons
                $options = '<a href="#" onclick="edit_service(this,' . $serviceId . '); return false;" '
                    . ' data-name="' . $serviceNameAttr . '"'
                    . ' data-divisionid="' . (!empty($aRow['divisionid']) ? (int) $aRow['divisionid'] : '') . '"'
                    . ' data-departmentid="' . (!empty($aRow['departmentid']) ? (int) $aRow['departmentid'] : '') . '"'
                    . ' data-sub_department="' . (!empty($aRow['sub_department']) ? (int) $aRow['sub_department'] : '') . '"'
                    . ' data-responsible="' . (!empty($aRow['responsible']) ? (int) $aRow['responsible'] : '') . '"'
                    . ' data-staff_type="' . (!empty($aRow['staff_type']) ? $aRow['staff_type'] : 'department') . '"'
                    . ' class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 tw-mr-2">'
                    . '<i class="fa-regular fa-pen-to-square fa-lg"></i></a>';

                // Toggle active/inactive button
                $toggle_text = $active ? 'Deactivate' : 'Activate';
                $toggle_class = $active ? 'btn-warning' : 'btn-success';
                $toggle_icon = $active ? 'fa-times' : 'fa-check';
                $options .= '<button type="button" class="btn btn-xs ' . $toggle_class . ' tw-mr-1" onclick="toggle_service_status(' . $serviceId . ', ' . $serviceNameJsArg . ')">'
                    . '<i class="fa ' . $toggle_icon . '"></i> ' . $toggle_text . '</button>';

                $row[]              = $options;
                $output['aaData'][] = $row;
            }
            echo json_encode($output);

            exit();
        }
        $data['title'] = _l('services');
        // Provide divisions and departments for the modal selects
        $this->load->model('divisions_model');
                $this->load->model('departments_model');
        $data['divisions']   = $this->divisions_model->get();
        $data['departments'] = $this->departments_model->get();
        $this->load->model('applications_model');
        $data['applications'] = $this->applications_model->get();
        $this->load->view('admin/tickets/services/manage', $data);
    }

    // Add new service od delete existing one
    public function service($id = '')
    {
        if (! is_admin() && get_option('staff_members_save_tickets_predefined_replies') == '0') {
            access_denied('Ticket Services');
        }

        if ($this->input->post()) {
            $post_data = $this->input->post();
            if (! $this->input->post('id')) {
                $requestFromTicketArea = isset($post_data['ticket_area']);
                if (isset($post_data['ticket_area'])) {
                    unset($post_data['ticket_area']);
                }
                $id = $this->tickets_model->add_service($post_data);
                if (! $requestFromTicketArea) {
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('service')));
                    }
                } else {
                    echo json_encode(['success' => $id ? true : false, 'id' => $id, 'name' => $post_data['name']]);
                }
            } else {
                $id = $post_data['id'];
                unset($post_data['id']);
                $success = $this->tickets_model->update_service($post_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('service')));
                }
            }

            exit;
        }
    }

    // Delete ticket service from database
    public function delete_service($id)
    {
        if (! is_admin()) {
            access_denied('Ticket Services');
        }
        if (! $id) {
            redirect(admin_url('tickets/services'));
        }
        $response = $this->tickets_model->delete_service($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('service_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('service')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('service_lowercase')));
        }
        redirect(admin_url('tickets/services'));
    }

    /* Toggle service active/inactive status */
    public function toggle_service_status($id)
    {
        // Ensure this is an AJAX request
        if (!$this->input->is_ajax_request()) {
            redirect(admin_url('tickets/services'));
        }

        if (!$id) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid service ID'
            ]);
            die;
        }

        $service = $this->tickets_model->get_service($id);
        if (!$service) {
            echo json_encode([
                'success' => false,
                'message' => 'Service not found'
            ]);
            die;
        }

        // Check if active column exists, default to 1 (active) if not
        $current_status = isset($service->active) ? $service->active : 1;
        $new_status = $current_status ? 0 : 1; // Toggle status

        // If deactivating, check for linked tickets
        if ($new_status == 0) {
            $has_linked_tickets = $this->tickets_model->has_linked_tickets($id);
            if ($has_linked_tickets) {
                // Return JSON for AJAX request to show confirmation modal
                $linked_tickets = $this->tickets_model->get_linked_tickets($id);
                echo json_encode([
                    'success' => false,
                    'show_confirmation' => true,
                    'linked_tickets' => $linked_tickets,
                    'service_name' => $service->name
                ]);
                die;
            }
        }

        // Perform the toggle
        $success = $this->tickets_model->toggle_service_status($id, $new_status);

        if (!$success && !isset($service->active)) {
            // Active column doesn't exist
            echo json_encode([
                'success' => false,
                'message' => 'Database migration required. Please run the SQL script to add the active column to the services table.'
            ]);
            die;
        }

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Service status updated successfully' : 'Failed to update service status'
        ]);
        die;
    }

    /* Force deactivate service (bypass confirmation) */
    public function force_deactivate_service($id)
    {
        if (!$id || !$this->input->is_ajax_request()) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request'
            ]);
            die;
        }

        $success = $this->tickets_model->toggle_service_status($id, 0);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Service deactivated successfully' : 'Failed to deactivate service'
        ]);
        die;
    }

    public function block_sender()
    {
        if ($this->input->post()) {
            $this->load->model('spam_filters_model');
            $sender  = $this->input->post('sender');
            $success = $this->spam_filters_model->add(['type' => 'sender', 'value' => $sender], 'tickets');
            if ($success) {
                set_alert('success', _l('sender_blocked_successfully'));
            }
        }
    }

    public function bulk_action()
    {
        hooks()->do_action('before_do_bulk_action_for_tickets');
        if ($this->input->post()) {
            $ids                  = $this->input->post('ids');
            $is_admin             = is_admin();
            $staffCanDeleteTicket = can_staff_delete_ticket();

            if (! is_array($ids)) {
                return;
            }

            if ($this->input->post('merge_tickets')) {
                $primary_ticket = $this->input->post('primary_ticket');
                $status         = $this->input->post('primary_ticket_status');

                if ($this->tickets_model->is_merged($primary_ticket)) {
                    set_alert('warning', _l('cannot_merge_into_merged_ticket'));

                    return;
                }

                $total_merged = $this->tickets_model->merge($primary_ticket, $status, $ids);
            } elseif ($this->input->post('mass_delete')) {
                $total_deleted = 0;
                if ($is_admin || $staffCanDeleteTicket) {
                    foreach ($ids as $id) {
                        if ($this->tickets_model->delete($id)) {
                            $total_deleted++;
                        }
                    }
                } else {
                    ajax_access_denied();

                    return;
                }
            } else {
                $status     = $this->input->post('status');
                $department = $this->input->post('department');
                $service    = $this->input->post('service');
                $priority   = $this->input->post('priority');
                $tags       = $this->input->post('tags');

                foreach ($ids as $id) {
                    if ($status) {
                        $this->db->where('ticketid', $id);
                        $this->db->update(db_prefix() . 'tickets', [
                            'status' => $status,
                        ]);
                    }
                    if ($department) {
                        $this->db->where('ticketid', $id);
                        $this->db->update(db_prefix() . 'tickets', [
                            'department' => $department,
                        ]);
                    }
                    if ($priority) {
                        $this->db->where('ticketid', $id);
                        $this->db->update(db_prefix() . 'tickets', [
                            'priority' => $priority,
                        ]);
                    }

                    if ($service) {
                        $this->db->where('ticketid', $id);
                        $this->db->update(db_prefix() . 'tickets', [
                            'service' => $service,
                        ]);
                    }
                    if ($tags) {
                        handle_tags_save($tags, $id, 'ticket');
                    }
                }
            }

            if ($this->input->post('mass_delete')) {
                set_alert('success', _l('total_tickets_deleted', $total_deleted));
            } elseif ($this->input->post('merge_tickets') && $total_merged > 0) {
                set_alert('success', _l('tickets_merged'));
            }
        }
    }

    // Get ticket handlers list for a ticket (returns JSON)
    public function ticket_handlers($ticketId)
    {
        header('Content-Type: application/json; charset=utf-8');
        $ticketId = (int) $ticketId;
        if ($ticketId <= 0) { echo json_encode([]); return; }

        try {
            // First check if ticket_handlers table exists
            $tableExists = $this->db->table_exists(db_prefix() . 'ticket_handlers');

            // Debug log what we're dealing with
            $debugInfo = [
                'ticketId' => $ticketId,
                'handlersTableExists' => $tableExists,
            ];

            // Count records in handlers table for this ticket
            if ($tableExists) {
                $prev = $this->db->db_debug; $this->db->db_debug = false;
                $handlerCount = $this->db->query('SELECT COUNT(*) as cnt FROM `'.db_prefix().'ticket_handlers` WHERE ticketid = ?', [$ticketId])->row();
                $this->db->db_debug = $prev;
                $debugInfo['handlerEntries'] = (int)($handlerCount->cnt ?? 0);

                // Get raw handler list
                if ($debugInfo['handlerEntries'] > 0) {
                    $prev = $this->db->db_debug; $this->db->db_debug = false;
                    $rawHandlers = $this->db->query('SELECT staffid FROM `'.db_prefix().'ticket_handlers` WHERE ticketid = ?', [$ticketId])->result_array();
                    $this->db->db_debug = $prev;
                    $debugInfo['rawHandlerIds'] = array_column($rawHandlers, 'staffid');
                } else {
                    $debugInfo['rawHandlerIds'] = [];
                }
            }

            $list = $this->tickets_model->get_ticket_handlers($ticketId, true);

            $debugInfo['finalListCount'] = is_array($list) ? count($list) : 0;

            // Only show debug info if there are issues (but currently silent for users)
            if ($debugInfo['handlerEntries'] > 0 && $debugInfo['finalListCount'] == 0) {
                log_message('debug', 'Ticket handlers debug: ' . json_encode($debugInfo));
            }

            if (empty($list)) {
                // Fallback: read raw staff IDs via defensive query (ignore db_debug errors)
                $prev = $this->db->db_debug; $this->db->db_debug = false;
                $rows = $this->db->query('SELECT staffid FROM `'.db_prefix().'ticket_handlers` WHERE ticketid = ?', [$ticketId])->result_array();
                $this->db->db_debug = $prev;
                if (!empty($rows)) {
                    $staffIds = array_map('intval', array_column($rows, 'staffid'));
                    $names = [];
                    if (!empty($staffIds)) {
                        $staffRows = $this->db->select('staffid, firstname, lastname')->from(db_prefix().'staff')->where_in('staffid', $staffIds)->get()->result_array();
                        foreach ($staffRows as $sr) {
                            $names[(int)$sr['staffid']] = trim(($sr['firstname'] ?? '').' '.($sr['lastname'] ?? ''));
                        }
                    }
                    $list = array_map(function($sid) use ($names){
                        $nm = isset($names[$sid]) && $names[$sid] !== '' ? $names[$sid] : ('#'.$sid);
                        return ['staffid' => (int)$sid, 'name' => $nm];
                    }, $staffIds);
                }
            }
            echo json_encode($list);
        } catch (\Throwable $e) {
            log_message('error', 'ticket_handlers error: ' . $e->getMessage());
            // Try fallback even on exception
            try {
                $prev = $this->db->db_debug; $this->db->db_debug = false;
                $rows = $this->db->query('SELECT staffid FROM `'.db_prefix().'ticket_handlers` WHERE ticketid = ?', [$ticketId])->result_array();
                $this->db->db_debug = $prev;
                $list = [];
                if (!empty($rows)) {
                    $staffIds = array_map('intval', array_column($rows, 'staffid'));
                    if (!empty($staffIds)) {
                        $staffRows = $this->db->select('staffid, firstname, lastname')->from(db_prefix().'staff')->where_in('staffid', $staffIds)->get()->result_array();
                        foreach ($staffRows as $sr) {
                            $list[] = [
                                'staffid' => (int)$sr['staffid'],
                                'name'    => trim(($sr['firstname'] ?? '').' '.($sr['lastname'] ?? '')),
                            ];
                        }
                    }
                }
                echo json_encode($list);
            } catch (\Throwable $e2) {
                log_message('error', 'ticket_handlers fallback error: ' . $e2->getMessage());
                echo json_encode([]);
            }
        }
        return;
    }

    // AJAX: Update ticket handlers (current ticket assignee or ticket creator can update)
    public function update_ticket_handlers()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $ticketId = (int)$this->input->post('ticket_id');
        $handlers = $this->input->post('handlers');
        // Normalize handlers into array of ints (accept array or comma-separated string)
        if (is_string($handlers)) {
            $handlers = array_filter(array_map('intval', explode(',', $handlers)));
        } elseif (!is_array($handlers)) {
            $handlers = [];
        }
        $ticket = $this->tickets_model->get_ticket_by_id($ticketId);
        if (!$ticket) { echo json_encode(['success'=>false,'message'=>'Invalid ticket']); die; }
        $current = get_staff_user_id();
        $isOwner   = (int)$ticket->assigned === (int)$current;
        $isCreator = (int)$ticket->admin === (int)$current;
        if (!is_admin() && !$isOwner && !$isCreator) { echo json_encode(['success'=>false,'message'=>'Not allowed']); die; }
        $ok = $this->tickets_model->set_ticket_handlers($ticketId, $handlers);

        // Handle approx resolution time update
        $date = trim($this->input->post('approx_resolution_time_date') ?: '');
        $priority = trim($this->input->post('priority') ?: '');

        $resolutionTime = null;
        if (!empty($date)) {
            $resolutionTime = $date . ' 23:59:59';
        }

        $updateData = [];
        if ($resolutionTime !== null) {
            $updateData['approx_resolution_time'] = $resolutionTime;
        }
        if (!empty($priority)) {
            $updateData['priority'] = (int)$priority;
        }
        if (!empty($updateData)) {
            $this->db->where('ticketid', $ticketId);
            $this->db->update(db_prefix() . 'tickets', $updateData);
        }

        echo json_encode(['success' => $ok]);
        die;
    }

    // Save SLA entries for a ticket
    public function save_sla_entries()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $ticketId = (int)$this->input->post('ticketid');
        $slaTexts = $this->input->post('sla_text');
        $slaEntryKeys = $this->input->post('sla_entry_key');

        // Debug logging
        log_message('debug', 'save_sla_entries called with: '.json_encode([
            'ticketId' => $ticketId,
            'slaTexts_count' => count($slaTexts ?? []),
            'slaEntryKeys' => $slaEntryKeys,
            'files_present' => isset($_FILES['sla_attachments']),
            'files_structure' => isset($_FILES['sla_attachments']) ? json_encode(array_keys($_FILES['sla_attachments'])) : 'No files'
        ]));

        $ticket = $this->tickets_model->get_ticket_by_id($ticketId);
        if (!$ticket) {
            echo json_encode(['success' => false, 'message' => 'Invalid ticket']); die;
        }

        $current = get_staff_user_id();
        $isOwner = (int)$ticket->assigned === (int)$current;
        $isCreator = (int)$ticket->admin === (int)$current;
        if (!is_admin() && !$isOwner && !$isCreator) {
            echo json_encode(['success' => false, 'message' => 'Not allowed']); die;
        }

        // Handle file uploads
        $uploadedFiles = [];
        if (isset($_FILES['sla_attachments']) && is_array($_FILES['sla_attachments']['name'])) {
            $this->load->helper('upload');
            $base_upload_path = FCPATH . 'uploads/ticket_sla_attachments/';

            log_message('debug', 'Processing files: '.json_encode($_FILES['sla_attachments']['name']));

            // Process attachments for each SLA entry
            foreach ($_FILES['sla_attachments']['name'] as $key => $files) {
                log_message('debug', 'Processing entry key: '.$key.' with files: '.json_encode($files));

                if (is_array($files) && !empty($files)) {
                    $uploadedFiles[$key] = [];

                    foreach ($files as $index => $fileName) {
                        if (!empty($fileName)) {
                            log_message('debug', 'Processing file: '.$fileName);

                            // Get the temp file path
                            $tmpFilePath = $_FILES['sla_attachments']['tmp_name'][$key][$index];

                            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                                // Extension validation
                                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                $allowed_extensions = ['pdf', 'doc', 'docx', 'txt', 'jpeg', 'jpg', 'png'];

                                if (in_array($extension, $allowed_extensions)) {
                                    // Create ticket-specific directory
                                    $ticket_dir = $base_upload_path . $ticketId . '/';
                                    if (!file_exists($ticket_dir)) {
                                        mkdir($ticket_dir, 0755, true);
                                        fopen($ticket_dir . 'index.html', 'w');
                                        log_message('debug', 'Created directory: '.$ticket_dir);
                                    }

                                    // Generate unique filename
                                    $filename = unique_filename($ticket_dir, $fileName);
                                    $newFilePath = $ticket_dir . $filename;

                                    log_message('debug', 'Moving file from '.$tmpFilePath.' to '.$newFilePath);

                                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                                        log_message('debug', 'File moved successfully: '.$filename);
                                        $uploadedFiles[$key][] = [
                                            'filename' => $filename,
                                            'original_filename' => $fileName,
                                            'file_path' => $filename, // Relative to ticket directory
                                            'type' => $_FILES['sla_attachments']['type'][$key][$index]
                                        ];
                                    } else {
                                        log_message('error', 'Failed to move uploaded file: '.$tmpFilePath.' to '.$newFilePath);
                                    }
                                } else {
                                    log_message('error', 'File type not allowed: '.$extension);
                                }
                            } else {
                                log_message('error', 'Empty temp file path for file: '.$fileName);
                            }
                        }
                    }
                }
            }
        }

        log_message('debug', 'Uploaded files array: '.json_encode($uploadedFiles));

        $success = $this->tickets_model->save_sla_entries($ticketId, $slaTexts, $uploadedFiles, $slaEntryKeys);

        log_message('debug', 'Save result: '.($success ? 'success' : 'failed'));

        echo json_encode(['success' => $success]);
        die;
    }

    // Get SLA entries for a ticket (JSON)
    public function get_sla_entries($ticketId)
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $ticketId = (int)$ticketId;
        $entries = $this->tickets_model->get_sla_entries($ticketId);

        echo json_encode($entries);
        die;
    }

    // Delete an SLA entry
    public function delete_sla_entry()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $entryId = (int)$this->input->post('entry_id');

        $success = $this->tickets_model->delete_sla_entry($entryId);

        echo json_encode(['success' => $success]);
        die;
    }
}
