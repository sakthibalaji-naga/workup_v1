<?php

use app\services\utilities\Date;
use app\services\tasks\TasksKanban;

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read Staff_model $staff_model
 * @property-read Tasks_model $tasks_model
 * @property-read Projects_model $projects_model
 */
class Tasks extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('projects_model');
        $this->load->model('divisions_model');
        $this->load->model('approval_flow_model');
    }

    /* Open also all taks if user access this /tasks url */
    public function index($id = '')
    {
        $this->list_tasks($id);
    }

    /* List all tasks */
    public function list_tasks($id = '')
    {
        close_setup_menu();
        // If passed from url
        $data['custom_view'] = $this->input->get('custom_view') ? $this->input->get('custom_view') : '';
        $data['taskid']      = $id;

        if ($this->input->get('kanban')) {
            $this->switch_kanban(0, true);
        }

        $data['switch_kanban'] = false;
        $data['bodyclass']     = 'tasks-page';

        if ($this->session->userdata('tasks_kanban_view') == 'true') {
            $data['switch_kanban'] = true;
            $data['bodyclass']     = 'tasks-page kan-ban-body';
        }

        $data['title'] = _l('tasks');
        $data['tasks_table'] = App_table::find('tasks');
        $this->load->view('admin/tasks/manage', $data);
    }

    public function table()
    {
        App_table::find('tasks')->output();
    }

    public function kanban()
    {
        echo $this->load->view('admin/tasks/kan_ban', [], true);
    }

    public function team_tasks()
    {
        if (staff_cant('view', 'tasks') && staff_cant('view_own', 'tasks')) {
            access_denied('tasks');
        }

        $statuses       = $this->tasks_model->get_statuses();
        $defaultStatues = array_map('intval', array_column(array_filter($statuses, function ($status) {
            return (int) $status['id'] !== Tasks_model::STATUS_COMPLETE;
        }), 'id'));

        $currentStaffId = get_staff_user_id();
        $teamStaffIds   = array_map('intval', get_staff_subordinate_ids(null, true, true));
        if ($currentStaffId) {
            $teamStaffIds[] = $currentStaffId;
        }
        $teamStaffIds = array_values(array_unique(array_filter($teamStaffIds)));

        $filtersSubmitted  = (bool) $this->input->get('filters_submitted');
        $requestedStatuses = $this->input->get('status');
        if ($requestedStatuses === null) {
            $requestedStatuses = $filtersSubmitted ? [] : $defaultStatues;
        } elseif ($requestedStatuses === 'all') {
            $requestedStatuses = [];
        }

        $selectedStaff = array_filter(array_map('intval', (array) $this->input->get('team_member')));
        if (! empty($teamStaffIds)) {
            $selectedStaff = array_values(array_intersect($selectedStaff, $teamStaffIds));
        }
        if (empty($selectedStaff)) {
            $selectedStaff = $teamStaffIds;
        }

        $sort          = $this->input->get('sort') ?: 'priority';
        $search        = $this->input->get('search');

        $filters = [
            'status' => $requestedStatuses,
            'staff'  => $selectedStaff,
            'search' => $search,
            'sort'   => $sort,
        ];

        $boardColumns = $this->tasks_model->get_team_tasks_board($filters);
        $members      = $this->staff_model->get('', ['active' => 1]);
        $members      = array_values(array_filter($members, function ($member) use ($teamStaffIds) {
            if (empty($teamStaffIds)) {
                return true;
            }

            return in_array((int) $member['staffid'], $teamStaffIds, true);
        }));
        usort($members, function ($a, $b) {
            return strcasecmp($a['full_name'], $b['full_name']);
        });

        $boardIndexed = [];
        foreach ($boardColumns as $column) {
            $boardIndexed[$column['staffid']] = $column;
        }

        $finalBoard = [];
        foreach ($members as $member) {
            $staffId = (int) $member['staffid'];

            if (! empty($selectedStaff) && ! in_array($staffId, $selectedStaff, true)) {
                continue;
            }

            if (isset($boardIndexed[$staffId])) {
                $finalBoard[] = $boardIndexed[$staffId];
                unset($boardIndexed[$staffId]);
                continue;
            }

            $finalBoard[] = [
                'staffid'       => $staffId,
                'full_name'     => $member['full_name'],
                'profile_image' => $member['profile_image'] ?? null,
                'tasks'         => [],
            ];
        }

        if (! empty($selectedStaff)) {
            foreach ($boardIndexed as $column) {
                $finalBoard[] = $column;
            }
        }

        // Move the current user's column to the first position
        if (!empty($finalBoard)) {
            $currentUserIndex = null;
            foreach ($finalBoard as $index => $column) {
                if ($column['staffid'] == $currentStaffId) {
                    $currentUserIndex = $index;
                    break;
                }
            }
            if ($currentUserIndex !== null && $currentUserIndex > 0) {
                $currentColumn = $finalBoard[$currentUserIndex];
                unset($finalBoard[$currentUserIndex]);
                array_unshift($finalBoard, $currentColumn);
            }
        }

        $data['team_board']        = $finalBoard;
        $data['staff_filters']     = $members;
        $data['statuses']          = $statuses;
        $data['selected_statuses'] = array_map('intval', (array) $requestedStatuses);
        $data['selected_staff']    = $selectedStaff;
        $data['search']            = $search;
        $data['sort']              = $sort;
        $data['title']             = _l('team_tasks');
        $data['bodyclass']         = 'team-tasks-board';

        $this->load->view('admin/tasks/team_tasks', $data);
    }

    public function my_tasks_kanban()
    {
        if (staff_cant('view', 'tasks') && staff_cant('view_own', 'tasks')) {
            access_denied('tasks');
        }

        $statuses       = $this->tasks_model->get_statuses();
        $defaultStatues = array_map('intval', array_column($statuses, 'id'));

        $currentStaffId = get_staff_user_id();

        $filtersSubmitted  = (bool) $this->input->get('filters_submitted');
        $requestedStatuses = $this->input->get('status');
        if ($requestedStatuses === null) {
            $requestedStatuses = $defaultStatues;
        } elseif ($requestedStatuses === 'all') {
            $requestedStatuses = [];
        }

        $sort          = $this->input->get('sort') ?: 'priority';
        $search        = $this->input->get('search');

        $filters = [
            'status' => $requestedStatuses,
            'search' => $search,
            'sort'   => $sort,
        ];

        $boardColumns = $this->tasks_model->get_my_tasks_status_board($currentStaffId, $filters);

        // Add Today and Overdue columns for date-based grouping
        $today = date('Y-m-d');
        $todayTasks = [];
        $overdueTasks = [];

        // Get all tasks to check for today/overdue
        $allTasks = [];
        foreach ($boardColumns as $column) {
            $allTasks = array_merge($allTasks, $column['tasks'] ?? []);
        }

        // Separate tasks into today and overdue
        foreach ($allTasks as $task) {
            if (!empty($task['duedate'])) {
                $dueDate = date('Y-m-d', strtotime($task['duedate']));
                if ($dueDate === $today) {
                    $todayTasks[] = $task;
                } elseif ($dueDate < $today && $task['status'] != Tasks_model::STATUS_COMPLETE) {
                    $overdueTasks[] = $task;
                }
            } elseif (!empty($task['startdate'])) {
                // If no due date but has start date, check if start date is today or past
                $startDate = date('Y-m-d', strtotime($task['startdate']));
                if ($startDate === $today) {
                    $todayTasks[] = $task;
                } elseif ($startDate < $today && $task['status'] != Tasks_model::STATUS_COMPLETE) {
                    $overdueTasks[] = $task;
                }
            }
        }

        // Add Today and Overdue columns at the beginning
        $specialColumns = [
            [
                'status' => 'today',
                'tasks'  => $todayTasks,
            ],
            [
                'status' => 'overdue',
                'tasks'  => $overdueTasks,
            ],
        ];

        // Remove today and overdue tasks from status columns to avoid duplication
        foreach ($boardColumns as &$column) {
            if (isset($column['tasks'])) {
                $column['tasks'] = array_filter($column['tasks'], function($task) use ($todayTasks, $overdueTasks) {
                    $taskId = $task['id'];
                    $isInToday = array_filter($todayTasks, fn($t) => $t['id'] == $taskId);
                    $isInOverdue = array_filter($overdueTasks, fn($t) => $t['id'] == $taskId);
                    return empty($isInToday) && empty($isInOverdue);
                });
            }
        }

        // Ensure all statuses are present in the board, even if empty
        $boardIndexed = [];
        foreach ($boardColumns as $column) {
            $boardIndexed[$column['status']] = $column;
        }

        $finalBoard = [];
        foreach ($statuses as $status) {
            $statusId = (int) $status['id'];

            if (isset($boardIndexed[$statusId])) {
                $finalBoard[] = $boardIndexed[$statusId];
                unset($boardIndexed[$statusId]);
                continue;
            }

            $finalBoard[] = [
                'status' => $statusId,
                'tasks'  => [],
            ];
        }

        if (! empty($boardIndexed)) {
            foreach ($boardIndexed as $column) {
                $finalBoard[] = $column;
            }
        }

        $data['board']           = $finalBoard;
        $data['special_columns'] = $specialColumns;
        $data['statuses']        = $statuses;
        $data['selected_statuses'] = array_map('intval', (array) $requestedStatuses);
        $data['search']          = $search;
        $data['sort']            = $sort;
        $data['title']           = _l('my_tasks_kanban');
        $data['bodyclass']       = 'my-tasks-board';

        $this->load->view('admin/tasks/my_tasks_kanban', $data);
    }

    public function ajax_search_assign_task_to_timer()
    {
        if ($this->input->is_ajax_request()) {
            $q = $this->input->post('q');
            $q = trim($q);
            $this->db->select('name, id,' . tasks_rel_name_select_query() . ' as subtext');
            $this->db->from(db_prefix() . 'tasks');
            $this->db->where('' . db_prefix() . 'tasks.id IN (SELECT taskid FROM ' . db_prefix() . 'task_assigned WHERE staffid = ' . get_staff_user_id() . ')');
            //   $this->db->where('id NOT IN (SELECT task_id FROM '.db_prefix().'taskstimers WHERE staff_id = ' . get_staff_user_id() . ' AND end_time IS NULL)');
            $this->db->where('status != ', 5);
            $this->db->where('billed', 0);
            $this->db->group_start();
            $this->db->like('name', $q);
            $this->db->or_like(tasks_rel_name_select_query(), $q);
            $this->db->group_end();
            echo json_encode($this->db->get()->result_array());
        }
    }

    public function tasks_kanban_load_more()
    {
        $status = $this->input->get('status');
        $page   = $this->input->get('page');

        $tasks = (new TasksKanban($status))
            ->search($this->input->get('search'))
            ->sortBy(
                $this->input->get('sort_by'),
                $this->input->get('sort')
            )
            ->forProject($this->input->get('project_id') ?: null)
            ->page($page)->get();


        foreach ($tasks as $task) {
            $this->load->view('admin/tasks/_kan_ban_card', [
                'task'   => $task,
                'status' => $status,
            ]);
        }
    }

    public function update_order()
    {
        $this->tasks_model->update_order($this->input->post());
    }

    public function switch_kanban($set = 0, $manual = false)
    {
        if ($set == 1) {
            $set = 'false';
        } else {
            $set = 'true';
        }

        $this->session->set_userdata([
            'tasks_kanban_view' => $set,
        ]);

        if ($manual == false) {
            redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
        }
    }

    // Used in invoice add/edit
    public function get_billable_tasks_by_project($project_id)
    {
        if ($this->input->is_ajax_request() && (staff_can('edit',  'invoices') || staff_can('create',  'invoices'))) {
            $customer_id = get_client_id_by_project_id($project_id);
            echo json_encode($this->tasks_model->get_billable_tasks($customer_id, $project_id));
        }
    }

    // Used in invoice add/edit
    public function get_billable_tasks_by_customer_id($customer_id)
    {
        if ($this->input->is_ajax_request() && (staff_can('edit',  'invoices') || staff_can('create',  'invoices'))) {
            echo json_encode($this->tasks_model->get_billable_tasks($customer_id));
        }
    }

    public function update_task_description($id)
    {
        if (staff_can('edit',  'tasks')) {
            $data = hooks()->apply_filters('before_update_task', [
                'description' => html_purify($this->input->post('description', false)),
            ], $id);

            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'tasks', $data);

            hooks()->do_action('after_update_task', $id);
        }
    }

    public function detailed_overview()
    {
        $overview = [];

        $has_permission_create = staff_can('create',  'tasks');
        $has_permission_view   = staff_can('view',  'tasks');

        if (!$has_permission_view) {
            $staff_id = get_staff_user_id();
        } elseif ($this->input->post('member')) {
            $staff_id = $this->input->post('member');
        } else {
            $staff_id = '';
        }

        $month = ($this->input->post('month') ? $this->input->post('month') : date('m'));
        if ($this->input->post() && $this->input->post('month') == '') {
            $month = '';
        }

        $status = $this->input->post('status');

        $fetch_month_from = 'startdate';

        $year       = ($this->input->post('year') ? $this->input->post('year') : date('Y'));
        $project_id = $this->input->get('project_id');

        for ($m = 1; $m <= 12; $m++) {
            if ($month != '' && $month != $m) {
                continue;
            }

            // Task rel_name
            $sqlTasksSelect = '*,' . tasks_rel_name_select_query() . ' as rel_name';

            // Task logged time
            $selectLoggedTime = get_sql_calc_task_logged_time('tmp-task-id');
            // Replace tmp-task-id to be the same like tasks.id
            $selectLoggedTime = str_replace('tmp-task-id', db_prefix() . 'tasks.id', $selectLoggedTime);

            if (is_numeric($staff_id)) {
                $selectLoggedTime .= ' AND staff_id=' . $this->db->escape_str($staff_id);
                $sqlTasksSelect .= ',(' . $selectLoggedTime . ')';
            } else {
                $sqlTasksSelect .= ',(' . $selectLoggedTime . ')';
            }

            $sqlTasksSelect .= ' as total_logged_time';

            // Task checklist items
            $sqlTasksSelect .= ',' . get_sql_select_task_total_checklist_items();

            if (is_numeric($staff_id)) {
                $sqlTasksSelect .= ',(SELECT COUNT(id) FROM ' . db_prefix() . 'task_checklist_items WHERE taskid=' . db_prefix() . 'tasks.id AND finished=1 AND finished_from=' . $staff_id . ') as total_finished_checklist_items';
            } else {
                $sqlTasksSelect .= ',' . get_sql_select_task_total_finished_checklist_items();
            }

            // Task total comment and total files
            $selectTotalComments = ',(SELECT COUNT(id) FROM ' . db_prefix() . 'task_comments WHERE taskid=' . db_prefix() . 'tasks.id';
            $selectTotalFiles    = ',(SELECT COUNT(id) FROM ' . db_prefix() . 'files WHERE rel_id=' . db_prefix() . 'tasks.id AND rel_type="task"';

            if (is_numeric($staff_id)) {
                $sqlTasksSelect .= $selectTotalComments . ' AND staffid=' . $staff_id . ') as total_comments_staff';
                $sqlTasksSelect .= $selectTotalFiles . ' AND staffid=' . $staff_id . ') as total_files_staff';
            }

            $sqlTasksSelect .= $selectTotalComments . ') as total_comments';
            $sqlTasksSelect .= $selectTotalFiles . ') as total_files';

            // Task assignees
            $sqlTasksSelect .= ',' . get_sql_select_task_asignees_full_names() . ' as assignees' . ',' . get_sql_select_task_assignees_ids() . ' as assignees_ids';

            $this->db->select($sqlTasksSelect);

            $this->db->where('MONTH(' . $fetch_month_from . ')', $m);
            $this->db->where('YEAR(' . $fetch_month_from . ')', $year);

            if ($project_id && $project_id != '') {
                $this->db->where('rel_id', $project_id);
                $this->db->where('rel_type', 'project');
            }

            if (!$has_permission_view) {
                $sqlWhereStaff = '(id IN (SELECT taskid FROM ' . db_prefix() . 'task_assigned WHERE staffid=' . $staff_id . ')';

                // User dont have permission for view but have for create
                // Only show tasks createad by this user.
                if ($has_permission_create) {
                    $sqlWhereStaff .= ' OR addedfrom=' . get_staff_user_id();
                }

                $sqlWhereStaff .= ')';
                $this->db->where($sqlWhereStaff);
            } elseif ($has_permission_view) {
                if (is_numeric($staff_id)) {
                    $this->db->where('(id IN (SELECT taskid FROM ' . db_prefix() . 'task_assigned WHERE staffid=' . $staff_id . '))');
                }
            }

            if ($status) {
                $this->db->where('status', $status);
            }

            $this->db->order_by($fetch_month_from, 'ASC');
            array_push($overview, $m);
            $overview[$m] = $this->db->get(db_prefix() . 'tasks')->result_array();
        }

        unset($overview[0]);

        $overview = [
            'staff_id' => $staff_id,
            'detailed' => $overview,
        ];

        $data['members']  = $this->staff_model->get();
        $data['overview'] = $overview['detailed'];
        $data['years']    = $this->tasks_model->get_distinct_tasks_years(($this->input->post('month_from') ? $this->input->post('month_from') : 'startdate'));
        $data['staff_id'] = $overview['staff_id'];
        $data['title']    = _l('detailed_overview');
        $this->load->view('admin/tasks/detailed_overview', $data);
    }

    public function init_relation_tasks($rel_id, $rel_type)
    {
        if ($this->input->is_ajax_request()) {
           App_table::find('related_tasks')->output([
                'rel_id'   => $rel_id,
                'rel_type' => $rel_type,
           ]);
        }
    }

    /* Create new task - loads form on new page */
    public function create()
    {
        if (staff_cant('edit', 'tasks') && staff_cant('create', 'tasks')) {
            access_denied('tasks');
        }

        $data = [];
        // For new task add directly from the projects milestones
        if ($this->input->get('milestone_id')) {
            $this->db->where('id', $this->input->get('milestone_id'));
            $milestone = $this->db->get(db_prefix() . 'milestones')->row();
            if ($milestone) {
                $data['_milestone_selected_data'] = [
                    'id'       => $milestone->id,
                    'due_date' => _d($milestone->due_date),
                ];
            }
        }
        if ($this->input->get('start_date')) {
            $data['start_date'] = $this->input->get('start_date');
        }

        $data['milestones']         = [];
        $data['checklistTemplates'] = $this->tasks_model->get_checklist_templates();

        $title = _l('add_new', _l('task'));

        $data['project_end_date_attrs'] = [];
        if ($this->input->get('rel_type') == 'project' && $this->input->get('rel_id')) {
            $project = $this->projects_model->get($this->input->get('rel_id'));

            if ($project->deadline) {
                $data['project_end_date_attrs'] = [
                    'data-date-end-date' => $project->deadline,
                ];
            }
        }
        $data['members']    = $this->staff_model->get('', ['active' => 1]);
        $data['divisions']  = $this->divisions_model->get();

        $prefill = $this->build_ticket_conversion_prefill(true);
        $data['prefill_task_name']        = $prefill['name'];
        $data['prefill_task_description'] = $prefill['description'];
        $data['prefill_ticket_followers'] = $prefill['followers'];

        $data['is_ticket_to_task']               = (bool) $this->input->get('ticket_to_task');
        $data['can_create_approval_flow']        = staff_can('create', 'approval_flow');
        $currentStaffId = get_staff_user_id();
        $canViewAllApprovalFlows = staff_can('view', 'approval_flow', $currentStaffId);

        $data['available_ticket_approval_flows'] = $data['is_ticket_to_task']
            ? $this->approval_flow_model->get_active_flows($canViewAllApprovalFlows ? null : $currentStaffId)
            : [];

        $data['id']      = '';
        $data['title']   = $title;
        $this->load->view('admin/tasks/task_create', $data);
    }

    /* Add new task or update existing */
    public function task($id = '')
    {
        if (staff_cant('edit', 'tasks') && staff_cant('create', 'tasks')) {
            ajax_access_denied();
        }

        $data = [];
        // FOr new task add directly from the projects milestones
        if ($this->input->get('milestone_id')) {
            $this->db->where('id', $this->input->get('milestone_id'));
            $milestone = $this->db->get(db_prefix() . 'milestones')->row();
            if ($milestone) {
                $data['_milestone_selected_data'] = [
                    'id'       => $milestone->id,
                    'due_date' => _d($milestone->due_date),
                ];
            }
        }
        if ($this->input->get('start_date')) {
            $data['start_date'] = $this->input->get('start_date');
        }
        if ($this->input->post()) {
            $data                = $this->input->post();
            if (isset($data['from_create_page'])) {
                unset($data['from_create_page']);
            }
            $data['description'] = html_purify($this->input->post('description', false));
            $ticketApprovalFlowId = null;
            if (isset($data['ticket_approval_flow_id']) && $data['ticket_approval_flow_id'] !== '') {
                $ticketApprovalFlowId = (int) $data['ticket_approval_flow_id'];
                unset($data['ticket_approval_flow_id']);
            }
            if ($id == '') {
                if (staff_cant('create', 'tasks')) {
                    header('HTTP/1.0 400 Bad error');
                    echo json_encode([
                        'success' => false,
                        'message' => _l('access_denied'),
                    ]);
                    die;
                }

                $id      = $this->tasks_model->add($data);
                $_id     = false;
                $success = false;
                $message = '';
                if ($id) {
                    $success       = true;
                    $_id           = $id;
                    $message       = _l('added_successfully', _l('task'));
                    $uploadedFiles = handle_task_attachments_array($id);
                    if ($uploadedFiles && is_array($uploadedFiles)) {
                        foreach ($uploadedFiles as $file) {
                            $this->misc_model->add_attachment_to_database($id, 'task', [$file]);
                        }
                    }

                    // Initialize task approvals if rel_type is 'approval'
                    if (isset($data['rel_type']) && $data['rel_type'] == 'approval' && isset($data['rel_id'])) {
                        $this->load->model('task_approval_model');
                        $this->task_approval_model->initialize_task_approvals($id, $data['rel_id']);
                    } elseif ($ticketApprovalFlowId) {
                        $this->load->model('task_approval_model');
                        $this->task_approval_model->initialize_task_approvals($id, $ticketApprovalFlowId);
                    }
                }
                echo json_encode([
                    'success' => $success,
                    'id'      => $_id,
                    'message' => $message,
                ]);
            } else {
                if (staff_cant('edit', 'tasks')) {
                    header('HTTP/1.0 400 Bad error');
                    echo json_encode([
                        'success' => false,
                        'message' => _l('access_denied'),
                    ]);
                    die;
                }
                $success = $this->tasks_model->update($data, $id);
                $message = '';
                if ($success) {
                    $message = _l('updated_successfully', _l('task'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'id'      => $id,
                ]);
            }
            die;
        }

        $data['milestones']         = [];
        $data['checklistTemplates'] = $this->tasks_model->get_checklist_templates();

        $copy_from_id = null;
        if ($id == '' && $this->input->get('copy_from')) {
            $copy_from_id = (int) $this->input->get('copy_from');
            if ($copy_from_id > 0) {
                if (staff_cant('create', 'tasks')) {
                    access_denied('tasks');
                }
                $copy_task = $this->tasks_model->get($copy_from_id);
                if (!$copy_task) {
                    show_404();
                }
                $data['task']         = $copy_task;
                $data['is_task_copy'] = true;
                if ($copy_task->rel_type == 'project') {
                    $data['milestones'] = $this->projects_model->get_milestones($copy_task->rel_id);
                }
            }
        }

        if ($id == '') {
            $title = _l('add_new', _l('task'));
            if (isset($data['is_task_copy']) && $data['is_task_copy'] && isset($data['task'])) {
                $title = _l('task_copy') . ': ' . $data['task']->name;
            }
        } else {
            $data['task'] = $this->tasks_model->get($id);
            if ($data['task']->rel_type == 'project') {
                $data['milestones'] = $this->projects_model->get_milestones($data['task']->rel_id);
            }
            $title = _l('edit', _l('task')) . ' ' . $data['task']->name;
        }

        $data['project_end_date_attrs'] = [];
        $project_rel_id                 = null;
        if ($this->input->get('rel_type') == 'project' && $this->input->get('rel_id')) {
            $project_rel_id = $this->input->get('rel_id');
        } elseif (isset($data['task']) && $data['task']->rel_type == 'project') {
            $project_rel_id = $data['task']->rel_id;
        }

        if ($project_rel_id) {
            $project = $this->projects_model->get($project_rel_id);
            if ($project && $project->deadline) {
                $data['project_end_date_attrs'] = [
                    'data-date-end-date' => $project->deadline,
                ];
            }
        }
        $data['members']   = $this->staff_model->get('', ['active' => 1]);
        $data['divisions'] = $this->divisions_model->get();

        $prefill = $this->build_ticket_conversion_prefill($id === '');
        $data['prefill_task_name']        = $prefill['name'];
        $data['prefill_task_description'] = $prefill['description'];
        $data['prefill_ticket_followers'] = $prefill['followers'];
        $data['id']        = $id;
        $data['title']     = $title;

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/tasks/task', $data);
        } else {
            $this->load->view('admin/tasks/task_create', $data);
        }
    }

    public function copy()
    {
        if (staff_can('create',  'tasks')) {
            $new_task_id = $this->tasks_model->copy($this->input->post());
            $response    = [
                'new_task_id' => '',
                'alert_type'  => 'warning',
                'message'     => _l('failed_to_copy_task'),
                'success'     => false,
            ];
            if ($new_task_id) {
                $response['message']     = _l('task_copied_successfully');
                $response['new_task_id'] = $new_task_id;
                $response['success']     = true;
                $response['alert_type']  = 'success';
            }
            echo json_encode($response);
        }
    }

    public function get_billable_task_data($task_id)
    {
        $task              = $this->tasks_model->get_billable_task_data($task_id);
        $task->description = seconds_to_time_format($task->total_seconds) . ' ' . _l('hours');
        echo json_encode($task);
    }

    /**
     * Task page view (full page, not modal)
     * @param  mixed $taskid
     * @return mixed
     */
    public function view($taskid)
    {
        $tasks_where = [];

        if (staff_cant('view', 'tasks')) {
            $tasks_where = get_tasks_where_string(false);
        }

        $task = $this->tasks_model->get($taskid, $tasks_where);

        if (!$task) {
            show_404();
        }

        $data['checklistTemplates'] = $this->tasks_model->get_checklist_templates();
        $data['task']               = $task;
        $data['id']                 = $task->id;
        $data['staff']              = $this->staff_model->get('', ['active' => 1]);
        $data['divisions']          = $this->divisions_model->get();
        $data['reminders']          = $this->tasks_model->get_reminders($taskid);

        $data['task_staff_members'] = $this->tasks_model->get_staff_members_that_can_access_task($taskid);
        // For backward compatibilities
        $data['staff_reminders'] = $data['task_staff_members'];

        $data['hide_completed_items'] = get_staff_meta(get_staff_user_id(), 'task-hide-completed-items-' . $taskid);

        $data['project_deadline'] = null;
        if ($task->rel_type == 'project') {
            $data['project_deadline'] = get_project_deadline($task->rel_id);
        }

        $data['approval_flow'] = null;
        $data['task_approvals'] = null;
        $data['current_user_next_approval'] = null;

        $this->load->model('task_approval_model');
        $approvals = $this->task_approval_model->get_by_task_id($task->id);
        if (! empty($approvals)) {
            $data['task_approvals'] = $this->append_remark_history_to_task_approvals($approvals);
            if ($task->rel_type == 'approval' && ! empty($task->rel_id)) {
                $data['approval_flow'] = $this->approval_flow_model->get($task->rel_id);
            } else {
                $data['approval_flow'] = $this->build_virtual_approval_flow_from_task_approvals($approvals);
            }
            $data['current_user_next_approval'] = $this->task_approval_model->get_next_pending_approval($task->id, get_staff_user_id());
        }

        $data['title'] = $task->name;
        $data['full_page_view'] = true;
        $this->load->view('admin/tasks/view', $data);
    }

    /**
     * Task ajax request modal
     * @param  mixed $taskid
     * @return mixed
     */
    public function get_task_data($taskid, $return = false)
    {
        $tasks_where = [];

        if (staff_cant('view', 'tasks')) {
            $tasks_where = get_tasks_where_string(false);
        }

        $task = $this->tasks_model->get($taskid, $tasks_where);

        if (!$task) {
            header('HTTP/1.0 404 Not Found');
            echo 'Task not found';
            die();
        }

        $data['checklistTemplates'] = $this->tasks_model->get_checklist_templates();
        $data['task']               = $task;
        $data['id']                 = $task->id;
        $data['staff']              = $this->staff_model->get('', ['active' => 1]);
        $data['reminders']          = $this->tasks_model->get_reminders($taskid);

        $data['task_staff_members'] = $this->tasks_model->get_staff_members_that_can_access_task($taskid);
        // For backward compatibilities
        $data['staff_reminders'] = $data['task_staff_members'];

        $data['hide_completed_items'] = get_staff_meta(get_staff_user_id(), 'task-hide-completed-items-' . $taskid);

        $data['project_deadline'] = null;
        if ($task->rel_type == 'project') {
            $data['project_deadline'] = get_project_deadline($task->rel_id);
        }

        $data['approval_flow'] = null;
        $data['task_approvals'] = null;
        $data['current_user_next_approval'] = null;

        $this->load->model('task_approval_model');
        $approvals = $this->task_approval_model->get_by_task_id($task->id);
        if (! empty($approvals)) {
            $data['task_approvals'] = $this->append_remark_history_to_task_approvals($approvals);
            if ($task->rel_type == 'approval' && ! empty($task->rel_id)) {
                $data['approval_flow'] = $this->approval_flow_model->get($task->rel_id);
            } else {
                $data['approval_flow'] = $this->build_virtual_approval_flow_from_task_approvals($approvals);
            }
            $data['current_user_next_approval'] = $this->task_approval_model->get_next_pending_approval($task->id, get_staff_user_id());
        }

        if ($return == false) {
            $this->load->view('admin/tasks/view_task_template', $data);
        } else {
            return $this->load->view('admin/tasks/view_task_template', $data, true);
        }
    }

    public function add_reminder($task_id)
    {
        $message    = '';
        $alert_type = 'warning';
        if ($this->input->post()) {
            $success = $this->misc_model->add_reminder($this->input->post(), $task_id);
            if ($success) {
                $alert_type = 'success';
                $message    = _l('reminder_added_successfully');
            }
        }
        echo json_encode([
            'taskHtml'   => $this->get_task_data($task_id, true),
            'alert_type' => $alert_type,
            'message'    => $message,
        ]);
    }

    public function edit_reminder($id)
    {
        $reminder = $this->misc_model->get_reminders($id);
        if ($reminder && ($reminder->creator == get_staff_user_id() || is_admin()) && $reminder->isnotified == 0) {
            $success = $this->misc_model->edit_reminder($this->input->post(), $id);
            echo json_encode([
                'taskHtml'   => $this->get_task_data($reminder->rel_id, true),
                'alert_type' => 'success',
                'message'    => ($success ? _l('updated_successfully', _l('reminder')) : ''),
            ]);
        }
    }

    public function delete_reminder($rel_id, $id)
    {
        $success    = $this->misc_model->delete_reminder($id);
        $alert_type = 'warning';
        $message    = _l('reminder_failed_to_delete');
        if ($success) {
            $alert_type = 'success';
            $message    = _l('reminder_deleted');
        }
        echo json_encode([
            'taskHtml'   => $this->get_task_data($rel_id, true),
            'alert_type' => $alert_type,
            'message'    => $message,
        ]);
    }

    public function get_staff_started_timers($return = false)
    {
        $data['startedTimers'] = $this->misc_model->get_staff_started_timers();
        $_data['html']         = $this->load->view('admin/tasks/started_timers', $data, true);
        $_data['total_timers'] = count($data['startedTimers']);

        $timers = json_encode($_data);
        if ($return) {
            return $timers;
        }

        echo $timers;
    }

    public function save_checklist_item_template()
    {
        if (staff_can('create',  'checklist_templates')) {
            $id = $this->tasks_model->add_checklist_template($this->input->post('description'));
            echo json_encode(['id' => $id]);
        }
    }

    public function remove_checklist_item_template($id)
    {
        if (staff_can('delete',  'checklist_templates')) {
            $success = $this->tasks_model->remove_checklist_item_template($id);
            echo json_encode(['success' => $success]);
        }
    }

    public function init_checklist_items()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $post_data                       = $this->input->post();
                $data['task_id']                 = $post_data['taskid'];
                $data['checklists']              = $this->tasks_model->get_checklist_items($post_data['taskid']);
                $data['task_staff_members']      = $this->tasks_model->get_staff_members_that_can_access_task($data['task_id']);
                $data['current_user_is_creator'] = $this->tasks_model->is_task_creator(get_staff_user_id(), $data['task_id']);
                $data['hide_completed_items']    = get_staff_meta(get_staff_user_id(), 'task-hide-completed-items-' . $data['task_id']);

                $this->load->view('admin/tasks/checklist_items_template', $data);
            }
        }
    }

    public function task_tracking_stats($task_id)
    {
        $data['stats'] = json_encode($this->tasks_model->task_tracking_stats($task_id));
        $this->load->view('admin/tasks/tracking_stats', $data);
    }

    public function task_timesheets_popup($task_id)
    {
        $data['task'] = $this->tasks_model->get($task_id);
        $this->load->view('admin/tasks/timesheets_popup', $data);
    }

    public function checkbox_action($listid, $value)
    {
        $this->db->where('id', $listid);
        $this->db->update(db_prefix() . 'task_checklist_items', [
            'finished' => $value,
        ]);

        if ($this->db->affected_rows() > 0) {
            if ($value == 1) {
                $this->db->where('id', $listid);
                $this->db->update(db_prefix() . 'task_checklist_items', [
                    'finished_from' => get_staff_user_id(),
                ]);
                hooks()->do_action('task_checklist_item_finished', $listid);
            }
        }
    }

    public function add_checklist_item()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                echo json_encode([
                    'success' => $this->tasks_model->add_checklist_item($this->input->post()),
                ]);
            }
        }
    }

    public function update_checklist_order()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $this->tasks_model->update_checklist_order($this->input->post());
            }
        }
    }

    public function delete_checklist_item($id)
    {
        $list = $this->tasks_model->get_checklist_item($id);
        if (staff_can('delete',  'tasks') || $list->addedfrom == get_staff_user_id()) {
            if ($this->input->is_ajax_request()) {
                echo json_encode([
                    'success' => $this->tasks_model->delete_checklist_item($id),
                ]);
            }
        }
    }

    public function update_checklist_item()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $desc = $this->input->post('description');
                $desc = trim($desc);
                $this->tasks_model->update_checklist_item($this->input->post('listid'), $desc);
                echo json_encode(['can_be_template' => (total_rows(db_prefix() . 'tasks_checklist_templates', ['description' => $desc]) == 0)]);
            }
        }
    }

    public function make_public($task_id)
    {
        if (staff_cant('edit', 'tasks')) {
            json_encode([
                'success' => false,
            ]);
            die;
        }
        echo json_encode([
            'success'  => $this->tasks_model->make_public($task_id),
            'taskHtml' => $this->get_task_data($task_id, true),
        ]);
    }

    public function add_external_attachment()
    {
        if ($this->input->post()) {
            $this->tasks_model->add_attachment_to_database(
                $this->input->post('task_id'),
                $this->input->post('files'),
                $this->input->post('external')
            );
        }
    }

    /* Add new task comment / ajax */
    public function add_task_comment()
    {
        $data   = $this->input->post();
        $taskId = (int) ($data['taskid'] ?? 0);
        $data['taskid'] = $taskId;

        if ($taskId <= 0) {
            echo json_encode([
                'success'    => false,
                'taskHtml'   => '',
                'alert_type' => 'warning',
                'message'    => _l('something_went_wrong'),
            ]);

            return;
        }

        $rawContent = $this->input->post('content', false);

        if ($rawContent === null) {
            $rawContent = $this->input->post('comment', false);
        }

        $rawContent = $rawContent ?? '';

        if ($this->input->post('no_editor')) {
            $data['content'] = nl2br($rawContent);
        } else {
            $data['content'] = html_purify($rawContent);
        }

        $comment_id = false;
        if (
            $data['content'] !== ''
            || (isset($_FILES['file']['name']) && is_array($_FILES['file']['name']) && count($_FILES['file']['name']) > 0)
        ) {
            $comment_id = $this->tasks_model->add_task_comment($data);
            if ($comment_id) {
                $commentAttachments = handle_task_attachments_array($data['taskid'], 'file');
                if ($commentAttachments && is_array($commentAttachments)) {
                    foreach ($commentAttachments as $file) {
                        $file['task_comment_id'] = $comment_id;
                        $this->misc_model->add_attachment_to_database($data['taskid'], 'task', [$file]);
                    }

                    if (count($commentAttachments) > 0) {
                        $this->db->query('UPDATE ' . db_prefix() . "task_comments SET content = CONCAT(content, '[task_attachment]')
                            WHERE id = " . $this->db->escape_str($comment_id));
                    }
                }
            }
        }

        $success    = $comment_id ? true : false;
        $alert_type = $success ? 'success' : 'warning';
        $message    = $success ? _l('task_comment_added') : _l('something_went_wrong');

        $taskHtml = '';
        if ($taskId > 0) {
            $taskHtml = $this->get_task_data($taskId, true);
        }

        echo json_encode([
            'success'    => $success,
            'taskHtml'   => $taskHtml,
            'alert_type' => $alert_type,
            'message'    => $message,
        ]);
    }

    public function download_files($task_id, $comment_id = null)
    {
        $taskWhere = 'external IS NULL';

        if ($comment_id) {
            $taskWhere .= ' AND task_comment_id=' . $this->db->escape_str($comment_id);
        }

        if (staff_cant('view', 'tasks')) {
            $taskWhere .= ' AND ' . get_tasks_where_string(false);
        }

        $files = $this->tasks_model->get_task_attachments($task_id, $taskWhere);

        if (count($files) == 0) {
            redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
        }

        $path = get_upload_path_by_type('task') . $task_id;

        $this->load->library('zip');

        foreach ($files as $file) {
            $this->zip->read_file($path . '/' . $file['file_name']);
        }

        $this->zip->download('files.zip');
        $this->zip->clear_data();
    }

    /* Add new task follower / ajax */
    public function add_task_followers()
    {
        $task = $this->tasks_model->get($this->input->post('taskid'));

        if (staff_can('edit', 'tasks') ||
                ($task->current_user_is_creator && staff_can('create', 'tasks'))) {
            $success = $this->tasks_model->add_task_followers($this->input->post());
            echo json_encode([
                'success'  => $success,
                'taskHtml' => $this->get_task_data($this->input->post('taskid'), true),
                'followerSummaryHtml' => $this->render_follower_summary_html($task->id),
            ]);
        }
    }

    /* Add task assignees / ajax */
    public function add_task_assignees()
    {
        $task = $this->tasks_model->get($this->input->post('taskid'));

        if (staff_can('edit', 'tasks') ||
                ($task->current_user_is_creator && staff_can('create', 'tasks'))) {
            $success = $this->tasks_model->add_task_assignees($this->input->post());
            $assigneeCount = total_rows(db_prefix() . 'task_assigned', ['taskid' => $this->input->post('taskid')]);
            echo json_encode([
                'success'       => $success,
                'taskHtml'      => $this->get_task_data($this->input->post('taskid'), true),
                'assigneeCount' => $assigneeCount,
                'assigneeSummaryHtml' => $this->render_assignee_summary_html($task->id),
            ]);
        }
    }

    public function edit_comment()
    {
        if ($this->input->post()) {
            $data            = $this->input->post();
            $data['content'] = html_purify($this->input->post('content', false));
            if ($this->input->post('no_editor')) {
                $data['content'] = nl2br(clear_textarea_breaks($this->input->post('content')));
            }
            $success = $this->tasks_model->edit_comment($data);
            $message = '';
            if ($success) {
                $message = _l('task_comment_updated');
            }
            echo json_encode([
                'success'  => $success,
                'message'  => $message,
                'taskHtml' => $this->get_task_data($data['task_id'], true),
            ]);
        }
    }

    /* Remove task comment / ajax */
    public function remove_comment($id)
    {
        echo json_encode([
            'success' => $this->tasks_model->remove_comment($id),
        ]);
    }

    /* Remove assignee / ajax */
    public function remove_assignee($id, $taskid)
    {
        $task = $this->tasks_model->get($taskid);

        if (staff_can('edit', 'tasks') ||
                ($task->current_user_is_creator && staff_can('create', 'tasks'))) {
            $success = $this->tasks_model->remove_assignee($id, $taskid);
            $message = '';
            if ($success) {
                $message = _l('task_assignee_removed');
            }
            $assigneeCount = total_rows(db_prefix() . 'task_assigned', ['taskid' => $taskid]);
            echo json_encode([
                'success'       => $success,
                'message'       => $message,
                'taskHtml'      => $this->get_task_data($taskid, true),
                'assigneeCount' => $assigneeCount,
                'assigneeSummaryHtml' => $this->render_assignee_summary_html($taskid),
            ]);
        }
    }

    /* Remove task follower / ajax */
    public function remove_follower($id, $taskid)
    {
        $task = $this->tasks_model->get($taskid);

        if (staff_can('edit', 'tasks') ||
                ($task->current_user_is_creator && staff_can('create', 'tasks'))) {
            $success = $this->tasks_model->remove_follower($id, $taskid);
            $message = '';
            if ($success) {
                $message = _l('task_follower_removed');
            }
            echo json_encode([
                'success'  => $success,
                'message'  => $message,
                'taskHtml' => $this->get_task_data($taskid, true),
                'followerSummaryHtml' => $this->render_follower_summary_html($taskid),
            ]);
        }
    }

    /* Unmark task as complete / ajax*/
    public function unmark_complete($id)
    {
        if (
            $this->tasks_model->is_task_assignee(get_staff_user_id(), $id)
            || $this->tasks_model->is_task_creator(get_staff_user_id(), $id)
            || staff_can('edit',  'tasks')
        ) {
            $success = $this->tasks_model->unmark_complete($id);

            // Don't do this query if the action is not performed via task single
            $taskHtml = $this->input->get('single_task') === 'true' ? $this->get_task_data($id, true) : '';

            $message = '';
            if ($success) {
                $message = _l('task_unmarked_as_complete');
            }
            echo json_encode([
                'success'  => $success,
                'message'  => $message,
                'taskHtml' => $taskHtml,
            ]);
        } else {
            echo json_encode([
                'success'  => false,
                'message'  => '',
                'taskHtml' => '',
            ]);
        }
    }

    public function mark_as($status, $id)
    {
        if (
            $this->tasks_model->is_task_assignee(get_staff_user_id(), $id)
            || $this->tasks_model->is_task_creator(get_staff_user_id(), $id)
            || staff_can('edit',  'tasks')
        ) {
            $success = $this->tasks_model->mark_as($status, $id);

            // Don't do this query if the action is not performed via task single
            $taskHtml = $this->input->get('single_task') === 'true' ? $this->get_task_data($id, true) : '';

            $message = '';

            if ($success) {
                $message = _l('task_marked_as_success', format_task_status($status, true, true));
            }

            echo json_encode([
                'success'  => $success,
                'message'  => $message,
                'taskHtml' => $taskHtml,
            ]);
        } else {
            echo json_encode([
                'success'  => false,
                'message'  => '',
                'taskHtml' => '',
            ]);
        }
    }

    public function change_priority($priority_id, $id)
    {
        if (staff_can('edit',  'tasks')) {
            $data = hooks()->apply_filters('before_update_task', ['priority' => $priority_id], $id);

            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'tasks', $data);

            $success = $this->db->affected_rows() > 0 ? true : false;

            hooks()->do_action('after_update_task', $id);

            // Don't do this query if the action is not performed via task single
            $taskHtml = $this->input->get('single_task') === 'true' ? $this->get_task_data($id, true) : '';
            echo json_encode([
                'success'  => $success,
                'taskHtml' => $taskHtml,
            ]);
        } else {
            echo json_encode([
                'success'  => false,
                'taskHtml' => $taskHtml,
            ]);
        }
    }

    public function change_milestone($milestone_id, $id)
    {
        if (staff_can('edit',  'tasks')) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'tasks', ['milestone' => $milestone_id]);

            $success = $this->db->affected_rows() > 0 ? true : false;
            // Don't do this query if the action is not performed via task single
            $taskHtml = $this->input->get('single_task') === 'true' ? $this->get_task_data($id, true) : '';
            echo json_encode([
                'success'  => $success,
                'taskHtml' => $taskHtml,
            ]);
        } else {
            echo json_encode([
                'success'  => false,
                'taskHtml' => $taskHtml,
            ]);
        }
    }

    public function task_single_inline_update($task_id)
    {
        if (staff_can('edit',  'tasks')) {
            $post_data = $this->input->post();
            foreach ($post_data as $key => $val) {
                $data = hooks()->apply_filters('before_update_task', [
                    $key => to_sql_date($val),
                ], $task_id);

                $this->db->where('id', $task_id);
                $this->db->update(db_prefix() . 'tasks', $data);

                hooks()->do_action('after_update_task', $task_id);
            }
        }
    }

    /* Delete task from database */
    public function delete_task($id)
    {
        if (staff_cant('delete', 'tasks')) {
            access_denied('tasks');
        }
        $success = $this->tasks_model->delete_task($id);
        $message = _l('problem_deleting', _l('task_lowercase'));
        if ($success) {
            $message = _l('deleted', _l('task'));
            set_alert('success', $message);
        } else {
            set_alert('warning', $message);
        }

        if (empty($_SERVER['HTTP_REFERER']) ||
            strpos($_SERVER['HTTP_REFERER'], 'tasks/index') !== false ||
            strpos($_SERVER['HTTP_REFERER'], 'tasks/view') !== false) {
            redirect(admin_url('tasks'));
        } else {
            redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
        }
    }

    /**
     * Remove task attachment
     * @since  Version 1.0.1
     * @param  mixed $id attachment it
     * @return json
     */
    public function remove_task_attachment($id)
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->tasks_model->remove_task_attachment($id));
        }
    }

    /**
     * Upload task attachment
     * @since  Version 1.0.1
     */
    public function upload_file()
    {
        if ($this->input->post()) {
            $taskid  = $this->input->post('taskid');
            $files   = handle_task_attachments_array($taskid, 'file');
            $success = false;

            if ($files) {
                $i   = 0;
                $len = count($files);
                foreach ($files as $file) {
                    $success = $this->tasks_model->add_attachment_to_database($taskid, [$file], false, ($i == $len - 1 ? true : false));
                    $i++;
                }
            }

            echo json_encode([
                'success'  => $success,
                'taskHtml' => $this->get_task_data($taskid, true),
            ]);
        }
    }

    public function timer_tracking()
    {
        $task_id   = $this->input->post('task_id');
        $adminStop = $this->input->get('admin_stop') && is_admin() ? true : false;

        if ($adminStop) {
            $this->session->set_flashdata('task_single_timesheets_open', true);
        }

        echo json_encode([
            'success' => $this->tasks_model->timer_tracking(
                $task_id,
                $this->input->post('timer_id'),
                nl2br($this->input->post('note')),
                $adminStop
            ),
            'taskHtml' => $this->input->get('single_task') === 'true' ? $this->get_task_data($task_id, true) : '',
            'timers'   => $this->get_staff_started_timers(true),
        ]);
    }

    public function delete_user_unfinished_timesheet($id)
    {
        $this->db->where('id', $id);
        $timesheet = $this->db->get(db_prefix() . 'taskstimers')->row();
        if ($timesheet && $timesheet->end_time == null && $timesheet->staff_id == get_staff_user_id()) {
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'taskstimers');
        }
        echo json_encode(['timers' => $this->get_staff_started_timers(true)]);
    }

    public function delete_timesheet($id)
    {
        if (staff_can('delete_timesheet', 'tasks') || staff_can('delete_own_timesheet', 'tasks') && total_rows(db_prefix() . 'taskstimers', ['staff_id' => get_staff_user_id(), 'id' => $id]) > 0) {
            $alert_type = 'warning';
            $success    = $this->tasks_model->delete_timesheet($id);
            if ($success) {
                $this->session->set_flashdata('task_single_timesheets_open', true);
                $message = _l('deleted', _l('project_timesheet'));
                set_alert('success', $message);
            }
            if (!$this->input->is_ajax_request()) {
                redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
            }
        }
    }

    public function update_timesheet()
    {
        if ($this->input->is_ajax_request()) {
            if (staff_can('edit_timesheet', 'tasks') || (staff_can('edit_own_timesheet', 'tasks') && total_rows(db_prefix() . 'taskstimers', ['staff_id' => get_staff_user_id(), 'id' => $this->input->post('timer_id')]) > 0)) {
                $success = $this->tasks_model->timesheet($this->input->post());
                if ($success === true) {
                    $this->session->set_flashdata('task_single_timesheets_open', true);
                    $message = _l('updated_successfully', _l('project_timesheet'));
                } else {
                    $message = _l('failed_to_update_timesheet');
                }

                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
                die;
            }

            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die;
        }
    }

    public function log_time()
    {
        $success = $this->tasks_model->timesheet($this->input->post());
        if ($success === true) {
            $this->session->set_flashdata('task_single_timesheets_open', true);
            $message = _l('added_successfully', _l('project_timesheet'));
        } elseif (is_array($success) && isset($success['end_time_smaller'])) {
            $message = _l('failed_to_add_project_timesheet_end_time_smaller');
        } else {
            $message = _l('project_timesheet_not_updated');
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
        die;
    }

    public function update_tags()
    {
        if (staff_can('create',  'tasks') || staff_can('edit',  'tasks')) {
            $id = $this->input->post('task_id');

            $data = hooks()->apply_filters('before_update_task', [
                'tags' => $this->input->post('tags'),
            ], $id);

            handle_tags_save($data['tags'], $id, 'task');

            hooks()->do_action('after_update_task', $id);
        }
    }

    public function bulk_action()
    {
        hooks()->do_action('before_do_bulk_action_for_tasks');
        $total_deleted = 0;
        if ($this->input->post()) {
            $status    = $this->input->post('status');
            $ids       = $this->input->post('ids');
            $tags      = $this->input->post('tags');
            $assignees = $this->input->post('assignees');
            $milestone = $this->input->post('milestone');
            $priority  = $this->input->post('priority');
            $billable  = $this->input->post('billable');
            $is_admin  = is_admin();
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if (staff_can('delete',  'tasks')) {
                            if ($this->tasks_model->delete_task($id)) {
                                $total_deleted++;
                            }
                        }
                    } else {
                        if ($status) {
                            if (
                                $this->tasks_model->is_task_creator(get_staff_user_id(), $id)
                                || $is_admin
                                || $this->tasks_model->is_task_assignee(get_staff_user_id(), $id)
                            ) {
                                $this->tasks_model->mark_as($status, $id);
                            }
                        }
                        if ($priority || $milestone || ($billable === 'billable' || $billable === 'not_billable')) {
                            $update = [];

                            if ($priority) {
                                $update['priority'] = $priority;
                            }

                            if ($milestone) {
                                $update['milestone'] = $milestone;
                            }

                            if ($billable) {
                                $update['billable'] = $billable === 'billable' ? 1 : 0;
                            }

                            $this->db->where('id', $id);
                            $this->db->update(db_prefix() . 'tasks', $update);
                        }
                        if ($tags) {
                            handle_tags_save($tags, $id, 'task');
                        }
                        if ($assignees) {
                            $notifiedUsers = [];
                            foreach ($assignees as $user_id) {
                                if (!$this->tasks_model->is_task_assignee($user_id, $id)) {
                                    $this->db->select('rel_type,rel_id');
                                    $this->db->where('id', $id);
                                    $task = $this->db->get(db_prefix() . 'tasks')->row();
                                    if ($task->rel_type == 'project') {
                                        // User is we are trying to assign the task is not project member
                                        if (total_rows(db_prefix() . 'project_members', ['project_id' => $task->rel_id, 'staff_id' => $user_id]) == 0) {
                                            $this->db->insert(db_prefix() . 'project_members', ['project_id' => $task->rel_id, 'staff_id' => $user_id]);
                                        }
                                    }
                                    $this->db->insert(db_prefix() . 'task_assigned', [
                                        'staffid'       => $user_id,
                                        'taskid'        => $id,
                                        'assigned_from' => get_staff_user_id(),
                                    ]);
                                    if ($user_id != get_staff_user_id()) {
                                        $notification_data = [
                                            'description' => 'not_task_assigned_to_you',
                                            'touserid'    => $user_id,
                                            'link'        => '#taskid=' . $id,
                                        ];

                                        $notification_data['additional_data'] = serialize([
                                            get_task_subject_by_id($id),
                                        ]);
                                        if (add_notification($notification_data)) {
                                            array_push($notifiedUsers, $user_id);
                                        }
                                    }
                                }
                            }
                            pusher_trigger_notification($notifiedUsers);
                        }
                    }
                }
            }
            if ($this->input->post('mass_delete')) {
                set_alert('success', _l('total_tasks_deleted', $total_deleted));
            }
        }
    }

    public function gantt_date_update($task_id)
    {
        if (staff_can('edit', 'tasks')) {
            $post_data = $this->input->post();
            foreach ($post_data as $key => $val) {
                $this->db->where('id', $task_id);
                $this->db->update(db_prefix() . 'tasks', [$key => $val]);
            }
        }
    }

    public function get_task_by_id($id)
    {
        if ($this->input->is_ajax_request()) {
            $tasks_where = [];
            if (staff_cant('view', 'tasks')) {
                $tasks_where = get_tasks_where_string(false);
            }
            $task = $this->tasks_model->get($id, $tasks_where);
            if (!$task) {
                header('HTTP/1.0 404 Not Found');
                echo 'Task not found';
                die();
            }
            echo json_encode($task);
        }
    }

    public function get_staff_names_for_mentions($taskid)
    {
        if ($this->input->is_ajax_request()) {
            $taskId = $this->db->escape_str($taskid);

            $members = $this->tasks_model->get_staff_members_that_can_access_task($taskId);
            $members = array_map(function ($member) {
                $_member['id'] = $member['staffid'];
                $_member['name'] = e($member['firstname'] . ' ' . $member['lastname']);

                return $_member;
            }, $members);

            echo json_encode($members);
        }
    }

    public function save_checklist_assigned_staff()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            $payload = $this->input->post();
            $item    = $this->tasks_model->get_checklist_item($payload['checklistId']);
            if ($item->addedfrom == get_staff_user_id()
                || is_admin() ||
                $this->tasks_model->is_task_creator(get_staff_user_id(), $payload['taskId'])) {
                $this->tasks_model->update_checklist_assigned_staff($payload);
                die;
            }

            ajax_access_denied();
        }
    }

    /**
     * Submit step approval with remarks and attachments
     */
    public function submit_step_approval()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Direct access not allowed');
        }

        $task_id = $this->input->post('task_id');
        $step_id = $this->input->post('step_id');
        $action_type = $this->input->post('action_type', true);
        $comments = $this->input->post('description', true);

        if (!$task_id || !$step_id) {
            echo json_encode([
                'success' => false,
                'message' => _l('something_went_wrong'),
            ]);
            return;
        }

        $this->load->model('task_approval_model');

        // Handle attachment upload if present
        $attachment_uploaded = false;
        $uploaded_files = handle_task_attachments_array($task_id, 'attachment');
        if ($uploaded_files && is_array($uploaded_files)) {
            $attachment_uploaded = true;
            foreach ($uploaded_files as $file) {
                $this->misc_model->add_attachment_to_database($task_id, 'task', [$file]);
            }
        }

        // Perform approval/rejection action based on action_type
        $success = false;
        $message = '';
        $alert_type = 'warning';

        if ($action_type === 'remark') {
            // Save remarks without changing approval status
            $success = $this->task_approval_model->save_remarks($task_id, get_staff_user_id(), $comments);
            $message = $success ? _l('task_approval_remark_added') : _l('task_approval_remark_failed');
            $alert_type = $success ? 'success' : 'warning';
        } else {
            // Handle approve/reject actions (this would be for buttons in UI)
            if ($action_type === 'approve') {
                $success = $this->task_approval_model->approve_step($task_id, get_staff_user_id(), $comments);
                if ($success) {
                    $message = _l('task_approval_approved');
                    $alert_type = 'success';
                } else {
                    $message = _l('task_approval_failed');
                }
            } elseif ($action_type === 'reject') {
                $success = $this->task_approval_model->reject_step($task_id, get_staff_user_id(), $comments);
                if ($success) {
                    $message = _l('task_approval_rejected');
                    $alert_type = 'success';
                } else {
                    $message = _l('task_approval_failed');
                }
            }
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'alert_type' => $alert_type,
            'taskHtml' => $this->get_task_data($task_id, true),
        ]);
    }

    /**
     * Approve task step
     */
    public function approve_task_step($task_id)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('task_approval_model');
            $comments = $this->input->post('comments', true);

            $success = $this->task_approval_model->approve_step($task_id, get_staff_user_id(), $comments);

            $message = '';
            $alert_type = 'warning';

            if ($success) {
                $alert_type = 'success';
                $message = _l('task_approval_approved');
            } else {
                $message = _l('task_approval_failed');
            }

            echo json_encode([
                'success' => $success,
                'message' => $message,
                'alert_type' => $alert_type,
                'taskHtml' => $this->get_task_data($task_id, true),
            ]);
        }
    }

    /**
     * Reject task step
     */
    public function reject_task_step($task_id)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('task_approval_model');
            $comments = $this->input->post('comments', true);

            $success = $this->task_approval_model->reject_step($task_id, get_staff_user_id(), $comments);

            $message = '';
            $alert_type = 'warning';

            if ($success) {
                $alert_type = 'success';
                $message = _l('task_approval_rejected');
            } else {
                $message = _l('task_approval_failed');
            }

            echo json_encode([
                'success' => $success,
                'message' => $message,
                'alert_type' => $alert_type,
                'taskHtml' => $this->get_task_data($task_id, true),
            ]);
        }
    }

    /**
     * Revert an approved/rejected task step back to pending
     */
    public function revert_task_step()
    {
        if (! $this->input->is_ajax_request()) {
            show_error('Direct access not allowed');
        }

        $task_id = (int) $this->input->post('task_id');
        $approval_id = (int) $this->input->post('approval_id');
        $reason = trim((string) $this->input->post('reason', true));

        if ($task_id <= 0 || $approval_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => _l('task_approval_revert_failed'),
            ]);
            return;
        }

        $this->load->model('task_approval_model');
        $approval = $this->task_approval_model->find($approval_id);

        if (! $approval || (int) $approval['task_id'] !== $task_id) {
            echo json_encode([
                'success' => false,
                'message' => _l('task_approval_revert_failed'),
            ]);
            return;
        }

        $actorId = (int) get_staff_user_id();
        $hasAccess = $actorId === (int) $approval['staff_id'];

        if (! $hasAccess) {
            echo json_encode([
                'success' => false,
                'message' => _l('task_approval_revert_no_permission'),
            ]);
            return;
        }

        if ($reason === '') {
            echo json_encode([
                'success' => false,
                'message' => _l('task_approval_revert_reason_required'),
            ]);
            return;
        }

        $success = $this->task_approval_model->revert_step($approval_id, $actorId, $reason);

        $message = $success ? _l('task_approval_revert_success') : _l('task_approval_revert_failed');
        $alert_type = $success ? 'success' : 'warning';

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'alert_type' => $alert_type,
            'taskHtml' => $this->get_task_data($task_id, true),
        ]);
    }

    /**
     * Fetch remark history modal html
     */
    public function get_remark_history_modal()
    {
        if (! $this->input->is_ajax_request()) {
            show_error('Direct access not allowed');
        }

        $task_id = (int) $this->input->post('task_id');
        $approval_id = (int) $this->input->post('approval_id');

        if ($task_id <= 0 || $approval_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => _l('task_approval_remark_history_modal_error'),
            ]);
            return;
        }

        $tasks_where = [];

        if (staff_cant('view', 'tasks')) {
            $tasks_where = get_tasks_where_string(false);
        }

        $task = $this->tasks_model->get($task_id, $tasks_where);

        if (! $task) {
            echo json_encode([
                'success' => false,
                'message' => _l('task_approval_remark_history_modal_error'),
            ]);
            return;
        }

        $this->load->model('task_approval_model');
        $approval = $this->task_approval_model->find($approval_id);

        if (! $approval || (int) $approval['task_id'] !== (int) $task->id) {
            echo json_encode([
                'success' => false,
                'message' => _l('task_approval_remark_history_modal_error'),
            ]);
            return;
        }

        $history = $this->task_approval_model->get_remark_history($approval_id);

        $html = $this->load->view('admin/tasks/remark_history_modal', [
            'task' => $task,
            'approval' => $approval,
            'history' => $history,
        ], true);

        echo json_encode([
            'success' => true,
            'html' => $html,
        ]);
    }

    /**
     * Render the task summary assignee avatar block for async updates.
     *
     * @param  int $taskId
     * @return string
     */
    private function append_remark_history_to_task_approvals($approvals)
    {
        if (empty($approvals)) {
            return $approvals;
        }

        $approvalIds = array_column($approvals, 'id');

        if (empty($approvalIds)) {
            return $approvals;
        }

        $histories = $this->task_approval_model->get_remark_history_for_approvals($approvalIds);

        foreach ($approvals as &$approval) {
            $approval['remark_history'] = $histories[$approval['id']] ?? [];
        }

        unset($approval);

        return $approvals;
    }

    /**
     * Build a virtual approval flow object derived from existing task approvals.
     * This keeps the approval UI functional even when the task is related to a ticket,
     * by inferring the flow steps from the stored approval rows.
     *
     * @param  array $approvals
     * @return object
     */
    private function build_virtual_approval_flow_from_task_approvals($approvals)
    {
        $steps = [];
        foreach ($approvals as $approval) {
            $order    = (int) ($approval['step_order'] ?? 0);
            $stepName = isset($approval['step_name']) && $approval['step_name'] !== ''
                ? $approval['step_name']
                : 'Step ' . ($order > 0 ? $order : 1);

            $steps[] = [
                'id'         => $approval['id'] ?? null,
                'staff_id'   => (int) ($approval['staff_id'] ?? 0),
                'step_order' => $order,
                'firstname'  => $approval['firstname'] ?? '',
                'lastname'   => $approval['lastname'] ?? '',
                'step_name'  => $stepName,
            ];
        }

        return (object) [
            'name'  => _l('task_approvals_heading'),
            'steps' => $steps,
        ];
    }

    private function render_assignee_summary_html($taskId)
    {
        $task = $this->tasks_model->get($taskId);

        if (! $task) {
            return '';
        }

        $assignees = [];
        if (! empty($task->assignees)) {
            foreach ($task->assignees as $assignee) {
                $fullname = trim(($assignee['firstname'] ?? '') . ' ' . ($assignee['lastname'] ?? ''));
                if ($fullname === '') {
                    $fullname = get_staff_full_name($assignee['assigneeid']);
                }

                $assignees[] = [
                    'name' => $fullname,
                    'id'   => $assignee['assigneeid'],
                    'img'  => staff_profile_image(
                        $assignee['assigneeid'],
                        ['staff-profile-image-small', 'task-summary-avatar'],
                        'small',
                        ['alt' => $fullname]
                    ),
                    'url'  => admin_url('profile/' . $assignee['assigneeid']),
                ];
            }
        }

        $canManageAssignees = staff_can('edit', 'tasks')
            || ($task->current_user_is_creator && staff_can('create', 'tasks'));

        return $this->load->view('admin/tasks/_summary_assignees', [
            'assignees'          => $assignees,
            'taskId'             => $taskId,
            'canManageAssignees' => $canManageAssignees,
        ], true);
    }

    /**
     * Render the task summary follower avatar block for async updates.
     *
     * @param  int $taskId
     * @return string
     */
    private function render_follower_summary_html($taskId)
    {
        $task = $this->tasks_model->get($taskId);

        if (! $task) {
            return '';
        }

        $followers = [];
        if (! empty($task->followers)) {
            foreach ($task->followers as $follower) {
                $fullname = trim(($follower['firstname'] ?? '') . ' ' . ($follower['lastname'] ?? ''));
                if ($fullname === '') {
                    $fullname = get_staff_full_name($follower['followerid']);
                }

                $followers[] = [
                    'name' => $fullname,
                    'id'   => $follower['followerid'],
                    'img'  => staff_profile_image(
                        $follower['followerid'],
                        ['staff-profile-image-small', 'task-summary-avatar'],
                        'small',
                        ['alt' => $fullname]
                    ),
                    'url'  => admin_url('profile/' . $follower['followerid']),
                ];
            }
        }

        $canManageFollowers = staff_can('edit', 'tasks')
            || ($task->current_user_is_creator && staff_can('create', 'tasks'));

        return $this->load->view('admin/tasks/_summary_followers', [
            'followers'          => $followers,
            'taskId'             => $taskId,
            'canManageFollowers' => $canManageFollowers,
        ], true);
    }

    private function build_ticket_conversion_prefill(bool $allowPrefill): array
    {
        $prefill = [
            'name'        => '',
            'description' => '',
            'followers'   => [],
        ];

        if (! $allowPrefill) {
            return $prefill;
        }

        $shouldPrefillFromTicket = $this->input->get('ticket_to_task')
            && $this->input->get('rel_type') === 'ticket'
            && is_numeric($this->input->get('rel_id'));

        if (! $shouldPrefillFromTicket) {
            return $prefill;
        }

        $ticketId = (int) $this->input->get('rel_id');
        if ($ticketId <= 0) {
            return $prefill;
        }

        $this->load->model('tickets_model');
        $ticket = $this->tickets_model->get_ticket_by_id($ticketId);

        if (! $ticket) {
            return $prefill;
        }

        $prefill['name']        = trim((string) ($ticket->subject ?? ''));
        $prefill['description'] = (string) ($ticket->message ?? '');
        $prefill['followers']   = $this->tickets_model->get_ticket_staff_followers($ticketId, $ticket);

        $replyId = (int) $this->input->get('ticket_reply_id');
        if ($replyId > 0) {
            $reply = $this->db->select('message')
                ->from(db_prefix() . 'ticket_replies')
                ->where('ticketid', $ticketId)
                ->where('id', $replyId)
                ->get()
                ->row();

            if ($reply) {
                $prefill['description'] = (string) $reply->message;
            }
        }

        return $prefill;
    }
}
