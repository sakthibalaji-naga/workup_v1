<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$approvalTeam = $approvalTeam ?? [];
$approvedStaffIds = $approvedStaffIds ?? [];
$rejectedStaffIds = $rejectedStaffIds ?? [];
?>
<?php if (! empty($approvalTeam)) { ?>
<div class="task-summary-avatars">
    <?php
    $count = 0;
    foreach ($approvalTeam as $memberData) {
        if ($count >= 7) break; // Limit to 7 members
        $memberId = $memberData['id'];
        $isRejected = in_array($memberId, $rejectedStaffIds);
        $isApproved = ! $isRejected && in_array($memberId, $approvedStaffIds);
        ?>
    <span class="task-summary-avatar-wrapper<?= ($isApproved || $isRejected) ? ' approval-member-wrapper' : ''; ?>"
        data-toggle="tooltip"
        data-title="<?= e($memberData['name']); ?>">
        <a href="<?= e($memberData['url']); ?>" target="_blank">
            <?php if ($isRejected) { ?>
            <div class="approval-avatar-container">
                <div class="approval-avatar-ring approval-avatar-ring--rejected">
                    <?= $memberData['img']; ?>
                </div>
                <div class="approval-cross-mark">
                    <i class="fa-solid fa-xmark"></i>
                </div>
            </div>
            <?php } elseif ($isApproved) { ?>
            <div class="approval-avatar-container">
                <div class="approval-avatar-ring">
                    <?= $memberData['img']; ?>
                </div>
                <div class="approval-tick-mark">
                    <i class="fa-solid fa-check"></i>
                </div>
            </div>
            <?php } else { ?>
                <?= $memberData['img']; ?>
            <?php } ?>
        </a>
    </span>
    <?php $count++; } ?>
</div>
<?php } else { ?>
<p class="task-summary-empty">No approval team members</p>
<?php } ?>
