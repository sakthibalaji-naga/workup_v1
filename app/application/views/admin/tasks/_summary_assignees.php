<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$assignees            = $assignees ?? [];
$taskId               = $taskId ?? null;
$canManageAssignees   = $canManageAssignees ?? false;
$wrapperManageClasses = $canManageAssignees ? ' task-summary-avatar-wrapper--manageable' : '';
?>
<?php
$maxVisibleAssignees = 9;
$visibleAssignees    = array_slice($assignees, 0, $maxVisibleAssignees);
$overflowAssignees   = array_slice($assignees, $maxVisibleAssignees);
?>
<?php if (! empty($assignees)) { ?>
<div class="task-summary-avatars">
    <?php foreach ($visibleAssignees as $assigneeData) { ?>
    <span class="task-summary-avatar-wrapper<?= $wrapperManageClasses; ?>"
        data-toggle="tooltip"
        data-title="<?= e($assigneeData['name']); ?>">
        <a href="<?= e($assigneeData['url']); ?>" target="_blank">
            <?= $assigneeData['img']; ?>
        </a>
        <?php if ($canManageAssignees && $taskId) { ?>
        <button type="button"
            class="task-summary-avatar-remove"
            onclick="remove_assignee(<?= e($assigneeData['id']); ?>, <?= e($taskId); ?>); return false;">
            <i class="fa fa-times"></i>
        </button>
        <?php } ?>
    </span>
    <?php } ?>
    <?php if (! empty($overflowAssignees)) { ?>
    <?php
    $overflowListItems = '';
    foreach ($overflowAssignees as $assigneeData) {
        $overflowListItems .= '<div class="task-summary-overflow-item">';
        $overflowListItems .= '<span class="task-summary-overflow-name">' . e($assigneeData['name']) . '</span>';

        if ($canManageAssignees && $taskId) {
            $overflowListItems .= '<button type="button" class="task-summary-overflow-remove" onclick="remove_assignee(' . e($assigneeData['id']) . ',' . e($taskId) . '); return false;"><i class=\'fa fa-times\'></i></button>';
        }

        $overflowListItems .= '</div>';
    }
    $overflowContent = '<div class="task-summary-overflow-list">' . $overflowListItems . '</div>';
    ?>
    <span class="task-summary-avatar-wrapper<?= $wrapperManageClasses; ?>">
        <span class="task-summary-avatar-more js-task-assignee-overflow"
            role="button"
            tabindex="0"
            data-toggle="popover"
            data-html="true"
            data-container="body"
            data-placement="top"
            data-content="<?= htmlspecialchars($overflowContent, ENT_QUOTES, 'UTF-8'); ?>">
            +<?= count($overflowAssignees); ?>
        </span>
    </span>
    <?php } ?>
</div>
<?php } else { ?>
<p class="task-summary-empty"><?= _l('task_single_not_assigned'); ?></p>
<?php } ?>
