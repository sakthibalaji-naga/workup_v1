<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$staffName = trim(($approval['firstname'] ?? '') . ' ' . ($approval['lastname'] ?? ''));
$historyEntries = $history ?? [];
$stepDisplay = e($approval['step_order'] ?? '—');
$approverDisplay = e($staffName);
?>

<div class="modal fade task-remark-history-modal" id="taskRemarkHistoryModal" tabindex="-1" role="dialog"
    aria-labelledby="taskRemarkHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="taskRemarkHistoryModalLabel">
                    <?= _l('task_approval_remark_history_title'); ?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= _l('close'); ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="remark-history-summary">
                    <p class="text-muted tw-mb-1">
                        <?= sprintf(
                            _l('task_approval_remark_history_for_step'),
                            $stepDisplay,
                            $approverDisplay
                        ); ?>
                    </p>
                    <p class="text-muted tw-mb-0">
                        <?= _l('task'); ?>: <strong><?= e($task->name ?? ''); ?></strong>
                    </p>
                </div>
                <hr class="hr-10">
                <?php if (! empty($historyEntries)) { ?>
                <div class="remark-history-list">
                    <?php foreach ($historyEntries as $entry) {
                        $entryName = trim(($entry['firstname'] ?? '') . ' ' . ($entry['lastname'] ?? ''));
                        $actionType = strtolower($entry['action_type'] ?? 'remark');
                        $actionLabels = [
                            'remark' => _l('task_approval_history_action_remark'),
                            'approve' => _l('task_approval_history_action_approve'),
                            'reject' => _l('task_approval_history_action_reject'),
                            'revert' => _l('task_approval_history_action_revert'),
                        ];
                        $actionLabel = $actionLabels[$actionType] ?? _l('task_approval_history_action_remark');
                    ?>
                    <div class="remark-history-entry">
                        <div class="remark-history-entry__header">
                            <div>
                                <span class="remark-history-entry__author">
                                    <?= e($entryName ?: _l('task_approval_history_unknown_user')); ?>
                                </span>
                                <span class="remark-history-entry__timestamp">
                                    <?= _dt($entry['created_at']); ?>
                                </span>
                            </div>
                            <span class="remark-history-entry__badge remark-history-entry__badge--<?= e($actionType); ?>">
                                <?= e($actionLabel); ?>
                            </span>
                        </div>
                        <div class="remark-history-entry__content">
                            <?php if (trim(strip_tags($entry['comments'] ?? '')) !== '') { ?>
                                <?= process_text_content_for_display($entry['comments']); ?>
                            <?php } else { ?>
                                <span class="text-muted"><?= _l('task_approval_remark_history_no_comment'); ?></span>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <?php } else { ?>
                <p class="text-muted text-center tw-mt-3 tw-mb-0">
                    <?= _l('task_approval_remark_history_empty'); ?>
                </p>
                <?php } ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>

<style>
    .remark-history-summary p {
        font-size: 13px;
    }

    .remark-history-entry {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 12px;
        background: #fdfdff;
    }

    .remark-history-entry__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        gap: 10px;
    }

    .remark-history-entry__author {
        font-weight: 600;
        color: #0f172a;
        margin-right: 8px;
    }

    .remark-history-entry__timestamp {
        font-size: 12px;
        color: #64748b;
    }

    .remark-history-entry__badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .remark-history-entry__badge--remark {
        background: rgba(14, 165, 233, 0.1);
        color: #0369a1;
    }

    .remark-history-entry__badge--approve {
        background: rgba(34, 197, 94, 0.12);
        color: #166534;
    }

    .remark-history-entry__badge--reject {
        background: rgba(248, 113, 113, 0.12);
        color: #b91c1c;
    }

    .remark-history-entry__content {
        font-size: 13px;
        color: #334155;
        line-height: 1.5;
    }
</style>
