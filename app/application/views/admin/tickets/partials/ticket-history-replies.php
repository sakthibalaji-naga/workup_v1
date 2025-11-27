<?php if (!isset($ticket_replies) || empty($ticket_replies) || count($ticket_replies) == 0) { ?>
<div class="tw-relative tw-overflow-hidden">
    <div class="tw-text-center tw-py-16 tw-px-8 tw-text-neutral-500 tw-bg-gradient-to-br tw-from-white tw-via-neutral-50 tw-to-blue-50 tw-rounded-xl tw-mx-6 tw-my-8 tw-border tw-border-neutral-200 tw-shadow-sm">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-r tw-from-blue-400/5 tw-to-purple-400/5 tw-animate-pulse"></div>
        <div class="tw-relative tw-z-10">
            <div class="tw-inline-block tw-p-4 tw-rounded-full tw-bg-gradient-to-br tw-from-blue-100 tw-to-indigo-100 tw-mb-6 tw-shadow-lg">
                <i class="fa fa-comments fa-5x tw-text-indigo-600 tw-animate-bounce"></i>
            </div>
            <h4 class="tw-text-xl tw-font-bold tw-text-neutral-800 tw-mb-3 tw-tracking-tight"><?php echo _l('ticket_no_replies_yet'); ?></h4>
            <p class="tw-text-base tw-text-neutral-600 tw-mb-6 tw-max-w-md tw-mx-auto tw-leading-relaxed"><?php echo _l('ticket_no_replies_description'); ?></p>
            <div class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-indigo-50 tw-border tw-border-indigo-200 tw-rounded-full tw-text-sm tw-font-medium tw-text-indigo-700">
                <i class="fa fa-lightbulb tw-text-amber-500"></i>
                <span><?php echo _l('ticket_start_conversation'); ?></span>
            </div>
        </div>
    </div>
</div>
<?php } else { ?>
<?php foreach ($ticket_replies as $reply) { ?>
<div class="panel_s tw-mt-5 tw-mb-4">
    <div
        class="panel-body<?= $reply['admin'] == null ? ' client-reply' : ''; ?> ticket-thread tw-p-4">
        <div class="tw-flex tw-flex-wrap tw-mb-6">
            <div class="tw-grow">
                <div class="tw-flex tw-items-center tw-gap-3">
                    <p class="tw-my-0 tw-font-semibold">
                        <?php if ($reply['admin'] == null || $reply['admin'] == 0) { ?>
                        <?php if ($reply['userid'] != 0) { ?>
                        <a
                            href="<?= admin_url('clients/client/' . $reply['userid'] . '?contactid=' . $reply['contactid']); ?>">
                            <?= e($reply['submitter']); ?>
                        </a>
                        <?php } else { ?>
                        <span><?= e($reply['submitter']); ?></span>
                        <br />
                        <a
                            href="mailto:<?= e($reply['reply_email']); ?>">
                            <?= e($reply['reply_email']); ?>
                        </a>
                        <?php } ?>
                        <?php } else { ?>
                        <a
                            href="<?= admin_url('profile/' . $reply['admin']); ?>">
                            <?= e($reply['submitter']); ?>
                        </a>
                        <?php } ?>
                    </p>
                    <?php if ($reply['admin'] !== null || $reply['admin'] != 0) {
                        $role_info = get_staff_role_info($reply['admin'], $ticket); ?>
                    <span
                        class="label <?= $role_info['class']; ?>"><?= $role_info['label']; ?></span>
                    <?php } elseif ($reply['userid'] != 0) { ?>
                    <span
                        class="label label-primary"><?= _l('ticket_client_string'); ?></span>
                    <?php } ?>
                </div>
            </div>
            <div class="tw-space-x-4 tw-flex tw-items-center rtl:tw-space-x-reverse">
                <p class="tw-text-neutral-600 tw-font-medium tw-text-sm tw-my-0">
                    <i class="fa fa-clock tw-mr-1"></i>
                    <?= e(_dt($reply['date'])); ?>
                </p>
                <?php if (staff_can('create', 'tasks')) { ?>
                <a href="#"
                    class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-600 tw-text-sm tw-font-semibold"
                    onclick="convert_ticket_to_task(<?= e($reply['id']); ?>,'reply'); return false;">
                    <?= _l('convert_to_task'); ?>
                </a>
                <?php } ?>
                <?php if (! empty($reply['message'])) { ?>
                <a href="#" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-600"
                    onclick="print_ticket_message(<?= e($reply['id']); ?>,'reply'); return false;">
                    <i class="fa fa-print"></i>
                </a>
                <?php } ?>
                <a href="#" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-600"
                    onclick="edit_ticket_message(<?= e($reply['id']); ?>,'reply'); return false;">
                    <i class="fa-regular fa-pen-to-square"></i>
                </a>
                <?php if (can_staff_delete_ticket_reply()) { ?>
                <a href="<?= admin_url('tickets/delete_ticket_reply/' . $ticket->ticketid . '/' . $reply['id']); ?>"
                    class="_delete tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-600">
                    <i class="fa-regular fa-trash-can"></i>
                </a>
                <?php } ?>
            </div>
        </div>
        <div data-reply-id="<?= e($reply['id']); ?>"
            class="tc-content">
            <?php
                if (empty($reply['admin'])) {
                    echo process_text_content_for_display($reply['message']);
                } else {
                    echo check_for_links($reply['message']);
                }
    ?>
        </div>
        <?php if (count($reply['attachments']) > 0) { ?>
        <hr />
        <div class="row">
            <div class="col-md-12">
                <strong>Attachments:</strong><br>
                <?php foreach ($reply['attachments'] as $attachment) { ?>
                <a href="<?= site_url('download/file/ticket/' . $attachment['id']); ?>" class="btn btn-sm btn-default" target="_blank">
                    <i class="fa fa-download"></i> <?= e($attachment['file_name']); ?>
                </a>
                <?php if (is_admin() || (!is_admin() && get_option('allow_non_admin_staff_to_delete_ticket_attachments') == '1')) { ?>
                <a href="<?= admin_url('tickets/delete_attachment/' . $attachment['id']); ?>" class="text-danger _delete" title="<?= _l('delete'); ?>" style="margin-left: 5px;">
                    <i class="fa fa-trash"></i>
                </a>
                <?php } ?>
                <?php } ?>
    </div>
</div>
<?php } ?>
    </div>
</div>
<?php } ?>
<?php } ?>
