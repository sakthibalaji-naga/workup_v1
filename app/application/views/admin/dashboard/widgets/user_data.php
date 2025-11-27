<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('user_widget'); ?>">
    <div class="panel_s user-data">
        <div class="panel-body home-activity">
            <div class="widget-dragger"></div>
            <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                <div class="scroller scroller-left arrow-left"><i class="fa fa-angle-left"></i></div>
                <div class="scroller scroller-right arrow-right"><i class="fa fa-angle-right"></i></div>
                <div class="horizontal-tabs">
                    <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#home_tab_tickets" onclick="init_table_tickets(true);"
                                aria-controls="home_tab_tickets" role="tab" data-toggle="tab">
                                <i class="fa-regular fa-life-ring menu-icon"></i> <?php echo _l('home_tickets'); ?>
                            </a>
                        </li>
                        <li role="presentation" style="display:none;">
                            <a href="#home_tab_tasks" aria-controls="home_tab_tasks" role="tab" data-toggle="tab">
                                <i class="fa fa-tasks menu-icon"></i> <?php echo _l('home_my_tasks'); ?>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#home_tab_assigned_tickets" onclick="init_table_assigned_tickets(true);"
                                aria-controls="home_tab_assigned_tickets" role="tab" data-toggle="tab">
                                <i class="fa fa-user menu-icon"></i> Assigned Tickets
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#home_my_reminders"
                                onclick="initDataTable('.table-my-reminders', admin_url + 'misc/my_reminders', undefined, undefined,undefined,[2,'asc']);"
                                aria-controls="home_my_reminders" role="tab" data-toggle="tab">
                                <i class="fa-regular fa-clock menu-icon"></i> <?php echo _l('my_reminders'); ?>
                                <?php
                        $total_reminders = total_rows(
    db_prefix() . 'reminders',
    [
                           'isnotified' => 0,
                           'staff'      => get_staff_user_id(),
                        ]
);
                        if ($total_reminders > 0) {
                            echo '<span class="badge">' . $total_reminders . '</span>';
                        }
                        ?>
                            </a>
                        </li>
                        <?php if (is_staff_member()) { ?>
                        <li role="presentation">
                            <a href="#home_announcements" onclick="init_table_announcements(true);"
                                aria-controls="home_announcements" role="tab" data-toggle="tab">
                                <i class="fa fa-bullhorn menu-icon"></i> <?php echo _l('home_announcements'); ?>
                                <?php if ($total_undismissed_announcements != 0) {
                            echo '<span class="badge">' . $total_undismissed_announcements . '</span>';
                        } ?>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if (is_admin()) { ?>
                        <li role="presentation">
                            <a href="#home_tab_activity" aria-controls="home_tab_activity" role="tab" data-toggle="tab">
                                <i class="fa fa-window-maximize menu-icon"></i>
                                <?php echo _l('home_latest_activity'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        <?php hooks()->do_action('after_user_data_widget_tabs'); ?>
                    </ul>
                </div>
            </div>
            <div class="tab-content tw-mt-5">
                <div role="tabpanel" class="tab-pane active" id="home_tab_tickets">
                    <a href="<?php echo admin_url('tickets'); ?>"
                        class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
                    <div class="clearfix"></div>
                    <div class="_hidden_inputs _filters tickets_filters">
                        <?php
                           // On home only show on hold, open and in progress for tickets owned by current user
                           echo form_hidden('ticket_status_1', true);
                           echo form_hidden('ticket_status_2', true);
                           echo form_hidden('ticket_status_4', true);
                           echo form_hidden('userid', get_staff_user_id());
                           ?>
                    </div>
                    <?php echo AdminTicketsTableStructure(); ?>
                </div>
                <div role="tabpanel" class="tab-pane" id="home_tab_tasks">
                    <a href="<?php echo admin_url('tasks/list_tasks'); ?>"
                        class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
                    <div class="clearfix"></div>
                    <div class="_hidden_inputs _filters _tasks_filters">
                        <?php echo form_hidden('my_tasks', true); ?>
                    </div>
                    <?php $this->load->view('admin/tasks/_table'); ?>
                </div>
                <div role="tabpanel" class="tab-pane" id="home_tab_assigned_tickets">
                    <a href="<?php echo admin_url('tickets'); ?>"
                        class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
                    <div class="clearfix"></div>
                    <div class="_hidden_inputs _filters tickets_assigned_filters">
                        <?php
                           // On home only show on hold, open and in progress assigned to current user
                           echo form_hidden('ticket_status_1', true);
                           echo form_hidden('ticket_status_2', true);
                           echo form_hidden('ticket_status_4', true);
                           echo form_hidden('assigned', get_staff_user_id());
                           ?>
                    </div>
                    <table class="table customizable-table number-index-1 dt-table-loading tickets-assigned-table table-tickets" id="tickets-assigned-table" data-last-order-identifier="tickets" data-default-order="<?php echo get_table_last_order('tickets'); ?>">
                        <thead>
                            <tr>
                                <th class="not_visible"><span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all"><label></label></div></th>
                                <th class="toggleable" id="th-number">#</th>
                                <th class="toggleable" id="th-subject"><?php echo _l('ticket_dt_subject') ?></th>
                                <th class="toggleable not_visible" id="th-tags"><?php echo _l('tags') ?></th>
                                <th class="toggleable" id="th-department"><?php echo _l('ticket_dt_department') ?></th>
                                <th class="toggleable not_visible" id="th-submitter"><?php echo _l('ticket_dt_submitter') ?></th>
                                <th class="toggleable" id="th-client-division">Div - Dept</th>
                                <th class="toggleable" id="th-assigned-to"><?php echo _l('ticket_assigned') ?></th>
                                <th class="toggleable" id="th-ticket-handler"><?php echo _l('ticket_handler') ?></th>
                                <th class="toggleable" id="th-status"><?php echo _l('ticket_dt_status') ?></th>
                                <th class="toggleable" id="th-last-reply"><?php echo _l('ticket_dt_last_reply') ?></th>
                                <th class="toggleable" id="th-created-by"><?php echo _l('ticket_owner') ?></th>
                                <th class="toggleable ticket_created_column" id="th-created"><?php echo _l('ticket_date_created') ?></th>
                                <?php foreach (get_table_custom_fields('tickets') as $field) { ?>
                                <th><?php echo $field['name'] ?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <script id="hidden-columns-table-tickets-assigned-table" type="text/json"><?php echo get_staff_meta(get_staff_user_id(), 'hidden-columns-table-tickets'); ?></script>
                </div>
                <div role="tabpanel" class="tab-pane" id="home_my_projects">
                    <a href="<?php echo admin_url('projects'); ?>"
                        class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
                    <div class="clearfix"></div>
                    <?php render_datatable([
                        _l('project_name'),
                        _l('project_start_date'),
                        _l('project_deadline'),
                        _l('project_status'),
                        ], 'staff-projects', [], [
                        'data-last-order-identifier' => 'my-projects',
                        'data-default-order'         => get_table_last_order('my-projects'),
                        ]);
                        ?>
                </div>
                <div role="tabpanel" class="tab-pane" id="home_my_reminders">
                    <a href="<?php echo admin_url('misc/reminders'); ?>" class="mbot20 inline-block full-width">
                        <?php echo _l('home_widget_view_all'); ?>
                    </a>
                    <?php render_datatable([
                        _l('reminder_related'),
                        _l('reminder_description'),
                        _l('reminder_date'),
                        ], 'my-reminders'); ?>
                </div>
                <?php if (is_staff_member()) { ?>
                <div role="tabpanel" class="tab-pane" id="home_announcements">
                    <?php if (is_admin()) { ?>
                    <a href="<?php echo admin_url('announcements'); ?>"
                        class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
                    <div class="clearfix"></div>
                    <?php } ?>
                    <?php render_datatable([_l('announcement_name'), _l('announcement_date_list')], 'announcements'); ?>
                </div>
                <?php } ?>
                <?php if (is_admin()) { ?>
                <div role="tabpanel" class="tab-pane" id="home_tab_activity">
                    <a href="<?php echo admin_url('utilities/activity_log'); ?>"
                        class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
                    <div class="clearfix"></div>
                    <div class="activity-feed">
                        <?php foreach ($activity_log as $log) { ?>
                        <div class="feed-item">
                            <div class="date">
                                <span class="text-has-action" data-toggle="tooltip"
                                    data-title="<?php echo e(_dt($log['date'])); ?>">
                                    <?php echo e(time_ago($log['date'])); ?>
                                </span>
                            </div>
                            <div class="text">
                                <?php echo e($log['staffid']); ?><br />
                                <?php echo e($log['description']); ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <?php hooks()->do_action('after_user_data_widge_tabs_content'); ?>
            </div>
        </div>
    </div>
</div>
