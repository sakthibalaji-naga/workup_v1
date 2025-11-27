<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .team-tasks-board-page {
        background: #f8fafc;
        min-height: calc(100vh - 120px);
    }

    .team-tasks-surface {
        background: #fff;
        border-radius: 14px;
        padding: 32px 32px 24px;
        box-shadow: 0 20px 65px rgba(15, 23, 42, 0.08);
    }

    .team-tasks__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
        flex-wrap: wrap;
    }

    .team-tasks__header h4 {
        font-weight: 600;
        margin: 0;
        font-size: 24px;
        color: #0f172a;
    }

    .team-tasks__header p {
        margin: 4px 0 0;
        color: #475569;
    }

    .team-tasks__header-actions .btn {
        margin-left: 8px;
    }

    .team-tasks__tabs {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-top: 24px;
        border-bottom: 1px solid #e2e8f0;
    }

    .team-tasks__tab {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 12px 0;
        font-weight: 600;
        font-size: 15px;
        color: #94a3b8;
        border-bottom: 3px solid transparent;
        text-decoration: none;
    }

    .team-tasks__tab.active {
        color: #2563eb;
        border-color: #2563eb;
    }

    .team-tasks__filters {
        margin-top: 20px;
        padding: 18px 20px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .team-board-wrapper {
        margin-top: 28px;
    }

    .team-board-scroll {
        overflow-x: auto;
        padding-bottom: 16px;
        position: relative;
        scrollbar-width: thin;
        scrollbar-color: #c7d2fe #eef2ff;
    }

    .team-board-scroll::after {
        content: '';
        position: absolute;
        bottom: 4px;
        left: 32px;
        right: 32px;
        height: 6px;
        border-radius: 999px;
        background: linear-gradient(90deg, rgba(199, 210, 254, 0.2), rgba(99, 102, 241, 0.35), rgba(199, 210, 254, 0.2));
        pointer-events: none;
    }

    .team-board-scroll::-webkit-scrollbar {
        height: 10px;
    }

    .team-board-scroll::-webkit-scrollbar-track {
        background: #eef2ff;
        border-radius: 999px;
    }

    .team-board-scroll::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, #a78bfa, #6366f1);
        border-radius: 999px;
    }

    .team-board-scroll::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(90deg, #7c3aed, #4c1d95);
    }

    .team-board {
        display: flex;
        gap: 20px;
        min-height: 360px;
    }

    .team-column {
        background: linear-gradient(180deg, #eff4ff 0%, #f7f9ff 100%);
        border-radius: 24px;
        padding: 18px;
        width: 340px;
        flex: 0 0 340px;
        display: flex;
        flex-direction: column;
        max-height: 80vh;
        border: 1px solid #e0e7ff;
        box-shadow: 0 25px 45px rgba(15, 23, 42, 0.08);
    }

    .team-column__header {
        margin-bottom: 0;
    }

    .team-column__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
        border-radius: 18px;
        padding: 12px 16px;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
    }

    .team-column__meta {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .team-column__avatar img {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15);
    }

    .team-column__name {
        margin: 0;
        font-weight: 600;
        color: #0f172a;
    }

    .team-column__you {
        margin-left: 6px;
        font-size: 13px;
        font-weight: 500;
        color: #475569;
    }

    .team-column__count {
        font-size: 13px;
        color: #94a3b8;
    }

    .team-column__meta-actions {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .team-column__count-pill {
        min-width: 32px;
        height: 32px;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
    }

    .team-column__add {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f1f5ff;
        border: 1px solid #dbeafe;
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .team-column__add:hover {
        background: #e0ecff;
    }

    .team-column__body {
        overflow-y: auto;
        padding-right: 6px;
        margin-top: 18px;
        scrollbar-width: none;
    }

    .team-column__body::-webkit-scrollbar {
        display: none;
    }

    .team-column__empty {
        color: #94a3b8;
        font-style: italic;
        padding: 18px;
        text-align: center;
    }

    .team-task-card {
        background: #fff;
        border-radius: 22px;
        padding: 18px;
        box-shadow: 0 25px 50px rgba(15, 23, 42, 0.12);
        margin-bottom: 18px;
        border: 1px solid #e0e7ff;
        position: relative;
    }

    .team-task-card--overdue {
        border-color: #fecaca;
    }

    .team-task-card__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .team-task-card__top-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .team-task-card__code-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eef2ff;
        color: #475569;
        border-radius: 14px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 600;
    }

    .team-task-card__status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
        border-radius: 14px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 600;
    }

    .team-task-card__followers-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
        border-radius: 14px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 600;
    }

    .team-task-card__alert {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #fee2e2;
        color: #b91c1c;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .team-task-card__title {
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
        text-decoration: none;
        display: block;
        margin-bottom: 10px;
    }

    .team-task-card__title:hover {
        color: #2563eb;
    }

    .team-task-card__progress-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        font-size: 13px;
        color: #475569;
    }

    .team-task-card__progress-track {
        flex: 1;
        height: 6px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .team-task-card__progress-fill {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, #34d399, #10b981);
    }

    .team-task-card__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }

    .team-task-card__chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .team-task-card__chip-icon {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
    }

    .team-task-card__chip--tag {
        background: rgba(236, 72, 153, 0.15);
        color: #be185d;
    }

    .team-task-card__chip--tag .team-task-card__chip-icon {
        background: #f472b6;
        color: #fff;
    }

    .team-task-card__chip--status {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
    }

    .team-task-card__chip--assignee {
        background: #f1f5f9;
        color: #0f172a;
    }

    .team-task-card__chip--assignee .team-task-card__chip-icon {
        background: #e0e7ff;
        color: #4338ca;
    }

    .team-task-card__chip-avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e0e7ff;
    }

    .team-task-card__chip-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        display: block;
    }

    .team-task-card__chip--overflow {
        cursor: pointer;
        background: rgba(15, 23, 42, 0.07);
        color: #0f172a;
    }

    .team-task-card__overflow-list {
        min-width: 180px;
    }

    .team-task-card__overflow-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 0;
        border-bottom: 1px solid #eef2ff;
    }

    .team-task-card__overflow-item:last-child {
        border-bottom: 0;
    }

    .team-task-card__overflow-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        overflow: hidden;
    }

    .team-task-card__overflow-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .team-task-card__chip--followers {
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
    }

    .team-task-card__assignees {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .team-task-card__assignee-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 14px;
        background: #f8fafc;
        color: #0f172a;
        font-size: 12px;
        font-weight: 500;
    }

    .team-task-card__assignee-initials {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #e0ecff;
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 12px;
    }

    .team-task-card__followers {
        margin-top: 12px;
    }

    .team-tasks-empty {
        text-align: center;
        padding: 60px 0;
        color: #94a3b8;
        border: 2px dashed #cbd5f5;
        border-radius: 16px;
        background: #f8fafc;
    }

    @media (max-width: 991px) {
        .team-tasks__filters .form-group {
            margin-bottom: 12px;
        }
        .team-board {
            flex-direction: column;
        }
        .team-column {
            width: 100%;
            max-height: none;
        }
    }
</style>
<div id="wrapper">
    <div class="content team-tasks-board-page" id="teamTasksBoardPage">
        <div class="team-tasks-surface">
            <div class="team-tasks__header">
                <div>
                    <h4><?= _l('team_tasks'); ?></h4>
                    <p><?= _l('team_tasks_description'); ?></p>
                </div>
                <div class="team-tasks__header-actions">
                    <button type="button" class="btn btn-default" id="teamTasksRefresh">
                        <i class="fa fa-rotate-right tw-mr-1"></i>
                        <?= _l('team_tasks_refresh'); ?>
                    </button>
                    <?php if (staff_can('create', 'tasks')) { ?>
                        <a href="<?= admin_url('tasks/create'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?= _l('new_task'); ?>
                        </a>
                    <?php } ?>
                </div>
            </div>

            <div class="team-tasks__tabs">
                <a href="<?= admin_url('tasks/team_tasks'); ?>" class="team-tasks__tab active">
                    <?= _l('team_tasks_tab_kanban'); ?>
                </a>
                <a href="<?= admin_url('tasks'); ?>" class="team-tasks__tab">
                    <?= _l('team_tasks_tab_table'); ?>
                </a>
                <a href="<?= admin_url('utilities/calendar'); ?>" class="team-tasks__tab">
                    <?= _l('team_tasks_tab_calendar'); ?>
                </a>
            </div>

            <form method="get" class="team-tasks__filters" id="teamTasksFilters">
                <input type="hidden" name="filters_submitted" value="1" />
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label"><?= _l('search_tasks'); ?></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input type="search" name="search" class="form-control"
                                    value="<?= e($search); ?>"
                                    placeholder="<?= _l('search_tasks'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label"><?= _l('task_status'); ?></label>
                            <select name="status[]" class="selectpicker" multiple
                                data-width="100%"
                                data-actions-box="true"
                                data-live-search="false"
                                data-none-selected-text="<?= _l('team_tasks_status_filter_placeholder'); ?>">
                                <?php foreach ($statuses as $status) { ?>
                                    <option value="<?= e($status['id']); ?>"
                                        <?= in_array($status['id'], $selected_statuses, true) ? 'selected' : ''; ?>>
                                        <?= e($status['name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label"><?= _l('team_tasks_member_filter'); ?></label>
                            <select name="team_member[]" class="selectpicker" multiple
                                data-width="100%"
                                data-actions-box="true"
                                data-live-search="true"
                                data-none-selected-text="<?= _l('team_tasks_member_filter_placeholder'); ?>">
                                <?php foreach ($staff_filters as $member) { ?>
                                    <option value="<?= e($member['staffid']); ?>"
                                        <?= in_array($member['staffid'], $selected_staff, true) ? 'selected' : ''; ?>>
                                        <?= e($member['full_name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label class="control-label"><?= _l('team_tasks_sort_by'); ?></label>
                            <select name="sort" class="selectpicker" data-width="100%">
                                <option value="priority" <?= $sort === 'priority' ? 'selected' : ''; ?>>
                                    <?= _l('team_tasks_sort_priority'); ?>
                                </option>
                                <option value="due_date" <?= $sort === 'due_date' ? 'selected' : ''; ?>>
                                    <?= _l('team_tasks_sort_due_date'); ?>
                                </option>
                                <option value="recent" <?= $sort === 'recent' ? 'selected' : ''; ?>>
                                    <?= _l('team_tasks_sort_recent'); ?>
                                </option>
                                <option value="alpha" <?= $sort === 'alpha' ? 'selected' : ''; ?>>
                                    <?= _l('team_tasks_sort_alpha'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-info">
                            <?= _l('apply'); ?>
                        </button>
                        <a href="<?= admin_url('tasks/team_tasks'); ?>" class="btn btn-link">
                            <?= _l('reset'); ?>
                        </a>
                    </div>
                </div>
            </form>

            <div class="team-board-wrapper">
                <?php if (empty($team_board)) { ?>
                    <div class="team-tasks-empty">
                        <i class="fa-regular fa-face-smile fa-2x m-b-10"></i>
                        <p><?= _l('team_tasks_empty_state'); ?></p>
                    </div>
                <?php } else { ?>
                    <div class="team-board-scroll">
                        <div class="team-board">
                            <?php foreach ($team_board as $column) { ?>
                                <?php
                                    $count               = count($column['tasks']);
                                    $countLabel          = $count === 1 ? _l('team_tasks_member_single_task', $count) : _l('team_tasks_member_tasks', $count);
                                    $isCurrentUserColumn = get_staff_user_id() === (int) $column['staffid'];
                                ?>
                                <section class="team-column" data-staff-id="<?= e($column['staffid']); ?>">
                                    <header class="team-column__header">
                                        <div class="team-column__top">
                                            <div class="team-column__meta">
                                                <div class="team-column__avatar">
                                                    <?= staff_profile_image($column['staffid'], ['team-column__avatar-img'], 'small'); ?>
                                                </div>
                                                <div>
                                                    <p class="team-column__name">
                                                        <?= e($column['full_name']); ?>
                                                        <?php if ($isCurrentUserColumn) { ?>
                                                            <span class="team-column__you">(<?= _l('team_tasks_you_label'); ?>)</span>
                                                        <?php } ?>
                                                    </p>
                                                    <span class="team-column__count">
                                                        <?= e($countLabel); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="team-column__meta-actions">
                                                <span class="team-column__count-pill"><?= e($count); ?></span>
                                                <?php if (staff_can('create', 'tasks')) { ?>
                                                    <a href="<?= admin_url('tasks/create'); ?>" class="team-column__add" data-toggle="tooltip" title="<?= _l('new_task'); ?>">
                                                        <i class="fa fa-plus"></i>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </header>
                                    <div class="team-column__body">
                                        <?php if (empty($column['tasks'])) { ?>
                                            <div class="team-column__empty">
                                                <?= _l('team_tasks_empty_column'); ?>
                                            </div>
                                        <?php } else { ?>
                                            <?php foreach ($column['tasks'] as $task) { ?>
                                                <?php
                                                    $isOverdue = ! empty($task['duedate']) && $task['status'] != Tasks_model::STATUS_COMPLETE && strtotime($task['duedate']) < strtotime(date('Y-m-d'));
                                                    $progressPercent = 0;
                                                    if ($task['total_checklist_items'] > 0) {
                                                        $progressPercent = round(($task['total_finished_checklist_items'] / $task['total_checklist_items']) * 100);
                                                    }
                                                    $tags = get_tags_in($task['id'], 'task');
                                                ?>
                                                <?php $taskStatusText = trim(strip_tags(format_task_status($task['status']))); ?>
                                                <article class="team-task-card<?= $isOverdue ? ' team-task-card--overdue' : ''; ?>">
                                                    <div class="team-task-card__top">
                                                        <div class="team-task-card__top-left">
                                                            <span class="team-task-card__code-pill">
                                                                <i class="fa fa-clipboard-list"></i>
                                                                <?= $task['rel_name'] ? e($task['rel_name']) : '#' . e($task['id']); ?>
                                                            </span>
                                                            <span class="team-task-card__status-pill">
                                                                <i class="fa fa-circle"></i><?= e($taskStatusText); ?>
                                                            </span>
                                                            <?php if (! empty($task['followers'])) { ?>
                                                                <span class="team-task-card__followers-pill">
                                                                    <i class="fa fa-users"></i><?= count($task['followers']); ?>
                                                                </span>
                                                            <?php } ?>
                                                        </div>
                                                        <?php if ($isOverdue) { ?>
                                                            <span class="team-task-card__alert" data-toggle="tooltip" title="<?= _l('task_overdue'); ?>">
                                                                <i class="fa fa-bell"></i>
                                                            </span>
                                                        <?php } elseif (! empty($task['duedate'])) { ?>
                                                            <span class="team-task-card__code-pill">
                                                                <i class="fa fa-calendar"></i><?= e(_d($task['duedate'])); ?>
                                                            </span>
                                                        <?php } ?>
                                                    </div>
                                                    <a href="<?= admin_url('tasks/view/' . $task['id']); ?>"
                                                        class="team-task-card__title"
                                                        onclick="init_task_modal(<?= e($task['id']); ?>); return false;">
                                                        <?= e($task['name']); ?>
                                                    </a>
                                                    <?php if ($task['total_checklist_items'] > 0) { ?>
                                                        <div class="team-task-card__progress-row">
                                                            <span><i class="fa fa-check-square-o"></i><?= e($task['total_finished_checklist_items']); ?>/<?= e($task['total_checklist_items']); ?></span>
                                                            <div class="team-task-card__progress-track">
                                                                <span class="team-task-card__progress-fill" style="width: <?= e($progressPercent); ?>%;"></span>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                    <div class="team-task-card__chips">
                                                        <?php if (! empty($tags)) { ?>
                                                            <?php foreach ($tags as $tag) { ?>
                                                                <span class="team-task-card__chip team-task-card__chip--tag">
                                                                    <span class="team-task-card__chip-icon"><?= e(mb_substr($tag['name'], 0, 1)); ?></span>
                                                                    <?= e($tag['name']); ?>
                                                                </span>
                                                            <?php } ?>
                                                        <?php } ?>
                                                        <?php if (! empty($task['assignees'])) { ?>
                                                            <?php
                                                                $assigneeVisible  = array_slice($task['assignees'], 0, 2);
                                                                $assigneeOverflow = array_slice($task['assignees'], 2);
                                                            ?>
                                                            <?php foreach ($assigneeVisible as $assigneeChip) { ?>
                                                                <span class="team-task-card__chip team-task-card__chip--assignee">
                                                                    <span class="team-task-card__chip-avatar">
                                                                        <?= staff_profile_image($assigneeChip['id'], ['team-task-card__chip-avatar-img'], 'small'); ?>
                                                                    </span>
                                                                    <?= e($assigneeChip['name']); ?>
                                                                </span>
                                                            <?php } ?>
                                                            <?php if (! empty($assigneeOverflow)) { ?>
                                                                <?php
                                                                    $overflowContent = '<div class="team-task-card__overflow-list">';
                                                                    foreach ($assigneeOverflow as $overflowAssignee) {
                                                                        $overflowContent .= '<div class="team-task-card__overflow-item">';
                                                                        $overflowContent .= '<span class="team-task-card__overflow-avatar">' . staff_profile_image($overflowAssignee['id'], ['team-task-card__overflow-avatar-img'], 'small') . '</span>';
                                                                        $overflowContent .= '<span>' . e($overflowAssignee['name']) . '</span>';
                                                                        $overflowContent .= '</div>';
                                                                    }
                                                                    $overflowContent .= '</div>';
                                                                ?>
                                                                <span class="team-task-card__chip team-task-card__chip--assignee team-task-card__chip--overflow js-team-assignee-overflow"
                                                                    data-toggle="popover"
                                                                    data-html="true"
                                                                    data-placement="top"
                                                                    data-trigger="hover focus"
                                                                    data-content="<?= htmlspecialchars($overflowContent, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    +<?= count($assigneeOverflow); ?>
                                                                </span>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </div>
                                                </article>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                </section>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        init_selectpicker();
        $('#teamTasksFilters select.selectpicker').selectpicker();

        $('#teamTasksFilters select').on('changed.bs.select', function() {
            $('#teamTasksFilters').submit();
        });

        $('#teamTasksRefresh').on('click', function() {
            window.location = '<?= admin_url('tasks/team_tasks'); ?>';
        });

        var currentStaffId = <?= json_encode((int) get_staff_user_id()); ?>;
        var $boardScroll   = $('.team-board-scroll');

        if ($boardScroll.length) {
            var $currentColumn = $boardScroll.find('.team-column[data-staff-id="' + currentStaffId + '"]').first();

            if ($currentColumn.length) {
                var scrollTarget = $currentColumn.offset().left - $boardScroll.offset().left + $boardScroll.scrollLeft();
                var maxScroll    = Math.max($boardScroll[0].scrollWidth - $boardScroll.outerWidth(), 0);
                scrollTarget     = Math.min(Math.max(scrollTarget - 16, 0), maxScroll);

                if (scrollTarget > 0) {
                    $boardScroll.animate({
                        scrollLeft: scrollTarget
                    }, 400);
                }
            }
        }

        $('body').popover({
            selector: '.js-team-assignee-overflow',
            container: 'body',
            html: true,
            trigger: 'hover focus'
        });
    });
</script>
</body>
</html>
