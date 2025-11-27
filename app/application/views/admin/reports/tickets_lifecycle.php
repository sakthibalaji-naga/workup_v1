<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .lifecycle-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
    }
    .lifecycle-stat-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px;
        background: #fff;
        box-shadow: 0 8px 16px rgba(15, 23, 42, 0.05);
        min-height: 150px;
    }
    .lifecycle-stat-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .lifecycle-stat-card__label {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #6b7280;
        font-weight: 600;
    }
    .lifecycle-stat-card__value {
        font-size: 28px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 6px;
    }
    .lifecycle-stat-card__meta {
        font-size: 13px;
        color: #6b7280;
    }
    .trend-chip {
        display: inline-flex;
        align-items: center;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(34, 197, 94, 0.15);
        color: #16a34a;
    }
    .trend-chip i {
        margin-right: 4px;
    }
    .trend-chip.trend-down {
        background: rgba(239, 68, 68, 0.15);
        color: #dc2626;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="no-margin"><?php echo _l('ticket_lifecycle_report'); ?></h4>
                                <hr class="hr-panel-heading" />
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <form method="POST" action="<?php echo admin_url('reports/tickets_lifecycle'); ?>" id="ticket-lifecycle-form" class="panel_s p-3">
                                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                                    <div class="row tw-items-end">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="from_date" class="control-label"><?php echo _l('from_date'); ?></label>
                                                <input type="date" name="from_date" id="from_date" class="form-control"
                                                       value="<?php echo $this->input->post('from_date'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="to_date" class="control-label"><?php echo _l('to_date'); ?></label>
                                                <input type="date" name="to_date" id="to_date" class="form-control"
                                                       value="<?php echo $this->input->post('to_date'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="division" class="control-label"><?php echo _l('Division'); ?></label>
                                                <select name="division[]" id="division" class="form-control selectpicker" multiple data-width="100%" data-actions-box="true" data-live-search="true" title="<?php echo _l('all'); ?>">
                                                    <?php foreach ($divisions as $div) { ?>
                                                        <option value="<?php echo $div['divisionid']; ?>" <?php echo in_array($div['divisionid'], $selected_divisions) ? 'selected' : ''; ?>>
                                                            <?php echo $div['name']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="department" class="control-label"><?php echo _l('department'); ?></label>
                                                <select name="department[]" id="department" class="form-control selectpicker" multiple data-width="100%" data-actions-box="true" data-live-search="true" title="<?php echo _l('all_departments'); ?>">
                                                    <?php foreach ($departments as $dept) { ?>
                                                        <option value="<?php echo $dept['departmentid']; ?>" <?php echo in_array($dept['departmentid'], $selected_departments) ? 'selected' : ''; ?>>
                                                            <?php echo $dept['name']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row tw-items-end">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="status" class="control-label"><?php echo _l('status'); ?></label>
                                                <select name="status[]" id="status" class="form-control selectpicker" multiple data-width="100%" data-actions-box="true" data-live-search="true" title="<?php echo _l('all_statuses'); ?>">
                                                    <?php foreach ($ticket_statuses as $status) { ?>
                                                        <option value="<?php echo $status['ticketstatusid']; ?>" <?php echo in_array($status['ticketstatusid'], $selected_statuses) ? 'selected' : ''; ?>>
                                                            <?php echo $status['name']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-9 text-right tw-space-x-2">
                                            <button type="submit" class="btn btn-primary"><?php echo _l('filter'); ?></button>
                                            <button type="button" class="btn btn-default" id="clear-filters"><?php echo _l('clear'); ?></button>
                                            <?php if ($can_export_report) { ?>
                                                <button type="button" class="btn btn-success" id="export-tickets"><?php echo _l('export'); ?></button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Statistics Summary -->
                        <?php
                        $has_tickets = !empty($tickets);
                        $total_tickets = $has_tickets ? count($tickets) : 0;
                        $lifespan_values = $has_tickets ? array_filter(array_column($tickets, 'lifespan_hours'), function($value) {
                            return $value !== null;
                        }) : [];
                        $avg_lifespan = !empty($lifespan_values) ? array_sum($lifespan_values) / count($lifespan_values) : 0;
                        $response_values = $has_tickets ? array_filter(array_column($tickets, 'response_time_hours'), function($value) {
                            return $value !== null;
                        }) : [];
                        $avg_response_time = !empty($response_values) ? array_sum($response_values) / count($response_values) : 0;
                        $reopened_count = $has_tickets ? count(array_filter($tickets, function($t) { return $t['reopen_count'] > 0; })) : 0;
                        $closed_count = $has_tickets ? count(array_filter($tickets, function($t) { return !empty($t['closed_date']); })) : 0;
                        $lifespan_target_hours = 72;
                        $response_target_hours = 8;
                        $closed_percent = $total_tickets > 0 ? round(($closed_count / $total_tickets) * 100, 1) : 0;
                        $reopened_percent = $total_tickets > 0 ? round(($reopened_count / $total_tickets) * 100, 1) : 0;
                        $first_time_resolved = $total_tickets - $reopened_count;
                        $first_time_percent = $total_tickets > 0 ? round(($first_time_resolved / $total_tickets) * 100, 1) : 0;
                        $lifespan_trend = $lifespan_target_hours > 0 ? round((($lifespan_target_hours - $avg_lifespan) / $lifespan_target_hours) * 100, 1) : 0;
                        $response_trend = $response_target_hours > 0 ? round((($response_target_hours - $avg_response_time) / $response_target_hours) * 100, 1) : 0;
                        $lifespan_is_positive = $lifespan_trend >= 0;
                        $response_is_positive = $response_trend >= 0;
                        $reopened_is_positive = $reopened_percent == 0;
                        ?>
                        <div class="row mt-4 ticket-lifecycle-statistics <?php echo $has_tickets ? '' : 'hide'; ?>" id="ticket-lifecycle-statistics">
                            <div class="col-md-12">
                                <div class="panel_s">
                                <div class="panel-heading">
                                    <h4 class="panel-title"><?php echo _l('ticket_lifecycle_statistics'); ?></h4>
                                </div>
                                <div class="panel-body">
                                    <div class="lifecycle-stats-grid">
                                        <div class="lifecycle-stat-card" data-stat-card="total">
                                            <div class="lifecycle-stat-card__head">
                                                <span class="lifecycle-stat-card__label"><?php echo _l('total_tickets'); ?></span>
                                                <span class="trend-chip trend-up" data-stat-trend="total">
                                                    <i class="fa fa-arrow-up"></i>
                                                    <span data-stat-trend-value="total">+<?php echo $closed_percent; ?>%</span>
                                                </span>
                                            </div>
                                            <div class="lifecycle-stat-card__value" data-stat-value="total"><?php echo $total_tickets; ?></div>
                                            <div class="lifecycle-stat-card__meta" data-stat-meta="total"><?php echo sprintf('%s: %s', _l('closed_tickets'), $closed_count); ?></div>
                                        </div>
                                        <div class="lifecycle-stat-card" data-stat-card="lifespan">
                                            <div class="lifecycle-stat-card__head">
                                                <span class="lifecycle-stat-card__label"><?php echo _l('average_lifespan'); ?></span>
                                                <span class="trend-chip <?php echo $lifespan_is_positive ? 'trend-up' : 'trend-down'; ?>" data-stat-trend="lifespan">
                                                    <i class="fa <?php echo $lifespan_is_positive ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                                                    <span data-stat-trend-value="lifespan"><?php echo ($lifespan_trend >= 0 ? '+' : '') . $lifespan_trend; ?>%</span>
                                                </span>
                                            </div>
                                            <div class="lifecycle-stat-card__value" data-stat-value="lifespan"><?php echo number_format($avg_lifespan, 1); ?>h</div>
                                            <div class="lifecycle-stat-card__meta" data-stat-meta="lifespan"><?php echo 'Target: ' . $lifespan_target_hours . 'h'; ?></div>
                                        </div>
                                        <div class="lifecycle-stat-card" data-stat-card="response">
                                            <div class="lifecycle-stat-card__head">
                                                <span class="lifecycle-stat-card__label"><?php echo _l('average_response_time'); ?></span>
                                                <span class="trend-chip <?php echo $response_is_positive ? 'trend-up' : 'trend-down'; ?>" data-stat-trend="response">
                                                    <i class="fa <?php echo $response_is_positive ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                                                    <span data-stat-trend-value="response"><?php echo ($response_trend >= 0 ? '+' : '') . $response_trend; ?>%</span>
                                                </span>
                                            </div>
                                            <div class="lifecycle-stat-card__value" data-stat-value="response"><?php echo number_format($avg_response_time, 1); ?>h</div>
                                            <div class="lifecycle-stat-card__meta" data-stat-meta="response"><?php echo 'Target: ' . $response_target_hours . 'h'; ?></div>
                                        </div>
                                        <div class="lifecycle-stat-card" data-stat-card="closed">
                                            <div class="lifecycle-stat-card__head">
                                                <span class="lifecycle-stat-card__label"><?php echo _l('closed_tickets'); ?></span>
                                                <span class="trend-chip trend-up" data-stat-trend="closed">
                                                    <i class="fa fa-arrow-up"></i>
                                                    <span data-stat-trend-value="closed">+<?php echo $closed_percent; ?>%</span>
                                                </span>
                                            </div>
                                            <div class="lifecycle-stat-card__value" data-stat-value="closed"><?php echo $closed_count; ?> (<?php echo $closed_percent; ?>%)</div>
                                            <div class="lifecycle-stat-card__meta" data-stat-meta="closed"><?php echo sprintf('%s: %s', _l('total_tickets'), $total_tickets); ?></div>
                                        </div>
                                        <div class="lifecycle-stat-card" data-stat-card="reopened">
                                            <div class="lifecycle-stat-card__head">
                                                <span class="lifecycle-stat-card__label"><?php echo _l('reopened_tickets'); ?></span>
                                                <span class="trend-chip <?php echo $reopened_is_positive ? 'trend-up' : 'trend-down'; ?>" data-stat-trend="reopened">
                                                    <i class="fa <?php echo $reopened_is_positive ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                                                    <span data-stat-trend-value="reopened"><?php echo $reopened_percent > 0 ? '-' . $reopened_percent : '+0'; ?>%</span>
                                                </span>
                                            </div>
                                            <div class="lifecycle-stat-card__value" data-stat-value="reopened"><?php echo $reopened_count; ?> (<?php echo $reopened_percent; ?>%)</div>
                                            <div class="lifecycle-stat-card__meta" data-stat-meta="reopened"><?php echo sprintf('%s: %s', _l('total_tickets'), $total_tickets); ?></div>
                                        </div>
                                        <div class="lifecycle-stat-card" data-stat-card="first">
                                            <div class="lifecycle-stat-card__head">
                                                <span class="lifecycle-stat-card__label"><?php echo _l('first_time_resolved'); ?></span>
                                                <span class="trend-chip trend-up" data-stat-trend="first">
                                                    <i class="fa fa-arrow-up"></i>
                                                    <span data-stat-trend-value="first">+<?php echo $first_time_percent; ?>%</span>
                                                </span>
                                            </div>
                                            <div class="lifecycle-stat-card__value" data-stat-value="first"><?php echo $first_time_resolved; ?> (<?php echo $first_time_percent; ?>%)</div>
                                            <div class="lifecycle-stat-card__meta" data-stat-meta="first"><?php echo sprintf('%s: %s', _l('total_tickets'), $total_tickets); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- Data Table -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="ticket-lifecycle-table" class="table table-striped table-bordered dt-table">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('ticket'); ?> #</th>
                                                <th><?php echo _l('subject'); ?></th>
                                                <th><?php echo _l('created'); ?></th>
                                                <th><?php echo _l('created_by'); ?></th>
                                                <th><?php echo _l('assigned'); ?></th>
                                                <th><?php echo _l('handlers'); ?></th>
                                                <th><?php echo _l('department'); ?></th>
                                                <th><?php echo _l('Division'); ?></th>
                                                <th><?php echo _l('service'); ?></th>
                                                <th><?php echo _l('priority'); ?></th>
                                                <th><?php echo _l('status'); ?></th>
                                                <th><?php echo _l('first_reply'); ?></th>
                                                <th><?php echo _l('response_time'); ?></th>
                                                <th><?php echo _l('fixed_at'); ?></th>
                                                <th><?php echo _l('closed_at'); ?></th>
                                                <th><?php echo _l('lifespan'); ?></th>
                                                <th><?php echo _l('reopened'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tickets as $ticket) { ?>
                                            <tr>
                                                <td><a href="<?php echo admin_url('tickets/ticket/' . $ticket['ticketid']); ?>" target="_blank">#<?php echo $ticket['ticket_number']; ?></a></td>
                                                <td><a href="<?php echo admin_url('tickets/ticket/' . $ticket['ticketid']); ?>" target="_blank"><?php echo $ticket['subject']; ?></a></td>
                                                <td><?php echo $ticket['created_date_formatted']; ?></td>
                                                <td><?php echo $ticket['created_by_firstname'] . ' ' . $ticket['created_by_lastname']; ?></td>
                                                <td><?php echo ($ticket['assigned_to_firstname'] && $ticket['assigned_to_lastname']) ?
                                                    $ticket['assigned_to_firstname'] . ' ' . $ticket['assigned_to_lastname'] : 'Unassigned'; ?></td>
                                                <td><?php echo $ticket['handlers_list'] ?: 'No handlers'; ?></td>
                                                <td><?php echo $ticket['department_name'] ?: 'No department'; ?></td>
                                                <td><?php echo $ticket['division_name'] ?: 'No division'; ?></td>
                                                <td><?php echo $ticket['service_name'] ?: 'No service'; ?></td>
                                                <td><?php echo $ticket['priority_name'] ?: 'No priority'; ?></td>
                                                <td><?php echo $ticket['status_name'] ?: 'Unknown'; ?></td>
                                                <td><?php echo $ticket['first_staff_reply_date_formatted'] ?: 'No reply'; ?></td>
                                                <td><?php echo $ticket['response_time_hours'] ? $ticket['response_time_hours'] . 'h' : 'N/A'; ?></td>
                                                <td><?php echo $ticket['fixed_date_formatted'] ?: 'Not fixed'; ?></td>
                                                <td><?php echo $ticket['closed_date_formatted'] ?: 'Open'; ?></td>
                                                <td><?php echo $ticket['lifespan_hours'] ? $ticket['lifespan_hours'] . 'h' : 'N/A'; ?></td>
                                                <td><?php echo $ticket['reopen_count'] > 0 ? 'Yes (' . $ticket['reopen_count'] . ')' : 'No'; ?></td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var canExport = <?php echo $can_export_report ? 'true' : 'false'; ?>;
    var tableSelector = '#ticket-lifecycle-table';
    var table;
    var lifecycleLifespanTarget = <?php echo $lifespan_target_hours; ?>;
    var lifecycleResponseTarget = <?php echo $response_target_hours; ?>;
    var lifecycleText = {
        closed: "<?php echo _l('closed_tickets'); ?>",
        reopened: "<?php echo _l('reopened_tickets'); ?>",
        first: "<?php echo _l('first_time_resolved'); ?>",
        total: "<?php echo _l('total_tickets'); ?>",
        targetLabel: "Target"
    };

    function formatTrendValue(value) {
        var num = parseFloat(value);
        if (!isFinite(num)) {
            num = 0;
        }
        var prefix = num > 0 ? '+' : '';
        return prefix + num.toFixed(1) + '%';
    }

    function setStatCard(key, valueText, trendText, metaText, isPositive) {
        var container = $('#ticket-lifecycle-statistics');
        if (!container.length) {
            return;
        }
        var valueEl = container.find('[data-stat-value="' + key + '"]');
        if (!valueEl.length) {
            return;
        }
        valueEl.text(valueText);
        container.find('[data-stat-meta="' + key + '"]').text(metaText);
        var trendEl = container.find('[data-stat-trend="' + key + '"]');
        var icon = trendEl.find('i');
        trendEl.removeClass('trend-up trend-down').addClass(isPositive ? 'trend-up' : 'trend-down');
        icon.removeClass('fa-arrow-up fa-arrow-down').addClass(isPositive ? 'fa-arrow-up' : 'fa-arrow-down');
        container.find('[data-stat-trend-value="' + key + '"]').text(trendText);
    }

    function updateLifecycleStats(stats) {
        var container = $('#ticket-lifecycle-statistics');
        if (!container.length) {
            return;
        }
        if (stats.totalTickets > 0) {
            container.removeClass('hide');
        } else {
            container.addClass('hide');
        }
        setStatCard('total', stats.totalTickets, '+' + stats.closedPercent + '%', lifecycleText.closed + ': ' + stats.closedCount, true);
        setStatCard('lifespan', stats.avgLifespan.toFixed(1) + 'h', formatTrendValue(stats.lifespanTrend), lifecycleText.targetLabel + ': ' + lifecycleLifespanTarget + 'h', stats.lifespanTrend >= 0);
        setStatCard('response', stats.avgResponseTime.toFixed(1) + 'h', formatTrendValue(stats.responseTrend), lifecycleText.targetLabel + ': ' + lifecycleResponseTarget + 'h', stats.responseTrend >= 0);
        setStatCard('closed', stats.closedCount + ' (' + stats.closedPercent + '%)', '+' + stats.closedPercent + '%', lifecycleText.total + ': ' + stats.totalTickets, true);
        var reopenedTrendDisplay = stats.reopenedPercent > 0 ? '-' + stats.reopenedPercent + '%' : '+0%';
        setStatCard('reopened', stats.reopenedCount + ' (' + stats.reopenedPercent + '%)', reopenedTrendDisplay, lifecycleText.total + ': ' + stats.totalTickets, stats.reopenedPercent === 0);
        setStatCard('first', stats.firstTimeCount + ' (' + stats.firstTimePercent + '%)', '+' + stats.firstTimePercent + '%', lifecycleText.total + ': ' + stats.totalTickets, true);
    }

    // Initialize DataTable
    if ($(tableSelector).length > 0) {
        var inlineOptions = {
            supportsButtons: canExport,
            supportsLoading: true,
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "<?php echo _l('all'); ?>"]
            ],
            order: [[0, 'desc']]
        };

        if (typeof appDataTableInline === 'function') {
            appDataTableInline(tableSelector, inlineOptions);
            table = $(tableSelector).DataTable();
        } else {
            // Fallback if helper is not available
            var domTemplate = "<'row'<'col-md-6'l{buttons}><'col-md-6'f>>rt<'row'<'col-md-4'i><'col-md-8 dataTables_paging'p>>";
            var domWithoutButtons = "<'row'<'col-md-6'l><'col-md-6'f>>rt<'row'<'col-md-4'i><'col-md-8 dataTables_paging'p>>";
            var dtConfig = {
                order: [[0, "desc"]],
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "<?php echo _l('all'); ?>"]
                ],
                paging: true,
                searching: true,
                language: {
                    emptyTable: "<?php echo _l('no_data_available'); ?>"
                },
                dom: canExport ? domTemplate.replace('{buttons}', 'B') : domWithoutButtons
            };

            if (canExport) {
                if (typeof get_datatable_buttons === 'function') {
                    dtConfig.buttons = get_datatable_buttons(tableSelector);
                } else {
                    dtConfig.buttons = [
                        { extend: 'excel', title: 'tickets_lifecycle' },
                        { extend: 'csv', title: 'tickets_lifecycle' }
                    ];
                }
            }

            table = $(tableSelector).DataTable(dtConfig);
        }

        if (canExport && table) {
            $('#export-tickets').on('click', function() {
                if (table.button('.buttons-collection').length) {
                    table.button('.buttons-collection').trigger();
                } else if (table.button('.buttons-excel').length) {
                    table.button('.buttons-excel').trigger();
                } else if (table.button(0).length) {
                    table.button(0).trigger();
                }
            });
        }
    }

    // Form submission via AJAX
    $('#ticket-lifecycle-form').on('submit', function(e) {
        e.preventDefault();

        if (!table) {
            console.error('DataTable instance not initialized.');
            return false;
        }

        var formData = $(this).serialize();
        var tableBody = $('#ticket-lifecycle-table tbody');

        // Show loading
        tableBody.html('<tr><td colspan="17" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');

        $.ajax({
            url: '<?php echo admin_url('reports/tickets_lifecycle'); ?>',
            type: 'POST',
            data: formData + '&is_ajax_request=1',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            dataType: 'json',
            success: function(response) {
                if (response.data) {
                    table.clear().draw();
                    table.rows.add(response.data).draw();

                    var totalTickets = response.data.length;
                    var avgLifespan = 0;
                    var avgResponseTime = 0;
                    var reopenedCount = 0;
                    var closedCount = 0;
                    var validLifespanCount = 0;
                    var validResponseCount = 0;

                    response.data.forEach(function(ticket) {
                        if (ticket[15] && ticket[15].replace('h', '') !== 'N/A') {
                            avgLifespan += parseFloat(ticket[15].replace('h', ''));
                            validLifespanCount++;
                        }
                        if (ticket[12] && ticket[12] !== 'N/A') {
                            avgResponseTime += parseFloat(ticket[12].replace('h', ''));
                            validResponseCount++;
                        }
                        if (ticket[16] && ticket[16].startsWith('Yes')) {
                            reopenedCount++;
                        }
                        if (ticket[14] && ticket[14] !== 'Open') {
                            closedCount++;
                        }
                    });

                    avgLifespan = validLifespanCount > 0 ? avgLifespan / validLifespanCount : 0;
                    avgResponseTime = validResponseCount > 0 ? avgResponseTime / validResponseCount : 0;

                    var closedPercent = totalTickets > 0 ? ((closedCount / totalTickets) * 100).toFixed(1) : 0;
                    var reopenedPercent = totalTickets > 0 ? ((reopenedCount / totalTickets) * 100).toFixed(1) : 0;
                    var firstTimeCount = totalTickets - reopenedCount;
                    var firstTimePercent = totalTickets > 0 ? ((firstTimeCount / totalTickets) * 100).toFixed(1) : 0;
                    var lifespanTrend = lifecycleLifespanTarget > 0 ? (((lifecycleLifespanTarget - avgLifespan) / lifecycleLifespanTarget) * 100).toFixed(1) : 0;
                    var responseTrend = lifecycleResponseTarget > 0 ? (((lifecycleResponseTarget - avgResponseTime) / lifecycleResponseTarget) * 100).toFixed(1) : 0;

                    updateLifecycleStats({
                        totalTickets: totalTickets,
                        closedCount: closedCount,
                        closedPercent: parseFloat(closedPercent),
                        reopenedCount: reopenedCount,
                        reopenedPercent: parseFloat(reopenedPercent),
                        firstTimeCount: firstTimeCount,
                        firstTimePercent: parseFloat(firstTimePercent),
                        avgLifespan: avgLifespan,
                        avgResponseTime: avgResponseTime,
                        lifespanTrend: parseFloat(lifespanTrend),
                        responseTrend: parseFloat(responseTrend)
                    });
                } else {
                    updateLifecycleStats({
                        totalTickets: 0,
                        closedCount: 0,
                        closedPercent: 0,
                        reopenedCount: 0,
                        reopenedPercent: 0,
                        firstTimeCount: 0,
                        firstTimePercent: 0,
                        avgLifespan: 0,
                        avgResponseTime: 0,
                        lifespanTrend: 0,
                        responseTrend: 0
                    });
                }
            },
            error: function(xhr, status, error) {
                tableBody.html('<tr><td colspan="17" class="text-center text-danger">Error loading data. Please refresh the page and try again. (Error: ' + xhr.status + ')</td></tr>');
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
                console.error('Status:', xhr.status);
            }
        });

        return false;
    });

    // Clear filters
    $('#clear-filters').on('click', function() {
        $('#from_date').val('');
        $('#to_date').val('');
        $('#division').selectpicker('deselectAll');
        $('#department').selectpicker('deselectAll');
        $('#status').selectpicker('deselectAll');
        $('#ticket-lifecycle-form').submit();
    });
});
</script>

<?php init_tail(); ?>
