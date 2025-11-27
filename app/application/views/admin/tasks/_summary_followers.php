<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$followers            = $followers ?? [];
$taskId               = $taskId ?? null;
$canManageFollowers   = $canManageFollowers ?? false;
$wrapperManageClasses = $canManageFollowers ? ' task-summary-avatar-wrapper--manageable' : '';
?>
<?php
$maxVisibleFollowers = 9;
$visibleFollowers    = array_slice($followers, 0, $maxVisibleFollowers);
$overflowFollowers   = array_slice($followers, $maxVisibleFollowers);
?>
<?php if (! empty($followers)) { ?>
<div class="task-summary-avatars">
    <?php foreach ($visibleFollowers as $followerData) { ?>
    <span class="task-summary-avatar-wrapper<?= $wrapperManageClasses; ?>"
        data-toggle="tooltip"
        data-title="<?= e($followerData['name']); ?>">
        <a href="<?= e($followerData['url']); ?>" target="_blank">
            <?= $followerData['img']; ?>
        </a>
        <?php if ($canManageFollowers && $taskId) { ?>
        <button type="button"
            class="task-summary-avatar-remove"
            onclick="remove_follower(<?= e($followerData['id']); ?>, <?= e($taskId); ?>); return false;">
            <i class="fa fa-times"></i>
        </button>
        <?php } ?>
    </span>
    <?php } ?>
    <?php if (! empty($overflowFollowers)) { ?>
    <?php
    $overflowListItems = '';
    foreach ($overflowFollowers as $followerData) {
        $overflowListItems .= '<div class="task-summary-overflow-item">';
        $overflowListItems .= '<span class="task-summary-overflow-name">' . e($followerData['name']) . '</span>';

        if ($canManageFollowers && $taskId) {
            $overflowListItems .= '<button type="button" class="task-summary-overflow-remove" onclick="remove_follower(' . e($followerData['id']) . ',' . e($taskId) . '); return false;"><i class=\'fa fa-times\'></i></button>';
        }

        $overflowListItems .= '</div>';
    }
    $overflowContent = '<div class="task-summary-overflow-list">' . $overflowListItems . '</div>';
    ?>
    <span class="task-summary-avatar-wrapper<?= $wrapperManageClasses; ?>">
        <span class="task-summary-avatar-more js-task-follower-overflow"
            role="button"
            tabindex="0"
            data-toggle="popover"
            data-html="true"
            data-container="body"
            data-placement="top"
            data-content="<?= htmlspecialchars($overflowContent, ENT_QUOTES, 'UTF-8'); ?>">
            +<?= count($overflowFollowers); ?>
        </span>
    </span>
    <?php } ?>
</div>
<?php } else { ?>
<p class="task-summary-empty"><?= _l('task_no_followers'); ?></p>
<?php } ?>
