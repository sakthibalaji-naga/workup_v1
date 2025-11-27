<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php set_ticket_open($ticket->adminread, $ticket->ticketid); ?>
<?php if (isset($pending_reassignment) && $pending_reassignment && (int)$pending_reassignment->to_assigned === (int) get_staff_user_id()) { ?>
<script>
    window.addEventListener('load', function(){
        function ready(fn){ if (window.jQuery && typeof $==='function'){ fn(); } else { setTimeout(function(){ ready(fn); }, 50); } }
        ready(function(){
            // Hide change status dropdown and action buttons for view-only state
            $('#ticketStatusDropdown').closest('.dropdown').remove();
            $('a._delete').remove();
            // Hide reassign and handler buttons if present
            $('button[data-target="#reassignTicketModal"]').hide();
            $('button[data-target="#ticketHandlerModal"]').hide();
            // Hide tabs that allow editing: reply, note, reminders
            $('a[href="#addreply"]').closest('li').remove();
            $('a[href="#note"]').closest('li').remove();
            $('a[href="#tab_reminders"]').closest('li').remove();
        });
    });
    </script>
<?php } ?>
<?php if ($closeRequestBanner) { ?>
<script>
    window.addEventListener('load', function(){
        function ready(fn){ if (window.jQuery && typeof $==='function'){ fn(); } else { setTimeout(function(){ ready(fn); }, 50); } }
        ready(function(){
            // Disable reply tab for view-only state during close approval
            $('a[href="#addreply"]').closest('li').addClass('disabled');
            $('a[href="#addreply"]').attr('data-toggle', 'none').css('pointer-events', 'none').css('opacity', '0.5');
            // Switch away from the reply tab to prevent using the form
            $('.nav-tabs a[href="#note"]').tab('show');
            // Disable form elements in the reply content to be extra sure
            $('#addreply input, #addreply textarea, #addreply button').prop('disabled', true).css('opacity', '0.5');
        });
    });
    </script>
<?php } ?>
<?php
    $ticketIsClosed = isset($ticket_is_closed) && $ticket_is_closed;
    $reopenRequest = isset($reopen_request) ? $reopen_request : null;
    $canRequestReopen = isset($can_request_reopen) && $can_request_reopen;
    $canHandleReopenRequest = isset($can_handle_reopen_request) && $can_handle_reopen_request;
    $canViewSLA = is_admin() || (isset($ticket->assigned) && (int)$ticket->assigned === (int)get_staff_user_id()) || (isset($ticket->ticketid) ? $this->tickets_model->is_ticket_handler((int)$ticket->ticketid, get_staff_user_id()) : false);
    $closeRequestBanner = isset($close_request) && $close_request;
?>
<div id="wrapper">
    <div class="tw-h-full">
        <div class="sm:tw-flex tw-h-full">
            <div class="tw-flex-1 sm:tw-pr-6 ticket-left-content" style="flex:1 1 auto;">
                <div class="tw-h-full">
                    <div
                        class="tw-py-4 tw-px-6 tw-bg-gradient-to-r tw-from-neutral-50 tw-to-white tw-border-b tw-border-solid tw-border-neutral-300 tw-sticky tw-top-0 tw-z-20 sm:tw-h-[61px]">
                        <div class="sm:tw-flex sm:tw-items-center sm:tw-justify-between sm:tw-space-x-4 rtl:tw-space-x-reverse"
                            id="ticketLeftInformation">
                            <div
                                class="sm:tw-flex sm:tw-items-center sm:tw-space-x-3 tw-mb-2 sm:tw-mb-0 rtl:tw-space-x-reverse">
                                <h3 class="tw-font-bold tw-text-lg tw-my-0 tw-max-w-full sm:tw-max-w-lg sm:tw-truncate"
                                    title="<?= e($ticket->subject); ?>">
                                    <span
                                        id="ticket_subject">#<?= e($ticket->ticket_number ?: $ticket->ticketid); ?>
                                        -
                                        <?= e($ticket->subject); ?>
                                    </span>
                                </h3>
                                <?php
                                $isClosePending = isset($close_request) && $close_request;
                                $isReassignmentPending = isset($pending_reassignment) && $pending_reassignment;
                                $dropdownDisabled = $ticketIsClosed || $isClosePending || $isReassignmentPending;
                                ?>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle single-ticket-status-label label tw-inline-flex tw-items-center tw-gap-1 tw-flex-nowrap<?php if ($dropdownDisabled) { ?> hover:tw-opacity-80<?php } else { ?> hover:tw-opacity-80<?php } ?> tw-align-middle<?php if ($dropdownDisabled) { ?> disabled<?php } ?>"
                                        style="color:<?= e($ticket->statuscolor); ?>;border:1px solid <?= adjust_hex_brightness($ticket->statuscolor, 0.4); ?>;background: <?= adjust_hex_brightness($ticket->statuscolor, 0.04); ?><?php if ($dropdownDisabled) { ?>;pointer-events:none;opacity:0.6;<?php } ?>"
                                        id="ticketStatusDropdown"<?php if (!$dropdownDisabled) { ?> data-toggle="dropdown"<?php } ?> aria-haspopup="true"
                                        aria-expanded="false">
                                        <?= e(ticket_status_translate($ticket->ticketstatusid)); ?>
                                        <i class="chevron tw-shrink-0"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="ticketStatusDropdown">
                                        <?php foreach ($statuses as $status) {
                                            if (! is_array($status)) {
                                                continue;
                                            } ?>
                                        <li>
                                            <a href="#" class="change-ticket-status"
                                                data-status="<?= $status['ticketstatusid']; ?>">
                                                <?= e(ticket_status_translate($status['ticketstatusid'])); ?>
                                            </a>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <?php
                                $priority_name = ticket_priority_translate($ticket->priority);

                                // Get priority color from database
                                $priority_info = $this->tickets_model->get_priority($ticket->priority);
                                $priority_color = '#6c757d'; // default gray
                                if ($priority_info && isset($priority_info->color) && !empty($priority_info->color)) {
                                    $priority_color = $priority_info->color;
                                }
                                ?>
                                <span class="label tw-inline-flex tw-items-center tw-gap-1 tw-flex-nowrap tw-ml-2"
                                    style="color:<?= $priority_color; ?>;border:1px solid <?= adjust_hex_brightness($priority_color, 0.4); ?>;background: <?= adjust_hex_brightness($priority_color, 0.04); ?>;">
                                    <?= e($priority_name); ?>
                                </span>
                            </div>

                            <div class="tw-space-x-4 tw-inline-flex tw-items-center rtl:tw-space-x-reverse">
                                <?php if (is_ai_provider_enabled() && get_option('ai_enable_ticket_summarization') == '1') { ?>
                                <button class="btn btn-secondary btn-ai-summarize tw-border-0"
                                    data-loading-text="<?= _l('wait_text'); ?>">
                                    <i class="fa-solid fa-robot"></i>
                                    <?= _l('ticket_summarize_ai'); ?>
                                </button>
                                <?php } ?>
                                <?php if ($ticketIsClosed && $canRequestReopen) { ?>
                                <a href="<?= admin_url('tickets/reopen_request/' . $ticket->ticketid); ?>"
                                    class="btn btn-warning btn-sm tw-text-white tw-w-full sm:tw-w-auto">
                                    <?= _l('ticket_reopen_request_button'); ?>
                                </a>
                                <?php } ?>

                            </div>
                        </div>
                    </div>
                <div class="ticket-left-scroll">
<?php
    $closeRequestBanner = isset($close_request) && $close_request;
    if ($closeRequestBanner) {
        $closeRequestCanAct = ((int) ($ticket->admin ?? 0) === (int) get_staff_user_id()) || is_admin();
        $closeRequestedByName = '';
        if (isset($close_request->requested_by)) {
            $closeRequestedByName = function_exists('get_staff_full_name') ? trim(get_staff_full_name($close_request->requested_by)) : '';
        }
        if ($closeRequestedByName === '') {
            $closeRequestedByName = '#' . (int) ($close_request->requested_by ?? 0);
        }
        $closeRequestedAt = isset($close_request->requested_at) && $close_request->requested_at
            ? _dt($close_request->requested_at)
            : '';
        $closeRequestDetail = sprintf(
            _l('ticket_close_request_pending_detail'),
            $closeRequestedByName,
            $closeRequestedAt !== '' ? $closeRequestedAt : _l('ticket_close_request_pending_detail_time_unknown')
        );
        $closeRequestContext = '';
        $creatorId = (int) ($ticket->admin ?? 0);
        if ($closeRequestCanAct) {
            $closeRequestContext = _l('ticket_close_request_pending_for_you');
        } elseif ($creatorId > 0) {
            $creatorName = function_exists('get_staff_full_name') ? trim(get_staff_full_name($creatorId)) : '';
            if ($creatorName === '') {
                $creatorName = '#' . $creatorId;
            }
            $closeRequestContext = _l('ticket_close_request_pending_waiting_for', $creatorName);
        }
    }

    $reopenRequestBanner = $ticketIsClosed && $reopenRequest;
    if ($reopenRequestBanner) {
        $reopenRequestedByName = '';
        if (isset($reopenRequest->requested_by)) {
            $reopenRequestedByName = function_exists('get_staff_full_name') ? trim(get_staff_full_name($reopenRequest->requested_by)) : '';
        }
        if ($reopenRequestedByName === '') {
            $reopenRequestedByName = '#' . (int) ($reopenRequest->requested_by ?? 0);
        }
        $reopenRequestedAt = isset($reopenRequest->requested_at) && $reopenRequest->requested_at
            ? _dt($reopenRequest->requested_at)
            : '';
        $reopenRequestDetail = sprintf(
            _l('ticket_reopen_request_pending_detail'),
            $reopenRequestedByName,
            $reopenRequestedAt !== '' ? $reopenRequestedAt : _l('ticket_close_request_pending_detail_time_unknown')
        );
        $reopenRequestContext = '';
        if ($canHandleReopenRequest) {
            $reopenRequestContext = _l('ticket_reopen_request_pending_for_you');
        } elseif (isset($ticket->assigned) && (int) $ticket->assigned > 0) {
            $assigneeName = function_exists('get_staff_full_name') ? trim(get_staff_full_name($ticket->assigned)) : '';
            if ($assigneeName === '') {
                $assigneeName = '#' . (int) $ticket->assigned;
            }
            $reopenRequestContext = _l('ticket_reopen_request_pending_waiting_for', $assigneeName);
        }
    }
?>
<?php if ($closeRequestBanner) { ?>
                    <div class="tw-px-6 tw-pt-4">
                        <div class="alert alert-warning tw-mb-0 tw-flex tw-flex-col sm:tw-flex-row tw-gap-3 tw-justify-between tw-items-start sm:tw-items-center">
                            <div>
                                <strong><?= _l('ticket_close_request_pending_heading'); ?></strong>
                                <div class="tw-text-sm tw-text-neutral-700">
                                    <?= e($closeRequestDetail); ?>
                                    <?php if (!empty($closeRequestContext)) { ?>
                                    <div class="tw-mt-1 tw-text-neutral-700">
                                        <?= e($closeRequestContext); ?>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php if ($closeRequestCanAct) { ?>
                            <div class="tw-flex tw-gap-2 tw-flex-shrink-0">
                                <a href="<?= admin_url('tickets/close_request_action/' . $ticket->ticketid . '/approve'); ?>" class="btn btn-success btn-sm">
                                    <?= _l('ticket_close_request_button_approve'); ?>
                                </a>
                                <a href="<?= admin_url('tickets/close_request_action/' . $ticket->ticketid . '/reopen'); ?>" class="btn btn-danger btn-sm">
                                    <?= _l('ticket_close_request_button_reopen'); ?>
                                </a>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
<?php } ?>
<?php if ($reopenRequestBanner) { ?>
                    <div class="tw-px-6 tw-pt-4">
                        <div class="alert alert-info tw-mb-0 tw-flex tw-flex-col sm:tw-flex-row tw-gap-3 tw-justify-between tw-items-start sm:tw-items-center">
                            <div>
                                <strong><?= _l('ticket_reopen_request_pending_heading'); ?></strong>
                                <div class="tw-text-sm tw-text-neutral-700">
                                    <?= e($reopenRequestDetail); ?>
                                    <?php if (!empty($reopenRequestContext)) { ?>
                                    <div class="tw-mt-1 tw-text-neutral-700">
                                        <?= e($reopenRequestContext); ?>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php if ($canHandleReopenRequest) { ?>
                            <div class="tw-flex tw-gap-2 tw-flex-shrink-0">
                                <a href="<?= admin_url('tickets/reopen_request_action/' . $ticket->ticketid . '/approve'); ?>" class="btn btn-success btn-sm">
                                    <?= _l('ticket_reopen_request_button_approve'); ?>
                                </a>
                                <a href="<?= admin_url('tickets/reopen_request_action/' . $ticket->ticketid . '/decline'); ?>" class="btn btn-danger btn-sm">
                                    <?= _l('ticket_reopen_request_button_decline'); ?>
                                </a>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
<?php } ?>
                    <div class="tw-pb-8 tw-pt-4 tw-px-6">
                        <div class="!tw-space-y-3 tw-mb-6">
                            <div
                                class="alert alert-warning staff_replying_notice<?= ($ticket->staff_id_replying === null || $ticket->staff_id_replying === get_staff_user_id()) ? ' hide' : '' ?>">
                                <?php if ($ticket->staff_id_replying !== null && $ticket->staff_id_replying !== get_staff_user_id()) { ?>
                                <p class="tw-font-medium">
                                    <?= e(_l('staff_is_currently_replying', get_staff_full_name($ticket->staff_id_replying))); ?>
                                </p>
                                <?php } ?>
                            </div>

                        <?php if (count($merged_tickets) > 0) { ?>
                        <div class="alert alert-info">
                                <h4 class="alert-title">
                                    <?= _l('ticket_merged_tickets_header', count($merged_tickets)) ?>
                                </h4>
                                <ul>
                                    <?php foreach ($merged_tickets as $merged_ticket) { ?>
                                    <a href="<?= admin_url('tickets/ticket/' . ($merged_ticket['ticket_number'] ?: $merged_ticket['ticketid'])) ?>"
                                        class="alert-link">
                                        #<?= $merged_ticket['ticket_number'] ?: $merged_ticket['ticketid'] ?>
                                        -
                                        <?= e($merged_ticket['subject']) ?>
                                    </a>
                                    <?php } ?>
                                </ul>
                            </div>
                        <?php } ?>

                        <?php if (isset($pending_reassignment) && $pending_reassignment) { ?>
                        <div class="alert alert-warning" role="alert">
                            <div class="tw-flex tw-items-center tw-justify-between">
                                <div>
                                    <strong>Reassignment Pending:</strong>
                                    Ticket reassignment is pending
                                    <?php if ((int)($pending_reassignment->to_assigned ?? 0) === (int) get_staff_user_id()) { ?>
                                        for you to accept.
                                    <?php } else { ?>
                                        approval from the target assignee.
                                    <?php } ?>
                                    <?php if (!empty($pending_reassignment->expires_at) && $pending_reassignment->expires_at !== '0000-00-00 00:00:00') { ?>
                                        <div class="tw-mt-1 tw-text-xs tw-text-neutral-700">
                                            <?= _l('ticket_reassign_pending_expires_on', _dt($pending_reassignment->expires_at)); ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="tw-flex tw-gap-2">
                                    <?php if ((int)($pending_reassignment->to_assigned ?? 0) === (int) get_staff_user_id()) { ?>
                                        <a class="btn btn-success btn-sm" href="<?= admin_url('tickets/reassign_accept/' . (int)$pending_reassignment->id); ?>">Accept</a>
                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#reassignRejectModal">Decline</button>
                                    <?php } ?>
                                    <?php if ((int)($pending_reassignment->created_by ?? 0) === (int) get_staff_user_id()) { ?>
                                        <a class="btn btn-warning btn-sm" href="<?= admin_url('tickets/reassign_cancel/' . $ticket->ticketid); ?>" onclick="return confirm('Are you sure you want to cancel this reassignment request?')">Cancel Request</a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if (isset($pending_reassignment) && $pending_reassignment && (int)$pending_reassignment->to_assigned === (int) get_staff_user_id()) { ?>
                        <div class="modal fade" id="reassignRejectModal" tabindex="-1" role="dialog" aria-labelledby="reassignRejectLabel">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="reassignRejectLabel">Decline Reassignment</h4>
                              </div>
                              <form method="post" action="<?= admin_url('tickets/reassign_reject/' . (int)$pending_reassignment->id); ?>">
                              <div class="modal-body">
                                <div class="form-group">
                                  <label>Remarks (required)</label>
                                  <textarea class="form-control" name="remarks" rows="4" required placeholder="Please provide reason"></textarea>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-danger">Submit</button>
                              </div>
                              </form>
                            </div>
                          </div>
                        </div>
                        <?php } ?>

                            <?php if ($ticket->merged_ticket_id !== null) { ?>
                            <div class="alert alert-info" role="alert">
                                <div class="tw-flex tw-justify-between tw-items-center">
                                    <p class="tw-font-semibold tw-mb-0">
                                        <?= _l('ticket_merged_notice'); ?>:
                                        <?= e($ticket->merged_ticket_id); ?>
                                    </p>
                                    <a href="<?= admin_url('tickets/ticket/' . $ticket->merged_ticket_id); ?>"
                                        class="alert-link">
                                        <?= _l('view_primary_ticket'); ?>
                                    </a>
                                </div>
                            </div>
                            <?php } ?>
                        </div>

                        <div class="horizontal-scrollable-tabs tw-mb-3">
                            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                            <div class="horizontal-tabs">
                                <ul class="nav nav-tabs nav-tabs-segmented nav-tabs-horizontal tw-mb-2" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#addreply" aria-controls="addreply" role="tab" data-toggle="tab">
                                            <i class="fa-solid fa-reply menu-icon"></i>
                                            <?= _l('ticket_single_add_reply'); ?>
                                        </a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#note" aria-controls="note" role="tab" data-toggle="tab">
                                            <i class="fa-regular fa-note-sticky menu-icon"></i>
                                            <?= _l('ticket_single_add_note'); ?>
                                        </a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#tab_reminders"
                                            onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?= $ticket->ticketid; ?> + '/' + 'ticket', undefined, undefined, undefined,[1,'asc']); return false;"
                                            aria-controls="tab_reminders" role="tab" data-toggle="tab">
                                            <i class="fa-regular fa-bell menu-icon"></i>
                                            <?= _l('ticket_reminders'); ?>
                                            <?php
                                                 $total_reminders = total_rows(
                                                     db_prefix() . 'reminders',
                                                     [
                                                         'isnotified' => 0,
                                                         'staff'      => get_staff_user_id(),
                                                         'rel_type'   => 'ticket',
                                                         'rel_id'     => $ticket->ticketid,
                                                     ]
                                                 ); ?>

                                            <?php if ($total_reminders > 0) { ?>
                                            <span class="badge">
                                                <?= $total_reminders; ?>
                                            </span>
                                            <?php } ?>
                                        </a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#othertickets" onclick="init_table_tickets(true);"
                                            aria-controls="othertickets" role="tab" data-toggle="tab">
                                            <i class="fa-regular fa-life-ring menu-icon"></i>
                                            <?= _l('ticket_single_other_user_tickets'); ?>
                                        </a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#tasks"
                                            onclick="init_rel_tasks_table(<?= e($ticket->ticketid); ?>,'ticket'); return false;"
                                            aria-controls="tasks" role="tab" data-toggle="tab">
                                            <i class="fa-regular fa-circle-check menu-icon"></i>
                                            <?= _l('tasks'); ?>
                                        </a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#logs" aria-controls="logs" role="tab" data-toggle="tab">
                                            <i class="fa-regular fa-list-alt menu-icon"></i>
                                            <?= _l('logs'); ?>
                                        </a>
                                    </li>
                                    <?php if ($canViewSLA) { ?>
                                    <li role="presentation">
                                        <a href="#sla" aria-controls="sla" role="tab" data-toggle="tab">
                                            <i class="fa-solid fa-file-contract menu-icon"></i>
                                            SLA
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php do_action_deprecated('add_single_ticket_tab_menu_item', $ticket, '3.0.7', 'after_admin_single_ticket_tab_menu_last_item'); ?>
                                    <?php hooks()->do_action('after_admin_single_ticket_tab_menu_last_item', $ticket); ?>
                                </ul>
                            </div>
                        </div>

                        <div class="panel_s">
                            <div class="panel-body">
                                <div class="tab-content">
                                    <?php $this->load->view('admin/tickets/partials/ticket-tabpanel-add-reply'); ?>
                                    <?php $this->load->view('admin/tickets/partials/ticket-tabpanel-notes'); ?>
                                    <?php $this->load->view('admin/tickets/partials/ticket-tabpanel-reminders'); ?>
                                    <?php $this->load->view('admin/tickets/partials/ticket-tabpanel-other-tickets'); ?>
                                    <?php $this->load->view('admin/tickets/partials/ticket-tabpanel-tasks'); ?>
                                    <?php $this->load->view('admin/tickets/partials/ticket-tabpanel-logs'); ?>
                                    <?php if ($canViewSLA) { ?>
                                    <?php $this->load->view('admin/tickets/partials/ticket-tabpanel-sla'); ?>
                                    <?php } ?>

                                    <?php hooks()->do_action('after_admin_single_ticket_tab_menu_last_content', $ticket); ?>
                                </div>
                            </div>
                        </div>

                        <h4 class="tw-mt-6 tw-mb-4 tw-font-bold tw-text-lg">
                            <?= _l('ticket_request_history'); ?>
                        </h4>

                        <?php $this->load->view('admin/tickets/partials/ticket-history-message'); ?>

                        <h5 class="tw-mt-5 tw-mb-3 tw-font-semibold tw-text-base"><?= _l('ticket_replies'); ?></h5>
                        <?php $this->load->view('admin/tickets/partials/ticket-history-replies'); ?>
                    </div>
                </div>
            </div>
            <div class="tw-relative" style="flex:0 0 320px;">
                <div class="tw-px-6 sm:tw-px-0">
                    <div class="ticket-right-column tw-h-full tw-bg-white tw-border ltr:sm:tw-border-l sm:tw-border-r-0 sm:tw-border-y-0 tw-border-solid tw-border-neutral-300 tw-rounded-md sm:tw-rounded-none rtl:sm:tw-border-r fixed-ticket-sidebar"
                        id="ticketDetails">
                        <div class="ticket-right-header tw-py-4 tw-px-6 tw-bg-gradient-to-r tw-from-neutral-50 tw-to-white tw-border-b tw-border-solid tw-border-neutral-300">
                            <div class="sm:tw-flex sm:tw-items-center sm:tw-justify-between sm:tw-space-x-4 rtl:tw-space-x-reverse" id="ticketRightInformation">
                                <div class="sm:tw-flex sm:tw-items-center sm:tw-space-x-3 tw-mb-2 sm:tw-mb-0 rtl:tw-space-x-reverse">
                                    <h4 class="tw-font-bold tw-text-lg tw-my-0 sm:tw-truncate sm:tw-max-w-[240px] tw-flex tw-items-center tw-gap-x-2">
                                        <?= _l('clients_single_ticket_information_heading'); ?>
                                    </h4>
                                </div>

                                <div class="tw-space-x-4 tw-inline-flex tw-items-center rtl:tw-space-x-reverse">
                                    <a href="#" class="btn btn-primary btn-sm save_changes_settings_single_ticket<?php
                                        $is_owner = isset($ticket->assigned) && (int)$ticket->assigned === (int)get_staff_user_id();
                                        if (!is_admin() && !$is_owner) echo ' disabled';
                                    ?>">
                                        <?= _l('submit'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="ticket-right-body">
                            <div class="tw-py-4 tw-px-6">
                                <?php if ($ticket->project_id) { ?>
                                <p class="tw-text-base tw-font-medium tw-mb-6">
                                    <?= _l('ticket_linked_to_project', '<a href="' . admin_url('projects/view/' . $ticket->project_id) . '">' . e(get_project_name_by_id($ticket->project_id)) . '</a>'); ?>
                                </p>
                                <?php } ?>
                                <?php $this->load->view('admin/tickets/partials/ticket-settings'); ?>
                            </div>
                        </div> <!-- end body -->
                    </div>
                </div>
            </div>
            <?php if (count($ticket_replies) > 1) { ?>
            <a href="#top" id="toplink" style="display:none;">↑</a>
            <a href="#bot" id="botlink" style="display:none;">↓</a>
            <?php } ?>
        </div>
    </div>
</div>
<?php if (can_staff_edit_ticket_message()) {?>
<!-- Edit Ticket Messsage Modal -->
<div class="modal fade" id="ticket-message" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <?= form_open(admin_url('tickets/edit_message')); ?>
        <div class="modal-content">
            <div id="edit-ticket-message-additional"></div>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <?= _l('ticket_message_edit'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <?= render_textarea('data', '', '', [], [], '', 'tinymce-ticket-edit'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                    data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit"
                    class="btn btn-primary"><?= _l('submit'); ?></button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>
<?php } ?>
<?php $this->load->view('admin/tickets/partials/ai_ticket_modals'); ?>

<script>
    var _ticket_message;
</script>
<?php $this->load->view('admin/tickets/services/service'); ?>
<style>
    /* Sidebar fixed and internal scroll */
    .fixed-ticket-sidebar {
        position: fixed;
        top: 58px; /* header height */
        right: 0;
        width: 320px;
        max-width: 320px;
        bottom: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        z-index: 5;
    }

    .fixed-ticket-sidebar .ticket-right-header {
        flex: 0 0 auto;
    }

    .fixed-ticket-sidebar .ticket-right-body {
        flex: 1 1 auto;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        /* Hide scrollbar visually but keep scroll functional */
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE 10+ */
    }

    /* WebKit browsers */
    .fixed-ticket-sidebar .ticket-right-body::-webkit-scrollbar {
        width: 0;
        height: 0;
    }

    .ticket-left-scroll {
        overflow-y: auto;
        max-height: calc(100vh - 61px);
        width: 100%;
        padding-right: 1.5rem;
        padding-bottom: 2rem;
        box-sizing: border-box;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Firefox */
    }

    .ticket-left-scroll::-webkit-scrollbar {
        width: 0; /* WebKit */
        height: 0;
    }

    .ticket-left-scroll {
        -ms-overflow-style: none; /* IE 10+ */
    }

    /* Close tab header / content area */
    .ticket-left-content {
        padding-right: 0;
        flex: 1 1 calc(100% - 320px);
        max-width: calc(100% - 320px);
    }

    /* Add spacing to left content to avoid overlap with fixed sidebar */
    .ticket-left-content { padding-right: 0; }

    @media (max-width: 991px) {
        .fixed-ticket-sidebar { position: static; width: auto; max-width: none; bottom: auto; }
        .ticket-left-content { flex: 1 1 100%; max-width: 100%; padding-right: 0; }
        .ticket-left-scroll { width: 100%; max-height: none; overflow-y: visible; padding-right: 0; padding-bottom: 1.5rem; }
    }
</style>
<?php init_tail(); ?>
<?php hooks()->do_action('ticket_admin_single_page_loaded', $ticket); ?>
<script>
    $(function() {
        var content = document.getElementById('ticketDetails');
        var header = document.getElementById('header');

        function makeTicketDetailsSticky() {
            var scrolledPixels = getHeaderScrolledPixels();

            if (scrolledPixels > 0) {
                content.style.top = ((header.clientHeight + 1) - scrolledPixels) + 'px'
            } else {
                content.style.top = '';
            }
        }

        function setTicketSubjectMaxWidthBasedOnFreeSpace() {
            const parent = document.getElementById(
                'ticketLeftInformation');

            const children = parent.querySelectorAll('div');

            if (children.length < 3) {
                console.error('There must be at least three child divs.');
                return;
            }

            const firstSibling = children[1];
            const targetElement = children[0].firstChild.nextSibling;
            const lastSibling = children[2];

            const parentWidth = parent.clientWidth;

            // Calculate the width of the siblings
            const firstSiblingWidth = firstSibling.offsetWidth + getTotalHorizontalMargin(firstSibling);
            const lastSiblingWidth = lastSibling.offsetWidth + getTotalHorizontalMargin(lastSibling);

            // Calculate the max width for the target element
            const maxWidth = parentWidth - firstSiblingWidth - lastSiblingWidth;

            targetElement.style.maxWidth = `${maxWidth}px`;
        }

        if (!is_mobile()) {
            setTicketSubjectMaxWidthBasedOnFreeSpace()
            makeTicketDetailsSticky()
            window.addEventListener('resize', setTicketSubjectMaxWidthBasedOnFreeSpace);

            window.addEventListener('scroll', makeTicketDetailsSticky);
        }

        $('#single-ticket-form').appFormValidator();

        init_ajax_search('contact', '#contactid.ajax-search', {
            tickets_contacts: true
        });

        init_ajax_search('project', 'select[name="project_id"]', {
            customer_id: function() {
                return $('input[name="userid"]').val();
            }
        });

        $('body').on('shown.bs.modal', '#_task_modal', function() {
            if (typeof(_ticket_message) != 'undefined') {
                // Init the task description editor
                if (!is_mobile()) {
                    $(this).find('#description').click();
                } else {
                    $(this).find('#description').focus();
                }
                setTimeout(function() {
                    var editor = tinymce.get('description');
                    if (editor) {
                        editor.setContent(_ticket_message);
                    }
                    $('#_task_modal input[name="name"]').val($('#ticket_subject').text()
                        .replace(/\s+/g, ' ').trim());
                }, 100);
            }
        });

        var editorMessage = tinymce.get('message');

        if (typeof(editorMessage) != 'undefined') {
            var firstTypeCheckPerformed = false;

            editorMessage.on('change', function() {
                if (!firstTypeCheckPerformed) {
                    // make AJAX Request
                    $.get(admin_url +
                        'tickets/check_staff_replying/<?= e($ticket->ticketid); ?>',
                        function(result) {
                            var data = JSON.parse(result)
                            if (data.is_other_staff_replying === true || data
                                .is_other_staff_replying === 'true') {
                                $('.staff_replying_notice').html('<p>' + data.message +
                                    '</p>');
                                $('.staff_replying_notice').removeClass('hide');
                            } else {
                                $('.staff_replying_notice').addClass('hide');
                            }
                        });

                    firstTypeCheckPerformed = true;
                }

                $.post(admin_url +
                    'tickets/update_staff_replying/<?= e($ticket->ticketid); ?>/<?= get_staff_user_id()?>'
                );
            });

            $(document).on('pagehide, beforeunload', function() {
                $.post(admin_url +
                    'tickets/update_staff_replying/<?= e($ticket->ticketid); ?>'
                );
            })

            $(document).on('visibilitychange', function() {
                if (document.visibilityState === 'visible' || (editorMessage.getContent()
                        .trim() !=
                        ''))
                    return;
                $.post(admin_url +
                    'tickets/update_staff_replying/<?= e($ticket->ticketid); ?>'
                );
            })
        }
    });


    var Ticket_message_editor;
    var edit_ticket_message_additional = $('#edit-ticket-message-additional');

    function edit_ticket_message(id, type) {
        edit_ticket_message_additional.empty();
        // type is either ticket or reply
        _ticket_message = $('[data-' + type + '-id="' + id + '"]').html();
        init_ticket_edit_editor();
        $('#ticket-message').modal('show');
        setTimeout(function() {
            tinyMCE.activeEditor.setContent(_ticket_message);
        }, 1000)
        edit_ticket_message_additional.append(hidden_input('type', type));
        edit_ticket_message_additional.append(hidden_input('id', id));
        edit_ticket_message_additional.append(hidden_input('main_ticket', $('input[name="ticketid"]').val()));
    }

    function init_ticket_edit_editor() {
        if (typeof(Ticket_message_editor) !== 'undefined') {
            return true;
        }
        Ticket_message_editor = init_editor('.tinymce-ticket-edit');
    }

    <?php if (staff_can('create', 'tasks')) { ?>
    function convert_ticket_to_task(id, type) {
        var new_task_url = admin_url +
            'tasks/create?rel_id=<?= e($ticket->ticketid); ?>&rel_type=ticket&ticket_to_task=true';

        if (type === 'reply') {
            new_task_url += '&ticket_reply_id=' + encodeURIComponent(id);
        }

        window.open(new_task_url, '_blank');
    }
    <?php } ?>
</script>
</body>

</html>
