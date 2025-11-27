<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$isFullPageView = ! empty($full_page_view);
$headerClass    = $isFullPageView ? 'task-single-header task-single-header--page' : 'modal-header task-single-header';
$bodyClass      = $isFullPageView ? 'task-single-body task-single-body--page' : 'modal-body task-single-body';
$titleTag       = $isFullPageView ? 'h3' : 'h4';
?>

<div class="<?= $bodyClass; ?>">
    <input id="taskid" type="hidden" value="<?= $task->id?>">
    <div class="row">
        <div class="col-md-8 task-single-col-left">


            <?php if ($task->billed == 0) {
                $is_assigned = $task->current_user_is_assigned;
                if (! $this->tasks_model->is_timer_started($task->id)) { ?>

            <?php } else { ?>
            <?php } ?>
            <?php if (staff_can('create', 'tasks') && count($task->timesheets) > 0) { ?>
            <p class="no-margin pull-left mright5">
                <a href="#" class="btn btn-default mright5" data-toggle="tooltip"
                    data-title="<?= _l('task_statistics'); ?>"
                    onclick="task_tracking_stats(<?= e($task->id); ?>); return false;">
                    <i class="fa fa-bar-chart"></i>
                </a>
            </p>
            <?php } ?>
            <?php
            } ?>
            <div class="clearfix"></div>
            <hr class="hr-10" />
            <div id="task_single_timesheets"
                class="<?= ! $this->session->flashdata('task_single_timesheets_open') ? 'hide' : ''; ?>">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="tw-text-sm tw-bg-neutral-50">
                                    <?= _l('timesheet_user'); ?>
                                </th>
                                <th class="tw-text-sm tw-bg-neutral-50">
                                    <?= _l('timesheet_start_time'); ?>
                                </th>
                                <th class="tw-text-sm tw-bg-neutral-50">
                                    <?= _l('timesheet_end_time'); ?>
                                </th>
                                <th class="tw-text-sm tw-bg-neutral-50">
                                    <?= _l('timesheet_time_spend'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                              $timers_found = false;

foreach ($task->timesheets as $timesheet) { ?>
                            <?php if (staff_can('edit', 'tasks') || staff_can('create', 'tasks') || staff_can('delete', 'tasks') || $timesheet['staff_id'] == get_staff_user_id()) {
                                $timers_found = true; ?>
                            <tr>
                                <td class="tw-text-sm">
                                    <?php if ($timesheet['note']) {
                                        echo '<i class="fa fa-comment" data-html="true" data-placement="right" data-toggle="tooltip" data-title="' . e($timesheet['note']) . '"></i>';
                                    } ?>
                                    <a href="<?= admin_url('staff/profile/' . $timesheet['staff_id']); ?>"
                                        target="_blank">
                                        <?= e($timesheet['full_name']); ?></a>
                                </td>
                                <td class="tw-text-sm">
                                    <?= e(_dt($timesheet['start_time'], true)); ?>
                                </td>
                                <td class="tw-text-sm">
                                    <?php
                                      if ($timesheet['end_time'] !== null) {
                                          echo e(_dt($timesheet['end_time'], true));
                                      } else {
                                          // Allow admins to stop forgotten timers by staff member
                                          if (! $task->billed && is_admin()) { ?>
                                    <a href="#" data-toggle="popover" data-placement="bottom" data-html="true"
                                        data-trigger="manual"
                                        data-title="<?= _l('note'); ?>"
                                        data-content='<?= render_textarea('timesheet_note'); ?><button type="button" onclick="timer_action(this, <?= e($task->id); ?>, <?= e($timesheet['id']); ?>, 1);" class="btn btn-primary btn-sm"><?= _l('save'); ?></button>'
                                        class="text-danger" onclick="return false;">
                                        <i class="fa-regular fa-clock"></i>
                                        <?= _l('task_stop_timer'); ?>
                                    </a>
                                    <?php
                                          }
                                      } ?>
                                </td>
                                <td class="tw-text-sm">
                                    <div class="tw-flex">
                                        <div class="tw-grow">
                                            <?php
                                                  if ($timesheet['time_spent'] == null) {
                                                      echo _l('time_h') . ': ' . e(seconds_to_time_format(time() - $timesheet['start_time'])) . '<br />';
                                                      echo _l('time_decimal') . ': ' . e(sec2qty(time() - $timesheet['start_time'])) . '<br />';
                                                  } else {
                                                      echo _l('time_h') . ': ' . e(seconds_to_time_format($timesheet['time_spent'])) . '<br />';
                                                      echo _l('time_decimal') . ': ' . e(sec2qty($timesheet['time_spent'])) . '<br />';
                                                  } ?>
                                        </div>
                                        <?php
                                    if (! $task->billed) { ?>
                                        <div
                                            class="tw-flex tw-items-center tw-shrink-0 tw-self-start tw-space-x-1.5 tw-ml-2">
                                            <?php
                                  if (staff_can('delete_timesheet', 'tasks') || (staff_can('delete_own_timesheet', 'tasks') && $timesheet['staff_id'] == get_staff_user_id())) {
                                      echo '<a href="' . admin_url('tasks/delete_timesheet/' . $timesheet['id']) . '" class="task-single-delete-timesheet text-danger" data-task-id="' . $task->id . '"><i class="fa fa-remove"></i></a>';
                                  }
                                        if (staff_can('edit_timesheet', 'tasks') || (staff_can('edit_own_timesheet', 'tasks') && $timesheet['staff_id'] == get_staff_user_id())) {
                                            echo '<a href="#" class="task-single-edit-timesheet text-info" data-toggle="tooltip" data-title="' . _l('edit') . '" data-timesheet-id="' . $timesheet['id'] . '">
                                    <i class="fa fa-edit"></i>
                                    </a>';
                                        }
                                        ?>
                                        </div>
                                        <?php } ?>
                                    </div>
                </div>
                </td>
                </tr>
                <tr>
                    <td class="timesheet-edit task-modal-edit-timesheet-<?= $timesheet['id'] ?> hide"
                        colspan="5">
                        <form class="task-modal-edit-timesheet-form">
                            <input type="hidden" name="timer_id"
                                value="<?= $timesheet['id'] ?>">
                            <input type="hidden" name="task_id"
                                value="<?= $task->id ?>">
                            <div class="timesheet-start-end-time">
                                <div class="col-md-6">
                                    <?= render_datetime_input('start_time', 'task_log_time_start', _dt($timesheet['start_time'], true)); ?>
                                </div>
                                <div class="col-md-6">
                                    <?= render_datetime_input('end_time', 'task_log_time_end', _dt($timesheet['end_time'], true)); ?>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">
                                        <?= _l('task_single_log_user'); ?>
                                    </label>
                                    <br />
                                    <select name="staff_id" class="selectpicker" data-width="100%">
                                        <?php foreach ($task->assignees as $assignee) {
                                            if ((staff_cant('create', 'task') && staff_cant('edit', 'task') && $assignee['assigneeid'] != get_staff_user_id()) || ($task->rel_type == 'project' && staff_cant('edit', 'projects') && $assignee['assigneeid'] != get_staff_user_id())) {
                                                continue;
                                            }
                                            $selected = '';
                                            if ($assignee['assigneeid'] == $timesheet['staff_id']) {
                                                $selected = ' selected';
                                            } ?>
                                        <option<?= e($selected); ?>
                                            value="<?= e($assignee['assigneeid']); ?>">
                                            <?= e($assignee['full_name']); ?>
                                            </option>
                                            <?php
                                        } ?>
                                    </select>
                                </div>
                                <?= render_textarea('note', 'note', $timesheet['note'], ['id' => 'note' . $timesheet['id']]); ?>
                            </div>
                            <div class="col-md-12 text-right">
                                <button type="button"
                                    class="btn btn-default edit-timesheet-cancel"><?= _l('cancel'); ?></button>
                                <button class="btn btn-success edit-timesheet-submit"></i>
                                    <?= _l('submit'); ?></button>
                            </div>
                        </form>
                    </td>
                </tr>
                <?php } ?>
                <?php } ?>
                <?php if ($timers_found == false) { ?>
                <tr>
                    <td colspan="5" class="text-center bold">
                        <?= _l('no_timers_found'); ?>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($task->billed == 0 && ($is_assigned || (count($task->assignees) > 0 && is_admin())) && $task->status != Tasks_model::STATUS_COMPLETE) {
                    ?>
                <tr class="odd">
                    <td colspan="5" class="add-timesheet">
                        <div class="col-md-12">
                            <p class="font-medium bold mtop5">
                                <?= _l('add_timesheet'); ?>
                            </p>
                            <hr class="mtop10 mbot10" />
                        </div>
                        <div class="timesheet-start-end-time">
                            <div class="col-md-6">
                                <?= render_datetime_input('timesheet_start_time', 'task_log_time_start'); ?>
                            </div>
                            <div class="col-md-6">
                                <?= render_datetime_input('timesheet_end_time', 'task_log_time_end'); ?>
                            </div>
                        </div>
                        <div class="timesheet-duration hide">
                            <div class="col-md-12">
                                <i class="fa-regular fa-circle-question pointer pull-left mtop2" data-toggle="popover"
                                    data-html="true" data-content="
                                    :15 - 15 <?= _l('minutes'); ?><br />
                                    2 - 2 <?= _l('hours'); ?><br />
                                    5:5 - 5 <?= _l('hours'); ?> & 5 <?= _l('minutes'); ?><br />
                                    2:50 - 2 <?= _l('hours'); ?> & 50 <?= _l('minutes'); ?><br />
                                    "></i>
                                <?= render_input('timesheet_duration', 'project_timesheet_time_spend', '', 'text', ['placeholder' => 'HH:MM']); ?>
                            </div>
                        </div>
                        <div class="col-md-12 mbot15 mntop15">
                            <a href="#" class="timesheet-toggle-enter-type">
                                <span class="timesheet-duration-toggler-text switch-to">
                                    <?= _l('timesheet_duration_instead'); ?>
                                </span>
                                <span class="timesheet-date-toggler-text hide ">
                                    <?= _l('timesheet_date_instead'); ?>
                                </span>
                            </a>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">
                                    <?= _l('task_single_log_user'); ?>
                                </label>
                                <br />
                                <select name="single_timesheet_staff_id" class="selectpicker" data-width="100%">
                                    <?php foreach ($task->assignees as $assignee) {
                                        if ((staff_cant('create', 'tasks') && staff_cant('edit', 'tasks') && $assignee['assigneeid'] != get_staff_user_id()) || ($task->rel_type == 'project' && staff_cant('edit', 'projects') && $assignee['assigneeid'] != get_staff_user_id())) {
                                            continue;
                                        }
                                        $selected = '';
                                        if ($assignee['assigneeid'] == get_staff_user_id()) {
                                            $selected = ' selected';
                                        } ?>
                                    <option<?= e($selected); ?>
                                        value="<?= e($assignee['assigneeid']); ?>">
                                        <?= e($assignee['full_name']); ?>
                                        </option>
                                        <?php
                                    } ?>
                                </select>
                            </div>
                            <?= render_textarea('task_single_timesheet_note', 'note'); ?>
                        </div>
                        <div class="col-md-12 text-right">
                            <?php
                                         $disable_button = '';
                    if ($this->tasks_model->is_timer_started_for_task($task->id, ['staff_id' => get_staff_user_id()])) {
                        $disable_button = 'disabled ';
                        echo '<div class="text-right mbot15 text-danger">' . _l('add_task_timer_started_warning') . '</div>';
                    } ?>
                            <button
                                <?= e($disable_button); ?>data-task-id="<?= e($task->id); ?>"
                                class="btn btn-success task-single-add-timesheet"><i class="fa fa-plus"></i>
                                <?= _l('submit'); ?></button>
                        </div>
                    </td>
                </tr>
                <?php
                } ?>
                </tbody>
                </table>
            </div>
            <hr />
        </div>
        <div class="clearfix"></div>
        <?php
        hooks()->do_action('before_task_description_section', $task);

        $raw_description         = $task->description ?? '';
        $plain_description       = trim(strip_tags($raw_description));
        $has_description         = $plain_description !== '';
        $description_word_count  = $has_description ? str_word_count($plain_description) : 0;
        $description_char_count  = $has_description ? (function_exists('mb_strlen') ? mb_strlen($plain_description) : strlen($plain_description)) : 0;
        $description_reading_min = $description_word_count > 0 ? max(1, (int) ceil($description_word_count / 200)) : 0;
        ?>
        <div class="task-description-card <?= $has_description ? '' : 'task-description-card--empty'; ?>">
            <div class="task-description-card__header">
                <div class="task-description-card__intro">
                    <div>
                        <p class="task-description-label"><?= _l('task_view_description'); ?></p>
                    </div>
                </div>
            </div>
            <?php if ($has_description) { ?>
            <div class="task-description-meta">
                <?php if ($description_word_count > 0) { ?>
                <span class="task-description-meta__item">
                    <i class="fa-regular fa-a"></i>
                    <?= number_format($description_word_count); ?> words
                </span>
                <?php } ?>
                <?php if ($description_char_count > 0) { ?>
                <span class="task-description-meta__item">
                    <i class="fa-solid fa-text-width" aria-hidden="true"></i>
                    <?= number_format($description_char_count); ?> chars
                </span>
                <?php } ?>
                <?php if ($description_reading_min > 0) { ?>
                <span class="task-description-meta__item">
                    <i class="fa-regular fa-clock" aria-hidden="true"></i>
                    ~<?= $description_reading_min; ?> min read
                </span>
                <?php } ?>

            </div>
            <?php } ?>
            <div class="task-description-body">
                <div
                    id="task_view_description"
                    class="tc-content task-description-content <?= $has_description ? '' : 'task-no-description task-description-placeholder'; ?>">
                    <?php if ($has_description) { ?>
                    <?= check_for_links($task->description); ?>
                    <?php } else { ?>
                    <p><?= _l('task_no_description'); ?></p>
                    <ul class="task-description-placeholder__tips">
                        <li><i class="fa-regular fa-circle-check"></i> Summarize the goal or expected outcome.</li>
                        <li><i class="fa-regular fa-circle-check"></i> Call out dependencies, blockers, or owners.</li>
                        <li><i class="fa-regular fa-circle-check"></i> Link to resources, specs, or decision logs.</li>
                    </ul>
                    <?php if (staff_can('edit', 'tasks')) { ?>
                    <button type="button"
                        class="task-utility-btn task-utility-btn--primary tw-mt-3"
                        onclick="edit_task_inline_description(this,<?= e($task->id); ?>); return false;">
                        <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                        <span class="task-utility-btn-label"><?= _l('edit'); ?></span>
                    </button>
                    <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <hr />

        <?php if (isset($approval_flow) && $approval_flow && isset($approval_flow->steps) && is_array($approval_flow->steps) && count($approval_flow->steps) > 0) { ?>
        <?php
        $totalApprovalSteps = count($approval_flow->steps);
        $timelineSummary = [
            'approved' => 0,
            'pending'  => 0,
            'rejected' => 0,
        ];

        if (! empty($task_approvals)) {
            foreach ($task_approvals as $approvalRow) {
                $statusKey = $approvalRow['status'] ?? 'pending';
                if (isset($timelineSummary[$statusKey])) {
                    $timelineSummary[$statusKey]++;
                }
            }
        }

        $timelineSummary['pending'] = max(0, $totalApprovalSteps - ($timelineSummary['approved'] + $timelineSummary['rejected']));
        ?>
        <div class="task-approval-timeline-wrapper tw-mb-6">
            <div class="task-approval-timeline-header">
                <div>
                    <p class="tw-text-xs tw-uppercase tw-tracking-wide tw-text-slate-500 tw-mb-1">
                        <?= _l('step_by_step_approval'); ?>
                    </p>
                    <h4 class="th tw-font-semibold tw-text-lg tw-mb-1">
                        <?= ! empty($approval_flow->name) ? e($approval_flow->name) : _l('task_approvals_heading'); ?>
                    </h4>
                    <p class="tw-text-sm tw-text-slate-500 tw-mb-0">
                        <?= $totalApprovalSteps; ?> <?= _l('task_approval_info_steps'); ?>
                    </p>
                </div>
                <div class="task-approval-timeline-header__meta">
                    <span><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?= $timelineSummary['approved']; ?> Approved</span>
                    <span><i class="fa-regular fa-clock" aria-hidden="true"></i><?= $timelineSummary['pending']; ?> Pending</span>
                    <span><i class="fa-regular fa-circle-xmark" aria-hidden="true"></i><?= $timelineSummary['rejected']; ?> Rejected</span>
                </div>
            </div>
            <div class="approval-timeline">
                <?php
                $previous_approved = true;
                $stepIndex = 0;
                foreach ($approval_flow->steps as $step) {
                    $stepIndex++;
                    $currentApproval = null;

                    if (! empty($task_approvals)) {
                        foreach ($task_approvals as $approval) {
                            if ($approval['step_order'] == $step['step_order']) {
                                $currentApproval = $approval;
                                break;
                            }
                        }
                    }

                    $stepStatus = $currentApproval['status'] ?? 'pending';
                    $step_approved = $stepStatus === 'approved';
                    $step_rejected = $stepStatus === 'rejected';
                    $step_pending = ! $step_approved && ! $step_rejected;

                    $can_approve = $previous_approved && $step_pending && (isset($current_user_next_approval) && $current_user_next_approval && $current_user_next_approval['step_order'] == $step['step_order']);
                    $statusClass = $step_approved ? 'is-approved' : ($step_rejected ? 'is-rejected' : 'is-pending');
                    $statusIcon = $step_approved ? 'fa-solid fa-circle-check' : ($step_rejected ? 'fa-solid fa-circle-xmark' : 'fa-regular fa-clock');
                    $statusLabel = ucfirst($stepStatus);
                    $can_revert = false;
                    $laterStepsLocked = false;
                    if (! empty($task_approvals)) {
                        foreach ($task_approvals as $approvalCheck) {
                            if ($approvalCheck['step_order'] > $step['step_order'] && $approvalCheck['status'] !== 'pending') {
                                $laterStepsLocked = true;
                                break;
                            }
                        }
                    }
                    if (! empty($currentApproval) && ($step_approved || $step_rejected) && ! $laterStepsLocked) {
                        $assignedStaffId = (int) ($currentApproval['staff_id'] ?? ($step['staff_id'] ?? 0));
                        $currentStaffId = (int) get_staff_user_id();
                        $can_revert = $assignedStaffId === $currentStaffId;
                    }

                    $statusSummary = '';
                    if ($step_rejected) {
                        $statusSummary = _l('task_approval_step_status_rejected');
                    } elseif ($step_pending && ((int) $step['step_order']) === 1) {
                        $statusSummary = _l('task_approval_step_first_prompt');
                    } elseif ($step_pending && $can_approve) {
                        $statusSummary = _l('task_approval_action_required');
                    } elseif ($step_pending) {
                        $statusSummary = _l('waiting_for_previous_step');
                    } else {
                        $statusSummary = '';
                    }

                    $historyEntries = $currentApproval['remark_history'] ?? [];
                    $historyPreview = '';
                    if (! empty($historyEntries)) {
                        $previewEntries = array_slice($historyEntries, 0, 3);
                        $historyPreviewParts = [];
                        foreach ($previewEntries as $historyEntry) {
                            $historyAction = strtolower($historyEntry['action_type'] ?? 'remark');
                            $actionLabels = [
                                'remark' => _l('task_approval_history_action_remark'),
                                'approve' => _l('task_approval_history_action_approve'),
                                'reject' => _l('task_approval_history_action_reject'),
                                'revert' => _l('task_approval_history_action_revert'),
                            ];
                            $actionLabel = $actionLabels[$historyAction] ?? _l('task_approval_history_action_remark');
                            $historyComment = trim(strip_tags($historyEntry['comments'] ?? ''));
                            if ($historyComment === '') {
                                $historyComment = _l('task_approval_history_default_note');
                            }
                            $maxLength = 120;
                            if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                                $historyComment = mb_strlen($historyComment) > $maxLength
                                    ? mb_substr($historyComment, 0, $maxLength) . '…'
                                    : $historyComment;
                            } else {
                                $historyComment = strlen($historyComment) > $maxLength
                                    ? substr($historyComment, 0, $maxLength) . '…'
                                    : $historyComment;
                            }
                            $historyPreviewParts[] = html_escape($actionLabel . ' · ' . _dt($historyEntry['created_at']) . ' — ' . $historyComment);
                        }
                        if (! empty($historyPreviewParts)) {
                            $historyPreview = implode('<br>', $historyPreviewParts);
                        }
                    }
                    if ($historyPreview === '') {
                        $historyPreview = html_escape(_l('task_approval_history_tooltip_empty'));
                    }

                    $revertHistory = [];
                    if (! empty($historyEntries)) {
                        foreach ($historyEntries as $historyEntry) {
                            if (($historyEntry['action_type'] ?? '') === 'revert') {
                                $revertHistory[] = $historyEntry;
                            }
                        }
                    }

                    $isLastStep = $stepIndex === $totalApprovalSteps;
                    ?>
                <div class="approval-timeline-step <?= $statusClass; ?><?= $can_approve ? ' is-actionable' : ''; ?><?= $isLastStep ? ' is-last' : ''; ?>">
                    <div class="approval-timeline-marker">
                        <span class="marker-dot">
                            <i class="<?= $statusIcon; ?>" aria-hidden="true"></i>
                        </span>
                        <?php if (! $isLastStep) { ?>
                        <span class="marker-line"></span>
                        <?php } ?>
                    </div>
                    <div class="approval-timeline-card">
                        <div class="approval-timeline-card__header">
                            <div>
                                <p class="tw-text-xs tw-uppercase tw-text-slate-500 tw-mb-1">
                                    Step <?= e($step['step_order']); ?>
                                </p>
                                <h5 class="tw-text-base tw-font-semibold tw-mb-0">
                                    <?= e(trim(($step['firstname'] ?? '') . ' ' . ($step['lastname'] ?? ''))); ?>
                                </h5>
                            </div>
                            <div class="approval-card-status-tools">
                                <button type="button"
                                    class="task-approval-history-trigger"
                                    data-toggle="tooltip"
                                    data-html="true"
                                    data-placement="top"
                                    data-title="<?= $historyPreview; ?>"
                                    onclick="openRemarkHistoryModal(<?= e($task->id); ?>, <?= e($currentApproval['id']); ?>)"
                                    aria-label="<?= _l('task_approval_history_view_all'); ?>">
                                    <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                                </button>
                                <span class="approval-status-badge badge-<?= $statusClass; ?>">
                                    <?= e($statusLabel); ?>
                                </span>
                            </div>
                        </div>
                        <div class="approval-timeline-card__body">
                            <?php if ($statusSummary !== '') { ?>
                            <p class="tw-text-sm tw-text-slate-600 tw-mb-2">
                                <?= e($statusSummary); ?>
                            </p>
                            <?php } ?>
                            <?php if ($step_approved && ! empty($currentApproval['approved_at'])) { ?>
                            <div class="approval-timeline-card__meta">
                                <span>
                                    <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                                    <?= _l('task_approval_step_completed_at'); ?> <?= _dt($currentApproval['approved_at']); ?>
                                </span>
                            </div>
                            <?php } elseif ($step_rejected && ! empty($currentApproval['rejected_at'])) { ?>
                            <div class="approval-timeline-card__meta">
                                <span>
                                    <i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i>
                                    <?= _l('task_approval_step_rejected_at'); ?> <?= _dt($currentApproval['rejected_at']); ?>
                                </span>
                            </div>
                            <?php } ?>
                            <?php if (! empty($currentApproval['comments'])) { ?>
                            <div class="approval-timeline-card__note">
                                <i class="fa-regular fa-comment-lines" aria-hidden="true"></i>
                                <div>
                                    <p class="tw-text-xs tw-uppercase tw-text-slate-500 tw-mb-0.5"><?= _l('task_approval_step_latest_remark'); ?></p>
                                    <div class="tw-text-sm tw-text-slate-700">
                                        <?= process_text_content_for_display($currentApproval['comments']); ?>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if (! empty($revertHistory)) {
                                $latestRevert = $revertHistory[0];
                                $revertCount = count($revertHistory);
                                $latestRevertMessage = trim(strip_tags($latestRevert['comments'] ?? ''));
                                if ($latestRevertMessage === '') {
                                    $latestRevertMessage = _l('task_approval_revert_card_no_message');
                                }
                            ?>
                            <div class="approval-timeline-card__notice">
                                <div class="approval-timeline-card__notice-header">
                                    <span class="label"><?= _l('task_approval_revert_card_count', $revertCount); ?></span>
                                    <span class="timestamp">
                                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                        <?= _l('task_approval_revert_card_last', _dt($latestRevert['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="approval-timeline-card__notice-body">
                                    <?= e($latestRevertMessage); ?>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="approval-timeline-card__actions">
                            <button type="button"
                                class="task-approval-action task-approval-action--approve"
                                onclick="openApproveTaskModal(<?= e($task->id); ?>)"
                                <?= $can_approve ? '' : 'disabled'; ?>>
                                <i class="fa-regular fa-circle-check" aria-hidden="true"></i>
                                <span><?= _l('task_approval_step_action_approve'); ?></span>
                            </button>
                            <button type="button"
                                class="task-approval-action task-approval-action--reject"
                                onclick="openRejectTaskModal(<?= e($task->id); ?>)"
                                <?= $can_approve ? '' : 'disabled'; ?>>
                                <i class="fa-regular fa-circle-xmark" aria-hidden="true"></i>
                                <span><?= _l('task_approval_step_action_reject'); ?></span>
                            </button>
                            <button type="button"
                                class="task-approval-action task-approval-action--secondary"
                                onclick="toggleStepRemarkPanel(<?= e($step['id']); ?>)"
                                <?= $can_approve ? '' : 'disabled'; ?>>
                                <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
                                <span><?= _l('task_approval_step_action_remark'); ?></span>
                            </button>
                            <?php if ($can_revert && ! empty($currentApproval['id'])) { ?>
                            <button type="button"
                                class="task-approval-action task-approval-action--ghost"
                                onclick="openRevertApprovalModal(<?= e($task->id); ?>, <?= e($currentApproval['id']); ?>)"
                                <i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i>
                                <span><?= _l('task_approval_revert_action'); ?></span>
                            </button>
                            <?php } ?>
                        </div>
                        <div class="step-action-panel" id="step-panel-<?= e($step['id']); ?>">
                            <div class="step-action-panel__header">
                                <h6 class="tw-text-sm tw-font-semibold tw-mb-0">
                                    <?= _l('task_approval_step_remark_title'); ?>
                                </h6>
                                <button type="button" class="step-action-panel__close" onclick="toggleStepRemarkPanel(<?= e($step['id']); ?>, true)">
                                    <i class="fa-regular fa-xmark" aria-hidden="true"></i>
                                </button>
                            </div>
                            <form class="step-approval-form" enctype="multipart/form-data">
                                <input type="hidden" name="step_id" value="<?= e($step['id']); ?>">
                                <input type="hidden" name="task_id" value="<?= e($task->id); ?>">
                                <div class="form-group">
                                    <label for="step-description-<?= e($step['id']); ?>"><?= _l('description_optional'); ?></label>
                                    <textarea name="description" id="step-description-<?= e($step['id']); ?>" class="form-control" rows="3" placeholder="Add any comments or description for this approval step"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="step-attachment-<?= e($step['id']); ?>"><?= _l('attachment_optional'); ?></label>
                                    <input type="file" name="attachment" id="step-attachment-<?= e($step['id']); ?>" class="form-control">
                                </div>
                                <div class="step-action-panel__footer">
                                    <button type="button" class="btn btn-success" onclick="submitStepApproval(<?= e($step['id']); ?>, 'remark')"><?= _l('submit'); ?></button>
                                    <button type="button" class="btn btn-default" onclick="toggleStepRemarkPanel(<?= e($step['id']); ?>, true)"><?= _l('cancel'); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
                    $previous_approved = $step_approved;
                }
                ?>
            </div>
        </div>
        <hr />
        <?php } ?>

        <div class="tw-flex tw-justify-between tw-items-center">
            <h4 class="chk-heading tw-my-0 tw-font-semibold tw-text-base">
                <?= _l('task_checklist_items'); ?>
            </h4>
            <div class="tw-flex tw-items-center tw-space-x-2">
                <div>
                    <div class="chk-toggle-buttons">
                        <?php if (count($task->checklist_items) > 0) { ?>
                        <button
                            class="tw-bg-transparent tw-border-0 tw-text-sm tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 tw-px-0<?= $hide_completed_items == 1 ? ' hide' : '' ?>"
                            data-hide="1" onclick="toggle_completed_checklist_items_visibility(this)">
                            <?= _l('hide_task_checklist_items_completed'); ?>
                        </button>
                        <?php $finished = array_filter($task->checklist_items, function ($item) {
                            return $item['finished'] == 1;
                        }); ?>
                        <button
                            class="tw-bg-transparent tw-border-0 tw-text-sm tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 tw-px-0<?= $hide_completed_items == 1 ? '' : ' hide' ?>"
                            data-hide="0" onclick="toggle_completed_checklist_items_visibility(this)">
                            <?= _l('show_task_checklist_items_completed', '(<span class="task-total-checklist-completed">' . count($finished) . '</span>)'); ?>
                        </button>
                        <?php } ?>
                    </div>
                </div>
                <button type="button" data-toggle="tooltip"
                    data-title="<?= _l('add_checklist_item'); ?>"
                    class="tw-inline-flex tw-bg-transparent tw-border-0 tw-p-1.5 hover:tw-bg-neutral-100 tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 tw-rounded-md ltr:tw-ml-2 rtl:tw-mr-2"
                    onclick="add_task_checklist_item('<?= e($task->id); ?>', undefined, this); return false">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
        </div>

        <div
            class="[&_button]:!tw-pr-0 [&_.caret]:!tw-right-1.5 [&_.caret]:!tw-mr-px form-group !tw-mt-0 tw-mb-0 checklist-templates-wrapper simple-bootstrap-select task-single-checklist-templates<?= count($checklistTemplates) == 0 ? ' hide' : ''; ?>">
            <select id="checklist_items_templates" class="selectpicker checklist-items-template-select"
                data-none-selected-text="<?= _l('insert_checklist_templates') ?>"
                data-width="100%" data-live-search="true">
                <option value=""></option>
                <?php foreach ($checklistTemplates as $chkTemplate) { ?>
                <option
                    value="<?= e($chkTemplate['id']); ?>">
                    <?= e($chkTemplate['description']); ?>
                </option>
                <?php } ?>
            </select>
        </div>

        <div class="clearfix"></div>

        <p class="hide text-muted no-margin" id="task-no-checklist-items">
            <?= _l('task_no_checklist_items_found'); ?>
        </p>

        <div class="row checklist-items-wrapper">
            <div class="col-md-12 ">
                <div id="checklist-items">
                    <?php $this->load->view(
                        'admin/tasks/checklist_items_template',
                        [
                            'task_id'                 => $task->id,
                            'current_user_is_creator' => $task->current_user_is_creator,
                            'checklists'              => $task->checklist_items, ]
                    ); ?>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php if (count($task->attachments) > 0) { ?>
        <div class="row task_attachments_wrapper">
            <div class="col-md-12" id="attachments">
                <hr />
                <h4 class="th tw-font-semibold tw-text-base mbot15">
                    <?= _l('task_view_attachments'); ?>
                </h4>
                <div class="row">
                    <?php
                    $i = 1;
            // Store all url related data here
            $comments_attachments            = [];
            $attachments_data                = [];
            $show_more_link_task_attachments = hooks()->apply_filters('show_more_link_task_attachments', 6); // Increased from 2 to 6 for better display

            foreach ($task->attachments as $attachment) { ?>
                    <?php ob_start(); ?>
                    <div data-num="<?= e($i); ?>"
                        data-commentid="<?= e($attachment['comment_file_id']); ?>"
                        data-comment-attachment="<?= e($attachment['task_comment_id']); ?>"
                        data-task-attachment-id="<?= e($attachment['id']); ?>"
                        class="col-md-4<?= $i > $show_more_link_task_attachments ? ' hide task-attachment-col-more' : ''; ?> mbot20">
                        <div class="task-attachment-card<?= strtotime($attachment['dateadded']) >= strtotime('-16 hours') ? ' highlight-bg' : ''; ?>">
                            <div class="task-attachment-header">
                                <div class="task-attachment-actions">
                                    <?php if ($attachment['staffid'] == get_staff_user_id() || is_admin()) { ?>
                                    <button type="button" class="task-attachment-delete" onclick="remove_task_attachment(this,<?= e($attachment['id']); ?>); return false;">
                                        <i class="fa fa-times"></i>
                                    </button>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="task-attachment-content">
                                <?php
                                $externalPreview = false;
                $is_image                            = false;
                $path                                = get_upload_path_by_type('task') . $task->id . '/' . $attachment['file_name'];
                $href_url                            = site_url('download/file/taskattachment/' . $attachment['attachment_key']);
                $isHtml5Video                        = is_html5_video($path);
                if (empty($attachment['external'])) {
                    $is_image = is_image($path);
                    $img_url  = site_url('download/preview_image?path=' . protected_file_url_by_path($path, true) . '&type=' . $attachment['filetype']);
                } elseif ((! empty($attachment['thumbnail_link']) || ! empty($attachment['external']))
                && ! empty($attachment['thumbnail_link'])) {
                    $is_image        = true;
                    $img_url         = optimize_dropbox_thumbnail($attachment['thumbnail_link']);
                    $externalPreview = $img_url;
                    $href_url        = $attachment['external_link'];
                } elseif (! empty($attachment['external']) && empty($attachment['thumbnail_link'])) {
                    $href_url = $attachment['external_link'];
                }

                                if (! $isHtml5Video) { ?>
                                <a href="<?= ! $externalPreview ? $href_url : $externalPreview; ?>"
                                    target="_blank"
                                    <?php if ($is_image) { ?>
                                    data-lightbox="task-attachment"
                                    <?php } ?>
                                    class="task-attachment-link">
                                <?php } ?>

                                    <?php if ($is_image) { ?>
                                    <div class="task-attachment-preview">
                                        <img src="<?= e($img_url); ?>" alt="<?= e($attachment['file_name']); ?>" class="img img-responsive">
                                    </div>
                                    <?php } elseif ($isHtml5Video) { ?>
                                    <div class="task-attachment-preview">
                                        <video width="100%" height="200"
                                            src="<?= site_url('download/preview_video?path=' . protected_file_url_by_path($path) . '&type=' . $attachment['filetype']); ?>"
                                            controls>
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                    <?php } else { ?>
                                    <div class="task-attachment-file">
                                        <i class="fa fa-2x <?= get_mime_class($attachment['filetype']); ?>"></i>
                                        <div class="task-attachment-filename" title="<?= e($attachment['file_name']); ?>">
                                            <?= e(strlen($attachment['file_name']) > 30 ? substr($attachment['file_name'], 0, 27) . '...' : $attachment['file_name']); ?>
                                        </div>
                                    </div>
                                    <?php } ?>

                                <?php if (! $isHtml5Video) { ?>
                                </a>
                                <?php } ?>

                                <?php if (! empty($attachment['external']) && $attachment['external'] == 'dropbox' && $is_image) { ?>
                                <a href="<?= e($href_url); ?>"
                                    target="_blank" class="external-link-icon" data-toggle="tooltip"
                                    data-title="<?= _l('open_in_dropbox'); ?>"><i
                                        class="fa fa-dropbox" aria-hidden="true"></i></a>
                                <?php } elseif (! empty($attachment['external']) && $attachment['external'] == 'gdrive') { ?>
                                <a href="<?= e($href_url); ?>"
                                    target="_blank" class="external-link-icon" data-toggle="tooltip"
                                    data-title="<?= _l('open_in_google'); ?>"><i
                                        class="fa-brands fa-google" aria-hidden="true"></i></a>
                                <?php } ?>
                            </div>
                            <div class="task-attachment-footer">
                                <div class="task-attachment-info">
                                    <span class="task-attachment-uploader">
                                        <?php if ($attachment['staffid'] != 0) {
                                            echo '<a href="' . admin_url('profile/' . $attachment['staffid']) . '" target="_blank">' . e(get_staff_full_name($attachment['staffid'])) . '</a>';
                                        } elseif ($attachment['contact_id'] != 0) {
                                            echo '<a href="' . admin_url('clients/client/' . get_user_id_by_contact_id($attachment['contact_id']) . '?contactid=' . $attachment['contact_id']) . '" target="_blank">' . e(get_contact_full_name($attachment['contact_id'])) . '</a>';
                                        } else {
                                            echo _l('task_attachment_uploaded_by_system');
                                        } ?>
                                    </span>
                                    <span class="task-attachment-time" data-toggle="tooltip" data-title="<?= _dt($attachment['dateadded']); ?>" title="">
                                        <?= e(time_ago($attachment['dateadded'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                     $attachments_data[$attachment['id']] = ob_get_contents();
                if ($attachment['task_comment_id'] != 0) {
                    $comments_attachments[$attachment['task_comment_id']][$attachment['id']] = $attachments_data[$attachment['id']];
                }
                ob_end_clean();
                echo $attachments_data[$attachment['id']];
                ?>
                    <?php
                $i++;
            } ?>
                </div>
            </div>
            <div class="clearfix"></div>
            <?php if (($i - 1) > $show_more_link_task_attachments) { ?>
            <div class="col-md-12" id="show-more-less-task-attachments-col">
                <a href="#" class="task-attachments-more"
                    onclick="slideToggle('.task_attachments_wrapper .task-attachment-col-more', task_attachments_toggle); return false;">
                    <?= _l('show_more'); ?>
                </a>
                <a href="#" class="task-attachments-less hide"
                    onclick="slideToggle('.task_attachments_wrapper .task-attachment-col-more', task_attachments_toggle); return false;">
                    <?= _l('show_less'); ?>
                </a>
            </div>
            <?php } ?>
            <div class="col-md-12 text-center">
                <hr />
                <a href="<?= admin_url('tasks/download_files/' . $task->id); ?>"
                    class="bold">
                    <?= _l('download_all'); ?>
                    (.zip)
                </a>
            </div>
        </div>
        <?php } ?>
        <hr />
        <a href="#" id="taskCommentSlide" onclick="slideToggle('.tasks-comments'); return false;">
            <h4 class="mbot20 tw-font-semibold tw-text-base">
                <?= _l('task_comments'); ?>
            </h4>
        </a>
        <div class="tasks-comments inline-block full-width simple-editor" <?= count($task->comments) == 0 ? ' style="display:none"' : ''; ?>>
            <?= form_open(admin_url('tasks/add_task_comment'), [
                'id' => 'task-comment-form',
                'class' => 'task-comment-form',
                'style' => 'min-height:auto;background-color:#fff;',
            ]); ?>
            <?= form_hidden('taskid', $task->id); ?>
            <textarea name="content"
                placeholder="<?= _l('task_single_add_new_comment'); ?>"
                id="task_comment_basic" rows="3" class="form-control ays-ignore"></textarea>

            <div class="dropzone-task-comment-previews dropzone-previews"></div>
            <button type="button" class="btn btn-primary mtop10 pull-right" id="addTaskCommentBtn"
                autocomplete="off"
                data-loading-text="<?= _l('wait_text'); ?>"
                onclick="submitCommentForm()"
                data-comment-task-id="<?= e($task->id); ?>">
                <?= _l('task_single_add_new_comment'); ?>
            </button>
            <?= form_close(); ?>
            <div class="clearfix"></div>
            <?= count($task->comments) > 0 ? '<hr />' : ''; ?>
            <div id="task-comments" class="mtop10">
                <?php
                  $comments = '';
$len                        = count($task->comments);
$i                          = 0;

foreach ($task->comments as $comment) {
    $comments .= '<div id="comment_' . $comment['id'] . '" data-commentid="' . $comment['id'] . '" data-task-attachment-id="' . $comment['file_id'] . '" class="tc-content tw-group/comment task-comment' . (strtotime($comment['dateadded']) >= strtotime('-16 hours') ? ' highlight-bg' : '') . '">';
    $comments .= '<a data-task-comment-href-id="' . $comment['id'] . '" href="' . admin_url('tasks/view/' . $task->id) . '#comment_' . $comment['id'] . '" class="task-date-as-comment-id"><span class="tw-text-sm"><span class="text-has-action inline-block" data-toggle="tooltip" data-title="' . e(_dt($comment['dateadded'])) . '">' . e(time_ago($comment['dateadded'])) . '</span></span></a>';
    if ($comment['staffid'] != 0) {
        $comments .= '<a href="' . admin_url('profile/' . $comment['staffid']) . '" target="_blank">' . staff_profile_image($comment['staffid'], [
            'staff-profile-image-small',
            'media-object img-circle pull-left mright10',
        ]) . '</a>';
    } elseif ($comment['contact_id'] != 0) {
        $comments .= '<img src="' . e(contact_profile_image_url($comment['contact_id'])) . '" class="client-profile-image-small media-object img-circle pull-left mright10">';
    }
    if ($comment['staffid'] == get_staff_user_id() || is_admin()) {
        $comment_added = strtotime($comment['dateadded']);
        $minus_1_hour  = strtotime('-1 hours');
        if (get_option('client_staff_add_edit_delete_task_comments_first_hour') == 0 || (get_option('client_staff_add_edit_delete_task_comments_first_hour') == 1 && $comment_added >= $minus_1_hour) || is_admin()) {
            $comments .= '<span class="pull-right tw-mx-2.5 tw-opacity-0 group-hover/comment:tw-opacity-100"><a href="#" onclick="remove_task_comment(' . $comment['id'] . '); return false;" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa fa-trash-can"></i></span></a>';
            $comments .= '<span class="pull-right tw-opacity-0 group-hover/comment:tw-opacity-100"><a href="#" onclick="edit_task_comment(' . $comment['id'] . '); return false;" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa-regular fa-pen-to-square"></i></span></a>';
        }
    }

    $comments .= '<div class="media-body comment-wrapper">';
    $comments .= '<div class="mleft40">';

    if ($comment['staffid'] != 0) {
        $comments .= '<a href="' . admin_url('profile/' . $comment['staffid']) . '" target="_blank">' . e($comment['staff_full_name']) . '</a> <br />';
    } elseif ($comment['contact_id'] != 0) {
        $comments .= '<span class="label label-info mtop5 mbot5 inline-block">' . _l('is_customer_indicator') . '</span><br /><a href="' . admin_url('clients/client/' . get_user_id_by_contact_id($comment['contact_id']) . '?contactid=' . $comment['contact_id']) . '" class="pull-left" target="_blank">' . e(get_contact_full_name($comment['contact_id'])) . '</a> <br />';
    }

    $comments .= '<div data-edit-comment="' . $comment['id'] . '" class="hide edit-task-comment"><textarea rows="5" id="task_comment_' . $comment['id'] . '" class="ays-ignore form-control">' . str_replace('[task_attachment]', '', $comment['content']) . '</textarea>
                  <div class="clearfix mtop20"></div>
                  <button type="button" class="btn btn-primary pull-right" onclick="save_edited_comment(' . $comment['id'] . ',' . $task->id . ')">' . _l('submit') . '</button>
                  <button type="button" class="btn btn-default pull-right mright5" onclick="cancel_edit_comment(' . $comment['id'] . ')">' . _l('cancel') . '</button>
                  </div>';
    if ($comment['file_id'] != 0) {
        $comment['content'] = str_replace('[task_attachment]', '<div class="clearfix"></div>' . $attachments_data[$comment['file_id']], $comment['content']);
        // Replace lightbox to prevent loading the image twice
        $comment['content'] = str_replace('data-lightbox="task-attachment"', 'data-lightbox="task-attachment-comment-' . $comment['id'] . '"', $comment['content']);
    } elseif (count($comment['attachments']) > 0 && isset($comments_attachments[$comment['id']])) {
        $comment_attachments_html = '';

        foreach ($comments_attachments[$comment['id']] as $comment_attachment) {
            $comment_attachments_html .= trim($comment_attachment);
        }
        $comment['content'] = str_replace('[task_attachment]', '<div class="clearfix"></div>' . $comment_attachments_html, $comment['content']);
        // Replace lightbox to prevent loading the image twice
        $comment['content'] = str_replace('data-lightbox="task-attachment"', 'data-lightbox="task-comment-files-' . $comment['id'] . '"', $comment['content']);
        $comment['content'] .= '<div class="clearfix"></div>';
        $comment['content'] .= '<div class="text-center download-all">
                   <hr class="hr-10" />
                   <a href="' . admin_url('tasks/download_files/' . $task->id . '/' . $comment['id']) . '" class="bold">' . _l('download_all') . ' (.zip)
                   </a>
                   </div>';
    }
    $comments .= '<div class="comment-content mtop10">' . app_happy_text(check_for_links($comment['content'])) . '</div>';
    $comments .= '</div>';
    if ($i >= 0 && $i != $len - 1) {
        $comments .= '<hr class="task-info-separator" />';
    }
    $comments .= '</div>';
    $comments .= '</div>';
    $i++;
}
echo $comments;
?>
            </div>
        </div>
    </div>
    <div class="col-md-4 task-single-col-right">

            <p class="no-margin pull-left mright5">
                <a href="#" class="btn btn-default mright5" data-toggle="tooltip"
                    data-title="<?= _l('task_timesheets'); ?>"
                    onclick="task_timesheets_popup(<?= e($task->id); ?>); return false;">
                    <i class="fa fa-th-list"></i>
                </a>
            </p>
            <?php if ($task->current_user_is_creator || is_admin()) { ?>
            <p class="no-margin pull-left mright5">
                <a href="#" class="btn btn-default mright5" data-toggle="tooltip"
                    data-title="<?= _l('task_copy'); ?>"
                    onclick="copy_task(<?= e($task->id); ?>); return false;">
                    <i class="fa fa-copy"></i>
                </a>
            </p>
            <p class="no-margin pull-left mright5">
                <a href="<?= admin_url('tasks/delete_task/' . $task->id); ?>"
                    class="btn btn-danger _delete task-delete mright5" data-toggle="tooltip"
                    data-title="<?= _l('task_single_delete'); ?>">
                    <i class="fa fa-trash"></i>
                </a>
            </p>
            <?php } ?>
        <div class="clearfix"></div>


        <hr class="task-info-separator" />
        <div class="clearfix"></div>
        <?php if ($task->current_user_is_assigned) {
            foreach ($task->assignees as $assignee) {
                if ($assignee['assigneeid'] == get_staff_user_id() && get_staff_user_id() != $assignee['assigned_from'] && $assignee['assigned_from'] != 0 || $assignee['is_assigned_from_contact'] == 1) {
                    if ($assignee['is_assigned_from_contact'] == 0) {
                        echo '<p class="text-muted task-assigned-from">' . _l('task_assigned_from', '<a href="' . admin_url('profile/' . $assignee['assigned_from']) . '" target="_blank">' . e(get_staff_full_name($assignee['assigned_from']))) . '</a></p>';
                    } else {
                        echo '<p class="text-muted task-assigned-from task-assigned-from-contact">' . e(_l('task_assigned_from', get_contact_full_name($assignee['assigned_from']))) . '<br /><span class="label inline-block mtop5 label-info">' . _l('is_customer_indicator') . '</span></p>';
                    }

                    break;
                }
            }
        } ?>
        <div class="tw-flex tw-items-center tw-justify-between tw-space-x-2 rtl:tw-space-x-reverse">
            <h4 class="task-info-heading tw-font-semibold tw-text-base tw-flex tw-items-center tw-text-neutral-800">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="tw-w-5 tw-h-5 tw-text-neutral-500 tw-mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0M3.124 7.5A8.969 8.969 0 015.292 3m13.416 0a8.969 8.969 0 012.168 4.5" />
                </svg>
                <?= _l('reminders'); ?>
            </h4>

            <button type="button"
                class="tw-inline-flex tw-bg-transparent tw-border-0 tw-p-1.5 hover:tw-bg-neutral-100 tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 tw-rounded-md ltr:tw-ml-2 rtl:tw-mr-2"
                onclick="new_task_reminder(<?= e($task->id); ?>); return false;">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>

        <?php if (count($reminders) == 0) { ?>
        <p class="text-muted tw-mt-0 tw-text-sm">
            <?= _l('no_reminders_for_this_task'); ?>
        </p>
        <?php } else { ?>
        <ul class="mtop10">
            <?php foreach ($reminders as $rKey => $reminder) { ?>
            <li class="tw-group<?= $reminder['isnotified'] == '1' ? ' tw-line-through' : ''; ?>"
                data-id="<?= e($reminder['id']); ?>">
                <div class="mbot15">
                    <div class="tw-flex">
                        <p class="tw-text-neutral-500 tw-font-medium">
                            <?= e(_l('reminder_for', [
                                get_staff_full_name($reminder['staff']),
                                _dt($reminder['date']),
                            ])); ?>
                        </p>
                        <?php if ($reminder['creator'] == get_staff_user_id() || is_admin()) { ?>
                        <div class="tw-flex tw-space-x-2 rtl:tw-space-x-reverse tw-self-start">
                            <?php if ($reminder['isnotified'] == 0) { ?>
                            <a href="#" class="text-muted tw-opacity-0 group-hover:tw-opacity-100"
                                onclick="edit_reminder(<?= e($reminder['id']); ?>, this); return false;">
                                <i class="fa fa-edit"></i>
                            </a>
                            <?php } ?>
                            <a href="<?= admin_url('tasks/delete_reminder/' . $task->id . '/' . $reminder['id']); ?>"
                                class="text-muted delete-reminder tw-opacity-0 group-hover:tw-opacity-100">
                                <i class="fa-regular fa-trash-can"></i>
                            </a>
                        </div>
                        <?php } ?>

                    </div>
                    <div class="tw-truncate hover:tw-text-clip hover:tw-overflow-auto hover:tw-whitespace-normal">
                        <?php if (! empty($reminder['description'])) {
                            echo process_text_content_for_display($reminder['description']);
                        } else {
                            echo '<p class="text-muted tw-mb-0">' . _l('no_description_provided') . '</p>';
                        } ?>
                    </div>
                    <?php if (count($reminders) - 1 != $rKey) { ?>
                    <hr class="hr-10" />
                    <?php } ?>
                </div>
            </li>
            <?php
            } ?>
        </ul>
        <?php } ?>
        <div class="clearfix"></div>
        <div id="newTaskReminderToggle" class="mtop15" style="display:none;">
            <?= form_open('', ['id' => 'form-reminder-task']); ?>
            <?php $this->load->view('admin/includes/reminder_fields', ['members' => $staff_reminders, 'id' => $task->id, 'name' => 'task']); ?>
            <button class="btn btn-primary btn-sm pull-right" type="submit" id="taskReminderFormSubmit">
                <?= _l('create_reminder'); ?>
            </button>
            <div class="clearfix"></div>
            <?= form_close(); ?>
        </div>
        <hr class="task-info-separator" />
        <div class="clearfix"></div>

        <hr class="task-info-separator" />
        <?= form_open_multipart('admin/tasks/upload_file', ['id' => 'task-attachment', 'class' => 'dropzone tw-mt-5']); ?>
        <?= form_close(); ?>
<div class="tw-my-2 tw-inline-flex tw-items-end tw-w-full tw-flex-col tw-space-y-2 tw-justify-end">
            <button class="gpicker">
                <i class="fa-brands fa-google" aria-hidden="true"></i>
                <?= _l('choose_from_google_drive'); ?>
            </button>
            <div id="dropbox-chooser-task"></div>
        </div>
    </div>
</div>
</div>
<style>
.task-description-card {
    border: 1px solid #e2e8f0;
    border-radius: 26px;
    padding: 28px;
    background: #fff;
    box-shadow: 0 25px 60px rgba(15, 23, 42, .06);
    margin-bottom: 1.5rem;
}

.task-description-card--empty {
    border-style: dashed;
    background: #f8fafc;
}

.task-description-card__header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.task-description-card__intro {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.task-description-icon {
    width: 54px;
    height: 54px;
    border-radius: 18px;
    background: linear-gradient(140deg, #eef2ff, #e0f2fe);
    color: #1d4ed8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

.task-description-label {
    margin: 0;
    font-size: .8rem;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: #94a3b8;
}

.task-description-title {
    margin: .15rem 0 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #0f172a;
}

.task-description-actions {
    display: inline-flex;
    gap: .65rem;
    flex-wrap: wrap;
}

.task-utility-btn {
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #1f2937;
    padding: .45rem .95rem;
    border-radius: 999px;
    font-size: .8rem;
    font-weight: 600;
    letter-spacing: .01em;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    transition: all .2s ease;
}

.task-utility-btn--ghost {
    background: #f8fafc;
    border-color: #e2e8f0;
}

.task-utility-btn--primary {
    background: #eef2ff;
    border-color: #c7d2fe;
    color: #1d4ed8;
}

.task-utility-btn:hover,
.task-utility-btn:focus {
    transform: translateY(-1px);
    border-color: #c7d2fe;
    color: #1d4ed8;
}

.task-utility-btn.is-copied {
    border-color: #22c55e;
    color: #15803d;
    background: #ecfdf5;
}

.task-description-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .65rem;
    margin-bottom: 1.4rem;
}

.task-description-meta__item {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .4rem .9rem;
    font-size: .78rem;
    border-radius: 999px;
    background: #f5f7fb;
    color: #475569;
}

.task-description-body {
    font-size: .98rem;
    color: #0f172a;
    line-height: 1.75;
}

.task-description-content {
    margin: 0;
}

.task-description-placeholder {
    border: 2px dashed #e5e7eb;
    border-radius: 20px;
    background: #fff;
    padding: 2rem 1.5rem;
    text-align: left;
}

.task-description-placeholder__tips {
    list-style: none;
    padding-left: 0;
    margin: 1rem 0 0;
    font-size: .8rem;
    color: #6b7280;
}

.task-description-placeholder__tips li {
    display: flex;
    align-items: center;
    gap: .35rem;
    margin-bottom: .35rem;
}

.task-description-placeholder__tips i {
    color: #22c55e;
}

.task-approval-timeline-wrapper {
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    padding: 1.25rem;
    background: #fff;
    box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
}

.task-approval-timeline-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .75rem;
    flex-wrap: wrap;
}

.task-approval-timeline-header__meta {
    display: inline-flex;
    flex-wrap: wrap;
    gap: .4rem;
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #475569;
}

.task-approval-timeline-header__meta span {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    background: #f8fafc;
    padding: .25rem .65rem;
    border-radius: 999px;
    border: 1px solid #e2e8f0;
}

.task-approval-timeline-header__meta i {
    color: #475569;
}

.approval-timeline {
    margin-top: 1.2rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.approval-timeline-step {
    display: flex;
    gap: .85rem;
    position: relative;
}

.approval-timeline-marker {
    width: 2.2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.approval-timeline-marker .marker-dot {
    width: 2rem;
    height: 2rem;
    border-radius: 999px;
    background: #eef2ff;
    color: #1d4ed8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    box-shadow: inset 0 0 0 4px #fff;
    border: 1px solid #e0e7ff;
}

.approval-timeline-step.is-approved .marker-dot {
    background: #ecfdf5;
    border-color: #bbf7d0;
    color: #047857;
}

.approval-timeline-step.is-rejected .marker-dot {
    background: #fef2f2;
    border-color: #fecdd3;
    color: #b91c1c;
}

.approval-timeline-marker .marker-line {
    flex: 1;
    width: 2px;
    background: linear-gradient(180deg, #e5e7eb 0%, #e5e7eb 60%, rgba(229, 231, 235, 0) 100%);
    margin-top: .25rem;
}

.approval-timeline-step.is-last .marker-line {
    display: none;
}

.approval-timeline-card {
    flex: 1;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 0.8rem 1rem;
    background: #fff;
    box-shadow: 0 4px 12px rgba(15, 23, 42, .03);
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}

.approval-timeline-step.is-actionable .approval-timeline-card {
    border-color: #c7d2fe;
    box-shadow: 0 15px 40px rgba(99, 102, 241, .2);
}

.approval-timeline-card__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.approval-status-badge {
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 600;
    letter-spacing: .05em;
    text-transform: uppercase;
    padding: .35rem .85rem;
    border: 1px solid transparent;
}

.badge-is-approved {
    background: #ecfdf5;
    border-color: #bbf7d0;
    color: #059669;
}

.badge-is-rejected {
    background: #fef2f2;
    border-color: #fecdd3;
    color: #dc2626;
}

.badge-is-pending {
    background: #f8fafc;
    border-color: #e2e8f0;
    color: #475569;
}

.approval-timeline-card__body {
    margin-top: .75rem;
}

.approval-timeline-card__note {
    display: flex;
    gap: .75rem;
    padding: .65rem .9rem;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
}

.latest-remark-header {
    display: flex;
    align-items: center;
    gap: .4rem;
}

.remark-history-trigger {
    border: none;
    background: transparent;
    padding: 0;
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .95rem;
    transition: color .2s ease;
}

.remark-history-trigger:hover,
.remark-history-trigger:focus {
    color: #0f172a;
}

.approval-card-status-tools {
    display: flex;
    align-items: center;
    gap: .45rem;
}

.task-approval-history-trigger {
    border: none;
    background: transparent;
    color: #475569;
    padding: 0;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: color .2s ease;
}

.task-approval-history-trigger:hover,
.task-approval-history-trigger:focus {
    color: #0f172a;
}

.approval-timeline-card__note i {
    color: #6366f1;
    font-size: 1.05rem;
}

.approval-timeline-card__notice {
    border: 1px dashed #e2e8f0;
    border-radius: 12px;
    padding: .75rem .9rem;
    background: #fff;
    margin-top: .75rem;
}

.approval-timeline-card__notice-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .5rem;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: .45rem;
}

.approval-timeline-card__notice-header .label {
    font-weight: 600;
    color: #334155;
}

.approval-timeline-card__notice-header .timestamp {
    color: #64748b;
    font-size: .75rem;
    display: inline-flex;
    align-items: center;
    gap: .25rem;
}

.approval-timeline-card__notice-body {
    font-size: .85rem;
    color: #0f172a;
}

.approval-timeline-card__meta {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-bottom: .5rem;
}

.approval-timeline-card__meta span {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-size: .78rem;
    color: #475569;
    background: #f1f5f9;
    border-radius: 999px;
    padding: .2rem .65rem;
}

.approval-timeline-card__actions {
    margin-top: .9rem;
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}

.task-approval-action {
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    background: #fff;
    padding: .35rem .85rem;
    font-size: .8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    color: #1f2937;
    transition: transform .2s ease, border-color .2s ease, background .2s ease;
}

.task-approval-action--approve {
    border-color: #bbf7d0;
    background: #ecfdf5;
    color: #047857;
}

.task-approval-action--reject {
    border-color: #fecdd3;
    background: #fef2f2;
    color: #b91c1c;
}

.task-approval-action--secondary {
    background: #eef2ff;
    border-color: #c7d2fe;
    color: #4338ca;
}

.task-approval-action--ghost {
    background: #fff;
    border-style: dashed;
    color: #475569;
}

.task-approval-action--ghost:not(:disabled):hover,
.task-approval-action--ghost:not(:disabled):focus {
    background: #f8fafc;
    color: #0f172a;
}

.task-approval-action:disabled {
    opacity: .45;
    cursor: not-allowed;
}

.task-approval-action:not(:disabled):hover,
.task-approval-action:not(:disabled):focus {
    transform: translateY(-1px);
}

.step-action-panel {
    display: none;
    margin-top: .85rem;
    border: 1px dashed #c7d2fe;
    border-radius: 14px;
    background: #f8faff;
    padding: 1rem;
}

.step-action-panel.is-open {
    display: block;
}

.step-action-panel__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.step-action-panel__close {
    border: none;
    background: transparent;
    color: #94a3b8;
    font-size: 1.1rem;
}

.step-action-panel__footer {
    display: flex;
    gap: .75rem;
}

@media (max-width: 768px) {
    .approval-timeline-step {
        flex-direction: column;
    }

    .approval-timeline-marker {
        flex-direction: row;
        width: auto;
    }

    .approval-timeline-marker .marker-line {
        width: 100%;
        height: 2px;
    }
}

/* Task Attachment Card Styles */
.task-attachment-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
    position: relative;
}

.task-attachment-card:hover {
    box-shadow: 0 8px 25px rgba(15, 23, 42, 0.12);
    border-color: #cbd5f5;
}

.task-attachment-card.highlight-bg {
    border-color: #fbbf24;
    box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.2);
}

.task-attachment-header {
    position: absolute;
    top: 8px;
    right: 8px;
    z-index: 2;
}

.task-attachment-actions {
    display: flex;
    gap: 4px;
}

.task-attachment-delete {
    background: rgba(239, 68, 68, 0.9);
    color: #fff;
    border: none;
    border-radius: 6px;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.2s ease, background-color 0.2s ease;
}

.task-attachment-card:hover .task-attachment-delete {
    opacity: 1;
}

.task-attachment-delete:hover {
    background: rgba(220, 38, 38, 0.95);
}

.task-attachment-content {
    position: relative;
}

.task-attachment-link {
    display: block;
    text-decoration: none;
    color: inherit;
}

.task-attachment-preview {
    width: 100%;
    height: 180px;
    overflow: hidden;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
}

.task-attachment-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
    width: 100%;
    height: 100%;
}

.task-attachment-file {
    height: 180px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    padding: 20px;
    text-align: center;
    color: #64748b;
}

.task-attachment-file i {
    margin-bottom: 12px;
    color: #94a3b8;
}

.task-attachment-filename {
    font-size: 12px;
    color: #475569;
    line-height: 1.4;
    max-width: 100%;
    word-break: break-word;
}

.external-link-icon {
    position: absolute;
    top: 8px;
    left: 8px;
    background: rgba(255, 255, 255, 0.9);
    color: #374151;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 12px;
    z-index: 1;
}

.external-link-icon:hover {
    background: #fff;
    border-color: #d1d5db;
}

.task-attachment-footer {
    padding: 12px 16px;
    background: #fff;
    border-top: 1px solid #f1f5f9;
}

.task-attachment-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: #64748b;
}

.task-attachment-uploader {
    font-weight: 500;
    color: #334155;
}

.task-attachment-uploader a {
    color: inherit;
    text-decoration: none;
}

.task-attachment-uploader a:hover {
    color: #1d4ed8;
}

.task-attachment-time {
    color: #94a3b8;
    font-size: 11px;
}
</style>
<div class="modal fade" id="taskApproveModal" tabindex="-1" role="dialog" aria-labelledby="taskApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="taskApproveForm">
                <div class="modal-header">
                    <h4 class="modal-title" id="taskApproveModalLabel"><?= _l('task_approval_approve_modal_title'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?= _l('close'); ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted"><?= _l('task_approval_confirm_approve'); ?></p>
                    <div class="form-group">
                        <label for="approve-reason"><?= _l('task_approval_approve_reason_label'); ?></label>
                        <textarea id="approve-reason" name="approve_reason" class="form-control" rows="3" placeholder="<?= _l('task_approval_comments_optional'); ?>"></textarea>
                    </div>
                    <input type="hidden" name="approve_task_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('cancel'); ?></button>
                    <button type="submit" class="btn btn-success"><?= _l('task_approval_approve_submit'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="taskRejectApprovalModal" tabindex="-1" role="dialog" aria-labelledby="taskRejectApprovalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="taskRejectApprovalForm">
                <div class="modal-header">
                    <h4 class="modal-title" id="taskRejectApprovalModalLabel"><?= _l('task_approval_reject_modal_title'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?= _l('close'); ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted"><?= _l('task_approval_confirm_reject'); ?></p>
                    <div class="form-group">
                        <label for="reject-reason"><?= _l('task_approval_reject_reason_label'); ?></label>
                        <textarea id="reject-reason" name="reject_reason" class="form-control" rows="4" required placeholder="<?= _l('task_approval_comments_required'); ?>"></textarea>
                    </div>
                    <input type="hidden" name="reject_task_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('cancel'); ?></button>
                    <button type="submit" class="btn btn-danger"><?= _l('task_approval_reject_submit'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="taskRevertApprovalModal" tabindex="-1" role="dialog" aria-labelledby="taskRevertApprovalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="taskRevertApprovalForm">
                <div class="modal-header">
                    <h4 class="modal-title" id="taskRevertApprovalModalLabel"><?= _l('task_approval_revert_modal_title'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?= _l('close'); ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted"><?= _l('task_approval_revert_confirm'); ?></p>
                    <div class="form-group">
                        <label for="revert-reason"><?= _l('task_approval_revert_reason_label'); ?></label>
                        <textarea id="revert-reason" name="revert_reason" class="form-control" rows="4" required placeholder="<?= _l('task_approval_revert_reason_prompt'); ?>"></textarea>
                    </div>
                    <input type="hidden" name="revert_task_id">
                    <input type="hidden" name="revert_approval_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('cancel'); ?></button>
                    <button type="submit" class="btn btn-danger"><?= _l('task_approval_revert_submit'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function initTaskTemplateScriptsWhenReady() {
    if (typeof window.jQuery !== 'function'
        || typeof window.appCreateDropzoneOptions === 'undefined'
        || typeof window.Dropzone === 'undefined') {
        setTimeout(initTaskTemplateScriptsWhenReady, 50);
        return;
    }

    window.jQuery(function($) {
        window.handleTaskHtmlResponse = function(html) {
            var $fullWrapper = $('.task-single-wrapper--page');
            if ($fullWrapper.length) {
                var $temp = $('<div>').html(html);
                $temp.find('script').remove();
                $fullWrapper.html($temp.html());
                if (typeof window.initTaskTemplateScriptsWhenReady === 'function') {
                    setTimeout(window.initTaskTemplateScriptsWhenReady, 0);
                }
                $('[data-toggle="tooltip"]').tooltip();
                initTaskDescriptionCardInteractions();
            } else {
                _task_append_html(html);
                initTaskDescriptionCardInteractions();
            }
        };

        function smoothScrollToTaskTarget($target) {
            if (!$target.length) {
                return;
            }

            try {
                $target[0].scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            } catch (err) {
                var targetTop = $target.offset().top - 80;
                $('html, body').stop(true).animate({
                    scrollTop: targetTop
                }, 320);
            }
        }

        function focusTaskCommentInput() {
            var $field = $('#task_comment');
            if ($field.length && $field.is(':visible')) {
                setTimeout(function() {
                    $field.trigger('focus');
                }, 80);
            }
        }

        function openTaskCommentsAndScroll() {
            var $wrapper = $('.tasks-comments');
            var $target = $('#task-comments');

            var performScroll = function() {
                smoothScrollToTaskTarget($target);
                focusTaskCommentInput();
            };

            if (!$wrapper.length) {
                performScroll();
                return;
            }

            if ($wrapper.is(':visible')) {
                performScroll();
            } else {
                $wrapper.stop(true, true).slideDown(220, performScroll);
            }
        }

        $('body').off('click.taskCommentsAnchor').on('click.taskCommentsAnchor', 'a[href="#task-comments"]', function(e) {
            e.preventDefault();
            openTaskCommentsAndScroll();
        });

        function initTaskDescriptionCardInteractions() {
            var $cards = $('.task-description-card');

            if (!$cards.length) {
                return;
            }

            var copyFeedbackDelay = 1600;
            var resizeCallbacks = [];

            function fallbackCopyText(text, onSuccess) {
                var temp = document.createElement('textarea');
                temp.value = text;
                temp.setAttribute('readonly', '');
                temp.style.position = 'fixed';
                temp.style.top = '-9999px';
                document.body.appendChild(temp);
                temp.focus();
                temp.select();
                try {
                    document.execCommand('copy');
                    if (typeof onSuccess === 'function') {
                        onSuccess();
                    }
                } catch (err) {
                    window.console && console.warn('Copy action failed', err);
                }
                document.body.removeChild(temp);
            }

            $cards.each(function() {
                var $card = $(this);

                if ($card.data('descriptionCardInitialized')) {
                    return;
                }

                $card.data('descriptionCardInitialized', true);

                var $content = $card.find('.task-description-content');
                var $toggleBtn = $card.find('[data-description-toggle]');
                var $copyBtn = $card.find('[data-description-copy]');
                var expandLabel;
                var collapseLabel;

                if ($toggleBtn.length) {
                    expandLabel = $toggleBtn.data('expandLabel') || $.trim($toggleBtn.text()) || 'Expand';
                    collapseLabel = $toggleBtn.data('collapseLabel') || 'Collapse';
                }

                function setToggleState(isExpanded) {
                    if (!$toggleBtn.length) {
                        return;
                    }

                    expandLabel = expandLabel || 'Expand';
                    collapseLabel = collapseLabel || 'Collapse';

                    var $icon = $toggleBtn.find('i');
                    var $label = $toggleBtn.find('.task-utility-btn-label');

                    if ($label.length) {
                        $label.text(isExpanded ? collapseLabel : expandLabel);
                    }

                    if ($icon.length) {
                        $icon.toggleClass('fa-maximize', !isExpanded);
                        $icon.toggleClass('fa-minimize', isExpanded);
                    }
                }

                function evaluateOverflow() {
                    if (!$content.length) {
                        return;
                    }

                    var contentEl = $content[0];
                    var hasOverflow = Math.ceil(contentEl.scrollHeight) > Math.ceil($content.innerHeight());

                    $card.toggleClass('task-description-card--has-overflow', hasOverflow);

                    if ($toggleBtn.length) {
                        $toggleBtn.toggleClass('is-hidden', !hasOverflow);
                        setToggleState(hasOverflow && $card.hasClass('is-expanded'));
                    }

                    if (!hasOverflow) {
                        $card.removeClass('is-expanded');
                    }
                }

                evaluateOverflow();
                setTimeout(evaluateOverflow, 250);
                resizeCallbacks.push(evaluateOverflow);

                if ($toggleBtn.length) {
                    setToggleState($card.hasClass('is-expanded'));

                    $toggleBtn.on('click', function() {
                        if (!$card.hasClass('task-description-card--has-overflow')) {
                            return;
                        }

                        var isExpanded = $card.toggleClass('is-expanded').hasClass('is-expanded');
                        setToggleState(isExpanded);
                    });
                }

                if ($copyBtn.length) {
                    $copyBtn.on('click', function() {
                        var $btn = $(this);
                        var storedText = $btn.data('copyText');
                        var copyText = storedText && storedText.length
                            ? storedText
                            : ($content.length ? $.trim($content.text()) : '');

                        if (!copyText) {
                            return;
                        }

                        function showCopiedState() {
                            var $label = $btn.find('.task-utility-btn-label');

                            if ($label.length && !$btn.data('originalLabel')) {
                                $btn.data('originalLabel', $label.text());
                            }

                            if ($label.length) {
                                $label.text('Copied');
                            }

                            $btn.addClass('is-copied');

                            var existingTimeout = $btn.data('copyTimeout');
                            if (existingTimeout) {
                                clearTimeout(existingTimeout);
                            }

                            var timeoutId = setTimeout(function() {
                                $btn.removeClass('is-copied');
                                if ($label.length) {
                                    $label.text($btn.data('originalLabel') || 'Copy');
                                }
                            }, copyFeedbackDelay);

                            $btn.data('copyTimeout', timeoutId);
                        }

                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(copyText).then(showCopiedState).catch(function() {
                                fallbackCopyText(copyText, showCopiedState);
                            });
                        } else {
                            fallbackCopyText(copyText, showCopiedState);
                        }
                    });
                }
            });

            if (resizeCallbacks.length) {
                var resizeTimeout;
                $(window).off('resize.taskDescriptionCard').on('resize.taskDescriptionCard', function() {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(function() {
                        resizeCallbacks.forEach(function(cb) {
                            if (typeof cb === 'function') {
                                cb();
                            }
                        });
                    }, 180);
                });
            }
        }

        function refreshTaskAssigneeSummary(response) {
            if (!response) {
                return;
            }

            var $assigneeCard = $('.task-summary-card .fa-user-check').closest('.task-summary-card');

            if (!$assigneeCard.length) {
                return;
            }

            if (typeof response.assigneeCount !== 'undefined') {
                var count = parseInt(response.assigneeCount, 10);
                var $summaryTitle = $assigneeCard.find('.task-summary-title');

                if ($summaryTitle.length) {
                    if (count > 0) {
                        $summaryTitle.text(count + ' Assigned');
                    } else {
                        $summaryTitle.text('Not assigned');
                    }
                }
            }

            if (typeof response.assigneeSummaryHtml !== 'undefined') {
                var $summaryWrapper = $assigneeCard.find('.js-task-summary-assignees');
                if ($summaryWrapper.length) {
                    $summaryWrapper.html(response.assigneeSummaryHtml);
                    $summaryWrapper.find('[data-toggle="tooltip"]').tooltip();
                    initAssigneeOverflowPopovers($summaryWrapper);
                }
            }
        }

        function initAssigneeOverflowPopovers(context) {
            var $scope = context || $('body');
            var $triggers = $scope.find('.js-task-assignee-overflow');

            if (!$triggers.length) {
                return;
            }

            $triggers.each(function() {
                var $trigger = $(this);

                if ($trigger.data('assigneeOverflowInitialized')) {
                    return;
                }

                $trigger.data('assigneeOverflowInitialized', true);

                $trigger.popover({
                    html: true,
                    trigger: 'manual',
                    container: 'body',
                    placement: $trigger.data('placement') || 'top'
                });

                $trigger.on('mouseenter.assigneeOverflow focus.assigneeOverflow', function() {
                    showAssigneeOverflowPopover($trigger);
                }).on('mouseleave.assigneeOverflow blur.assigneeOverflow', function() {
                    scheduleHideAssigneeOverflowPopover($trigger);
                });
            });
        }

        function showAssigneeOverflowPopover($trigger) {
            if (!$trigger || !$trigger.length) {
                return;
            }

            $trigger.popover('show');

            var popover = $trigger.data('bs.popover');
            if (!popover) {
                return;
            }

            var $tip = typeof popover.tip === 'function' ? $(popover.tip()) : popover.$tip;
            if (!$tip || !$tip.length) {
                return;
            }

            $tip.addClass('task-assignee-overflow-popover');
            $tip.off('mouseenter.assigneeOverflow').on('mouseenter.assigneeOverflow', function() {
                $tip.data('hover', true);
            }).off('mouseleave.assigneeOverflow').on('mouseleave.assigneeOverflow', function() {
                $tip.data('hover', false);
                scheduleHideAssigneeOverflowPopover($trigger);
            });
        }

        function scheduleHideAssigneeOverflowPopover($trigger) {
            if (!$trigger || !$trigger.length) {
                return;
            }

            setTimeout(function() {
                var popover = $trigger.data('bs.popover');
                if (!popover) {
                    return;
                }

                var $tip = typeof popover.tip === 'function' ? $(popover.tip()) : popover.$tip;
                if ($tip && $tip.length && ($tip.is(':hover') || $tip.data('hover'))) {
                    return;
                }

                $trigger.popover('hide');
            }, 120);
        }

        window.remove_assignee = function(id, task_id) {
            if (confirm_delete()) {
                requestGetJSON("tasks/remove_assignee/" + id + "/" + task_id).done(function(response) {
                    if (response.success === true || response.success == "true") {
                        alert_float("success", response.message);
                        handleTaskHtmlResponse(response.taskHtml);
                        refreshTaskAssigneeSummary(response);
                    }
                });
            }
        };

        window.remove_follower = function(id, task_id) {
            if (confirm_delete()) {
                requestGetJSON("tasks/remove_follower/" + id + "/" + task_id).done(function(response) {
                    if (response.success === true || response.success == "true") {
                        alert_float("success", response.message);
                        handleTaskHtmlResponse(response.taskHtml);
                        refreshTaskFollowerSummary(response);
                    }
                });
            }
        };
    if (typeof window.commonTaskPopoverMenuOptions == 'undefined') {
        window.commonTaskPopoverMenuOptions = {
            html: true,
            placement: 'bottom',
            trigger: 'click',
            template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"></div></div></div>',
        };
    }

    // Clear memory leak
    if (typeof window.taskPopoverMenus == 'undefined') {
        window.taskPopoverMenus = [{
                selector: '.task-menu-options',
                title: "<?= _l('actions'); ?>",
            },
            {
                selector: '.task-menu-status',
                title: "<?= _l('ticket_single_change_status'); ?>",
            },
            {
                selector: '.task-menu-priority',
                title: "<?= _l('task_single_priority'); ?>",
            },
            {
                selector: '.task-menu-milestones',
                title: "<?= _l('task_milestone'); ?>",
            },
        ];
    }

    for (var i = 0; i < taskPopoverMenus.length; i++) {
        $(taskPopoverMenus[i].selector + ' .trigger').popover($.extend({}, commonTaskPopoverMenuOptions, {
            title: taskPopoverMenus[i].title,
            content: $('body').find(taskPopoverMenus[i].selector + ' .content-menu').html()
        }));
    }

    if (typeof(Dropbox) != 'undefined') {
        document.getElementById("dropbox-chooser-task").appendChild(Dropbox.createChooseButton({
            success: function(files) {
                taskExternalFileUpload(files,
                    'dropbox', <?= e($task->id); ?> );
            },
            linkType: "preview",
            extensions: app.options.allowed_files.split(','),
        }));
    }

    if (typeof init_selectpicker === 'function') {
        init_selectpicker();
    }
    if (typeof init_datepicker === 'function') {
        init_datepicker();
    }
    if (typeof init_lightbox === 'function') {
        init_lightbox();
    }

    if (typeof window.tinyMCE !== 'undefined') {
        tinyMCE.remove('#task_view_description');
    }

    if (typeof(taskAttachmentDropzone) != 'undefined') {
        taskAttachmentDropzone.destroy();
        taskAttachmentDropzone = null;
    }

        var attachmentElement = document.getElementById('task-attachment');
        if (attachmentElement && attachmentElement.dropzone) {
            attachmentElement.dropzone.destroy();
        }

        if (typeof taskAttachmentDropzone != 'undefined' && taskAttachmentDropzone !== null) {
            taskAttachmentDropzone.destroy();
            taskAttachmentDropzone = null;
        }

        if (typeof Dropzone !== 'undefined') {
            Dropzone.autoDiscover = false;
        }

        taskAttachmentDropzone = new Dropzone("#task-attachment", $.extend({}, appCreateDropzoneOptions({
            uploadMultiple: true,
            parallelUploads: 20,
            maxFiles: 20,
            dictDefaultMessage: "",
        paramName: 'file',
        sending: function(file, xhr, formData) {
            formData.append("taskid",
                '<?= e($task->id); ?>');
        },
        success: function(files, response) {
            response = JSON.parse(response);
            if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                handleTaskHtmlResponse(response.taskHtml);
            }
        }
    })));

    $('#task-modal').find('.gpicker').googleDrivePicker({
        onPick: function(pickData) {
            taskExternalFileUpload(pickData,
                'gdrive', <?= e($task->id); ?> );
        }
    });

    $('.edit-timesheet-cancel').click(function() {
        $('.timesheet-edit').addClass('hide');
        $('.add-timesheet').removeClass('hide');
    });

    $('.task-single-edit-timesheet').click(function() {
        var edit_timesheet_id = $(this).data('timesheet-id');
        $('.timesheet-edit, .add-timesheet').addClass('hide');
        $('.task-modal-edit-timesheet-' + edit_timesheet_id).removeClass('hide');
    });

    $('.task-modal-edit-timesheet-form').submit(event => {
        event.preventDefault();
        $('.edit-timesheet-submit').prop('disabled', true);

        var form = new FormData(event.target);
        var data = {};

        data.timer_id = form.get('timer_id');
        data.start_time = form.get('start_time');
        data.end_time = form.get('end_time');
        data.timesheet_staff_id = form.get('staff_id');
        data.timesheet_task_id = form.get('task_id');
        data.note = form.get('note');

        $.post(admin_url + 'tasks/update_timesheet', data).done(function(response) {
            response = JSON.parse(response);
            if (response.success === true || response.success == 'true') {
                init_task_modal(data.timesheet_task_id);
                alert_float('success', response.message);
            } else {
                alert_float('warning', response.message);
            }
            $('.edit-timesheet-submit').prop('disabled', false);
        });
    });

    // Task approval functions
    window.approve_task_step = function(task_id, comments) {
        return $.post(admin_url + 'tasks/approve_task_step/' + task_id, {
            comments: comments
        }).done(function(response) {
            response = JSON.parse(response);
            if (response.success) {
                alert_float(response.alert_type, response.message);
                if (typeof $approveModal !== 'undefined' && $approveModal.length) {
                    $approveModal.modal('hide');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                }
                handleTaskHtmlResponse(response.taskHtml);
            } else {
                alert_float(response.alert_type, response.message);
            }
        }).fail(function() {
            alert_float('danger', '<?= _l('task_approval_failed'); ?>');
        });
    };

    window.reject_task_step = function(task_id, comments) {
        return $.post(admin_url + 'tasks/reject_task_step/' + task_id, {
            comments: comments
        }).done(function(response) {
            response = JSON.parse(response);
            if (response.success) {
                alert_float(response.alert_type, response.message);
                if (typeof $rejectModal !== 'undefined' && $rejectModal.length) {
                    $rejectModal.modal('hide');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                }
                handleTaskHtmlResponse(response.taskHtml);
            } else {
                alert_float(response.alert_type, response.message);
            }
        }).fail(function() {
            alert_float('danger', '<?= _l('task_approval_failed'); ?>');
        });
    };

    var $approveModal = $('#taskApproveModal');
    var $approveForm = $('#taskApproveForm');
    var $rejectModal = $('#taskRejectApprovalModal');
    var $rejectForm = $('#taskRejectApprovalForm');
    var $revertModal = $('#taskRevertApprovalModal');
    var $revertForm = $('#taskRevertApprovalForm');

    if ($approveModal.length) {
        $approveModal.on('hidden.bs.modal', function() {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        });
    }

    if ($rejectModal.length) {
        $rejectModal.on('hidden.bs.modal', function() {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        });
    }

    window.openApproveTaskModal = function(taskId) {
        if (!$approveModal.length) {
            return;
        }

        $approveModal.find('input[name="approve_task_id"]').val(taskId);
        $approveModal.find('textarea[name="approve_reason"]').val('');
        setTimeout(function() {
            $approveModal.modal('show');
        }, 10);
    };

    window.openRejectTaskModal = function(taskId) {
        if (!$rejectModal.length) {
            return;
        }

        $rejectModal.find('input[name="reject_task_id"]').val(taskId);
        $rejectModal.find('textarea[name="reject_reason"]').val('');
        setTimeout(function() {
            $rejectModal.modal('show');
        }, 10);
    };

    window.openRevertApprovalModal = function(taskId, approvalId) {
        if (!$revertModal.length) {
            return;
        }

        $revertModal.find('input[name="revert_task_id"]').val(taskId);
        $revertModal.find('input[name="revert_approval_id"]').val(approvalId);
        $revertModal.find('textarea[name="revert_reason"]').val('');
        setTimeout(function() {
            $revertModal.modal('show');
        }, 10);
    };

    if ($revertModal.length) {
        $revertModal.on('hidden.bs.modal', function() {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        });
    }

    window.revertTaskStep = function(taskId, approvalId, reason) {
        return $.post(admin_url + 'tasks/revert_task_step', {
            task_id: taskId,
            approval_id: approvalId,
            reason: reason
        }).done(function(response) {
            response = JSON.parse(response);
            if (response.success) {
                alert_float(response.alert_type, response.message);
                $revertModal.modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                handleTaskHtmlResponse(response.taskHtml);
            } else {
                alert_float(response.alert_type || 'warning', response.message);
            }
        }).fail(function() {
            alert_float('danger', '<?= _l('task_approval_revert_failed'); ?>');
        });
    };

    if ($approveForm.length) {
        $approveForm.on('submit', function(e) {
            e.preventDefault();

            var taskId = $approveModal.find('input[name="approve_task_id"]').val();
            var reason = $approveModal.find('textarea[name="approve_reason"]').val().trim();

            var $submitBtn = $approveForm.find('button[type="submit"]');
            $submitBtn.prop('disabled', true);

            window.approve_task_step(taskId, reason).always(function() {
                $submitBtn.prop('disabled', false);
            });
        });
    }

    if ($rejectForm.length) {
        $rejectForm.on('submit', function(e) {
            e.preventDefault();

            var taskId = $rejectModal.find('input[name="reject_task_id"]').val();
            var reason = $rejectModal.find('textarea[name="reject_reason"]').val().trim();

            if (reason === '') {
                alert('<?= _l('task_approval_comments_required'); ?>');
                return;
            }

            var $submitBtn = $rejectForm.find('button[type="submit"]');
            $submitBtn.prop('disabled', true);

            window.reject_task_step(taskId, reason).always(function() {
                $submitBtn.prop('disabled', false);
            });
        });
    }

    if ($revertForm.length) {
        $revertForm.on('submit', function(e) {
            e.preventDefault();

            var taskId = $revertModal.find('input[name="revert_task_id"]').val();
            var approvalId = $revertModal.find('input[name="revert_approval_id"]').val();
            var reason = $revertModal.find('textarea[name="revert_reason"]').val().trim();

            if (reason === '') {
                alert('<?= _l('task_approval_revert_reason_required'); ?>');
                return;
            }

            var $submitBtn = $revertForm.find('button[type="submit"]');
            $submitBtn.prop('disabled', true);

            window.revertTaskStep(taskId, approvalId, reason).always(function() {
                $submitBtn.prop('disabled', false);
            });
        });
    }

    window.openRemarkHistoryModal = function(taskId, approvalId) {
        if (!taskId || !approvalId) {
            return;
        }

        $.ajax({
            url: admin_url + 'tasks/get_remark_history_modal',
            type: 'POST',
            dataType: 'json',
            data: {
                task_id: taskId,
                approval_id: approvalId,
            },
            success: function(response) {
                if (response.success && response.html) {
                    $('#taskRemarkHistoryModal').remove();
                    $('body').append(response.html);
                    $('#taskRemarkHistoryModal').modal('show');
                } else if (response.message) {
                    alert_float('warning', response.message);
                }
            },
            error: function() {
                alert_float('danger', '<?= _l('task_approval_remark_history_modal_error'); ?>');
            },
        });
    };

    // Step by step approval functions
    window.toggleStepRemarkPanel = function(step_id, forceClose) {
        var $panel = $('#step-panel-' + step_id);

        if (!$panel.length) {
            return;
        }

        var shouldClose = !!forceClose || $panel.hasClass('is-open');

        if (shouldClose) {
            $panel.removeClass('is-open').stop(true, true).slideUp(180);
            return;
        }

        $('.step-action-panel.is-open').removeClass('is-open').stop(true, true).slideUp(180);

        $panel.addClass('is-open').stop(true, true).slideDown(220, function() {
            var $textarea = $panel.find('textarea').first();
            if ($textarea.length) {
                $textarea.trigger('focus');
            }
        });
    };

    window.submitStepApproval = function(step_id, action) {
        var $panel = $('#step-panel-' + step_id);
        var form = $panel.find('.step-approval-form')[0];

        if (!form) {
            return;
        }

        var formData = new FormData(form);

        if (action) {
            formData.append('action_type', action);
        }

        // Add CSRF token if available
        if (typeof csrfData !== 'undefined') {
            formData.append(csrfData.token_name, csrfData.hash);
        }

        $.ajax({
            url: admin_url + 'tasks/submit_step_approval',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    toggleStepRemarkPanel(step_id, true);
                    alert_float('success', response.message);
                    handleTaskHtmlResponse(response.taskHtml);
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function() {
                alert_float('danger', 'An error occurred while submitting the approval.');
            }
        });
    };

    function initTaskAssigneePickerModal() {
        var $body = $('body');

        $body.off('click', '.task-assignee-picker-trigger').on('click', '.task-assignee-picker-trigger', function(e) {
            e.preventDefault();
            var $modal = $('#taskAssigneePickerModal');
            if ($modal.length) {
                $modal.modal('show');
            }
        });

        function filterAssigneePickerList() {
            var term = ($('#assigneePickerSearch').val() || '').toLowerCase();
            var divisionValue = ($('#assigneeDivisionFilter').val() || '').toLowerCase();
            $('#assigneePickerList .assignee-picker-item').each(function() {
                var name = ($(this).data('staff-name') || '').toString();
                var division = ($(this).data('staff-division') || '').toString();
                var empCode = ($(this).data('staff-emp-code') || '').toString();
                var matchesName = !term || name.indexOf(term) !== -1 || empCode.indexOf(term) !== -1;
                var matchesDivision = !divisionValue || division === divisionValue;
                $(this).toggle(matchesName && matchesDivision);
            });
        }

        $('#taskAssigneePickerModal').off('shown.bs.modal').on('shown.bs.modal', function() {
            var $search = $('#assigneePickerSearch');
            $search.val('');
            var $divisionFilter = $('#assigneeDivisionFilter');
            if ($divisionFilter.length) {
                $divisionFilter.selectpicker('val', '');
                $divisionFilter.selectpicker('refresh');
            }
            filterAssigneePickerList();
            setTimeout(function() {
                $search.focus();
            }, 200);
        });

        $body.off('keyup', '#assigneePickerSearch').on('keyup', '#assigneePickerSearch', filterAssigneePickerList);
        $body.off('change', '#assigneeDivisionFilter').on('change', '#assigneeDivisionFilter', filterAssigneePickerList);
        $body.off('changed.bs.select', '#assigneeDivisionFilter').on('changed.bs.select', '#assigneeDivisionFilter', filterAssigneePickerList);

        $body.off('click', '.add-assignee-picker').on('click', '.add-assignee-picker', function(e) {
            e.preventDefault();
            var staffId = $(this).data('staff-id');
            var taskId = $('#taskAssigneePickerModal').data('task-id');
            addTaskAssigneeFromPicker(staffId, taskId);
        });

        $body.off('click', '.remove-assignee-picker').on('click', '.remove-assignee-picker', function(e) {
            e.preventDefault();
            var taskAssignedId = $(this).data('task-assigned-id');
            var taskId = $('#taskAssigneePickerModal').data('task-id');
            removeTaskAssigneeFromPicker(taskAssignedId, taskId);
        });
    }

    function addTaskAssigneeFromPicker(staffId, taskId) {
        if (!staffId || !taskId) {
            return;
        }
        $('body').append('<div class="dt-loader"></div>');
        $.post(admin_url + 'tasks/add_task_assignees', {
            assignee: staffId,
            taskid: taskId
        }).done(function(response) {
            $('body').find('.dt-loader').remove();
            response = JSON.parse(response);
            handleTaskHtmlResponse(response.taskHtml);
            refreshTaskAssigneeSummary(response);

            // Update the task view assignee avatars by parsing the response HTML
            if (response.taskHtml) {
                var $tempHtml = $('<div>').html(response.taskHtml);
                var $newAssigneeWrapper = $tempHtml.find('.task_users_wrapper').first();
                if ($newAssigneeWrapper.length) {
                    $('.task_users_wrapper').first().html($newAssigneeWrapper.html());
                    // Reinitialize tooltips for the new content
                    $('[data-toggle="tooltip"]').tooltip();
                }
            }

            // Reload the task tables/grid to show updated assignee avatars
            reload_tasks_tables();

            $('#taskAssigneePickerModal').modal('hide');
            // Ensure backdrop is hidden
            setTimeout(function() {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
            }, 300);
        });
    }

    function removeTaskAssigneeFromPicker(taskAssignedId, taskId) {
        if (!taskAssignedId || !taskId) {
            return;
        }
        $('body').append('<div class="dt-loader"></div>');
        $.post(admin_url + 'tasks/remove_assignee/' + taskAssignedId + '/' + taskId).done(function(response) {
            $('body').find('.dt-loader').remove();
            response = JSON.parse(response);
            reload_tasks_tables();
            handleTaskHtmlResponse(response.taskHtml);
            refreshTaskAssigneeSummary(response);

            $('#taskAssigneePickerModal').modal('hide');
        });
    }

    initTaskAssigneePickerModal();
    initAssigneeOverflowPopovers();

    function initFollowerOverflowPopovers(context) {
        var $scope = context || $('body');
        var $triggers = $scope.find('.js-task-follower-overflow');

        if (!$triggers.length) {
            return;
        }

        $triggers.each(function() {
            var $trigger = $(this);

            if ($trigger.data('followerOverflowInitialized')) {
                return;
            }

            $trigger.data('followerOverflowInitialized', true);

            $trigger.popover({
                html: true,
                trigger: 'manual',
                container: 'body',
                placement: $trigger.data('placement') || 'top'
            });

            $trigger.on('mouseenter.followerOverflow focus.followerOverflow', function() {
                showFollowerOverflowPopover($trigger);
            }).on('mouseleave.followerOverflow blur.followerOverflow', function() {
                scheduleHideFollowerOverflowPopover($trigger);
            });
        });
    }

    function showFollowerOverflowPopover($trigger) {
        if (!$trigger || !$trigger.length) {
            return;
        }

        $trigger.popover('show');

        var popover = $trigger.data('bs.popover');
        if (!popover) {
            return;
        }

        var $tip = typeof popover.tip === 'function' ? $(popover.tip()) : popover.$tip;
        if (!$tip || !$tip.length) {
            return;
        }

        $tip.addClass('task-assignee-overflow-popover');
        $tip.off('mouseenter.followerOverflow').on('mouseenter.followerOverflow', function() {
            $tip.data('hover', true);
        }).off('mouseleave.followerOverflow').on('mouseleave.followerOverflow', function() {
            $tip.data('hover', false);
            scheduleHideFollowerOverflowPopover($trigger);
        });
    }

    function scheduleHideFollowerOverflowPopover($trigger) {
        if (!$trigger || !$trigger.length) {
            return;
        }

        setTimeout(function() {
            var popover = $trigger.data('bs.popover');
            if (!popover) {
                return;
            }

            var $tip = typeof popover.tip === 'function' ? $(popover.tip()) : popover.$tip;
            if ($tip && $tip.length && ($tip.is(':hover') || $tip.data('hover'))) {
                return;
            }

            $trigger.popover('hide');
        }, 120);
    }

    function initTaskFollowerPickerModal() {
        var $body = $('body');

        $body.off('click', '.task-follower-picker-trigger').on('click', '.task-follower-picker-trigger', function(e) {
            e.preventDefault();
            var $modal = $('#taskFollowerPickerModal');
            if ($modal.length) {
                $modal.modal('show');
            }
        });

        function filterFollowerPickerList() {
            var term = ($('#followerPickerSearch').val() || '').toLowerCase();
            var divisionValue = ($('#followerDivisionFilter').val() || '').toLowerCase();
            $('#followerPickerList .follower-picker-item').each(function() {
                var name = ($(this).data('staff-name') || '').toString();
                var division = ($(this).data('staff-division') || '').toString();
                var empCode = ($(this).data('staff-emp-code') || '').toString();
                var matchesName = !term || name.indexOf(term) !== -1 || empCode.indexOf(term) !== -1;
                var matchesDivision = !divisionValue || division === divisionValue;
                $(this).toggle(matchesName && matchesDivision);
            });
        }

        $('#taskFollowerPickerModal').off('shown.bs.modal').on('shown.bs.modal', function() {
            var $search = $('#followerPickerSearch');
            $search.val('');
            var $divisionFilter = $('#followerDivisionFilter');
            if ($divisionFilter.length) {
                $divisionFilter.selectpicker('val', '');
                $divisionFilter.selectpicker('refresh');
            }
            filterFollowerPickerList();
            setTimeout(function() {
                $search.focus();
            }, 200);
        });

        $body.off('keyup', '#followerPickerSearch').on('keyup', '#followerPickerSearch', filterFollowerPickerList);
        $body.off('change', '#followerDivisionFilter').on('change', '#followerDivisionFilter', filterFollowerPickerList);
        $body.off('changed.bs.select', '#followerDivisionFilter').on('changed.bs.select', '#followerDivisionFilter', filterFollowerPickerList);

        $body.off('click', '.add-follower-picker').on('click', '.add-follower-picker', function(e) {
            e.preventDefault();
            var staffId = $(this).data('staff-id');
            var taskId = $('#taskFollowerPickerModal').data('task-id');
            addTaskFollowerFromPicker(staffId, taskId);
        });

        $body.off('click', '.remove-follower-picker').on('click', '.remove-follower-picker', function(e) {
            e.preventDefault();
            var taskFollowerId = $(this).data('task-follower-id');
            var taskId = $('#taskFollowerPickerModal').data('task-id');
            removeTaskFollowerFromPicker(taskFollowerId, taskId);
        });
    }

    function addTaskFollowerFromPicker(staffId, taskId) {
        if (!staffId || !taskId) {
            return;
        }
        $('body').append('<div class="dt-loader"></div>');
        $.post(admin_url + 'tasks/add_task_followers', {
            follower: staffId,
            taskid: taskId
        }).done(function(response) {
            $('body').find('.dt-loader').remove();
            response = JSON.parse(response);
            handleTaskHtmlResponse(response.taskHtml);
            refreshTaskFollowerSummary(response);

            $('#taskFollowerPickerModal').modal('hide');
            // Ensure backdrop is hidden
            setTimeout(function() {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
            }, 300);
        });
    }

    function removeTaskFollowerFromPicker(taskFollowerId, taskId) {
        if (!taskFollowerId || !taskId) {
            return;
        }
        $('body').append('<div class="dt-loader"></div>');
        $.post(admin_url + 'tasks/remove_follower/' + taskFollowerId + '/' + taskId).done(function(response) {
            $('body').find('.dt-loader').remove();
            response = JSON.parse(response);
            handleTaskHtmlResponse(response.taskHtml);
            refreshTaskFollowerSummary(response);

            $('#taskFollowerPickerModal').modal('hide');
            // Ensure backdrop is hidden
            setTimeout(function() {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
            }, 300);
        });
    }

    function refreshTaskFollowerSummary(response) {
        if (!response) {
            return;
        }

        var $followerCard = $('.task-summary-card .fa-user-group').closest('.task-summary-card');

        if (!$followerCard.length) {
            return;
        }

        if (typeof response.followerSummaryHtml !== 'undefined') {
            var $summaryWrapper = $followerCard.find('.js-task-summary-followers');
            if ($summaryWrapper.length) {
                $summaryWrapper.html(response.followerSummaryHtml);
                $summaryWrapper.find('[data-toggle="tooltip"]').tooltip();
                initFollowerOverflowPopovers($summaryWrapper);

                // Update the follower count in the title
                var followerCount = $summaryWrapper.find('.task-summary-avatar-wrapper').length;
                var $titleElement = $followerCard.find('.task-summary-title');
                if ($titleElement.length) {
                    var titleText = followerCount > 0 ? followerCount + ' ' + '<?= _l('task_single_followers'); ?>' : '<?= _l('task_no_followers'); ?>';
                    $titleElement.text(titleText);
                }
            }
        }
    }

    initTaskFollowerPickerModal();
    initFollowerOverflowPopovers();
    initTaskDescriptionCardInteractions();

    // Handle follower select dropdown
    $('body').on('change', 'select[name="select-followers"]', function() {
        var followerId = $(this).val();
        var taskId = $(this).data('task-id');

        if (followerId && taskId) {
            $('body').append('<div class="dt-loader"></div>');
            $.post(admin_url + 'tasks/add_task_followers', {
                follower: followerId,
                taskid: taskId
            }).done(function(response) {
                $('body').find('.dt-loader').remove();
                response = JSON.parse(response);
                handleTaskHtmlResponse(response.taskHtml);
                refreshTaskFollowerSummary(response);
            });
        }

        // Reset the select
        $(this).selectpicker('val', '');
    });

    // Timesheets popup function
    window.task_timesheets_popup = function(task_id) {
        $.get(admin_url + 'tasks/task_timesheets_popup/' + task_id).done(function(response) {
            $('body').append(response);
        });
    };

    // Copy task function
    window.copy_task = function(task_id) {
        window.location.href = admin_url + 'tasks/task?copy_from=' + task_id;
    };

    // Custom submit comment form function
    window.submitCommentForm = function() {
        var $btn = $('#addTaskCommentBtn');
        var originalText = $btn.html();
        $btn.html('<i class="fa fa-spin fa-spinner"></i>&nbsp;' + $btn.data('loading-text')).prop('disabled', true);

        var $form = $('#task-comment-form');
        var formData = new FormData($form[0]);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    handleTaskHtmlResponse(response.taskHtml);
                    $('#task_comment_basic').val(''); // Clear the textarea
                }
                alert_float(response.alert_type || 'info', response.message);
            },
            error: function() {
                alert_float('danger', 'An error occurred while submitting the comment.');
            },
            complete: function() {
                $btn.html(originalText).prop('disabled', false);
            }
        });
    };
    });
}

initTaskTemplateScriptsWhenReady();
</script>
