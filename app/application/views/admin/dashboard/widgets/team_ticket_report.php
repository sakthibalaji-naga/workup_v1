<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('team_ticket_report'); ?>">
    <div class="row" id="team_ticket_report">
        <div class="col-md-12">
            <div class="panel_s">
                <div class="panel-body" style="padding: 5px;">
                    <div class="widget-dragger"></div>
                    <ul class="nav nav-tabs mtop5" role="tablist">
                        <li role="tab" class="active">
                            <a href="#team_tickets" aria-controls="team_tickets" role="tab" data-toggle="tab"><?php echo _l('my_team_tickets'); ?></a>
                        </li>
                        <li role="tab">
                            <a href="#team_tickets_creation" aria-controls="team_tickets_creation" role="tab" data-toggle="tab"><?php echo _l('my_team_creation_tickets'); ?></a>
                        </li>
                        <?php if (false && $this->db->where('leader_id', get_staff_user_id())->get('groups')->num_rows() > 0) { ?>
                        <li role="tab">
                            <a href="#group_members_tickets" aria-controls="group_members_tickets" role="tab" data-toggle="tab"><?php echo _l('group_members'); ?></a>
                        </li>
                        <?php } ?>
                    </ul>

                    <hr class="-tw-mx-3 tw-mt-2 tw-mb-4">

                    <div class="tab-content tw-mt-5">
                        <div role="tabpanel" class="tab-pane active" id="team_tickets">
                            <div class="tw-flex tw-justify-between tw-items-center">
                                <p class="tw-font-semibold tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse">
                                    <i class="fa-regular fa-life-ring fa-lg tw-text-neutral-500"></i>
                                    <span class="tw-text-neutral-700">
                                        <?php echo _l('my_team_tickets'); ?>
                                    </span>
                                </p>
                                <div>
                                    <div class="dropdown">
                                        <a href="#" id="team-ticket-report-mode" class="dropdown-toggle" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <span id="team-ticket-report-mode-name"> <?php echo _l('this_month') ?> </span>
                                            <i class="fa fa-caret-down" aria-hidden="true"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="team-ticket-report-mode">
                                            <li>
                                                <a href="#" data-type="this_week"
                                                    onclick="update_team_ticket_report_table(this); return false;"><?php echo _l('this_week') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="last_week"
                                                    onclick="update_team_ticket_report_table(this); return false;"><?php echo _l('last_week') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="this_month"
                                                    onclick="update_team_ticket_report_table(this); return false;"><?php echo _l('this_month') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="last_month"
                                                    onclick="update_team_ticket_report_table(this); return false;"><?php echo _l('last_month') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="this_year"
                                                    onclick="update_team_ticket_report_table(this); return false;"><?php echo _l('this_year') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="last_year"
                                                    onclick="update_team_ticket_report_table(this); return false;"><?php echo _l('last_year') ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="_filters _hidden_inputs">
                                        <?php echo form_hidden('display_mode', 'this_month') ?>
                                    </div>
                                </div>
                            </div>

                            <div id="team-ticket-report-table-wrapper" class="tw-p-2 tw-mt-4">
                                <?php $this->load->view('admin/dashboard/widgets/team_ticket_report_table'); ?>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="team_tickets_creation">
                            <div class="tw-flex tw-justify-between tw-items-center">
                                <p class="tw-font-semibold tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse">
                                    <i class="fa-regular fa-clipboard-list fa-lg tw-text-neutral-500"></i>
                                    <span class="tw-text-neutral-700">
                                        <?php echo _l('my_team_creation_tickets'); ?>
                                    </span>
                                </p>
                                <div>
                                    <div class="dropdown">
                                        <a href="#" id="team-ticket-creation-report-mode" class="dropdown-toggle" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <span id="team-ticket-creation-report-mode-name"> <?php echo _l('this_month') ?> </span>
                                            <i class="fa fa-caret-down" aria-hidden="true"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="team-ticket-creation-report-mode">
                                            <li>
                                                <a href="#" data-type="this_week"
                                                    onclick="update_team_ticket_creation_report_table(this); return false;"><?php echo _l('this_week') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="last_week"
                                                    onclick="update_team_ticket_creation_report_table(this); return false;"><?php echo _l('last_week') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="this_month"
                                                    onclick="update_team_ticket_creation_report_table(this); return false;"><?php echo _l('this_month') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="last_month"
                                                    onclick="update_team_ticket_creation_report_table(this); return false;"><?php echo _l('last_month') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="this_year"
                                                    onclick="update_team_ticket_creation_report_table(this); return false;"><?php echo _l('this_year') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="last_year"
                                                    onclick="update_team_ticket_creation_report_table(this); return false;"><?php echo _l('last_year') ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="_filters _hidden_inputs">
                                        <?php echo form_hidden('display_mode_creation', 'this_month') ?>
                                    </div>
                                </div>
                            </div>

                            <div id="team-ticket-creation-report-table-wrapper" class="tw-p-2 tw-mt-4">
                                <?php $this->load->view('admin/dashboard/widgets/team_ticket_creation_report_table'); ?>
                            </div>
                        </div>
                        <?php if (false && $this->db->where('leader_id', get_staff_user_id())->get('groups')->num_rows() > 0) { ?>
                        <div role="tabpanel" class="tab-pane" id="group_members_tickets">
                            <div class="tw-flex tw-justify-between tw-items-center">
                                <p class="tw-font-semibold tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse">
                                    <i class="fa-regular fa-user-group fa-lg tw-text-neutral-500"></i>
                                    <span class="tw-text-neutral-700">
                                        <?php echo _l('group_members'); ?>
                                    </span>
                                </p>
                                <div>
                                    <div class="dropdown">
                                        <a href="#" id="group-members-ticket-report-mode" class="dropdown-toggle" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <span id="group-members-ticket-report-mode-name"> <?php echo _l('this_month') ?> </span>
                                            <i class="fa fa-caret-down" aria-hidden="true"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="group-members-ticket-report-mode">
                                            <li>
                                                <a href="#" data-type="this_week"
                                                    onclick="update_group_members_ticket_report_table(this); return false;"><?php echo _l('this_week') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="last_week"
                                                    onclick="update_group_members_ticket_report_table(this); return false;"><?php echo _l('last_week') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="this_month"
                                                    onclick="update_group_members_ticket_report_table(this); return false;"><?php echo _l('this_month') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="last_month"
                                                    onclick="update_group_members_ticket_report_table(this); return false;"><?php echo _l('last_month') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="this_year"
                                                    onclick="update_group_members_ticket_report_table(this); return false;"><?php echo _l('this_year') ?></a>
                                            </li>
                                            <li>
                                                <a href="#" data-type="last_year"
                                                    onclick="update_group_members_ticket_report_table(this); return false;"><?php echo _l('last_year') ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="_filters _hidden_inputs">
                                        <?php echo form_hidden('display_mode', 'this_month') ?>
                                    </div>
                                </div>
                            </div>

                            <div id="group-members-ticket-report-table-wrapper" class="tw-p-2 tw-mt-4">
                                <!-- will load the table here -->
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
