<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .my-tasks-board-page {
        background: #f8fafc;
        min-height: calc(100vh - 120px);
    }

    .my-tasks-surface {
        background: #fff;
        border-radius: 14px;
        padding: 32px 32px 24px;
        box-shadow: 0 20px 65px rgba(15, 23, 42, 0.08);
    }

    .my-tasks__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
        flex-wrap: wrap;
    }

    .my-tasks__header h4 {
        font-weight: 600;
        margin: 0;
        font-size: 24px;
        color: #0f172a;
    }

    .my-tasks__header p {
        margin: 4px 0 0;
        color: #475569;
    }

    .my-tasks__header-actions .btn {
        margin-left: 8px;
    }

    .my-tasks__tabs {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-top: 24px;
        border-bottom: 1px solid #e2e8f0;
    }

    .my-tasks__tab {
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

    .my-tasks__tab.active {
        color: #2563eb;
        border-color: #2563eb;
    }

    .my-tasks__filters {
        margin-top: 20px;
        padding: 18px 20px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .my-board-wrapper {
        margin-top: 28px;
    }

    .my-board-scroll {
        overflow-x: auto;
        padding-bottom: 16px;
        position: relative;
        scrollbar-width: thin;
        scrollbar-color: #c7d2fe #eef2ff;
    }

    .my-board-scroll::after {
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

    .my-board-scroll::-webkit-scrollbar {
        height: 10px;
    }

    .my-board-scroll::-webkit-scrollbar-track {
        background: #eef2ff;
        border-radius: 999px;
    }

    .my-board-scroll::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, #a78bfa, #6366f1);
        border-radius: 999px;
    }

    .my-board-scroll::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(90deg, #7c3aed, #4c1d95);
    }

    .my-board {
        display: flex;
        gap: 20px;
        min-height: 360px;
    }

    .my-column {
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

    .my-column__header {
        margin-bottom: 0;
    }

    .my-column__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
        border-radius: 18px;
        padding: 12px 16px;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
    }

    .my-column__meta {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .my-column__icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #e0e7ff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        font-size: 20px;
    }

    .my-column__name {
        margin: 0;
        font-weight: 600;
        color: #0f172a;
    }

    .my-column__count {
        font-size: 13px;
        color: #94a3b8;
    }

    .my-column__meta-actions {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .my-column__count-pill {
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

    .my-column__add {
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

    .my-column__add:hover {
        background: #e0ecff;
    }

    .my-column__body {
        overflow-y: auto;
        padding-right: 6px;
        margin-top: 18px;
        scrollbar-width: none;
    }

    .my-column__body::-webkit-scrollbar {
        display: none;
    }

    .my-column__empty {
        color: #94a3b8;
        font-style: italic;
        padding: 18px;
        text-align: center;
    }

    .my-task-card {
        background: #fff;
        border-radius: 22px;
        padding: 18px;
        box-shadow: 0 25px 50px rgba(15, 23, 42, 0.12);
        margin-bottom: 18px;
        border: 1px solid #e0e7ff;
        position: relative;
    }

    .my-task-card--overdue {
        border-color: #fecaca;
    }

    .my-task-card__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .my-task-card__top-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .my-task-card__code-pill {
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

    .my-task-card__status-pill {
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

    .my-task-card__followers-pill {
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

    .my-task-card__alert {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #fee2e2;
        color: #b91c1c;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .my-task-card__title {
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
        text-decoration: none;
        display: block;
        margin-bottom: 10px;
    }

    .my-task-card__title:hover {
        color: #2563eb;
    }

    .my-task-card__progress-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        font-size: 13px;
        color: #475569;
    }

    .my-task-card__progress-track {
        flex: 1;
        height: 6px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .my-task-card__progress-fill {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, #34d399, #10b981);
    }

    .my-task-card__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }

    .my-task-card__chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .my-task-card__chip-icon {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
    }

    .my-task-card__chip--tag {
        background: rgba(236, 72, 153, 0.15);
        color: #be185d;
    }

    .my-task-card__chip--tag .my-task-card__chip-icon {
        background: #f472b6;
        color: #fff;
    }

    .my-task-card__chip--status {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
    }

    .my-task-card__chip--assignee {
        background: #f1f5f9;
        color: #0f172a;
    }

    .my-task-card__chip--assignee .my-task-card__chip-icon {
        background: #e0e7ff;
        color: #4338ca;
    }

    .my-task-card__chip-avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e0e7ff;
    }

    .my-task-card__chip-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        display: block;
    }

    .my-task-card__chip--overflow {
        cursor: pointer;
        background: rgba(15, 23, 42, 0.07);
        color: #0f172a;
    }

    .my-task-card__overflow-list {
        min-width: 180px;
    }

    .my-task-card__overflow-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 0;
        border-bottom: 1px solid #eef2ff;
    }

    .my-task-card__overflow-item:last-child {
        border-bottom: 0;
    }

    .my-task-card__overflow-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        overflow: hidden;
    }

    .my-task-card__overflow-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .my-task-card__chip--followers {
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
    }

    .my-task-card__assignees {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .my-task-card__assignee-chip {
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

    .my-task-card__assignee-initials {
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

    .my-task-card__followers {
        margin-top: 12px;
    }

    .my-tasks-empty {
        text-align: center;
        padding: 60px 0;
        color: #94a3b8;
        border: 2px dashed #cbd5f5;
        border-radius: 16px;
        background: #f8fafc;
    }

    @media (max-width: 991px) {
        .my-tasks__filters .form-group {
            margin-bottom: 12px;
        }
        .my-board {
            flex-direction: column;
        }
        .my-column {
            width: 100%;
            max-height: none;
        }
    }
</style>
<div id="wrapper">
    <div class="content my-tasks-board-page" id="myTasksBoardPage">
        <div class="my-tasks-surface">
            <div class="my-tasks__header">
                <div>
                    <h4><?= _l('my_tasks_kanban'); ?></h4>
                    <p><?= _l('my_tasks_kanban_description'); ?></p>
                </div>
                <div class="my-tasks__header-actions">
                    <button type="button" class="btn btn-default" id="myTasksRefresh">
                        <i class="fa fa-rotate-right tw-mr-1"></i>
                        <?= _l('my_tasks_refresh'); ?>
                    </button>
                    <?php if (staff_can('create', 'tasks')) { ?>
                        <a href="<?= admin_url('tasks/create'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?= _l('new_task'); ?>
                        </a>
                    <?php } ?>
                </div>
            </div>

            <div class="my-tasks__tabs">
                <a href="<?= admin_url('tasks/my_tasks_kanban'); ?>" class="my-tasks__tab active">
                    <?= _l('my_tasks_kanban'); ?>
                </a>
                <a href="<?= admin_url('tasks'); ?>" class="my-tasks__tab">
                    <?= _l('my_tasks_tab_table'); ?>
                </a>
                <a href="<?= admin_url('tasks/team_tasks'); ?>" class="my-tasks__tab">
                    <?= _l('team_tasks'); ?>
                </a>
                <a href="<?= admin_url('calendar'); ?>" class="my-tasks__tab">
                    <?= _l('team_tasks_tab_calendar'); ?>
                </a>
            </div>

            <form method="get" class="my-tasks__filters" id="myTasksFilters">
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
                                data-none-selected-text="<?= _l('my_tasks_status_filter_placeholder'); ?>">
                                <?php foreach ($statuses as $status) { ?>
                                    <option value="<?= e($status['id']); ?>"
                                        <?= in_array($status['id'], $selected_statuses, true) ? 'selected' : ''; ?>>
                                        <?= e($status['name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label"><?= _l('my_tasks_sort_by'); ?></label>
                            <select name="sort" class="selectpicker" data-width="100%">
                                <option value="priority" <?= $sort === 'priority' ? 'selected' : ''; ?>>
                                    <?= _l('my_tasks_sort_priority'); ?>
                                </option>
                                <option value="due_date" <?= $sort === 'due_date' ? 'selected' : ''; ?>>
                                    <?= _l('my_tasks_sort_due_date'); ?>
                                </option>
                                <option value="recent" <?= $sort === 'recent' ? 'selected' : ''; ?>>
                                    <?= _l('my_tasks_sort_recent'); ?>
                                </option>
                                <option value="alpha" <?= $sort === 'alpha' ? 'selected' : ''; ?>>
                                    <?= _l('my_tasks_sort_alpha'); ?>
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
                        <a href="<?= admin_url('tasks/my_tasks_kanban'); ?>" class="btn btn-link">
                            <?= _l('reset'); ?>
                        </a>
                    </div>
                </div>
            </form>

            <div class="my-board-wrapper">
                <?php if (empty($board)) { ?>
                    <div class="my-tasks-empty">
                        <i class="fa-regular fa-face-smile fa-2x m-b-10"></i>
                        <p><?= _l('my_tasks_empty_state'); ?></p>
                    </div>
                <?php } else { ?>
                    <div class="my-board-scroll">
                        <div class="my-board">
                            <?php if (!empty($special_columns)) { ?>
                                <?php foreach ($special_columns as $specialColumn) { ?>
                                    <?php
                                        $isToday = $specialColumn['status'] === 'today';
                                        $count = count($specialColumn['tasks']);
                                        $countLabel = $count === 1 ? _l('my_tasks_status_single_task', $count) : _l('my_tasks_status_tasks', $count);
                                        $columnTitle = $isToday ? _l('my_tasks_today_task') : _l('my_tasks_overdue_task');
                                    ?>
                                    <section class="my-column" data-status-id="<?= e($specialColumn['status']); ?>">
                                        <header class="my-column__header">
                                            <div class="my-column__top">
                                                <div class="my-column__meta">
                                                    <div class="my-column__icon" style="background-color: <?= $isToday ? '#fbbf24' : '#ef4444'; ?>20; color: <?= $isToday ? '#f59e0b' : '#dc2626'; ?>">
                                                        <i class="fa fa-<?= $isToday ? 'calendar-day' : 'exclamation-triangle'; ?>"></i>
                                                    </div>
                                                    <div>
                                                        <p class="my-column__name">
                                                            <?= e($columnTitle); ?>
                                                        </p>
                                                        <span class="my-column__count">
                                                            <?= e($countLabel); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="my-column__meta-actions">
                                                    <span class="my-column__count-pill"><?= e($count); ?></span>
                                                </div>
                                            </div>
                                        </header>
                                        <div class="my-column__body">
                                            <?php if (empty($specialColumn['tasks'])) { ?>
                                                <div class="my-column__empty">
                                                    <?= $isToday ? _l('my_tasks_empty_today') : _l('my_tasks_empty_overdue'); ?>
                                                </div>
                                            <?php } else { ?>
                                                <?php foreach ($specialColumn['tasks'] as $task) { ?>
                                                    <?php
                                                        $isTaskOverdue = ! empty($task['duedate']) && $task['status'] != Tasks_model::STATUS_COMPLETE && strtotime($task['duedate']) < strtotime(date('Y-m-d'));
                                                        $progressPercent = 0;
                                                        if ($task['total_checklist_items'] > 0) {
                                                            $progressPercent = round(($task['total_finished_checklist_items'] / $task['total_checklist_items']) * 100);
                                                        }
                                                        $tags = get_tags_in($task['id'], 'task');
                                                    ?>
                                                    <?php $taskStatusText = trim(strip_tags(format_task_status($task['status']))); ?>
                                                    <article class="my-task-card<?= $isTaskOverdue ? ' my-task-card--overdue' : ''; ?>">
                                                        <div class="my-task-card__top">
                                                            <div class="my-task-card__top-left">
                                                                <span class="my-task-card__code-pill">
                                                                    <i class="fa fa-clipboard-list"></i>
                                                                    <?= $task['rel_name'] ? e($task['rel_name']) : '#' . e($task['id']); ?>
                                                                </span>
                                                                <span class="my-task-card__status-pill">
                                                                    <i class="fa fa-circle"></i><?= e($taskStatusText); ?>
                                                                </span>
                                                                <?php if (! empty($task['followers'])) { ?>
                                                                    <span class="my-task-card__followers-pill">
                                                                        <i class="fa fa-users"></i><?= count($task['followers']); ?>
                                                                    </span>
                                                                <?php } ?>
                                                            </div>
                                                            <?php if ($isTaskOverdue) { ?>
                                                                <span class="my-task-card__alert" data-toggle="tooltip" title="<?= _l('task_overdue'); ?>">
                                                                    <i class="fa fa-bell"></i>
                                                                </span>
                                                            <?php } elseif (! empty($task['duedate'])) { ?>
                                                                <span class="my-task-card__code-pill">
                                                                    <i class="fa fa-calendar"></i><?= e(_d($task['duedate'])); ?>
                                                                </span>
                                                            <?php } ?>
                                                        </div>
                                                        <a href="<?= admin_url('tasks/view/' . $task['id']); ?>"
                                                            class="my-task-card__title"
                                                            onclick="init_task_modal(<?= e($task['id']); ?>); return false;">
                                                            <?= e($task['name']); ?>
                                                        </a>
                                                        <?php if ($task['total_checklist_items'] > 0) { ?>
                                                            <div class="my-task-card__progress-row">
                                                                <span><i class="fa fa-check-square-o"></i><?= e($task['total_finished_checklist_items']); ?>/<?= e($task['total_checklist_items']); ?></span>
                                                                <div class="my-task-card__progress-track">
                                                                    <span class="my-task-card__progress-fill" style="width: <?= e($progressPercent); ?>%;"></span>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                        <div class="my-task-card__chips">
                                                            <?php if (! empty($tags)) { ?>
                                                                <?php foreach ($tags as $tag) { ?>
                                                                    <span class="my-task-card__chip my-task-card__chip--tag">
                                                                        <span class="my-task-card__chip-icon"><?= e(mb_substr($tag['name'], 0, 1)); ?></span>
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
                                                                    <span class="my-task-card__chip my-task-card__chip--assignee">
                                                                        <span class="my-task-card__chip-avatar">
                                                                            <?= staff_profile_image($assigneeChip['id'], ['my-task-card__chip-avatar-img'], 'small'); ?>
                                                                        </span>
                                                                        <?= e($assigneeChip['name']); ?>
                                                                    </span>
                                                                <?php } ?>
                                                                <?php if (! empty($assigneeOverflow)) { ?>
                                                                    <?php
                                                                        $overflowContent = '<div class="my-task-card__overflow-list">';
                                                                        foreach ($assigneeOverflow as $overflowAssignee) {
                                                                            $overflowContent .= '<div class="my-task-card__overflow-item">';
                                                                            $overflowContent .= '<span class="my-task-card__overflow-avatar">' . staff_profile_image($overflowAssignee['id'], ['my-task-card__overflow-avatar-img'], 'small') . '</span>';
                                                                            $overflowContent .= '<span>' . e($overflowAssignee['name']) . '</span>';
                                                                            $overflowContent .= '</div>';
                                                                        }
                                                                        $overflowContent .= '</div>';
                                                                    ?>
                                                                    <span class="my-task-card__chip my-task-card__chip--assignee my-task-card__chip--overflow js-my-assignee-overflow"
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
                            <?php } ?>

                            <?php foreach ($board as $column) { ?>
                                <?php
                                    $status = array_filter($statuses, function ($s) use ($column) {
                                        return $s['id'] == $column['status'];
                                    });
                                    $status = reset($status);
                                    $count = count($column['tasks']);
                                    $countLabel = $count === 1 ? _l('my_tasks_status_single_task', $count) : _l('my_tasks_status_tasks', $count);
                                ?>
                                <section class="my-column" data-status-id="<?= e($column['status']); ?>">
                                    <header class="my-column__header">
                                        <div class="my-column__top">
                                            <div class="my-column__meta">
                                                <div class="my-column__icon" style="background-color: <?= e($status['color']); ?>20; color: <?= e($status['color']); ?>">
                                                    <i class="fa fa-circle"></i>
                                                </div>
                                                <div>
                                                    <p class="my-column__name">
                                                        <?= e($status['name']); ?>
                                                    </p>
                                                    <span class="my-column__count">
                                                        <?= e($countLabel); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="my-column__meta-actions">
                                                <span class="my-column__count-pill"><?= e($count); ?></span>
                                                <?php if (staff_can('create', 'tasks')) { ?>
                                                    <a href="<?= admin_url('tasks/create'); ?>" class="my-column__add" data-toggle="tooltip" title="<?= _l('new_task'); ?>">
                                                        <i class="fa fa-plus"></i>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </header>
                                    <div class="my-column__body">
                                        <?php if (empty($column['tasks'])) { ?>
                                            <div class="my-column__empty">
                                                <?= _l('my_tasks_empty_column'); ?>
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
                                                <article class="my-task-card<?= $isOverdue ? ' my-task-card--overdue' : ''; ?>">
                                                    <div class="my-task-card__top">
                                                        <div class="my-task-card__top-left">
                                                            <span class="my-task-card__code-pill">
                                                                <i class="fa fa-clipboard-list"></i>
                                                                <?= $task['rel_name'] ? e($task['rel_name']) : '#' . e($task['id']); ?>
                                                            </span>
                                                            <span class="my-task-card__status-pill">
                                                                <i class="fa fa-circle"></i><?= e($taskStatusText); ?>
                                                            </span>
                                                            <?php if (! empty($task['followers'])) { ?>
                                                                <span class="my-task-card__followers-pill">
                                                                    <i class="fa fa-users"></i><?= count($task['followers']); ?>
                                                                </span>
                                                            <?php } ?>
                                                        </div>
                                                        <?php if ($isOverdue) { ?>
                                                            <span class="my-task-card__alert" data-toggle="tooltip" title="<?= _l('task_overdue'); ?>">
                                                                <i class="fa fa-bell"></i>
                                                            </span>
                                                        <?php } elseif (! empty($task['duedate'])) { ?>
                                                            <span class="my-task-card__code-pill">
                                                                <i class="fa fa-calendar"></i><?= e(_d($task['duedate'])); ?>
                                                            </span>
                                                        <?php } ?>
                                                    </div>
                                                    <a href="<?= admin_url('tasks/view/' . $task['id']); ?>"
                                                        class="my-task-card__title"
                                                        onclick="init_task_modal(<?= e($task['id']); ?>); return false;">
                                                        <?= e($task['name']); ?>
                                                    </a>
                                                    <?php if ($task['total_checklist_items'] > 0) { ?>
                                                        <div class="my-task-card__progress-row">
                                                            <span><i class="fa fa-check-square-o"></i><?= e($task['total_finished_checklist_items']); ?>/<?= e($task['total_checklist_items']); ?></span>
                                                            <div class="my-task-card__progress-track">
                                                                <span class="my-task-card__progress-fill" style="width: <?= e($progressPercent); ?>%;"></span>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                    <div class="my-task-card__chips">
                                                        <?php if (! empty($tags)) { ?>
                                                            <?php foreach ($tags as $tag) { ?>
                                                                <span class="my-task-card__chip my-task-card__chip--tag">
                                                                    <span class="my-task-card__chip-icon"><?= e(mb_substr($tag['name'], 0, 1)); ?></span>
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
                                                                <span class="my-task-card__chip my-task-card__chip--assignee">
                                                                    <span class="my-task-card__chip-avatar">
                                                                        <?= staff_profile_image($assigneeChip['id'], ['my-task-card__chip-avatar-img'], 'small'); ?>
                                                                    </span>
                                                                    <?= e($assigneeChip['name']); ?>
                                                                </span>
                                                            <?php } ?>
                                                            <?php if (! empty($assigneeOverflow)) { ?>
                                                                <?php
                                                                    $overflowContent = '<div class="my-task-card__overflow-list">';
                                                                    foreach ($assigneeOverflow as $overflowAssignee) {
                                                                        $overflowContent .= '<div class="my-task-card__overflow-item">';
                                                                        $overflowContent .= '<span class="my-task-card__overflow-avatar">' . staff_profile_image($overflowAssignee['id'], ['my-task-card__overflow-avatar-img'], 'small') . '</span>';
                                                                        $overflowContent .= '<span>' . e($overflowAssignee['name']) . '</span>';
                                                                        $overflowContent .= '</div>';
                                                                    }
                                                                    $overflowContent .= '</div>';
                                                                ?>
                                                                <span class="my-task-card__chip my-task-card__chip--assignee my-task-card__chip--overflow js-my-assignee-overflow"
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
        $('#myTasksFilters select.selectpicker').selectpicker();

        $('#myTasksFilters select').on('changed.bs.select', function() {
            $('#myTasksFilters').submit();
        });

        $('#myTasksRefresh').on('click', function() {
            window.location = '<?= admin_url('tasks/my_tasks_kanban'); ?>';
        });

        $('body').popover({
            selector: '.js-my-assignee-overflow',
            container: 'body',
            html: true,
            trigger: 'hover focus'
        });
    });
</script>
</body>
</html>
