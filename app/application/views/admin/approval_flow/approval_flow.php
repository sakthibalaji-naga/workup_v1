<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$flowId          = isset($id) ? $id : '';
$staffDirectory  = isset($staffDirectory) && is_array($staffDirectory) ? $staffDirectory : [];
$modalStandalone = isset($approval_flow_modal_standalone) ? (bool) $approval_flow_modal_standalone : false;

if ($modalStandalone) {
    init_head();
    ?>
<div id="wrapper">
    <div class="content">
        <?php
}
?>
    <style>
    #approval_flow_modal .modal-dialog {
        margin-top: 20px;
    }

    #approval_flow_modal .modal-content {
        border-radius: 8px;
        border: 1px solid #ddd;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    #approval_flow_modal .modal-header {
        background: #f8f9fa;
        border-bottom: 1px solid #ddd;
        padding: 15px 20px;
    }

    #approval_flow_modal .modal-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
    }

    #approval_flow_modal .modal-body {
        padding: 20px;
        background: #fff;
    }

    #approval_flow_modal .modal-footer {
        border-top: 1px solid #ddd;
        padding: 15px 20px;
        background: #f8f9fa;
    }

    .approval-flow-section {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .approval-flow-hint {
        font-size: 12px;
        color: #666;
        margin: 5px 0 0;
    }

    .approval-flow-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border: 1px solid #ffcc02;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        color: #856404;
        position: relative;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .approval-flow-warning::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #ffaa00 0%, #ff8800 100%);
        border-radius: 8px 0 0 8px;
    }

    .approval-flow-warning .fa-exclamation-triangle {
        color: #dc3545;
        font-size: 18px;
        margin-right: 12px;
    }

    .warning-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .warning-content {
        line-height: 1.5;
    }

    .tasks-summary {
        margin-top: 12px;
    }

    .tasks-container {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        margin-top: 8px;
        max-height: 250px;
        overflow-y: auto;
    }

    .task-activity-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.2s ease;
    }

    .task-activity-item:last-child {
        border-bottom: none;
    }

    .task-activity-item:hover {
        background: rgba(0, 123, 255, 0.05);
    }

    .task-info {
        display: flex;
        align-items: center;
        flex: 1;
    }

    .task-link {
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
        display: inline;
        transition: color 0.2s ease;
    }

    .task-link:hover {
        color: #0056b3;
        text-decoration: underline !important;
    }

    .task-id {
        color: #495057;
        font-weight: 600;
        margin-right: 6px;
        font-size: 13px;
    }

    .task-name {
        color: #495057;
        font-size: 13px;
    }

    .task-status {
        flex-shrink: 0;
        margin-left: 15px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 12px;
        color: white;
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .warning-note {
        margin-top: 10px;
        padding: 8px 0;
        border-top: 1px solid rgba(255, 136, 0, 0.2);
    }

    .approval-flow-readonly {
        opacity: 0.6;
        pointer-events: none;
    }

    .approval-flow-readonly input,
    .approval-flow-readonly textarea,
    .approval-flow-readonly select {
        background-color: #f8f9fa !important;
        cursor: not-allowed;
    }

    .active-tasks-list {
        max-height: 200px;
        overflow-y: auto;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 10px;
    }

    .active-task-item {
        padding: 5px 0;
        border-bottom: 1px solid #dee2e6;
        font-size: 12px;
    }

    .active-task-item:last-child {
        border-bottom: none;
    }

    .active-task-item .task-link {
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
    }

    .active-task-item .task-link:hover {
        text-decoration: underline;
    }

    #approval-steps-container {
        margin-top: 10px;
    }

    .approval-step-item {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 10px;
    }

    .approval-step-item .step-number {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #007bff;
        color: #fff;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }

    .approval-step-item .remove-step {
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        background: #dc3545;
        color: #fff;
        border: none;
    }

    .approval-step-item .remove-step:hover {
        background: #c82333;
    }





    .approver-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 6px;
        background: #e9ecef;
        border: 1px solid #ced4da;
        font-weight: 500;
        color: #333;
        font-size: 14px;
    }

    .approver-chip .remove-chip {
        margin-left: 8px;
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        font-size: 16px;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .approver-chip .remove-chip:hover {
        background: #f8d7da;
    }

    .approver-chip__avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #007bff;
        color: #fff;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        font-size: 12px;
    }

    .approver-chip__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .approver-chip__meta {
        display: flex;
        gap: 6px;
        font-size: 11px;
        color: #666;
        flex-wrap: wrap;
    }

    .approver-chip__division {
        background: #d1ecf1;
        color: #0c5460;
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 10px;
        font-weight: 600;
    }

    .approver-chip__division--empty {
        background: #f8f9fa;
        color: #6c757d;
    }

    .approver-chip__code {
        font-weight: 600;
        color: #333;
    }

    .approver-chip--empty {
        border-style: dashed;
        border-color: #adb5bd;
        background: transparent;
        color: #6c757d;
        font-weight: 500;
    }

    #add-approval-step {
        border-radius: 4px;
        padding: 6px 12px;
        font-weight: 500;
        background: #28a745;
        color: #fff;
        border: none;
    }

    #add-approval-step:hover {
        background: #218838;
    }

    .approval-select-trigger {
        border-radius: 4px !important;
        border: 1px solid #ced4da !important;
        padding: 8px 12px;
        background: #fff !important;
        color: #495057;
    }

    .approval-select-trigger:hover,
    .approval-select-trigger:focus,
    .bootstrap-select.open > .approval-select-trigger {
        border-color: #007bff !important;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
    }

    .assignee-option {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 200px;
    }

    .assignee-option__avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #007bff;
        color: #fff;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        font-size: 12px;
    }

    .assignee-option__avatar--image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .assignee-option__body {
        display: flex;
        flex-direction: column;
    }

    .assignee-option__name {
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .assignee-option__meta {
        display: flex;
        gap: 6px;
        font-size: 11px;
        color: #666;
        flex-wrap: wrap;
    }

    .assignee-option__division {
        background: #d1ecf1;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 600;
        color: #0c5460;
        font-size: 10px;
    }

    .assignee-option__division--empty {
        background: #f8f9fa;
        color: #6c757d;
    }

    .assignee-option__code {
        font-weight: 600;
        color: #333;
    }

    @media (max-width: 767px) {
        #approval_flow_modal .modal-body {
            padding: 15px;
        }

        .approval-step-item {
            padding: 10px;
        }

        .approval-step-item .col-md-1,
        .approval-step-item .col-md-4,
        .approval-step-item .col-md-6 {
            margin-bottom: 10px;
        }
    }
</style>
<?php echo form_open(admin_url('approval_flow/approval_flow' . ($flowId ? '/' . $flowId : '')), ['id' => 'approval-flow-form']); ?>
<div class="modal fade<?php if (isset($approval_flow)) { echo ' edit'; } ?>" id="approval_flow_modal" tabindex="-1" role="dialog" aria-labelledby="approvalFlowModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="approvalFlowModalLabel">
                    <?php echo e($title); ?>
                </h4>
            </div>
            <div class="modal-body">
                <?php
                $isFlowInUse = isset($approval_flow_in_use) && $approval_flow_in_use;
                if ($isFlowInUse):
                ?>
                <div class="approval-flow-warning">
                    <div class="warning-content">
                        <div class="warning-header">
                            <i class="fa fa-exclamation-triangle"></i>
                            <span><?php echo _l('approval_flow_in_use_warning'); ?></span>
                        </div>
                        <?php
                        $activeTasksUsingFlow = isset($active_tasks_using_flow) ? $active_tasks_using_flow : [];
                        if (!empty($activeTasksUsingFlow)):
                        $taskCount = count($activeTasksUsingFlow);
                        ?>
                        <div class="tasks-summary">
                            <strong><i class="fa fa-tasks text-primary" style="margin-right: 8px;"></i><?php echo _l('active_tasks_count', $taskCount); ?>:</strong>
                            <div class="tasks-container">
                                <?php foreach ($activeTasksUsingFlow as $task): ?>
                                <div class="task-activity-item">
                                    <div class="task-info">
                                        <i class="fa fa-check-circle text-primary" style="margin-right: 8px;"></i>
                                        <a href="<?php echo admin_url('tasks/view/' . $task->id); ?>" class="task-link" target="_blank">
                                            <span class="task-id">#<?php echo $task->id; ?>:</span>
                                            <span class="task-name"><?php echo htmlspecialchars($task->name); ?></span>
                                        </a>
                                    </div>
                                    <div class="task-status">
                                        <?php
                                        $statusData = get_task_status_by_id($task->status);
                                        $statusColor = $statusData['color'] ?? '#64748b';
                                        ?>
                                        <span class="status-badge" style="background-color: <?php echo $statusColor; ?>;">
                                            <i class="fa fa-circle" style="font-size: 8px; margin-right: 5px;"></i>
                                            <?php echo $statusData['name']; ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="warning-note">
                                <i class="fa fa-info-circle text-muted" style="margin-right: 8px;"></i>
                                <small class="text-muted"><?php echo _l('editing_disabled_message'); ?></small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="approval-flow-sections<?php echo $isFlowInUse ? ' approval-flow-readonly' : ''; ?>">
                    <div class="approval-flow-section">
                        <?php
                        $value = (isset($approval_flow) ? $approval_flow->name : '');
                        echo render_input('name', 'approval_flow_name', $value, 'text', ['required' => true]);
                        ?>

                        <?php
                        $value = (isset($approval_flow) ? $approval_flow->description : '');
                        echo render_textarea('description', 'approval_flow_description', $value, ['rows' => 3]);
                        ?>
                    </div>

                    <div class="approval-flow-section">
                        <div class="form-group">
                            <label for="approval_steps"><?php echo _l('approval_steps'); ?></label>
                            <p class="approval-flow-hint"><?php echo _l('select_staff_member'); ?> for each stage and define the order approvers are notified.</p>
                            <div id="approval-steps-container">
                                <?php
                                $steps = isset($approval_flow) ? $approval_flow->steps : [];
                                if (empty($steps)) {
                                    $steps = [['staff_id' => '', 'step_name' => '']];
                                }
                                foreach ($steps as $index => $step) {
                                ?>
                                <div class="approval-step-item row" data-step="<?php echo $index; ?>">
                                    <div class="col-md-1">
                                        <span class="step-number"><?php echo $index + 1; ?></span>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="steps[<?php echo $index; ?>][step_name]" class="form-control"
                                               placeholder="<?php echo _l('step_name'); ?>"
                                               value="<?php echo isset($step['step_name']) ? $step['step_name'] : 'Step ' . ($index + 1); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="approver-select-wrapper">
                                            <select name="steps[<?php echo $index; ?>][staff_id]" class="form-control selectpicker"
                                                    data-style="approval-select-trigger"
                                                    data-live-search="true" data-none-selected-text="<?php echo _l('select_staff_member'); ?>">
                                                <option value=""></option>
                                                <?php foreach ($members as $member) { ?>
                                                <option value="<?php echo $member['staffid']; ?>"
                                                        <?php echo (isset($step['staff_id']) && $step['staff_id'] == $member['staffid']) ? 'selected' : ''; ?>>
                                                    <?php echo $member['firstname'] . ' ' . $member['lastname']; ?>
                                                </option>
                                                <?php } ?>
                                            </select>
                                            <div class="approver-preview"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-1 text-right">
                                        <?php if ($index > 0) { ?>
                                        <button type="button" class="btn btn-danger btn-sm remove-step">
                                            <i class="fa fa-times"></i>
                                        </button>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-success btn-sm" id="add-approval-step">
                                    <i class="fa fa-plus"></i> <?php echo _l('add_step'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <?php if (!$isFlowInUse): ?>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                <?php else: ?>
                <button type="button" class="btn btn-warning" disabled><?php echo _l('editing_disabled'); ?></button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php echo form_close(); ?>

<script>
(function waitForjQuery(callback) {
    if (window.jQuery && typeof window.jQuery === 'function') {
        callback(window.jQuery);
        return;
    }

    setTimeout(function() {
        waitForjQuery(callback);
    }, 50);
})(function($) {
    var approvalStaffDirectory = <?php echo json_encode($staffDirectory, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    var approvalStaffDirectoryIndex = {};
    var approvalNoDivisionLabel = 'No division';
    var approvalEmptySelectionLabel = <?php echo json_encode(_l('select_staff_member')); ?>;

    if (Array.isArray(approvalStaffDirectory)) {
        approvalStaffDirectory.forEach(function(member) {
            if (member && typeof member.id !== 'undefined') {
                approvalStaffDirectoryIndex[String(member.id)] = member;
            }
        });
    }

    function approvalEscapeHtml(str) {
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

    function buildFallbackStaff(staffId, fallbackName) {
        fallbackName = (fallbackName || '').trim();
        if (fallbackName === '') {
            fallbackName = 'Member #' + staffId;
        }
        var initials = fallbackName.split(' ').map(function(part) {
            return part.charAt(0);
        }).join('').substring(0, 2).toUpperCase();

        return {
            id: staffId,
            fullname: fallbackName,
            initials: initials || 'ST',
            avatar: '',
            division_id: null,
            division_name: '',
            emp_code: ''
        };
    }

    function decorateApprovalStaffSelect($elements) {
        if (!$elements || !$elements.length) {
            return;
        }
        $elements.each(function() {
            var $select = $(this);
            $select.find('option').each(function() {
                var $option = $(this);
                var staffId = parseInt($option.val(), 10);
                if (!staffId) {
                    $option.removeAttr('data-content').removeAttr('data-tokens');
                    return;
                }
                var staff = approvalStaffDirectoryIndex[String(staffId)];
                if (!staff) {
                    staff = buildFallbackStaff(staffId, $option.text());
                    approvalStaffDirectoryIndex[String(staffId)] = staff;
                }
                var hasDivision = staff.division_name && staff.division_name !== '';
                var divisionLabel = hasDivision ? staff.division_name : approvalNoDivisionLabel;
                var divisionBadgeClass = hasDivision ? 'assignee-option__division' : 'assignee-option__division assignee-option__division--empty';
                var empBadge = staff.emp_code ? '<span class="assignee-option__code">' + approvalEscapeHtml(staff.emp_code) + '</span>' : '';
                var avatarHtml = staff.avatar && staff.avatar !== ''
                    ? '<span class="assignee-option__avatar assignee-option__avatar--image"><img src="' + approvalEscapeHtml(staff.avatar) + '" alt="' + approvalEscapeHtml(staff.fullname || '') + '"></span>'
                    : '<span class="assignee-option__avatar">' + approvalEscapeHtml(staff.initials || '') + '</span>';
                var content = '<div class="assignee-option">' +
                    avatarHtml +
                    '<div class="assignee-option__body">' +
                    '<span class="assignee-option__name">' + approvalEscapeHtml(staff.fullname || '') + '</span>' +
                    '<div class="assignee-option__meta">' +
                    '<span class="' + divisionBadgeClass + '">' + approvalEscapeHtml(divisionLabel) + '</span>' +
                    empBadge +
                    '</div>' +
                    '</div>' +
                    '</div>';
                $option
                    .attr('data-content', content)
                    .attr('data-tokens', (staff.fullname || '') + ' ' + (staff.emp_code || '') + ' ' + (staff.division_name || ''));
            });
        });
    }

    function buildApproverChip(staff) {
        var avatar = staff.avatar && staff.avatar !== ''
            ? '<span class="approver-chip__avatar"><img src="' + approvalEscapeHtml(staff.avatar) + '" alt="' + approvalEscapeHtml(staff.fullname || '') + '"></span>'
            : '<span class="approver-chip__avatar">' + approvalEscapeHtml(staff.initials || '') + '</span>';
        var hasDivision = staff.division_name && staff.division_name !== '';
        var divisionBadgeClass = hasDivision ? 'approver-chip__division' : 'approver-chip__division approver-chip__division--empty';
        var divisionLabel = hasDivision ? staff.division_name : approvalNoDivisionLabel;
        var empBadge = staff.emp_code ? '<span class="approver-chip__code">' + approvalEscapeHtml(staff.emp_code) + '</span>' : '';

        return '<span class="approver-chip">' +
            avatar +
            '<span>' + approvalEscapeHtml(staff.fullname || '') + '</span>' +
            '<span class="approver-chip__meta">' +
            '<span class="' + divisionBadgeClass + '">' + approvalEscapeHtml(divisionLabel) + '</span>' +
            empBadge +
            '</span>' +
            '<button type="button" class="remove-chip">&times;</button>' +
            '</span>';
    }

    function renderApproverPreview($select) {
        if (!$select || !$select.length) {
            return;
        }
        var $wrapper = $select.closest('.approver-select-wrapper');
        var $preview = $wrapper.find('.approver-preview');
        var $bsWrapper = $select.closest('.bootstrap-select');
        if (!$preview.length) {
            return;
        }
        var staffId = $select.val();
        if (!staffId) {
            $bsWrapper.show();
            $preview.hide();
            return;
        }
        var staff = approvalStaffDirectoryIndex[String(staffId)];
        if (!staff) {
            var fallbackName = $select.find('option:selected').text();
            staff = buildFallbackStaff(parseInt(staffId, 10), fallbackName);
            approvalStaffDirectoryIndex[String(staffId)] = staff;
        }
        $bsWrapper.hide();
        $preview.html(buildApproverChip(staff)).show();
    }

    function getSelectedStaffIds() {
        var selectedIds = [];
        $('#approval-steps-container .selectpicker').each(function() {
            var val = $(this).val();
            if (val) {
                selectedIds.push(val);
            }
        });
        return selectedIds;
    }

    function updateSelectOptions($select) {
        if (!$select || !$select.length) {
            return;
        }
        var selectedIds = getSelectedStaffIds();
        var currentStepVal = $select.val();
        $select.find('option').each(function() {
            var $option = $(this);
            var val = $option.val();
            if (val && val !== currentStepVal && selectedIds.includes(val)) {
                $option.hide();
            } else {
                $option.show();
            }
        });
        decorateApprovalStaffSelect($select);
        $select.selectpicker('refresh');
    }

    function refreshApproverSelects($context) {
        var $selects = ($context && $context.length) ? $context.find('.selectpicker') : $('#approval-steps-container .selectpicker');
        if (!$selects.length) {
            return;
        }
        $selects.each(function() {
            updateSelectOptions($(this));
        });
        $selects.each(function() {
            renderApproverPreview($(this));
        });
    }

    function approval_flow_form_handler(form) {
        var formData = $(form).serialize();
        var url = form.action;
        var isCreatePage = window.location.href.includes('/create');

        $.post(url, formData).done(function(response) {
            response = JSON.parse(response);
            if (response.success) {
                alert_float('success', response.message);
                if (isCreatePage) {
                    // Redirect to main approval flow list page after create
                    window.location.href = "<?php echo admin_url('approval_flow'); ?>";
                } else {
                    $('#approval_flow_modal').modal('hide');
                    $('.table-approval_flow').DataTable().ajax.reload();
                }
            } else {
                alert_float('danger', response.message);
            }
        });

        return false;
    }

    window.approval_flow_form_handler = approval_flow_form_handler;

    $(function() {
        appValidateForm($('#approval-flow-form'), {
            name: 'required'
        }, approval_flow_form_handler);

        init_selectpicker();
        refreshApproverSelects();

        $('#add-approval-step').on('click', function() {
            var stepCount = $('.approval-step-item').length;
            var optionsHtml = '<option value=""></option>';
            <?php foreach ($members as $member) { ?>
            optionsHtml += '<option value="<?php echo $member['staffid']; ?>"><?php echo addslashes($member['firstname'] . ' ' . $member['lastname']); ?></option>';
            <?php } ?>

            var newStepHtml = '<div class="approval-step-item row" data-step="' + stepCount + '">' +
                '<div class="col-md-1">' +
                    '<span class="step-number">' + (stepCount + 1) + '</span>' +
                '</div>' +
                '<div class="col-md-4">' +
                    '<input type="text" name="steps[' + stepCount + '][step_name]" class="form-control" placeholder="<?php echo _l('step_name'); ?>" value="Step ' + (stepCount + 1) + '">' +
                '</div>' +
                '<div class="col-md-6">' +
                    '<div class="approver-select-wrapper">' +
                        '<select name="steps[' + stepCount + '][staff_id]" class="form-control selectpicker" data-style="approval-select-trigger" data-live-search="true" data-none-selected-text="<?php echo _l('select_staff_member'); ?>">' +
                            optionsHtml +
                        '</select>' +
                        '<div class="approver-preview"></div>' +
                    '</div>' +
                '</div>' +
                '<div class="col-md-1 text-right">' +
                    '<button type="button" class="btn btn-danger btn-sm remove-step">' +
                        '<i class="fa fa-times"></i>' +
                    '</button>' +
                '</div>' +
            '</div>';

            var $newStep = $(newStepHtml);
            $('#approval-steps-container').append($newStep);
            refreshApproverSelects($newStep);
            updateStepNumbers();
        });

        $(document).on('click', '.remove-step', function() {
            $(this).closest('.approval-step-item').remove();
            updateStepNumbers();
            // Update all select options after step removal
            $('#approval-steps-container .selectpicker').each(function() {
                updateSelectOptions($(this));
            });
        });

        $(document).on('changed.bs.select', '#approval-steps-container .selectpicker', function() {
            renderApproverPreview($(this));
            // Update all select options after change
            $('#approval-steps-container .selectpicker').each(function() {
                updateSelectOptions($(this));
            });
        });

        $(document).on('click', '.remove-chip', function() {
            var $chip = $(this).closest('.approver-chip');
            var $wrapper = $chip.closest('.approver-select-wrapper');
            var $select = $wrapper.find('select');
            $select.val('').selectpicker('refresh');
            renderApproverPreview($select);
            // Update all select options after removal
            $('#approval-steps-container .selectpicker').each(function() {
                updateSelectOptions($(this));
            });
        });

        function updateStepNumbers() {
            $('.approval-step-item').each(function(index) {
                $(this).find('.step-number').text((index + 1));
                $(this).attr('data-step', index);
                $(this).find('input, select').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                    }
                });
            });
        }
    });
});
</script>
<?php if ($modalStandalone) { ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
(function waitForjQuery(callback) {
    if (window.jQuery && typeof window.jQuery === 'function') {
        callback(window.jQuery);
        return;
    }

    setTimeout(function() {
        waitForjQuery(callback);
    }, 50);
})(function($) {
    var approvalFlowListUrl = <?php echo json_encode(admin_url('approval_flow')); ?>;
    var $modal = $('#approval_flow_modal');
    $modal.modal('show');
    $modal.on('hidden.bs.modal', function() {
        window.location.href = approvalFlowListUrl;
    });
});
</script>
</body>
</html>
<?php } ?>
