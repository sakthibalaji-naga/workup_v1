<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$statusData   = get_task_status_by_id($task->status);
$priorities   = get_tasks_priorities();
$priorityData = null;

foreach ($priorities as $priority) {
    if ((int) $priority['id'] === (int) $task->priority) {
        $priorityData = $priority;

        break;
    }
}

$assignees = [];
if (!empty($task->assignees)) {
    foreach ($task->assignees as $assignee) {
        $fullname = trim(($assignee['firstname'] ?? '') . ' ' . ($assignee['lastname'] ?? ''));
        if ($fullname !== '') {
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
}

$followers = [];
if (!empty($task->followers)) {
    foreach ($task->followers as $follower) {
        $fullname = trim($follower['full_name']);
        if ($fullname !== '') {
            $followers[] = [
                'name' => $fullname,
                'id'   => $follower['id'],
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
}

$approvalTeam = [];
$approvedStaffIds = [];
$rejectedStaffIds = [];
if (! empty($approval_flow) && isset($approval_flow->steps) && is_array($approval_flow->steps)) {
    foreach ($approval_flow->steps as $step) {
        if (!empty($step['staff_id'])) {
            $staffId = (int) $step['staff_id'];
            if ($staffId > 0) {
                $fullname = get_staff_full_name($staffId);
                if ($fullname !== '') {
                    $approvalTeam[] = [
                        'name' => $fullname,
                        'id'   => $staffId,
                        'img'  => staff_profile_image(
                            $staffId,
                            ['staff-profile-image-small', 'task-summary-avatar'],
                            'small',
                            ['alt' => $fullname]
                        ),
                        'url'  => admin_url('profile/' . $staffId),
                    ];
                }
            }
        }
    }
    // Remove duplicates that might exist across steps
    $approvalTeam = array_unique($approvalTeam, SORT_REGULAR);
    $approvalTeam = array_values($approvalTeam);

    // Approved/rejected staff from approvals (already provided by controller)
    if (! empty($task_approvals)) {
        foreach ($task_approvals as $approval) {
            if (($approval['status'] ?? '') === 'approved') {
                $approvedStaffIds[] = (int) $approval['staff_id'];
            } elseif (($approval['status'] ?? '') === 'rejected') {
                $rejectedStaffIds[] = (int) $approval['staff_id'];
            }
        }
        $approvedStaffIds = array_unique($approvedStaffIds);
        $rejectedStaffIds = array_unique($rejectedStaffIds);
    }
}

$assigneeCount        = count($assignees);
$followerCount        = count($followers);
$approvalTeamCount    = count($approvalTeam);
$assigneeOverflow     = 0;
$assigneeOverflowList = [];
$followerNames        = array_map(function ($follower) {
    return $follower['name'] ?? '';
}, $followers);
$followerNames        = array_filter($followerNames, function ($name) {
    return $name !== '';
});
$followerPreview      = $followerCount > 0 ? implode(', ', array_slice($followerNames, 0, 3)) : _l('task_no_followers');
$followerOverflow     = max($followerCount - 3, 0);
$relationLabel     = '';
$relationLink      = '';
$ownerLabel        = _l('not_assigned');
$relationIndicator = '';
$canManageAssignees = staff_can('edit', 'tasks') || ($task->current_user_is_creator && staff_can('create', 'tasks'));
$canManageFollowers = staff_can('edit', 'tasks') || ($task->current_user_is_creator && staff_can('create', 'tasks'));
$canChangeStatus = staff_can('edit', 'tasks') || $task->current_user_is_creator || $task->current_user_is_assigned;
$canChangePriority = staff_can('edit', 'tasks') || $task->current_user_is_creator;
$assigneeMetaLabel = $assigneeCount > 0 ? $assigneeCount . ' ' . _l('assigned') : _l('task_single_not_assigned');
$followerMetaLabel = $followerCount > 0 ? $followerCount . ' ' . _l('task_single_followers') : _l('task_no_followers');
$approvalTeamMetaLabel = $approvalTeamCount > 0 ? $approvalTeamCount . ' ' . _l('staff_members') : _l('not_applicable');

if (! empty($task->rel_id)) {
    $relationData  = get_relation_data($task->rel_type, $task->rel_id);
    $relationValue = get_relation_values($relationData, $task->rel_type);
    $relationLabel = $relationValue['name'];
    $relationLink  = $relationValue['link'];
    $relationIndicator = _l('task_single_related');
}

if ($task->is_added_from_contact == 0 && $task->addedfrom) {
    $ownerLabel = get_staff_full_name($task->addedfrom);
} elseif ($task->is_added_from_contact == 1 && $task->addedfrom) {
    $ownerLabel = get_contact_full_name($task->addedfrom);
}

$startDate      = $task->startdate ? _d($task->startdate) : _l('not_applicable');
$dueDate        = $task->duedate ? _d($task->duedate) : _l('not_applicable');
$createdAt      = _dt($task->dateadded);
$isOverdue      = $task->duedate && strtotime($task->duedate) < time() && $task->status != Tasks_model::STATUS_COMPLETE;
$totalLogged    = seconds_to_time_format($this->tasks_model->calc_task_total_time($task->id));
$myLogged       = $this->tasks_model->calc_task_total_time($task->id, ' AND staff_id=' . get_staff_user_id());
$myLoggedLabel  = $myLogged ? seconds_to_time_format($myLogged) : '';
$statusBg       = adjust_hex_brightness($statusData['color'], 0.92);
$priorityBg     = $priorityData ? adjust_hex_brightness($priorityData['color'], 0.92) : '';
?>
<style>
    .task-page-wrapper {
        background-color: #f5f7fb;
        padding-top: 25px;
        padding-bottom: 35px;
    }

    .task-hero-panel {
        border-radius: 18px;
        border: none;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
    }

    .task-hero-header {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: space-between;
        align-items: flex-start;
    }

    .task-hero-title-group {
        flex: 1;
        min-width: 260px;
    }

    .task-hero-title-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        margin-bottom: 10px;
    }

    .task-hero-title {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
    }

    .task-meta-muted {
        margin: 0;
        color: #6b7280;
        font-size: 13px;
    }

    .task-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 12px;
        text-transform: none;
        letter-spacing: .04em;
        border: 1px solid rgba(15, 23, 42, 0.1);
    }

    .task-pill--muted {
        color: #475569;
        background: #f1f5f9;
    }

    .task-pill--clickable {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .task-pill--clickable:hover {
        opacity: 0.8;
        transform: scale(1.02);
    }

    .task-selection-panel {
        position: absolute;
        width: 240px;
        background: #fff;
        border-radius: 14px;
        padding: 14px;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.18);
        border: 1px solid rgba(148, 163, 184, 0.35);
        z-index: 2000;
    }

    .task-selection-panel__title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #475569;
        margin-bottom: 10px;
    }

    .task-selection-panel__list {
        display: flex;
        flex-direction: column;
        gap: 6px;
        max-height: 220px;
        overflow-y: auto;
    }

    .task-selection-panel__item {
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid transparent;
        border-radius: 12px;
        padding: 10px 12px;
        width: 100%;
        background: #f8fafc;
        color: #0f172a;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .task-selection-panel__item:hover {
        border-color: rgba(59, 130, 246, 0.4);
        background: #fff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }

    .task-selection-panel__item.is-selected {
        border-color: rgba(59, 130, 246, 0.45);
        background: rgba(59, 130, 246, 0.06);
    }

    .task-selection-panel__swatch {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #cbd5f5;
        box-shadow: 0 0 0 4px rgba(148, 163, 184, 0.2);
    }

    .task-selection-panel__check {
        margin-left: auto;
        color: #0f172a;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .task-selection-panel__label {
        flex: 1;
        text-align: left;
    }

    .task-selection-panel__item.is-selected .task-selection-panel__check {
        opacity: 1;
    }

    .task-selection-panel__empty {
        padding: 14px 8px;
        text-align: center;
        font-size: 12px;
        color: #94a3b8;
    }

    .task-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .task-hero-actions .btn {
        border-radius: 999px;
        padding: 8px 18px;
    }

    .task-summary-grid {
        margin-top: 24px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
    }

    .task-summary-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 16px 18px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
        min-height: auto;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .task-summary-card:hover {
        border-color: #cbd5f5;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
    }

    .task-summary-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }

    .task-summary-icon--schedule {
        background: #eef2ff;
        color: #1d4ed8;
    }

    .task-summary-icon--people {
        background: #ecfdf5;
        color: #059669;
    }

    .task-summary-icon--followers {
        background: #fff7ed;
        color: #f97316;
    }

    .task-summary-icon--time {
        background: #fef2f2;
        color: #dc2626;
    }

    .task-summary-icon--link {
        background: #f5f3ff;
        color: #7c3aed;
    }

    .task-summary-icon--approval {
        background: #fef3c7;
        color: #d97706;
    }

    .task-summary-card-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .task-summary-card-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .task-summary-label {
        text-transform: uppercase;
        font-size: 11px;
        color: #6b7280;
        letter-spacing: .08em;
        margin: 0;
    }

    .task-summary-title {
        font-size: 15px;
        font-weight: 600;
        color: #0f172a;
        margin: 0;
    }

    .task-summary-meta {
        font-size: 12px;
        color: #94a3b8;
        margin: 0;
    }

    .task-summary-list {
        list-style: none;
        padding: 0;
        margin: 4px 0 0;
    }

    .task-summary-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        color: #475569;
        margin-bottom: 4px;
    }

    .task-summary-list strong {
        font-size: 14px;
        color: #0f172a;
    }

    .task-summary-avatars {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .task-summary-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.12);
    }

    .task-summary-avatar-wrapper {
        position: relative;
    }

    .task-summary-avatar-wrapper .task-summary-avatar-more {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #475569;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .popover.task-assignee-overflow-popover {
        min-width: 220px;
        padding: 12px 16px;
    }

    .task-summary-overflow-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .task-summary-overflow-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13px;
        color: #0f172a;
        gap: 12px;
    }

    .task-summary-overflow-name {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .task-summary-overflow-remove {
        border: none;
        background: transparent;
        color: #ef4444;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }

    .task-summary-overflow-remove i {
        pointer-events: none;
    }

    .task-summary-avatar-remove {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #ef4444;
        color: #fff;
        border: none;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        cursor: pointer;
    }

    .task-summary-avatar-wrapper--manageable:hover .task-summary-avatar-remove {
        display: inline-flex;
    }

    .task-summary-add-btn {
        border: 1px dashed #cbd5f5;
        background: #eef2ff;
        color: #1d4ed8;
        border-radius: 999px;
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .task-summary-add-btn:hover {
        background: #e0e7ff;
    }

    .task-summary-empty {
        font-size: 13px;
        color: #94a3b8;
        margin: 0;
    }

    .task-panel {
        border-radius: 18px;
        border: none;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
    }

    .task-panel .panel-body {
        padding: 0;
    }

    .task-single-wrapper--page {
        padding: 28px;
    }

    .task-summary-alert {
        color: #b91c1c;
        font-size: 12px;
    }

    .approval-member-wrapper {
        position: relative;
    }

    .approval-avatar-container {
        position: relative;
        display: inline-block;
    }

.approval-avatar-ring {
    position: relative;
    display: inline-block;
    border-radius: 50%;
    box-shadow: 0 0 0 2px #10b981; /* green border shadow */
}

.approval-avatar-ring--rejected {
    box-shadow: 0 0 0 2px #f87171;
}

.approval-avatar-ring .task-summary-avatar {
    border-radius: 50%;
}

    .approval-tick-mark {
        position: absolute;
        bottom: -2px;
        left: 50%;
        transform: translateX(-50%);
        background: #10b981;
        color: white;
        border-radius: 50%;
        width: 14px;
        height: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 8px;
    border: 2px solid #fff;
    z-index: 1;
}

.approval-cross-mark {
    position: absolute;
    bottom: -2px;
    left: 50%;
    transform: translateX(-50%);
    background: #ef4444;
    color: #fff;
    border-radius: 50%;
    width: 14px;
    height: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    border: 2px solid #fff;
    z-index: 1;
}

    @media (max-width: 991px) {
        .task-single-wrapper--page {
            padding: 18px;
        }

        .task-hero-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
<div id="wrapper">
    <div class="content task-page-wrapper">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s task-hero-panel">
                    <div class="panel-body">
                        <div class="task-hero-header">
                            <div class="task-hero-title-group">
                                <a href="<?= admin_url('tasks'); ?>" class="btn btn-default btn-sm tw-mb-2">
                                    <i class="fa-solid fa-arrow-left tw-mr-1"></i>
                                    <?= _l('tasks'); ?>
                                </a>
                                <div class="task-hero-title-row">
                                    <span class="task-pill task-pill--muted">#<?= e($task->id); ?></span>
                                    <h2 class="task-hero-title"><?= e($task->name); ?></h2>
                                    <?php if ($canChangeStatus) { ?>
                                    <span class="task-pill task-pill--clickable"
                                        style="color:<?= e($statusData['color']); ?>;border-color:<?= e($statusData['color']); ?>;"
                                        onclick="changeTaskStatus(this, <?= e($task->id); ?>, '<?= e($task->status); ?>')">
                                        <i class="fa-solid fa-circle-dot"></i>
                                        <?= e($statusData['name']); ?>
                                    </span>
                                    <?php } else { ?>
                                    <span class="task-pill"
                                        style="color:<?= e($statusData['color']); ?>;border-color:<?= e($statusData['color']); ?>;">
                                        <i class="fa-solid fa-circle-dot"></i>
                                        <?= e($statusData['name']); ?>
                                    </span>
                                    <?php } ?>
                                    <?php if ($priorityData) { ?>
                                    <?php if ($canChangePriority) { ?>
                                    <span class="task-pill task-pill--clickable"
                                        style="color:<?= e($priorityData['color']); ?>;border-color:<?= e($priorityData['color']); ?>;"
                                        onclick="changeTaskPriority(this, <?= e($task->id); ?>, <?= e($task->priority); ?>)">
                                        <i class="fa-solid fa-bolt-lightning"></i>
                                        <?= e($priorityData['name']); ?>
                                    </span>
                                    <?php } else { ?>
                                    <span class="task-pill"
                                        style="color:<?= e($priorityData['color']); ?>;border-color:<?= e($priorityData['color']); ?>;">
                                        <i class="fa-solid fa-bolt-lightning"></i>
                                        <?= e($priorityData['name']); ?>
                                    </span>
                                    <?php } ?>
                                    <?php } ?>
                                    <?php if ($task->recurring == 1) { ?>
                                    <span class="task-pill task-pill--muted">
                                        <i class="fa-solid fa-rotate"></i>
                                        <?= _l('recurring_task'); ?>
                                    </span>
                                    <?php } ?>
                                </div>
                                <p class="task-meta-muted">
                                    <?= _l('task_created_by', '<span class="tw-font-medium tw-text-neutral-800">' . e($ownerLabel) . '</span>'); ?>
                                    <span class="tw-ml-1">· <?= e($createdAt); ?></span>
                                </p>
                            </div>
                            <div class="task-hero-actions">
                                <?php if ($task->status == Tasks_model::STATUS_COMPLETE && staff_can('edit', 'tasks')) { ?>
                                <a href="#"
                                    class="btn btn-warning"
                                    onclick="$('#task-single-unmark-complete-btn').trigger('click'); return false;">
                                    <i class="fa-solid fa-rotate-left tw-mr-1"></i>
                                    <?= _l('task_unmark_as_complete'); ?>
                                </a>
                                <?php } elseif ($task->status != Tasks_model::STATUS_COMPLETE && ($task->current_user_is_assigned || staff_can('edit', 'tasks'))) { ?>
                                <a href="#"
                                    class="btn btn-success"
                                    onclick="$('#task-single-mark-complete-btn').trigger('click'); return false;">
                                    <i class="fa-solid fa-circle-check tw-mr-1"></i>
                                    <?= _l('task_single_mark_as_complete'); ?>
                                </a>
                                <?php } ?>
                                <?php if ($task->billed == 0) {
                                    $is_assigned = $task->current_user_is_assigned;
                                    if (! $this->tasks_model->is_timer_started($task->id)) { ?>
                                <a href="#"
                                    class="btn<?= ! $is_assigned || $task->status == Tasks_model::STATUS_COMPLETE ? ' btn-default disabled' : ' btn-success'; ?>"
                                    onclick="timer_action(this, <?= e($task->id); ?>); return false;"
                                    <?php if (! $is_assigned) { ?> data-toggle="tooltip" data-title="<?= _l('task_start_timer_only_assignee'); ?>"<?php } ?>>
                                    <i class="fa-regular fa-clock tw-mr-1"></i>
                                    <?= _l('task_start_timer'); ?>
                                </a>
                                <?php } else { ?>
                                <a href="#" data-toggle="popover"
                                    data-placement="bottom"
                                    data-html="true" data-trigger="manual"
                                    data-title="<?= _l('note'); ?>"
                                    data-content='<?= render_textarea('timesheet_note'); ?><button type="button" onclick="timer_action(this, <?= e($task->id); ?>, <?= $this->tasks_model->get_last_timer($task->id)->id; ?>);" class="btn btn-primary btn-sm"><?= _l('save'); ?></button>'
                                    class="btn btn-danger<?= ! $is_assigned ? ' disabled' : ''; ?>"
                                    onclick="return false;">
                                    <i class="fa-regular fa-clock tw-mr-1"></i>
                                    <?= _l('task_stop_timer'); ?>
                                </a>
                                <?php } ?>
                                <?php } ?>
                                <a href="<?= admin_url('tasks/task/' . $task->id); ?>" class="btn btn-default<?= !$task->current_user_is_creator ? ' disabled' : ''; ?>">
                                    <i class="fa-regular fa-pen-to-square tw-mr-1"></i>
                                    <?= _l('task_single_edit'); ?>
                                </a>
                                <a href="#task-comments" class="btn btn-default">
                                    <i class="fa-regular fa-comments tw-mr-1"></i>
                                    <?= _l('task_comments'); ?>
                                </a>
                            </div>
                        </div>

                        <div class="task-summary-grid">
                            <div class="task-summary-card">
                                <span class="task-summary-icon task-summary-icon--schedule">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </span>
                                <div class="task-summary-card-content">
                                    <p class="task-summary-label"><?= _l('task_single_start_date'); ?> &rarr; <?= _l('task_duedate'); ?></p>
                                    <h5 class="task-summary-title"><?= e($startDate); ?> → <?= e($dueDate); ?></h5>
                                    <div class="task-info task-info-total-logged-time">
                                        <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                                            <i class="fa-regular fa-clock fa-fw fa-lg task-info-icon"></i>Total logged time:
                                            <span class="text-success">
                                                <?= e($totalLogged); ?>
                                            </span>
                                        </h5>
                                    </div>
                                    <?php if ($isOverdue) { ?>
                                    <span class="task-summary-alert">
                                        <i class="fa-solid fa-triangle-exclamation tw-mr-1"></i>
                                        <?= _l('task_is_overdue'); ?>
                                    </span>
                                    <?php } ?>
                                    <p class="task-summary-meta">
                                        <?= _l('task_created_at', '<span class="tw-font-normal">' . e($createdAt) . '</span>'); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="task-summary-card">
                                <span class="task-summary-icon task-summary-icon--people">
                                    <i class="fa-solid fa-user-check"></i>
                                </span>
                                <div class="task-summary-card-content">
                                    <div class="task-summary-card-heading">
                                        <div>
                                            <p class="task-summary-label"><?= _l('task_assigned'); ?></p>
                                            <h5 class="task-summary-title"><?= e($assigneeMetaLabel); ?></h5>
                                        </div>
                                        <?php if ($canManageAssignees) { ?>
                                        <button type="button"
                                            class="task-summary-add-btn task-assignee-picker-trigger"
                                            data-toggle="tooltip"
                                            data-title="<?= _l('task_single_assignees_select_title'); ?>">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                        <?php } ?>
                                    </div>
                                    <div class="task-summary-assignees js-task-summary-assignees">
                                        <?php $this->load->view('admin/tasks/_summary_assignees', [
                                            'assignees'           => $assignees,
                                            'taskId'              => $task->id,
                                            'canManageAssignees'  => $canManageAssignees,
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="task-summary-card">
                                <span class="task-summary-icon task-summary-icon--followers">
                                    <i class="fa-solid fa-user-group"></i>
                                </span>
                                <div class="task-summary-card-content">
                                    <div class="task-summary-card-heading">
                                        <div>
                                            <p class="task-summary-label"><?= _l('task_single_followers'); ?></p>
                                            <h5 class="task-summary-title"><?= e($followerMetaLabel); ?></h5>
                                        </div>
                                        <?php if ($canManageFollowers) { ?>
                                        <button type="button"
                                            class="task-summary-add-btn task-follower-picker-trigger"
                                            data-toggle="tooltip"
                                            data-title="<?= _l('task_single_followers_select_title'); ?>">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                        <?php } ?>
                                    </div>
                                    <div class="task-summary-followers js-task-summary-followers">
                                        <?php $this->load->view('admin/tasks/_summary_followers', [
                                            'followers'           => $followers,
                                            'taskId'              => $task->id,
                                            'canManageFollowers'  => $canManageFollowers,
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="task-summary-card">
                                <?php if ($task->rel_type == 'approval' && !empty($approvalTeam)) { ?>
                                <span class="task-summary-icon task-summary-icon--approval">
                                    <i class="fa-solid fa-users-gear"></i>
                                </span>
                                <?php } else { ?>
                                <span class="task-summary-icon task-summary-icon--link">
                                    <i class="fa-solid fa-link"></i>
                                </span>
                                <?php } ?>
                                <div class="task-summary-card-content">
                                    <?php if ($task->rel_type == 'approval' && !empty($approvalTeam)) { ?>
                                    <div>
                                        <p class="task-summary-label">Approval Team</p>
                                        <h5 class="task-summary-title"><?= e($approvalTeamMetaLabel); ?> - <strong><?php
                                            $name = $relationLabel;
                                            if (strlen($name) > 8) {
                                                $name = substr($name, 0, 6) . '...';
                                            }
                                            echo e($name);
                                        ?></strong></h5>
                                        <div class="task-summary-approval-team">
                                            <?php $this->load->view('admin/tasks/_summary_approval_team', [
                                                'approvalTeam' => $approvalTeam,
                                                'approvedStaffIds' => $approvedStaffIds,
                                                'rejectedStaffIds' => $rejectedStaffIds,
                                            ]); ?>
                                        </div>
                                    </div>
                                    <?php } else { ?>
                                    <p class="task-summary-label"><?= _l('task_single_related'); ?></p>
                                    <h5 class="task-summary-title">
                                        <?= ! empty($task->rel_type) ? e(ucfirst($task->rel_type)) : _l('not_applicable'); ?>
                                    </h5>
                                    <?php if ($relationLabel !== '') { ?>
                                    <p class="task-summary-meta">
                                        <?php if ($relationLink) { ?>
                                        <a href="<?= $relationLink; ?>" target="_blank">
                                            <?= e($relationLabel); ?>
                                        </a>
                                        <?php } else { ?>
                                            <?= e($relationLabel); ?>
                                        <?php } ?>
                                    </p>
                                    <?php } else { ?>
                                    <p class="task-summary-empty"><?= _l('not_applicable'); ?></p>
                                    <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row tw-mt-4">
            <div class="col-md-12">
                <div class="panel_s task-panel">
                    <div class="panel-body">
                        <div class="task-single-wrapper task-single-wrapper--page">
                            <?php
                            $data['full_page_view'] = true;
                            $this->load->view('admin/tasks/view_task_template', $data);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
// Task status and priority data
var taskStatuses = <?php echo json_encode(get_task_statuses()); ?>;
var taskPriorities = <?php echo json_encode(get_tasks_priorities()); ?>;
var taskSelectionText = {
    statusTitle: <?php echo json_encode(_l('task_status')); ?>,
    priorityTitle: <?php echo json_encode(_l('task_priority')); ?>,
    emptyText: <?php echo json_encode(_l('not_applicable')); ?>
};

function closeTaskSelectionPanel() {
    $('.task-selection-panel').remove();
}

function positionTaskSelectionPanel($anchor, $panel) {
    var panelWidth = $panel.outerWidth();
    var panelHeight = $panel.outerHeight();
    var offset = $anchor.offset();
    var top = offset.top + $anchor.outerHeight() + 10;
    var left = offset.left;
    var viewportWidth = $(window).width();
    var viewportHeight = $(window).height();
    var scrollTop = $(window).scrollTop();

    if ((left + panelWidth + 20) > viewportWidth) {
        left = viewportWidth - panelWidth - 20;
    }

    if ((top + panelHeight) > (scrollTop + viewportHeight)) {
        top = offset.top - panelHeight - 10;
    }

    $panel.css({ top: top, left: left });
}

function openTaskSelectionPanel(config) {
    var items = config.items || [];
    var currentValue = config.currentValue != null ? String(config.currentValue) : '';
    closeTaskSelectionPanel();

    var $panel = $('<div/>', {
        'class': 'task-selection-panel',
        'data-selection-type': config.type || 'task'
    });

    if (config.title) {
        $('<div/>', {
            'class': 'task-selection-panel__title',
            text: config.title
        }).appendTo($panel);
    }

    var $list = $('<div/>', { 'class': 'task-selection-panel__list' });

    if (!items.length) {
        $('<div/>', {
            'class': 'task-selection-panel__empty',
            text: config.emptyText || taskSelectionText.emptyText
        }).appendTo($panel);
    } else {
        items.forEach(function(option) {
            var optionId = String(option.id);
            var $item = $('<button/>', {
                type: 'button',
                'class': 'task-selection-panel__item' + (optionId === currentValue ? ' is-selected' : ''),
                'data-value': option.id
            });

            $('<span/>', {
                'class': 'task-selection-panel__swatch',
            }).css('background-color', option.color || '#cbd5f5').appendTo($item);

            $('<span/>', {
                'class': 'task-selection-panel__label',
                text: option.name
            }).appendTo($item);

            $('<span/>', {
                'class': 'task-selection-panel__check',
                html: '<i class="fa-solid fa-check"></i>'
            }).appendTo($item);

            $item.on('click', function(e) {
                e.preventDefault();
                if (typeof config.onSelect === 'function') {
                    config.onSelect(option.id);
                }
            });

            $item.appendTo($list);
        });
    }

    $panel.append($list);
    $('body').append($panel);

    positionTaskSelectionPanel($(config.element), $panel);
}

// Task status and priority click handlers
window.changeTaskStatus = function(element, taskId, currentStatus) {
    openTaskSelectionPanel({
        element: element,
        items: taskStatuses,
        currentValue: currentStatus,
        type: 'status',
        title: taskSelectionText.statusTitle,
        onSelect: function(newStatus) {
            updateTaskStatus(newStatus, taskId, currentStatus);
        }
    });
};

window.updateTaskStatus = function(newStatus, taskId, oldStatus) {
    closeTaskSelectionPanel();

    if (newStatus === undefined || newStatus === null || newStatus === '' || String(newStatus) === String(oldStatus)) {
        return;
    }

    $.ajax({
        url: admin_url + 'tasks/mark_as/' + newStatus + '/' + taskId + '?single_task=true',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (typeof response === 'string') {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    alert('Error: Invalid response format');
                    return;
                }
            }
            if (response.success === true || response.success == 'true') {
                window.location.reload();
            } else {
                alert('Error updating task status: ' + (response.message || 'Unknown error'));
            }
        },
        error: function() {
            alert('Network error while updating status');
        }
    });
};

window.changeTaskPriority = function(element, taskId, currentPriority) {
    openTaskSelectionPanel({
        element: element,
        items: taskPriorities,
        currentValue: currentPriority,
        type: 'priority',
        title: taskSelectionText.priorityTitle,
        onSelect: function(newPriority) {
            updateTaskPriority(newPriority, taskId, currentPriority);
        }
    });
};

window.updateTaskPriority = function(newPriority, taskId, oldPriority) {
    closeTaskSelectionPanel();

    if (newPriority === undefined || newPriority === null || newPriority === '' || String(newPriority) === String(oldPriority)) {
        return;
    }

    $.ajax({
        url: admin_url + 'tasks/change_priority/' + newPriority + '/' + taskId + '?single_task=true',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (typeof response === 'string') {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    alert('Error: Invalid response format');
                    return;
                }
            }
            if (response.success === true || response.success == 'true') {
                window.location.reload();
            } else {
                alert('Error updating task priority: ' + (response.message || 'Unknown error'));
            }
        },
        error: function() {
            alert('Network error while updating priority');
        }
    });
};

$(document).on('click.taskSelection', function(e) {
    if ($(e.target).closest('.task-selection-panel, .task-pill--clickable').length === 0) {
        closeTaskSelectionPanel();
    }
});

$(document).on('keyup.taskSelection', function(e) {
    if (e.key === 'Escape') {
        closeTaskSelectionPanel();
    }
});

$(window).on('scroll.taskSelection resize.taskSelection', function() {
    closeTaskSelectionPanel();
});
</script>

</body>
</html>
