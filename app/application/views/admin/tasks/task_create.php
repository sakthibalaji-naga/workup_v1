<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .task-create-shell {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.85) 0%, #ffffff 60%);
        border-radius: 18px;
        padding: 32px 36px;
        box-shadow: 0 22px 46px rgba(15, 23, 42, 0.08);
        position: relative;
        overflow: hidden;
    }
    .task-create-shell::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 18px;
        padding: 1.6px;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.18), rgba(14, 165, 233, 0.08), rgba(147, 51, 234, 0.16));
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask-composite: exclude;
        pointer-events: none;
    }
    .task-create-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 30px;
    }
    .task-create-title {
        margin: 0;
        font-size: 32px;
        font-weight: 600;
        color: #1e293b;
        letter-spacing: -0.02em;
    }
    .task-create-subtitle {
        margin: 8px 0 0;
        font-size: 15px;
        color: #64748b;
        max-width: 540px;
    }
    .task-create-meta {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 500;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.16);
        color: #1d4ed8;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.15);
    }
    .task-create-meta i {
        font-size: 13px;
    }
    .task-section {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 14px;
        padding: 24px 26px;
        margin-bottom: 24px;
        box-shadow: 0 14px 24px rgba(15, 23, 42, 0.04);
    }
    .task-section + .task-section {
        margin-top: 28px;
    }
    .task-section-header {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        margin-bottom: 18px;
        width: 100%;
    }
    .task-section-title {
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        letter-spacing: 0.02em;
    }
    .task-section-title i {
        color: #2563eb;
        font-size: 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.1);
        border-radius: 10px;
        width: 28px;
        height: 28px;
    }
    .task-section-caption {
        font-size: 13px;
        color: #64748b;
        margin: 0;
        padding-left: 38px;
        max-width: 100%;
    }
    .approval-rel-select-group {
        display: block;
    }
    .approval-rel-dropdown-add {
        font-weight: 600;
        color: #16a34a;
        display: block;
        padding: 6px 12px;
    }
    .approval-rel-loading {
        margin-top: 6px;
        font-size: 12px;
        color: #64748b;
        display: none;
        align-items: center;
        gap: 6px;
    }
    .approval-rel-loading:not(.hide) {
        display: inline-flex;
    }
    .task-people-heading {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .task-people-heading .task-people-icon {
        color: #1d4ed8;
        font-size: 18px;
    }
    #task-form .form-control,
    #task-form .selectpicker + .dropdown-toggle {
        border-radius: 10px;
        border-color: #e2e8f0;
        box-shadow: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    #task-form .form-control:focus,
    #task-form .bootstrap-select .dropdown-toggle:focus,
    #task-form .bootstrap-select.open > .dropdown-toggle {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .task-select-trigger {
        border-radius: 10px !important;
        border-color: #dbeafe !important;
        background: #f8fafc !important;
        color: #1e293b !important;
        font-weight: 500;
    }
    .task-select-trigger:hover,
    .task-select-trigger:focus,
    .bootstrap-select.open > .task-select-trigger {
        background: #ffffff !important;
        border-color: #2563eb !important;
    }
    .task-create-checkbox-group {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f8fafc;
        border-radius: 12px;
        padding: 12px 16px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        margin-bottom: 20px;
    }
    .task-create-checkbox-group label {
        margin: 0;
        font-weight: 500;
        color: #1f2937;
    }
    .task-create-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }
    .task-select-toolbar {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 12px 0 14px;
        flex-wrap: wrap;
    }
    .task-select-toolbar button {
        border: 1px solid #dbeafe;
        background: #f1f5f9;
        color: #1d4ed8;
        padding: 6px 16px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.12);
    }
    .ticket-approval-flow-cta {
        border-radius: 16px;
        border: 1px solid rgba(59, 130, 246, 0.25);
        background: rgba(191, 219, 254, 0.3);
        padding: 18px 22px;
        margin-bottom: 24px;
    }
    .ticket-approval-flow-cta h5 {
        margin: 0 0 4px;
        font-size: 15px;
        font-weight: 600;
        color: #1e3a8a;
    }
    .ticket-approval-flow-cta p {
        margin: 0;
        color: #1e293b;
        font-size: 13px;
    }
    .ticket-approval-flow-cta__actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .ticket-approval-flow-cta__select {
        margin-top: 16px;
    }
    .task-select-toolbar button:hover {
        background: #dbeafe;
        color: #1d4ed8;
    }
    .task-select-toolbar button.active {
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
        box-shadow: 0 8px 16px rgba(37, 99, 235, 0.18);
    }
    .task-select-toolbar .task-select-clear {
        margin-left: auto;
        color: #64748b;
        border: 1px solid #e2e8f0;
        background: #fff;
    }
    .task-select-toolbar .task-select-clear:hover {
        color: #1f2937;
        border-color: #cbd5f5;
    }
    .task-chips {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        padding: 10px;
        background: #f8fafc;
        border: 1px solid #dbeafe;
        border-radius: 12px;
        min-height: 48px;
        align-items: center;
        margin-bottom: 8px;
    }
    .task-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: #2563eb;
        color: #fff;
        font-size: 13px;
        font-weight: 500;
        box-shadow: 0 6px 12px rgba(37, 99, 235, 0.18);
    }
    .task-chip--secondary {
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
        box-shadow: none;
    }
    .task-chip__remove {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
        font-size: 12px;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    .task-chip--secondary .task-chip__remove {
        background: rgba(37, 99, 235, 0.15);
        color: #1d4ed8;
    }
    .task-chip__remove:hover {
        background: rgba(255, 255, 255, 0.45);
    }
    .task-chip--secondary .task-chip__remove:hover {
        background: rgba(37, 99, 235, 0.25);
    }
    .task-chip__avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #2563eb, #22d3ee);
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
    }
    .task-chip__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .task-chip__overflow {
        position: relative;
        padding: 6px 12px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #0f172a;
        font-size: 13px;
        font-weight: 500;
        cursor: default;
        padding-bottom: 12px;
    }
    .task-chip__overflow:hover .task-chip__tooltip,
    .task-chip__count:hover .task-chip__tooltip,
    .task-chip__tooltip:hover {
        opacity: 1;
        visibility: visible;
        transform: translate(-50%, 0);
    }
    .task-chip__tooltip {
        position: absolute;
        left: 50%;
        bottom: calc(100% + 4px);
        transform: translate(-50%, 4px);
        background: #0f172a;
        color: #fff;
        font-size: 12px;
        line-height: 1.4;
        border-radius: 8px;
        padding: 8px 12px;
        min-width: 200px;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.25);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, transform 0.2s ease;
        z-index: 30;
        text-align: left;
        white-space: normal;
        pointer-events: auto;
    }
    .task-chip__tooltip:after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border-width: 6px;
        border-style: solid;
        border-color: #0f172a transparent transparent transparent;
    }
    .task-chip__count {
        position: relative;
        cursor: default;
    }
    .task-chip__tooltip-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 5px 0;
        cursor: pointer;
    }
    .task-chip__tooltip-item span {
        color: #fff;
        font-size: 12px;
    }
    .task-chip__tooltip-item span:first-child {
        flex: 1;
        text-align: left;
    }
    .task-chip__tooltip-item span:first-child {
        flex: 1;
        text-align: left;
    }
    .task-chip__tooltip-remove {
        font-weight: 600;
        font-size: 13px;
        padding: 2px 6px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.15);
    }
    .task-chip__tooltip-item:hover .task-chip__tooltip-remove {
        background: rgba(255, 255, 255, 0.3);
    }
    .task-chip__count {
        margin-left: auto;
        position: relative;
        padding-bottom: 12px;
    }
    .task-subsection-title {
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        color: #1d4ed8;
        letter-spacing: 0.08em;
        margin: 20px 0 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .task-subsection-title i {
        font-size: 12px;
        color: #2563eb;
    }
    .bootstrap-select .dropdown-menu {
        border-radius: 14px;
        border: 1px solid rgba(15, 23, 42, 0.06);
        box-shadow: 0 18px 30px rgba(15, 23, 42, 0.12);
        padding-top: 10px;
        padding-bottom: 10px;
    }
    .bootstrap-select .bs-searchbox .form-control {
        border-radius: 999px;
        padding-left: 42px;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"%2362748b\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"m21 21-4.35-4.35M11.25 18a6.75 6.75 0 1 1 0-13.5 6.75 6.75 0 0 1 0 13.5z\"/></svg>') no-repeat 14px center;
        background-size: 18px;
    }
    .assignee-option {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .assignee-option__avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #22d3ee);
        color: #fff;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        text-transform: uppercase;
        overflow: hidden;
    }
    .assignee-option__avatar--image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .assignee-option__body {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .assignee-option__name {
        font-weight: 600;
        color: #0f172a;
        font-size: 14px;
    }
    .assignee-option__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 12px;
        color: #64748b;
    }
    .assignee-option__division,
    .assignee-option__code {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 3px 10px;
        font-weight: 500;
        background: #eef2ff;
        color: #3730a3;
    }
    .assignee-option__code {
        background: #e0f2fe;
        color: #0369a1;
    }
    .assignee-option__division--empty {
        background: #f1f5f9;
        color: #64748b;
        border: 1px dashed rgba(100, 116, 139, 0.6);
    }
    label[for="startdate"] .text-danger {
        display: none !important;
    }
    .task-create-footer {
        border-top: 1px solid rgba(226, 232, 240, 0.6);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 22px 32px;
        background: #fff;
        border-radius: 0 0 18px 18px;
        box-shadow: 0 -12px 30px rgba(15, 23, 42, 0.06);
    }
    .task-create-footer .btn {
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 500;
    }
    .attachment-toggle-wrapper {
        text-align: center;
        margin: 20px 0;
    }
    .attachment-toggle-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
        color: #475569;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    .attachment-toggle-btn:hover {
        border-color: #2563eb;
        background: #eff6ff;
        color: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
    }
    .attachment-toggle-btn:active {
        transform: translateY(0);
    }
    .attachment-toggle-btn .toggle-icon {
        transition: transform 0.3s ease;
        font-size: 12px;
    }
    .attachment-toggle-btn.expanded .toggle-icon {
        transform: rotate(180deg);
    }
    .attachment-toggle-btn .fa-paperclip {
        color: #2563eb;
        font-size: 16px;
    }
    @media (max-width: 767px) {
        .task-create-shell {
            padding: 24px 20px;
        }
        .task-create-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .task-create-grid {
            grid-template-columns: 1fr;
        }
        .task-select-toolbar {
            flex-wrap: nowrap;
            overflow-x: auto;
        }
        .task-create-footer {
            flex-direction: column;
            gap: 12px;
        }
        .attachment-toggle-wrapper {
            margin: 15px 0;
        }
        .attachment-toggle-btn {
            padding: 10px 16px;
            font-size: 13px;
        }
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php
                        $has_task_context = isset($task);
                        $is_task_copy_mode = isset($is_task_copy) && $is_task_copy;
                        $is_edit_mode = $has_task_context && ! $is_task_copy_mode;
                        $task_edit_id = $is_edit_mode ? $task->id : '';
                        $form_action = admin_url('tasks/task' . ($task_edit_id ? '/' . $task_edit_id : ''));
                        $cancel_url  = $is_edit_mode ? admin_url('tasks/view/' . $task_edit_id) : admin_url('tasks');
                        if (!isset($milestones) || !is_array($milestones)) {
                            $milestones = [];
                        }
                        if (!isset($project_end_date_attrs) || !is_array($project_end_date_attrs)) {
                            $project_end_date_attrs = [];
                        }

                        $rel_type = $has_task_context ? $task->rel_type : '';
                        $rel_id   = $has_task_context ? $task->rel_id : '';
                        if (! $rel_type && $this->input->get('rel_id') && $this->input->get('rel_type')) {
                            $rel_id   = $this->input->get('rel_id');
                            $rel_type = $this->input->get('rel_type');
                        }

                        $selectedAssignees = [];
                        if ($has_task_context && !empty($task->assignees)) {
                            foreach ($task->assignees as $assignee) {
                                if (isset($assignee['assigneeid'])) {
                                    $selectedAssignees[] = (int) $assignee['assigneeid'];
                                }
                            }
                        }

                        $selectedFollowers = [];
                        if ($has_task_context && !empty($task->followers)) {
                            foreach ($task->followers as $follower) {
                                if (isset($follower['followerid'])) {
                                    $selectedFollowers[] = (int) $follower['followerid'];
                                }
                            }
                        } elseif (! $has_task_context) {
                            if (!empty($prefill_ticket_followers) && is_array($prefill_ticket_followers)) {
                                $selectedFollowers = array_map('intval', $prefill_ticket_followers);
                            } elseif (get_option('new_task_auto_follower_current_member') == '1') {
                                $selectedFollowers[] = (int) get_staff_user_id();
                            }
                        }

                        if (!empty($selectedFollowers)) {
                            $availableFollowerIds = array_map(static function ($member) {
                                return isset($member['staffid']) ? (int) $member['staffid'] : 0;
                            }, $members ?? []);
                            $selectedFollowers = array_values(array_intersect($selectedFollowers, $availableFollowerIds));
                        }

                        $prefillTaskName = isset($prefill_task_name) ? trim((string) $prefill_task_name) : '';
                        $prefillTaskDescription = isset($prefill_task_description) ? clear_textarea_breaks($prefill_task_description) : '';

                        $subjectValue = $has_task_context ? $task->name : $prefillTaskName;
                        $descriptionValue = $has_task_context ? clear_textarea_breaks($task->description) : $prefillTaskDescription;

                        $start_value = _d(date('Y-m-d'));
                        $start_attrs = ['readonly' => true];

                        $due_value = $has_task_context && !empty($task->duedate) ? _d($task->duedate) : '';
                        $due_attrs = $project_end_date_attrs;
                        // Remove readonly to make due date field functional with calendar icon
                        // $due_attrs['readonly'] = true;
                        // if ($is_task_copy_mode) {
                        //     unset($due_attrs['readonly']);
                        // }
                        if (! $has_task_context) {
                            $due_attrs = array_merge([
                                'data-date-start-date' => date('Y-m-d'),
                                'data-date-min-date'   => date('Y-m-d'),
                            ], $due_attrs);
                        }

                        $priority_default = get_option('default_task_priority');
                        $current_priority = $has_task_context ? (int) $task->priority : (int) $priority_default;

                        $repeat_selected      = '';
                        $show_custom_recurring = false;
                        $repeat_every_custom   = 1;
                        $repeat_type_custom    = 'day';

                        if ($has_task_context && $task->recurring == 1) {
                            if ($task->custom_recurring == 1) {
                                $repeat_selected       = 'custom';
                                $show_custom_recurring = true;
                                $repeat_every_custom   = $task->repeat_every;
                                $repeat_type_custom    = $task->recurring_type;
                            } else {
                                $repeat_selected = $task->repeat_every . '-' . $task->recurring_type;
                            }
                        }

                        $cycles_value             = $has_task_context ? (int) $task->cycles : 0;
                        $cycles_wrapper_class     = ($has_task_context && $task->recurring == 1) ? '' : ' hide';
                        $cycles_input_disabled    = (!$has_task_context || $cycles_value === 0) ? ' disabled' : '';
                        $unlimited_cycles_checked = (!$has_task_context || $cycles_value === 0) ? ' checked' : '';

                        $visible_to_customer_checked = ($has_task_context && (int) $task->visible_to_client === 1) ? ' checked' : '';
                        ?>
                        <?php echo form_open_multipart($form_action, ['id' => 'task-form']); ?>
                        <?php
                        $divisionMap = [];
                        if (!empty($divisions) && is_array($divisions)) {
                            foreach ($divisions as $division) {
                                if (!isset($division['divisionid'])) {
                                    continue;
                                }
                                $divisionMap[(int) $division['divisionid']] = $division['name'];
                            }
                        }

                        $staffDirectory           = [];
                        $divisionPresenceTracker  = [];
                        $hasDivisionlessStaff     = false;

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
                            if ($divisionId) {
                                $divisionPresenceTracker[$divisionId] = $divisionName;
                            } else {
                                $hasDivisionlessStaff = true;
                            }

                            $staffDirectory[] = [
                                'id'            => $staffId,
                                'fullname'      => $fullName,
                                'initials'      => $initials,
                                'division_id'   => $divisionId,
                                'division_name' => $divisionName,
                                'emp_code'      => isset($member['staff_emp_code']) ? (string) $member['staff_emp_code'] : '',
                                'avatar'        => staff_profile_image_url($staffId, 'small'),
                            ];
                        }

                        ksort($divisionPresenceTracker);
                        $divisionFilters = [];
                        if (!empty($divisionPresenceTracker)) {
                            foreach ($divisionPresenceTracker as $divisionId => $divisionName) {
                                $divisionFilters[] = [
                                    'id'   => $divisionId,
                                    'name' => $divisionName,
                                ];
                            }
                        }
                        if ($hasDivisionlessStaff) {
                            $divisionFilters[] = [
                                'id'   => 'none',
                                'name' => 'No Division',
                            ];
                        }

                        $taskOverviewTitle    = _l('task_general_info');
                        $taskOverviewCaption  = _l('task_add_edit_subject');
                        $taskPeopleTitle      = _l('task_single_assignees');
                        $taskPeopleCaption    = _l('task_add_edit_assign_task');
                        $taskDescriptionTitle = _l('task_add_edit_description');
                        $taskDescriptionCaption = _l('task_add_description');

                        if ($taskOverviewTitle === 'task_general_info') {
                            $taskOverviewTitle = 'Task Overview';
                        }
                        if ($taskOverviewCaption === 'task_add_edit_subject') {
                            $taskOverviewCaption = 'Core task details & relationships';
                        }
                        if ($taskPeopleTitle === 'task_single_assignees') {
                            $taskPeopleTitle = 'Assignees & Followers';
                        }
                        if ($taskPeopleCaption === 'task_add_edit_assign_task') {
                            $taskPeopleCaption = 'Choose who is responsible or kept in the loop';
                        }
                        if ($taskDescriptionTitle === 'task_add_edit_description') {
                            $taskDescriptionTitle = 'Task Description';
                        }
                        if ($taskDescriptionCaption === 'task_add_description') {
                            $taskDescriptionCaption = 'Describe the scope, notes, and expectations';
                        }
                        $taskScheduleTitle = 'Timeline & Priority';
                        ?>
                        <input type="hidden" name="from_create_page" value="1">
                        <div class="task-create-shell">
                            <div class="task-create-header">
                                <div>
                                    <h2 class="task-create-title"><?php echo e($title); ?></h2>
                                    <p class="task-create-subtitle">Shape the task brief, schedule and owners before it goes live.</p>
                                </div>
                                <div class="task-create-meta">
                                    <i class="fa fa-check-circle"></i>
                                    <span>Task Builder</span>
                                </div>
                                <?php if ($is_task_copy_mode && $has_task_context) { ?>
                                <div class="task-create-meta" title="<?php echo _l('task_copy'); ?>">
                                    <i class="fa fa-clone"></i>
                                    <span><?php echo _l('task_copy'); ?> #<?php echo e($task->id); ?></span>
                                </div>
                                <?php } ?>
                            </div>
                            <div class="task-section">
                                <div class="task-section-header">
                                    <h5 class="task-section-title"><i class="fa fa-pencil"></i><?php echo e($taskOverviewTitle); ?></h5>
                                </div>
                                <div class="row non-project-details">
                            <div class="col-md-12">
                                <div class="task-create-checkbox-group task-visible-to-customer tw-pt-2 checkbox checkbox-inline checkbox-primary<?php if ($rel_type != 'project') {
                                    echo ' hide';
                                } ?>">
                                    <input type="checkbox" id="task_visible_to_client" name="visible_to_client"<?= $visible_to_customer_checked; ?>>
                                    <label for="task_visible_to_client"><?php echo _l('task_visible_to_client'); ?></label>
                                </div>

                                <hr class="-tw-mx-3.5" />
                                <?php echo render_input('name', 'task_add_edit_subject', $subjectValue, 'text', ['placeholder' => _l('task_add_edit_subject')]); ?>
                                <div class="project-details<?php if ($rel_type != 'project') {
                                    echo ' hide';
                                } ?>">
                                    <div class="form-group">
                                        <label for="milestone"><?php echo _l('task_milestone'); ?></label>
                                        <select name="milestone" id="milestone" class="selectpicker" data-width="100%"
                                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                            <option value=""></option>
                                            <?php foreach ($milestones as $milestone) { ?>
                                            <?php
                                            $milestoneSelected = '';
                                            if (isset($_milestone_selected_data) && $_milestone_selected_data['id'] == $milestone['id']) {
                                                $milestoneSelected = 'selected';
                                            } elseif ($has_task_context && isset($task->milestone) && (int) $task->milestone === (int) $milestone['id']) {
                                                $milestoneSelected = 'selected';
                                            }
                                            ?>
                                            <option value="<?php echo e($milestone['id']); ?>" <?php echo $milestoneSelected; ?>><?php echo e($milestone['name']); ?></option>
                                            <?php } ?>
                                        </select>
                            </div>
                        </div>
                        <?php
                        $available_ticket_approval_flows = isset($available_ticket_approval_flows) ? $available_ticket_approval_flows : [];
                        $can_create_ticket_approval_flow = isset($can_create_approval_flow) ? $can_create_approval_flow : staff_can('create', 'approval_flow');
                        if ((!empty($available_ticket_approval_flows) || $can_create_ticket_approval_flow) && !empty($is_ticket_to_task)) { ?>
                        <div class="ticket-approval-flow-cta">
                            <div class="ticket-approval-flow-cta__actions">
                                <div class="tw-flex-1">
                                    <h5><?php echo _l('task_ticket_include_approval_title'); ?></h5>
                                    <p><?php echo _l('task_ticket_include_approval_desc'); ?></p>
                                </div>
                                <button type="button"
                                    class="btn btn-info"
                                    id="ticket-approval-flow-toggle"
                                    data-show-text="<?php echo _l('task_ticket_include_approval_button_add'); ?>"
                                    data-hide-text="<?php echo _l('task_ticket_include_approval_button_remove'); ?>">
                                    <?php echo _l('task_ticket_include_approval_button_add'); ?>
                                </button>
                            </div>
                            <div id="ticket-approval-flow-wrapper" class="ticket-approval-flow-cta__select hide">
                                <div class="form-group">
                                    <label for="ticket_approval_flow_id" class="control-label"><?php echo _l('task_ticket_include_approval_label'); ?></label>
                                    <select name="ticket_approval_flow_id"
                                        id="ticket_approval_flow_id"
                                        class="selectpicker"
                                        data-width="100%"
                                        data-live-search="true"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                        disabled="disabled">
                                        <option value=""></option>
                                        <?php foreach ($available_ticket_approval_flows as $flow) { ?>
                                        <option value="<?php echo (int) $flow['id']; ?>"><?php echo e($flow['name']); ?></option>
                                        <?php } ?>
                                        <?php if ($can_create_ticket_approval_flow) { ?>
                                        <?php if (!empty($available_ticket_approval_flows)) { ?>
                                        <option data-divider="true"></option>
                                        <?php } ?>
                                        <option value="__add_new_approval_flow__"
                                            data-content="<span class=&quot;approval-rel-dropdown-add&quot;><?php echo e('+ ' . _l('new_approval_flow')); ?></span>">
                                            <?php echo '+ ' . _l('new_approval_flow'); ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                    <small class="text-muted"><?php echo _l('task_ticket_include_approval_hint'); ?></small>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="row">
                                    <div class="col-md-6">
                                        <?php echo render_input('startdate', 'task_add_edit_start_date', $start_value, 'text', $start_attrs); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_date_input('duedate', 'task_add_edit_due_date', $due_value, $due_attrs); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="priority"
                                                class="control-label"><?php echo _l('task_add_edit_priority'); ?></label>
                                                <select name="priority" class="selectpicker" id="priority" data-width="100%"
                                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                <?php foreach (get_tasks_priorities() as $priority) { ?>
                                                <option value="<?php echo e($priority['id']); ?>" <?php if ($current_priority == $priority['id']) {
                                                    echo ' selected';
                                                } ?>><?php echo e($priority['name']); ?></option>
                                                <?php } ?>
                                                <?php hooks()->do_action('task_priorities_select', 0); ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="repeat_every"
                                                class="control-label"><?php echo _l('task_repeat_every'); ?></label>
                                                <select name="repeat_every" id="repeat_every" class="selectpicker" data-width="100%"
                                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                <option value=""></option>
                                                <option value="1-week" <?php if ($repeat_selected === '1-week') { echo 'selected'; } ?>><?php echo _l('week'); ?></option>
                                                <option value="2-week" <?php if ($repeat_selected === '2-week') { echo 'selected'; } ?>>2 <?php echo _l('weeks'); ?></option>
                                                <option value="1-month" <?php if ($repeat_selected === '1-month') { echo 'selected'; } ?>>1 <?php echo _l('month'); ?></option>
                                                <option value="2-month" <?php if ($repeat_selected === '2-month') { echo 'selected'; } ?>>2 <?php echo _l('months'); ?></option>
                                                <option value="3-month" <?php if ($repeat_selected === '3-month') { echo 'selected'; } ?>>3 <?php echo _l('months'); ?></option>
                                                <option value="6-month" <?php if ($repeat_selected === '6-month') { echo 'selected'; } ?>>6 <?php echo _l('months'); ?></option>
                                                <option value="1-year" <?php if ($repeat_selected === '1-year') { echo 'selected'; } ?>>1 <?php echo _l('year'); ?></option>
                                                <option value="custom" <?php if ($repeat_selected === 'custom') { echo 'selected'; } ?>><?php echo _l('recurring_custom'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="recurring_custom<?php echo $show_custom_recurring ? '' : ' hide'; ?>">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php echo render_input('repeat_every_custom', '', $repeat_every_custom, 'number', ['min' => 1]); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <select name="repeat_type_custom" id="repeat_type_custom" class="selectpicker"
                                                data-width="100%"
                                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                <option value="day" <?php if ($repeat_type_custom === 'day') { echo 'selected'; } ?>><?php echo _l('task_recurring_days'); ?></option>
                                                <option value="week" <?php if ($repeat_type_custom === 'week') { echo 'selected'; } ?>><?php echo _l('task_recurring_weeks'); ?></option>
                                                <option value="month" <?php if ($repeat_type_custom === 'month') { echo 'selected'; } ?>><?php echo _l('task_recurring_months'); ?></option>
                                                <option value="year" <?php if ($repeat_type_custom === 'year') { echo 'selected'; } ?>><?php echo _l('task_recurring_years'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div id="cycles_wrapper" class="<?php echo $cycles_wrapper_class; ?>">
                                    <div class="form-group recurring-cycles">
                                        <label for="cycles"><?php echo _l('recurring_total_cycles'); ?></label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" <?php echo $cycles_input_disabled; ?> name="cycles" id="cycles" value="<?php echo e($cycles_value); ?>">
                                            <div class="input-group-addon">
                                                <div class="checkbox">
                                                    <input type="checkbox" id="unlimited_cycles"<?php echo $unlimited_cycles_checked; ?>>
                                                    <label for="unlimited_cycles"><?php echo _l('cycles_infinity'); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="rel_type"
                                                class="control-label"><?php echo _l('task_related_to'); ?></label>
                                                <select name="rel_type" class="selectpicker" id="rel_type" data-width="100%"
                                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                <option value=""></option>
                                                <option value="project" <?php if ($rel_type == 'project') { echo 'selected'; } ?>><?php echo _l('project'); ?></option>
                                                <option value="invoice" <?php if ($rel_type == 'invoice') { echo 'selected'; } ?>><?php echo _l('invoice'); ?></option>
                                                <option value="customer" <?php if ($rel_type == 'customer') { echo 'selected'; } ?>><?php echo _l('client'); ?></option>
                                                <option value="estimate" <?php if ($rel_type == 'estimate') { echo 'selected'; } ?>><?php echo _l('estimate'); ?></option>
                                                <option value="contract" <?php if ($rel_type == 'contract') { echo 'selected'; } ?>><?php echo _l('contract'); ?></option>
                                                <option value="ticket" <?php if ($rel_type == 'ticket') { echo 'selected'; } ?>><?php echo _l('ticket'); ?></option>
                                                <option value="expense" <?php if ($rel_type == 'expense') { echo 'selected'; } ?>><?php echo _l('expense'); ?></option>
                                                <option value="lead" <?php if ($rel_type == 'lead') { echo 'selected'; } ?>><?php echo _l('lead'); ?></option>
                                                <option value="proposal" <?php if ($rel_type == 'proposal') { echo 'selected'; } ?>><?php echo _l('proposal'); ?></option>
                                                <option value="approval" <?php if ($rel_type == 'approval') { echo 'selected'; } ?>><?php echo _l('approval'); ?></option>
                                                <?php hooks()->do_action('task_modal_rel_type_select', ['task' => ($has_task_context ? $task : 0), 'rel_type' => $rel_type]); ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group<?= $rel_id == '' ? ' hide' : ''; ?>" id="rel_id_wrapper">
                                            <label for="rel_id" class="control-label"><span class="rel_id_label"></span></label>
                                            <div class="approval-rel-select-group">
                                                <div id="rel_id_select">
                                                    <select name="rel_id" id="rel_id" class="ajax-sesarch" data-width="100%"
                                                        data-live-search="true"
                                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                        <?php if ($rel_id != '' && $rel_type != '') {
                                                            $rel_data = get_relation_data($rel_type, $rel_id);
                                                            $rel_val  = get_relation_values($rel_data, $rel_type);
                                                            echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                                        } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="task-section">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group select-placeholder">
                                            <label for="assignees"><?php echo _l('task_single_assignees'); ?></label>
                                            <div class="task-select-toolbar" data-target="#assignees">
                                                <button type="button" class="active" data-filter="all">All</button>
                                                <?php foreach ($divisionFilters as $divisionFilter) { ?>
                                                <button type="button" data-filter="<?php echo e($divisionFilter['id']); ?>"><?php echo e($divisionFilter['name']); ?></button>
                                                <?php } ?>
                                                <button type="button" class="task-select-clear" data-action="clear">Clear all</button>
                                            </div>
                                            <div class="task-chips" data-chip-target="#assignees"></div>
                                            <select name="assignees[]" id="assignees" class="selectpicker" data-width="100%"
                                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                                data-chip-selected-text="<?php echo _l('task_single_assignees'); ?>"
                                                multiple data-live-search="true" data-live-search-placeholder="Search team members"
                                                data-style="task-select-trigger">
                                                <?php foreach ($members as $member) { ?>
                                                <?php $memberId = isset($member['staffid']) ? (int) $member['staffid'] : 0; ?>
                                                <option value="<?php echo e($memberId); ?>" <?php if (in_array($memberId, $selectedAssignees)) { echo 'selected'; } ?>>
                                                    <?php echo e($member['firstname'] . ' ' . $member['lastname']); ?>
                                                </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <!-- Approval Team (when Related To = Approval) -->
                                        <div class="form-group approval-team-section<?php if ($rel_type != 'approval') { echo ' hide'; } ?>" style="margin-top: 20px;">
                                            <label for="approval_team">Approval Team</label>
                                            <div class="task-chips" id="approval-team-chips" data-input-id="approval_team" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px;"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <?php $follower = []; ?>
                                        <div class="form-group select-placeholder">
                                            <label for="followers"><?php echo _l('task_single_followers'); ?></label>
                                            <div class="task-select-toolbar" data-target="#followers">
                                                <button type="button" class="active" data-filter="all">All</button>
                                                <?php foreach ($divisionFilters as $divisionFilter) { ?>
                                                <button type="button" data-filter="<?php echo e($divisionFilter['id']); ?>"><?php echo e($divisionFilter['name']); ?></button>
                                                <?php } ?>
                                                <button type="button" class="task-select-clear" data-action="clear">Clear all</button>
                                            </div>
                                            <div class="task-chips" data-chip-target="#followers"></div>
                                            <select name="followers[]" id="followers" class="selectpicker" data-width="100%"
                                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                                data-chip-selected-text="<?php echo _l('task_single_followers'); ?>"
                                                multiple data-live-search="true" data-live-search-placeholder="Search team members"
                                                data-style="task-select-trigger">
                                                <?php foreach ($members as $member) { ?>
                                                <?php $memberId = isset($member['staffid']) ? (int) $member['staffid'] : 0; ?>
                                                <option value="<?php echo e($memberId); ?>" <?php if (in_array($memberId, $selectedFollowers)) { echo 'selected'; } ?>>
                                                    <?php echo e($member['firstname'] . ' ' . $member['lastname']); ?>
                                                </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="task-section">
                                <div class="task-section-header">
                                    <h5 class="task-section-title">
                                        <i class="fa fa-align-left"></i>
                                        <?php echo _l('task_description_section'); ?>
                                    </h5>
                                    <p class="task-section-caption">
                                        <?php echo _l('task_description_section_hint'); ?>
                                    </p>
                                </div>
                                <div class="form-group checklist-templates-wrapper<?php if (count($checklistTemplates) == 0) {
                                    echo ' hide';
                                } ?>">
                                    <label for="checklist_items"><?php echo _l('insert_checklist_templates'); ?></label>
                                    <select id="checklist_items" name="checklist_items[]"
                                        class="selectpicker checklist-items-template-select" multiple="1"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex') ?>"
                                        data-width="100%" data-live-search="true" data-actions-box="true">
                                        <option value="" class="hide"></option>
                                        <?php foreach ($checklistTemplates as $chkTemplate) { ?>
                                        <option value="<?php echo e($chkTemplate['id']); ?>">
                                            <?php echo e($chkTemplate['description']); ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <?php echo render_custom_fields('tasks', $has_task_context ? $task->id : false); ?>
                                <hr />
                                <?php
                                echo render_textarea('description', '', $descriptionValue, ['rows' => 6, 'placeholder' => _l('task_add_description'), 'data-task-ae-editor' => true], [], 'no-mbot', 'tinymce-task');
                                ?>

                                <hr />
                                <div class="attachment-toggle-wrapper">
                                    <button type="button" class="btn btn-link attachment-toggle-btn" onclick="toggleAttachments()">
                                        <i class="fa fa-paperclip"></i>
                                        <span class="toggle-text"><?php echo _l('attach_files'); ?></span>
                                        <i class="fa fa-chevron-down toggle-icon"></i>
                                    </button>
                                </div>
                                <div id="new-task-attachments" class="hide">
                                    <div class="row attachments">
                                        <div class="attachment">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="attachment"
                                                        class="control-label"><?php echo _l('add_task_attachments'); ?></label>
                                                    <div class="input-group">
                                                        <input type="file"
                                                            extension="<?php echo str_replace('.', '', get_option('allowed_files')); ?>"
                                                            filesize="<?php echo file_upload_max_size(); ?>"
                                                            class="form-control" name="attachments[0]">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-default add_more_attachments"
                                                                type="button"><i class="fa fa-plus"></i></button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($this->input->get('ticket_to_task')) { ?>
                                <?php echo form_hidden('ticket_to_task', $this->input->get('rel_id')); ?>
                                <?php if ($this->input->get('ticket_reply_id')) { ?>
                                <?php echo form_hidden('ticket_reply_id', $this->input->get('ticket_reply_id')); ?>
                                <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer task-create-footer">
                        <a href="<?php echo e($cancel_url); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
                        <button type="submit" class="btn btn-primary pull-right"><?php echo _l('submit'); ?></button>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
var taskStaffDirectory = <?php echo json_encode($staffDirectory, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
var taskStaffDirectoryIndex = {};
var taskNoDivisionLabel = 'No division';
var taskBuilderIsEditMode = <?php echo $is_edit_mode ? 'true' : 'false'; ?>;
var taskBuilderListUrl = <?php echo json_encode(admin_url('tasks')); ?>;
var taskBuilderViewUrlBase = <?php echo json_encode(admin_url('tasks/view/')); ?>;
var taskBuilderExistingId = <?php echo $is_edit_mode ? (int) $task->id : 0; ?>;
if (Array.isArray(taskStaffDirectory)) {
    taskStaffDirectory.forEach(function(member) {
        if (member && typeof member.id !== 'undefined') {
            taskStaffDirectoryIndex[member.id] = member;
        }
    });
}

function escapeHtml(str) {
    if (typeof str !== 'string') {
        if (str === null || typeof str === 'undefined') {
            str = '';
        } else {
            str = String(str);
        }
    }
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function decorateStaffSelect(selector) {
    var $select = $(selector);
    if (!$select.length) {
        return;
    }
    $select.find('option').each(function() {
        var $option = $(this);
        var staffId = parseInt($option.val(), 10);
        if (!staffId) {
            return;
        }
        var staff = taskStaffDirectoryIndex[staffId];
        if (!staff) {
            var fallbackName = $option.text().trim();
            if (!fallbackName) {
                fallbackName = 'Member #' + staffId;
            }
            var initials = fallbackName.split(' ').map(function(part) {
                return part.charAt(0);
            }).join('').substring(0, 2).toUpperCase();
            staff = {
                id: staffId,
                fullname: fallbackName,
                initials: initials,
                avatar: '',
                division_id: null,
                division_name: '',
                emp_code: ''
            };
            taskStaffDirectoryIndex[staffId] = staff;
        }
        var divisionKey = staff.division_id !== null && staff.division_id !== '' ? staff.division_id : 'none';
        var hasDivision = staff.division_name && staff.division_name !== '';
        var divisionLabel = hasDivision ? staff.division_name : taskNoDivisionLabel;
        var divisionBadgeClass = hasDivision ? 'assignee-option__division' : 'assignee-option__division assignee-option__division--empty';
        var empBadge = staff.emp_code ? '<span class="assignee-option__code">' + escapeHtml(staff.emp_code) + '</span>' : '';
        var avatarHtml;
        if (staff.avatar && staff.avatar !== '') {
            avatarHtml = '<span class="assignee-option__avatar assignee-option__avatar--image"><img src="' + escapeHtml(staff.avatar) + '" alt="' + escapeHtml(staff.fullname || '') + '"></span>';
        } else {
            avatarHtml = '<span class="assignee-option__avatar">' + escapeHtml(staff.initials || '') + '</span>';
        }
        var content = '<div class="assignee-option">' +
            avatarHtml +
            '<div class="assignee-option__body">' +
            '<span class="assignee-option__name">' + escapeHtml(staff.fullname || '') + '</span>' +
            '<div class="assignee-option__meta">' +
            '<span class="' + divisionBadgeClass + '">' + escapeHtml(divisionLabel) + '</span>' +
            empBadge +
            '</div>' +
            '</div>' +
            '</div>';
        $option
            .attr('data-content', content)
            .attr('data-division', divisionKey)
            .attr('data-tokens', (staff.fullname || '') + ' ' + (staff.emp_code || '') + ' ' + (staff.division_name || ''));
    });
}

function initDivisionToolbars() {
    $('.task-select-toolbar').each(function() {
        var $toolbar = $(this);
        var selector = $toolbar.data('target');
        if (!selector) {
            return;
        }
        var $select = $(selector);
        if (!$select.length) {
            return;
        }

        var applyFilter = function(filterKey) {
            filterKey = filterKey || 'all';
            $toolbar.attr('data-active-filter', filterKey);
            $toolbar.find('button[data-filter]').removeClass('active');
            $toolbar.find('button[data-filter="' + filterKey + '"]').addClass('active');

            $select.find('option').each(function() {
                var $option = $(this);
                if (!$option.val()) {
                    return;
                }
                var optionDivision = $option.attr('data-division') || 'none';
                var show = filterKey === 'all' || optionDivision === filterKey;
                $option.prop('hidden', !show);
            });
            $select.selectpicker('refresh');
        };

        $toolbar.data('applyFilter', applyFilter);
        $toolbar.on('click', 'button[data-filter]', function(e) {
            e.preventDefault();
            applyFilter($(this).data('filter').toString());
        });
        $toolbar.on('click', 'button[data-action="clear"]', function(e) {
            e.preventDefault();
            $select.selectpicker('deselectAll');
            renderSelectionChips($select);
        });
        applyFilter($toolbar.attr('data-active-filter') || 'all');
        renderSelectionChips($select);
    });
}

function refreshDivisionToolbar(selector) {
    var $toolbar = $('.task-select-toolbar[data-target="' + selector + '"]');
    if (!$toolbar.length) {
        return;
    }
    var applyFilter = $toolbar.data('applyFilter');
    if (typeof applyFilter === 'function') {
        applyFilter($toolbar.attr('data-active-filter') || 'all');
        var $select = $(selector);
        if ($select.length) {
            renderSelectionChips($select);
        }
    }
}

var taskInitialAssignmentsCleared = false;
function resetInitialAssignments() {
    if (taskInitialAssignmentsCleared) {
        return;
    }
    ['#assignees', '#followers'].forEach(function(selector) {
        var $select = $(selector);
        if ($select.length) {
            $select.val([]).selectpicker('refresh');
            renderSelectionChips($select);
        }
    });
    taskInitialAssignmentsCleared = true;
}

function rebuildStaffSelect(selector) {
    decorateStaffSelect(selector);
    var $select = $(selector);
    if ($select.length) {
        $select.selectpicker('refresh');
        refreshDivisionToolbar(selector);
        renderSelectionChips($select);
    }
}

function renderSelectionChips($select) {
    var selector = '#' + $select.attr('id');
    var $chipBox = $('.task-chips[data-chip-target="' + selector + '"]');
    if (!$chipBox.length) {
        return;
    }

    var selectedOptions = $select.find('option:selected');
    updateSelectButtonLabel($select, selectedOptions.length);
    $chipBox.empty();

    if (!selectedOptions.length) {
        $chipBox.append('<span class="task-chip task-chip--secondary">No team members selected</span>');
        return;
    }

    var staffList = [];
    selectedOptions.each(function() {
        var staffId = parseInt($(this).val(), 10);
        if (!staffId) {
            return;
        }
        var staff = taskStaffDirectoryIndex[staffId];
        if (!staff) {
            var fallbackName = $(this).text().trim();
            if (!fallbackName) {
                fallbackName = 'Member #' + staffId;
            }
            var initials = fallbackName.split(' ').map(function(part) {
                return part.charAt(0);
            }).join('').substring(0, 2).toUpperCase();
            staff = {
                id: staffId,
                fullname: fallbackName,
                initials: initials,
                avatar: ''
            };
            taskStaffDirectoryIndex[staffId] = staff;
        }
        staffList.push(staff);
    });

    if (!staffList.length) {
        $chipBox.append('<span class="task-chip task-chip--secondary">No team members selected</span>');
        return;
    }

    var firstStaff = staffList[0];
    $chipBox.append(buildChip(firstStaff, selector, true));

    if (staffList.length > 1) {
        var overflowCount = staffList.length - 1;
        var overflowItems = staffList.slice(1).map(function(member) {
            return '<div class="task-chip__tooltip-item" data-remove-select="' + selector + '" data-remove-id="' + member.id + '"><span>' + escapeHtml(member.fullname || '') + '</span><span class="task-chip__tooltip-remove">&times;</span></div>';
        }).join('');
        var overflowHtml = '<span class="task-chip__overflow">+ ' + overflowCount + '<span class="task-chip__tooltip">' + overflowItems + '</span></span>';
        $chipBox.append(overflowHtml);
    }

    var totalLabel = staffList.length === 1 ? '1 member' : staffList.length + ' members';
    var countItems = staffList.map(function(member) {
        return '<div class="task-chip__tooltip-item" data-remove-select="' + selector + '" data-remove-id="' + member.id + '"><span>' + escapeHtml(member.fullname || '') + '</span><span class="task-chip__tooltip-remove">&times;</span></div>';
    }).join('');
    var countHtml = '<span class="task-chip task-chip--secondary task-chip__count">' + totalLabel + '<span class="task-chip__tooltip">' + countItems + '</span></span>';
    $chipBox.append(countHtml);
}

function updateSelectButtonLabel($select, count) {
    var $label = $select.closest('.bootstrap-select').find('.filter-option-inner-inner');
    if (!$label.length) {
        return;
    }
    var noneText = $select.attr('data-none-selected-text') || '';
    var selectedText = $select.data('chipSelectedText') || noneText;
    if (count > 0) {
        $label.text(selectedText);
    } else {
        $label.text(noneText);
    }
}

function buildChip(staff, selector, highlight) {
    var chipClasses = 'task-chip';
    if (!highlight) {
        chipClasses += ' task-chip--secondary';
    }
    var avatar;
    if (staff.avatar && staff.avatar !== '') {
        avatar = '<span class="task-chip__avatar"><img src="' + escapeHtml(staff.avatar) + '" alt="' + escapeHtml(staff.fullname || '') + '"></span>';
    } else {
        avatar = '<span class="task-chip__avatar">' + escapeHtml(staff.initials || '') + '</span>';
    }
    return '<span class="' + chipClasses + '" data-staff-id="' + staff.id + '" data-select="' + selector + '">' +
        avatar +
        '<span>' + escapeHtml(staff.fullname || '') + '</span>' +
        '<span class="task-chip__remove" data-remove-select="' + selector + '" data-remove-id="' + staff.id + '">&times;</span>' +
        '</span>';
}

function removeStaffFromSelect(selector, staffId) {
    var $select = $(selector);
    if (!$select.length) {
        return;
    }
    var current = $select.val() || [];
    current = current.filter(function(val) {
        return parseInt(val, 10) !== staffId;
    });
    $select.val(current);
    $select.selectpicker('refresh');
    renderSelectionChips($select);
}

function getMomentDateFormat() {
    if (typeof app !== 'undefined' && app.options && app.options.date_format) {
        return app.options.date_format
            .replace(/Y/g, 'YYYY')
            .replace(/m/g, 'MM')
            .replace(/d/g, 'DD');
    }
    return 'YYYY-MM-DD';
}

function applyDateMinGuards() {
    if (typeof taskBuilderIsEditMode !== 'undefined' && taskBuilderIsEditMode) {
        return;
    }
    if (typeof moment === 'undefined') {
        return;
    }
    var today = moment().startOf('day');
    var momentFormat = getMomentDateFormat();
    ['#duedate'].forEach(function(selector) {
        var $field = $(selector);
        if (!$field.length) {
            return;
        }
        var picker = $field.data('DateTimePicker');
        if (picker) {
            picker.minDate(today);
        }
        $field.attr('data-date-start-date', today.format('YYYY-MM-DD'));
        $field.attr('data-date-min-date', today.format('YYYY-MM-DD'));
        $field.off('dp.change.taskGuard').on('dp.change.taskGuard', function() {
            enforceFieldNotInPast($field, today, momentFormat);
        });
        $field.off('blur.taskGuard').on('blur.taskGuard', function() {
            enforceFieldNotInPast($field, today, momentFormat);
        });
    });
}

function enforceFieldNotInPast($field, today, momentFormat) {
    if (typeof moment === 'undefined') {
        return;
    }
    var raw = ($field.val() || '').trim();
    if (raw === '') {
        return;
    }
    var candidate;
    if (typeof unformat_date === 'function') {
        candidate = moment(unformat_date(raw), 'YYYY-MM-DD');
    } else {
        candidate = moment(raw, momentFormat);
    }
    if (!candidate.isValid()) {
        return;
    }
    if (candidate.isBefore(today, 'day')) {
        var formattedToday = today.format(momentFormat);
        $field.val(formattedToday);
        if (typeof alert_float === 'function') {
            alert_float('warning', 'Past dates are not allowed.');
        }
    }
}

var _rel_id = $('#rel_id'),
    _rel_type = $('#rel_type'),
    _rel_id_wrapper = $('#rel_id_wrapper'),
    _current_member = undefined,
    data = {},
    approvalRelationCache = null,
    approvalRelationRequest = null,
    approvalRelLoadingText = <?php echo json_encode(_l('loading')); ?>,
    APPROVAL_ADD_OPTION_VALUE = '__add_new_approval_flow__',
    approvalFlowCreateUrl = <?php echo json_encode(admin_url('approval_flow/create')); ?>,
    approvalAddNewLabel = <?php echo json_encode('+ ' . _l('new_approval_flow')); ?>;

var _milestone_selected_data;
_milestone_selected_data = undefined;

<?php if (isset($_milestone_selected_data)) { ?>
_milestone_selected_data = '<?php echo json_encode($_milestone_selected_data); ?>';
_milestone_selected_data = JSON.parse(_milestone_selected_data);
<?php } ?>

$(function() {
    appValidateForm($('#task-form'), {
        name: 'required',
        startdate: 'required',
        duedate: 'required',
        repeat_every_custom: {
            min: 1
        },
    }, function(form) {
        // Custom handler for create page - redirect to tasks list after success
        tinymce.triggerSave();
        $("#task-form").find('input[name="startdate"]').prop("disabled", false);
        $("#task-form").find('button[type="submit"]').prop("disabled", true);
        $("#task-form input[type=file]").each(function() {
            if ($(this).val() == "") {
                $(this).prop("disabled", true);
            }
        });
        var formData = new FormData($(form)[0]);
        // Append CSRF token to FormData
        if (typeof csrfData !== 'undefined') {
            formData.append(csrfData.token_name, csrfData.hash);
        }
        // Ensure multi-select values are synced
        formData.delete('assignees');
        formData.delete('assignees[]');
        var assigneesSelected = $('#assignees').val() || [];
        assigneesSelected.forEach(function(val) {
            formData.append('assignees[]', val);
        });
        formData.delete('followers');
        formData.delete('followers[]');
        var followersSelected = $('#followers').val() || [];
        console.log('Selected followers:', followersSelected); // Debug log
        followersSelected.forEach(function(val) {
            formData.append('followers[]', val);
        });
        $.ajax({
            type: $(form).attr("method"),
            data: formData,
            mimeType: $(form).attr("enctype"),
            contentType: false,
            cache: false,
            processData: false,
            url: $(form).attr("action")
        }).done(function(response) {
            response = JSON.parse(response);
            if (response.success === true || response.success === "true") {
                alert_float("success", response.message);
                var redirectTo = taskBuilderListUrl;
                if (taskBuilderIsEditMode) {
                    var targetId = response.id || taskBuilderExistingId;
                    if (targetId) {
                        redirectTo = taskBuilderViewUrlBase + targetId;
                    }
                }
                window.location.href = redirectTo;
            } else {
                alert_float("danger", response.message || "An error occurred");
            }
        }).fail(function(response) {
            alert_float("danger", JSON.parse(response.responseText).message || "An error occurred");
        });
    });

    decorateStaffSelect('#assignees');
    decorateStaffSelect('#followers');

    $('.rel_id_label').html(_rel_type.find('option:selected').text());

    _rel_type.on('change', function() {
        var clonedSelect = _rel_id.html('').clone();
        _rel_id.selectpicker('destroy').remove();
        _rel_id = clonedSelect;
        $('#rel_id_select').append(clonedSelect);
        $('.rel_id_label').html(_rel_type.find('option:selected').text());

        task_rel_select();
        if ($(this).val() != '') {
            _rel_id_wrapper.removeClass('hide');
        } else {
            _rel_id_wrapper.addClass('hide');
        }
        init_project_details(_rel_type.val());
        toggleApprovalFields();
        if ($(this).val() !== 'approval') {
            toggleApprovalRelationLoading(false);
        }

    });

    init_datepicker();
    var $startInput = $('#startdate');
    if ($startInput.length) {
        var picker = $startInput.data('DateTimePicker');
        if (picker) {
            picker.destroy();
        }
        $startInput.off('focus');
    }
    init_color_pickers();
    init_selectpicker();
    if (typeof init_editor === 'function') {
        init_editor('.tinymce-task', {
            height: 200
        });
    }
    initDivisionToolbars();
    refreshDivisionToolbar('#assignees');
    refreshDivisionToolbar('#followers');

    rebuildStaffSelect('#assignees');
    rebuildStaffSelect('#followers');
    if (!taskBuilderIsEditMode) {
        resetInitialAssignments();
        applyDateMinGuards();
    } else {
        renderSelectionChips($('#assignees'));
        renderSelectionChips($('#followers'));
    }
    task_rel_select();

    $('#assignees, #followers').on('changed.bs.select', function() {
        renderSelectionChips($(this));
    });

    $('body').on('click', '.task-chip__remove', function(e) {
        e.preventDefault();
        var selectSelector = $(this).data('remove-select');
        var staffId = parseInt($(this).data('remove-id'), 10);
        removeStaffFromSelect(selectSelector, staffId);
    });

    $('body').on('click', '.task-chip__tooltip-item', function(e) {
        e.preventDefault();
        var selectSelector = $(this).data('remove-select');
        var staffId = parseInt($(this).data('remove-id'), 10);
        removeStaffFromSelect(selectSelector, staffId);
    });

    $('body').on('changed.bs.select', '#rel_id', function(e) {
        if (handleApprovalRelSpecialSelection($(this))) {
            e.stopImmediatePropagation();
            e.preventDefault();
            return false;
        }
    });

    var _allAssigneeSelect = $("#assignees").html();

    $('body').on('change', '#rel_id', function() {
        var $select = $(this);
        if (handleApprovalRelSpecialSelection($select)) {
            return;
        }
        if ($(this).val() != '') {
            // Clear assignees first
            $("#assignees").val([]);
            $("#assignees").selectpicker('refresh');
            renderSelectionChips($("#assignees"));
            if (_rel_type.val() == 'project') {
                $.get(admin_url + 'projects/get_rel_project_data/' + $(this).val() + '/0',
                    function(project) {
                        $("select[name='milestone']").html(project.milestones);
                        if (typeof(_milestone_selected_data) != 'undefined') {
                            $("select[name='milestone']").val(_milestone_selected_data.id);
                            $('input[name="duedate"]').val(_milestone_selected_data.due_date)
                        }
                        $("select[name='milestone']").selectpicker('refresh');

                        $("#assignees").html(project.assignees);
                        $("#assignees").find('option').prop('selected', false);
                        rebuildStaffSelect('#assignees');
                        if (project.deadline) {
                            var $duedate = $('#duedate');
                            var currentSelectedTaskDate = $duedate.val();
                            $duedate.attr('data-date-end-date', project.deadline);
                            $duedate.datetimepicker('destroy');
                            init_datepicker($duedate);
                            applyDateMinGuards();

                            if (currentSelectedTaskDate) {
                                var dateTask = new Date(unformat_date(currentSelectedTaskDate));
                                var projectDeadline = new Date(project.deadline);
                                if (dateTask > projectDeadline) {
                                    $duedate.val(project.deadline_formatted);
                                }
                            }
                        } else {
                            reset_task_duedate_input();
                        }
                        init_project_details(_rel_type.val(), project.allow_to_view_tasks);
                    }, 'json');
            } else if (_rel_type.val() == 'approval') {
                $.get(admin_url + 'approval_flow/get_steps/' + $(this).val(),
                    function(response) {
                        if (response.success && response.staff_ids && response.staff_ids.length > 0) {
                            renderApprovalTeamChips(response.staff_ids);
                        } else {
                            renderApprovalTeamChips([]);
                        }
                        reset_task_duedate_input();
                    }, 'json');
            } else {
                reset_task_duedate_input();
            }
        }
    });

    <?php if ($rel_id != '') { ?>
    _rel_id.change();
    <?php } ?>

    _rel_type.on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue) {
        if (previousValue == 'project') {
            $("#assignees").html(_allAssigneeSelect);
            $("#assignees").find('option').prop('selected', false);
            rebuildStaffSelect('#assignees');
        }
        if ($(this).val() !== 'approval') {
            $("#assignees").val([]);
            $("#assignees").selectpicker('refresh');
            renderSelectionChips($("#assignees"));
        }
    });

});

function task_rel_select() {
    var currentType = _rel_type.val();
    if (!currentType) {
        return;
    }

    if (currentType === 'approval') {
        preloadApprovalRelations();
        return;
    }

    var serverData = {};
    serverData.rel_id = _rel_id.val();
    data.type = currentType;
    init_ajax_search(currentType, _rel_id, serverData);
    toggleApprovalRelationLoading(false);
}

function toggleApprovalRelationLoading(isLoading) {
    var $wrapper = $('#rel_id_wrapper');
    if (!$wrapper.length) {
        return;
    }
    var $indicator = $wrapper.find('.approval-rel-loading');
    if (!$indicator.length) {
        $indicator = $('<div class="approval-rel-loading hide"><i class="fa fa-spinner fa-spin"></i><span class="approval-rel-loading__text"></span></div>');
        $indicator.find('span').text(approvalRelLoadingText);
        $('#rel_id_select').after($indicator);
    }
    var $select = $('#rel_id_select');
    if (isLoading) {
        $indicator.removeClass('hide');
        $select.addClass('hide');
    } else {
        $indicator.addClass('hide');
        $select.removeClass('hide');
    }
}

function preloadApprovalRelations() {
    if (_rel_type.val() !== 'approval') {
        return;
    }

    if (approvalRelationCache) {
        renderApprovalRelationOptions(approvalRelationCache);
        return;
    }

    if (approvalRelationRequest) {
        return;
    }

    toggleApprovalRelationLoading(true);

    var postData = {
        type: 'approval',
        rel_id: '',
        q: ''
    };

    if (typeof csrfData !== 'undefined') {
        postData[csrfData.token_name] = csrfData.hash;
    }

    approvalRelationRequest = $.ajax({
        url: admin_url + 'misc/get_relation_data',
        type: 'POST',
        dataType: 'json',
        data: postData
    }).done(function(response) {
        approvalRelationCache = Array.isArray(response) ? response : [];
        renderApprovalRelationOptions(approvalRelationCache);
    }).fail(function(xhr) {
        console.error('Failed to preload approval relations', xhr);
    }).always(function() {
        approvalRelationRequest = null;
        toggleApprovalRelationLoading(false);
    });
}

function renderApprovalRelationOptions(options) {
    if (!_rel_id.length) {
        return;
    }

    var currentValue = _rel_id.val();
    var optionsHtml = '<option value=""></option>';

    options.forEach(function(option) {
        if (!option || typeof option.id === 'undefined') {
            return;
        }
        var label = escapeHtml(option.name || '');
        var subtextAttr = option.subtext ? ' data-subtext="' + escapeHtml(option.subtext) + '"' : '';
        optionsHtml += '<option value="' + option.id + '"' + subtextAttr + '>' + label + '</option>';
    });

    optionsHtml += '<option data-divider="true"></option>';
    optionsHtml += '<option value="' + APPROVAL_ADD_OPTION_VALUE + '" data-content="<span class=&quot;approval-rel-dropdown-add&quot;>' +
        escapeHtml(approvalAddNewLabel) + '</span>">' + escapeHtml(approvalAddNewLabel) + '</option>';

    _rel_id.html(optionsHtml);
    if (typeof _rel_id.selectpicker === 'function') {
        _rel_id.selectpicker();
        _rel_id.selectpicker('refresh');
        if (currentValue && currentValue !== APPROVAL_ADD_OPTION_VALUE) {
            _rel_id.selectpicker('val', currentValue);
        }
    }
    _rel_id_wrapper.removeClass('hide');
    toggleApprovalRelationLoading(false);
}

function handleApprovalRelSpecialSelection($select) {
    if (!$select || !$select.length) {
        return false;
    }
    if ($select.val() === APPROVAL_ADD_OPTION_VALUE) {
        $select.selectpicker('val', '');
        openApprovalFlowCreator();
        return true;
    }
    return false;
}

function openApprovalFlowCreator() {
    window.open(approvalFlowCreateUrl, '_blank');
}

function toggleApprovalFields() {
    var relType = _rel_type.val();
    if (relType === 'approval') {
        $('.approval-team-section').removeClass('hide');
        $('.assignees-section').addClass('hide');
    } else {
        $('.approval-team-section').addClass('hide');
        $('.assignees-section').removeClass('hide');
    }
}

function renderApprovalTeamChips(staffIds) {
    var $chipsContainer = $('#approval-team-chips');
    if (!$chipsContainer.length) {
        return;
    }

    $chipsContainer.empty();

    if (!Array.isArray(staffIds) || staffIds.length === 0) {
        $chipsContainer.append('<span class="task-chip task-chip--secondary">No team members assigned</span>');
        return;
    }

    var staffList = [];
    staffIds.forEach(function(staffId) {
        staffId = parseInt(staffId, 10);
        if (!staffId) {
            return;
        }
        var staff = taskStaffDirectoryIndex[staffId];
        if (!staff) {
            staff = {
                id: staffId,
                fullname: 'Member #' + staffId,
                initials: 'ST',
                avatar: ''
            };
            taskStaffDirectoryIndex[staffId] = staff;
        }
        staffList.push(staff);
    });

    if (!staffList.length) {
        $chipsContainer.append('<span class="task-chip task-chip--secondary">No team members assigned</span>');
        return;
    }

    // Display all approval team members as read-only chips (no remove buttons, no overflow)
    staffList.forEach(function(staff) {
        var avatarHtml = staff.avatar && staff.avatar !== '' ?
            '<span class="task-chip__avatar"><img src="' + escapeHtml(staff.avatar) + '" alt="' + escapeHtml(staff.fullname || '') + '"></span>' :
            '<span class="task-chip__avatar">' + escapeHtml(staff.initials || '') + '</span>';

        var chipHtml = '<span class="task-chip task-chip--secondary">' +
            avatarHtml +
            '<span>' + escapeHtml(staff.fullname || '') + '</span>' +
            '</span>';

        $chipsContainer.append(chipHtml);
    });
}

$(function() {
    var $toggle = $('#ticket-approval-flow-toggle');
    if (!$toggle.length) {
        return;
    }

    var $wrapper = $('#ticket-approval-flow-wrapper');
    var $select = $('#ticket_approval_flow_id');
    var showText = $toggle.data('show-text') || $toggle.text();
    var hideText = $toggle.data('hide-text') || $toggle.text();

    function handleTicketApprovalAddOption() {
        if ($select.val() === APPROVAL_ADD_OPTION_VALUE) {
            if (typeof $select.selectpicker === 'function') {
                $select.selectpicker('val', '');
            } else {
                $select.val('');
            }
            openApprovalFlowCreator();
        }
    }

    $toggle.on('click', function(e) {
        e.preventDefault();
        var isHidden = $wrapper.hasClass('hide');
        if (isHidden) {
            $wrapper.removeClass('hide');
            $select.prop('disabled', false);
            if (typeof $select.selectpicker === 'function') {
                $select.selectpicker('refresh');
            }
            $toggle.text(hideText);
        } else {
            $wrapper.addClass('hide');
            $select.val('').prop('disabled', true);
            if (typeof $select.selectpicker === 'function') {
                $select.selectpicker('refresh');
            }
            $toggle.text(showText);
        }
    });

    $select.on('changed.bs.select', handleTicketApprovalAddOption);
    $select.on('change', handleTicketApprovalAddOption);
});

function init_project_details(type, tasks_visible_to_customer) {
    var wrap = $('.non-project-details');
    if (type == 'project') {
        wrap.addClass('hide');
        $('.project-details').removeClass('hide');
    } else {
        wrap.removeClass('hide');
        $('.project-details').addClass('hide');
        $('.task-visible-to-customer').addClass('hide').prop('checked', false);
    }
    if (typeof(tasks_visible_to_customer) != 'undefined') {
        if (tasks_visible_to_customer == 1) {
            $('.task-visible-to-customer').removeClass('hide');
            $('.task-visible-to-customer input').prop('checked', true);
        } else {
            $('.task-visible-to-customer').addClass('hide')
            $('.task-visible-to-customer input').prop('checked', false);
        }
    }
}

function reset_task_duedate_input() {
    var $duedate = $('#duedate');
    $duedate.removeAttr('data-date-end-date');
    $duedate.datetimepicker('destroy');
    init_datepicker($duedate);
    applyDateMinGuards();
}

function toggleAttachments() {
    var $attachments = $('#new-task-attachments');
    var $toggleBtn = $('.attachment-toggle-btn');
    var $toggleText = $toggleBtn.find('.toggle-text');

    if ($attachments.hasClass('hide')) {
        // Show attachments
        $attachments.slideDown(300, function() {
            $attachments.removeClass('hide');
        });
        $toggleBtn.addClass('expanded');
        $toggleText.text('<?php echo _l('hide_attachments', 'Hide Attachments'); ?>');
    } else {
        // Hide attachments
        $attachments.slideUp(300, function() {
            $attachments.addClass('hide');
        });
        $toggleBtn.removeClass('expanded');
        $toggleText.text('<?php echo _l('attach_files'); ?>');
    }
}
</script>
