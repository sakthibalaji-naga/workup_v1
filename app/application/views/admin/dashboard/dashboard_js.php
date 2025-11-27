<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
var weekly_payments_statistics;
var monthly_payments_statistics;
var user_dashboard_visibility = <?php echo $user_dashboard_visibility; ?>;
$(function() {
    $("[data-container]").sortable({
        connectWith: "[data-container]",
        helper: 'clone',
        handle: '.widget-dragger',
        tolerance: 'pointer',
        forcePlaceholderSize: true,
        placeholder: 'placeholder-dashboard-widgets',
        start: function(event, ui) {
            $("body,#wrapper").addClass('noscroll');
            $('body').find('[data-container]').css('min-height', '20px');
        },
        stop: function(event, ui) {
            $("body,#wrapper").removeClass('noscroll');
            $('body').find('[data-container]').removeAttr('style');
        },
        update: function(event, ui) {
            if (this === ui.item.parent()[0]) {
                var data = {};
                $.each($("[data-container]"), function() {
                    var cId = $(this).attr('data-container');
                    data[cId] = $(this).sortable('toArray');
                    if (data[cId].length == 0) {
                        data[cId] = 'empty';
                    }
                });
                $.post(admin_url + 'staff/save_dashboard_widgets_order', data, "json");
            }
        }
    });

    // Read more for dashboard todo items
    $('.read-more').readmore({
        collapsedHeight: 150,
        moreLink: "<a href=\"#\"><?php echo _l('read_more'); ?></a>",
        lessLink: "<a href=\"#\"><?php echo _l('show_less'); ?></a>",
    });

    $('body').on('click', '#viewWidgetableArea', function(e) {
        e.preventDefault();

        if (!$(this).hasClass('preview')) {
            $(this).html("<?php echo _l('hide_widgetable_area'); ?>");
            $('[data-container]').append(
                '<div class="placeholder-dashboard-widgets pl-preview"></div>');
        } else {
            $(this).html("<?php echo _l('view_widgetable_area'); ?>");
            $('[data-container]').find('.pl-preview').remove();
        }

        $('[data-container]').toggleClass('preview-widgets');
        $(this).toggleClass('preview');
    });

    var $widgets = $('.widget');
    var widgetsOptionsHTML = '';
    widgetsOptionsHTML += '<div id="dashboard-options">';
    widgetsOptionsHTML +=
        "<div class=\"tw-flex tw-space-x-4 tw-items-center\"><h4 class='tw-font-medium tw-text-neutral-600 tw-text-lg'><i class='fa-regular fa-circle-question' data-toggle='tooltip' data-placement=\"bottom\" data-title=\"<?php echo _l('widgets_visibility_help_text'); ?>\"></i> <?php echo _l('widgets'); ?></h4><a href=\"<?php echo admin_url('staff/reset_dashboard'); ?>\" class=\"tw-text-sm\"><?php echo _l('reset_dashboard'); ?></a>";

    widgetsOptionsHTML +=
        ' <a href=\"#\" id="viewWidgetableArea" class=\"tw-text-sm\"><?php echo _l('view_widgetable_area'); ?></a></div>';

    $.each($widgets, function() {
        var widget = $(this);
        var widgetOptionsHTML = '';
        if (widget.data('name') && widget.html().trim().length > 0) {
            widgetOptionsHTML += '<div class="checkbox">';
            var wID = widget.attr('id');
            wID = wID.split('widget-');
            wID = wID[wID.length - 1];
            var checked = ' ';
            var db_result = $.grep(user_dashboard_visibility, function(e) {
                return e.id == wID;
            });
            if (db_result.length >= 0) {
                // no options saved or really visible
                if (typeof(db_result[0]) == 'undefined' || db_result[0]['visible'] == 1) {
                    checked = ' checked ';
                }
            }
            widgetOptionsHTML += '<input type="checkbox" class="widget-visibility" value="' + wID +
                '"' + checked + 'id="widget_option_' + wID + '" name="dashboard_widgets[' + wID + ']">';
            widgetOptionsHTML += '<label for="widget_option_' + wID + '">' + widget.data('name') +
                '</label>';
            widgetOptionsHTML += '</div>';
        }
        widgetsOptionsHTML += widgetOptionsHTML;
    });

    $('.screen-options-area').append(widgetsOptionsHTML);
    $('body').find('#dashboard-options input.widget-visibility').on('change', function() {
        if ($(this).prop('checked') == false) {
            $('#widget-' + $(this).val()).addClass('hide');
        } else {
            $('#widget-' + $(this).val()).removeClass('hide');
        }

        var data = {};
        var options = $('#dashboard-options input[type="checkbox"]').map(function() {
            return {
                id: this.value,
                visible: this.checked ? 1 : 0
            };
        }).get();

        data.widgets = options;
        /*
                if (typeof(csrfData) !== 'undefined') {
                    data[csrfData['token_name']] = csrfData['hash'];
                }
        */
        $.post(admin_url + 'staff/save_dashboard_widgets_visibility', data).fail(function(data) {
            // Demo usage, prevent multiple alerts
            if ($('body').find('.float-alert').length == 0) {
                alert_float('danger', data.responseText);
            }
        });
    });

    var tickets_chart_departments = $('#tickets-awaiting-reply-by-department');
    var tickets_chart_status = $('#tickets-awaiting-reply-by-status');
    var leads_chart = $('#leads_status_stats');
    var projects_chart = $('#projects_status_stats');

    if (tickets_chart_departments.length > 0) {
        // Tickets awaiting reply by department chart
        var tickets_dep_chart = new Chart(tickets_chart_departments, {
            type: 'doughnut',
            data: <?php echo $tickets_awaiting_reply_by_department; ?>,
        });
    }
    if (tickets_chart_status.length > 0) {
        // Tickets awaiting reply by department chart
        new Chart(tickets_chart_status, {
            type: 'doughnut',
            data: <?php echo $tickets_reply_by_status; ?>,
            options: {
                onClick: function(evt) {
                    onChartClickRedirect(evt, this);
                }
            },
        });
    }
    if (leads_chart.length > 0) {
        // Leads overview status
        new Chart(leads_chart, {
            type: 'doughnut',
            data: <?php echo $leads_status_stats; ?>,
            options: {
                maintainAspectRatio: false,
                onClick: function(evt) {
                    onChartClickRedirect(evt, this);
                }
            }
        });
    }
    if (projects_chart.length > 0) {
        // Projects statuses
        new Chart(projects_chart, {
            type: 'doughnut',
            data: <?php echo $projects_status_stats; ?>,
            options: {
                maintainAspectRatio: false,
                onClick: function(evt) {
                    onChartClickRedirect(evt, this);
                }
            }
        });
    }

    if ($(window).width() < 500) {
        // Fix for small devices weekly payment statistics
        $('#payment-statistics').attr('height', '250');
    }

    fix_user_data_widget_tabs();
    // Init tickets table for the active tab
    if ($('#home_tab_tickets').hasClass('active')) {
        init_table_tickets(true);
    }
    if ($('#home_tab_assigned_tickets').hasClass('active')) {
        init_table_assigned_tickets(true);
    }
    // Init my tickets widget table
    init_table_my_tickets(true);
    initTeamTicketReportDataTable();
    initTeamTicketCreationReportDataTable();
    $(window).on('resize', function() {
        $('.horizontal-scrollable-tabs ul.nav-tabs-horizontal').removeAttr('style');
        fix_user_data_widget_tabs();
    });
    // Payments statistics
    init_weekly_payment_statistics(<?php echo $weekly_payment_stats; ?>);

    $('select[name="currency"]').on('change', function() {
        let $activeChart = $('#Payment-chart-name').data('active-chart');

        if (typeof(weekly_payments_statistics) !== 'undefined') {
            weekly_payments_statistics.destroy();
        }

        if (typeof(monthly_payments_statistics) !== 'undefined') {
            monthly_payments_statistics.destroy();
        }

        if ($activeChart == 'weekly') {
            init_weekly_payment_statistics();
        } else if ($activeChart == 'monthly') {
            init_monthly_payment_statistics();
        }

    });

    // Load group members ticket report table when tab is shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).attr('href') === '#group_members_tickets') {
            if ($('#group-members-ticket-report-table-wrapper').children().length === 0) {
                $('#group-members-ticket-report-table-wrapper').load(admin_url + 'dashboard/group_members_ticket_widget/this_month', function() {
                    var $table = $('#group-members-ticket-report-table-wrapper .table-group-members-tickets');
                    if ($table.length && $.fn.DataTable && $.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().destroy();
                    }
                    initDataTableInline('#group-members-ticket-report-table-wrapper .table-group-members-tickets')
                });
            }
        }
    });
});

function fix_user_data_widget_tabs() {
    if ((app.browser != 'firefox' &&
            isRTL == 'false' && is_mobile()) || (app.browser == 'firefox' &&
            isRTL == 'false' && is_mobile())) {
        $('.horizontal-scrollable-tabs ul.nav-tabs-horizontal').css('margin-bottom', '26px');
    }
}

function init_weekly_payment_statistics(data) {
    if ($('#payment-statistics').length > 0) {

        if (typeof(weekly_payments_statistics) !== 'undefined') {
            weekly_payments_statistics.destroy();
        }
        if (typeof(data) == 'undefined') {
            var currency = $('select[name="currency"]').val();
            $.get(admin_url + 'dashboard/weekly_payments_statistics/' + currency, function(response) {
                weekly_payments_statistics = new Chart($('#payment-statistics'), {
                    type: 'bar',
                    data: response,
                    options: {
                        responsive: true,
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                }
                            }]
                        },
                    },
                });
            }, 'json');
        } else {
            weekly_payments_statistics = new Chart($('#payment-statistics'), {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                            }
                        }]
                    },
                },
            });
        }

    }
}

function init_monthly_payment_statistics() {
    if ($('#payment-statistics').length > 0) {

        if (typeof(monthly_payments_statistics) !== 'undefined') {
            monthly_payments_statistics.destroy();
        }

        var currency = $('select[name="currency"]').val();
        $.get(admin_url + 'dashboard/monthly_payments_statistics/' + currency, function(response) {
            monthly_payments_statistics = new Chart($('#payment-statistics'), {
                type: 'bar',
                data: response,
                options: {
                    responsive: true,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                            }
                        }]
                    },
                },
            });
        }, 'json');
    }
}

function update_payment_statistics(el) {
    let type = $(el).data('type');
    let $chartNameWrapper = $('#Payment-chart-name');
    $chartNameWrapper.data('active-chart', type);
    $chartNameWrapper.text($(el).text());

    if (typeof(weekly_payments_statistics) !== 'undefined') {
        weekly_payments_statistics.destroy();
    }

    if (typeof(monthly_payments_statistics) !== 'undefined') {
        monthly_payments_statistics.destroy();
    }

    console.log(type);

    if (type == 'weekly') {
        init_weekly_payment_statistics();
    } else if (type == 'monthly') {
        init_monthly_payment_statistics();
    }

}

function update_tickets_report_table(el) {
    var $el = $(el);
    var type = $el.data('type')
    $('#tickets-report-mode-name').text($el.text())

    $('#tickets-report-table-wrapper').load(admin_url + 'dashboard/ticket_widget/' + type, function(data) {
        $('.table-ticket-reports').dataTable().fnDestroy()
        initDataTableInline('.table-ticket-reports')
    });
    return false
}

function update_team_ticket_report_table(el) {
    var $el = $(el);
    var type = $el.data('type')
    $('#team-ticket-report-mode-name').text($el.text())

    var $wrapper = $('#team-ticket-report-table-wrapper');
    var $table = $wrapper.find('.table-team-ticket-reports');
    if ($table.length && $.fn.DataTable && $.fn.DataTable.isDataTable($table)) {
        $table.DataTable().destroy();
    }

    $wrapper.load(admin_url + 'dashboard/team_ticket_widget/' + type, function() {
        initTeamTicketReportDataTable();
    });
    return false
}

function update_team_ticket_creation_report_table(el) {
    var $el = $(el);
    var type = $el.data('type')
    $('#team-ticket-creation-report-mode-name').text($el.text())

    var $wrapper = $('#team-ticket-creation-report-table-wrapper');
    var $table = $wrapper.find('.table-team-creation-ticket-reports');
    if ($table.length && $.fn.DataTable && $.fn.DataTable.isDataTable($table)) {
        $table.DataTable().destroy();
    }

    $wrapper.load(admin_url + 'dashboard/team_ticket_creation_widget/' + type, function() {
        initTeamTicketCreationReportDataTable();
    });
    return false
}

function update_group_members_ticket_report_table(el) {
    var $el = $(el);
    var type = $el.data('type')
    $('#group-members-ticket-report-mode-name').text($el.text())

    $('#group-members-ticket-report-table-wrapper').load(admin_url + 'dashboard/group_members_ticket_widget/' + type, function(data) {
        $('#group-members-ticket-report-table-wrapper .table-group-members-tickets').dataTable().fnDestroy()
        initDataTableInline('#group-members-ticket-report-table-wrapper .table-group-members-tickets')
    });
    return false
}

function init_table_assigned_tickets(manual) {
    init_table_tickets(manual, 'tickets_assigned_filters', '.tickets-assigned-table');
}

function init_table_my_tickets(manual) {
    var table = init_table_tickets(manual, 'my_tickets_filters', '.my-tickets-table');

    if (table && $('body').hasClass('dashboard')) {
        table.column(4).visible(true, false);
        table.column(5).visible(true, false);
        table.columns.adjust();
    }

    return table;
}

function init_table_tickets(manual, filter_class = 'tickets_filters', table_selector = '.tickets-table') {
    // Single ticket is for other tickets from user
    if (void 0 === manual && ($("body").hasClass("dashboard") || $("body").hasClass("single-ticket"))) return false;
    if (0 !== $(table_selector).length) {
        var t = {}, a = $('._hidden_inputs._filters.' + filter_class + ' input'), i = $(table_selector + " thead .ticket_created_column").index();
        $.each(a, function() { t[$(this).attr("name")] = '[name="' + $(this).attr("name") + '"]' });
        t.tasks_related_to = '[name="tasks_related_to"]';
        var n = admin_url + "tickets";
        if ($("body").hasClass("tickets-page") && (n += "?bulk_actions=true"), _table_api = initDataTable(table_selector, n, [0], [0], t, [i, "desc"]), _table_api && $("body").hasClass("dashboard") && _table_api.columns([4, i, 5, 6]).visible(false, false).columns.adjust());
        return _table_api;
    }
}

function initTeamTicketReportDataTable() {
    initDashboardDataTable('#team-ticket-report-table-wrapper .table-team-ticket-reports');
}

function initTeamTicketCreationReportDataTable() {
    initDashboardDataTable('#team-ticket-creation-report-table-wrapper .table-team-creation-ticket-reports');
}

function initDashboardDataTable(selector) {
    if (typeof $.fn.DataTable === 'undefined') {
        return;
    }
    var $table = $(selector);
    if (!$table.length || $.fn.DataTable.isDataTable($table)) {
        return;
    }
    initDataTableInline(selector);
}
</script>
