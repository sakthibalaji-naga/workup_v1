<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="task-timesheets-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close close-task-timesheets" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <?= _l('task_timesheets'); ?> - <?= e($task->name); ?>
                </h4>
            </div>
            <div class="modal-body">
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
                                <?php if (!$task->billed) { ?>
                                <th class="tw-text-sm tw-bg-neutral-50">
                                    <?= _l('actions'); ?>
                                </th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $timers_found = false;
                            foreach ($task->timesheets as $timesheet) {
                                if (staff_can('edit', 'tasks') || staff_can('create', 'tasks') || staff_can('delete', 'tasks') || $timesheet['staff_id'] == get_staff_user_id()) {
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
                                        if (!$task->billed && is_admin()) { ?>
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
                                    </div>
                                </td>
                                <?php if (!$task->billed) { ?>
                                <td class="tw-text-sm">
                                    <div class="tw-flex tw-space-x-1">
                                        <?php
                                        if (staff_can('delete_timesheet', 'tasks') || (staff_can('delete_own_timesheet', 'tasks') && $timesheet['staff_id'] == get_staff_user_id())) {
                                            echo '<a href="' . admin_url('tasks/delete_timesheet/' . $timesheet['id']) . '" class="btn btn-sm btn-danger task-single-delete-timesheet" data-task-id="' . $task->id . '"><i class="fa fa-remove"></i></a>';
                                        }
                                        if (staff_can('edit_timesheet', 'tasks') || (staff_can('edit_own_timesheet', 'tasks') && $timesheet['staff_id'] == get_staff_user_id())) {
                                            echo '<a href="#" class="btn btn-sm btn-info task-single-edit-timesheet" data-toggle="tooltip" data-title="' . _l('edit') . '" data-timesheet-id="' . $timesheet['id'] . '">
                                                <i class="fa fa-edit"></i>
                                            </a>';
                                        }
                                        ?>
                                    </div>
                                </td>
                                <?php } ?>
                            </tr>
                            <tr>
                                <td class="timesheet-edit task-modal-edit-timesheet-<?= $timesheet['id'] ?> hide"
                                    colspan="<?= $task->billed ? '4' : '5' ?>">
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
                                <td colspan="<?= $task->billed ? '4' : '5' ?>" class="text-center bold">
                                    <?= _l('no_timers_found'); ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php if ($task->billed == 0 && ($task->current_user_is_assigned || (count($task->assignees) > 0 && is_admin())) && $task->status != Tasks_model::STATUS_COMPLETE) {
                                ?>
                            <tr class="odd">
                                <td colspan="<?= $task->billed ? '4' : '5' ?>" class="add-timesheet">
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
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<script>
$(document).ready(function() {
    $('#task-timesheets-modal').modal('show');

    $('.close-task-timesheets').click(function() {
        $('#task-timesheets-modal').modal('hide');
    });

    // Initialize datepickers and other components
    if (typeof init_datepicker === 'function') {
        init_datepicker();
    }
    if (typeof init_selectpicker === 'function') {
        init_selectpicker();
    }
});
</script>
