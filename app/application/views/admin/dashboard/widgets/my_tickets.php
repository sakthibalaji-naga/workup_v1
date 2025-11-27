<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('my_tickets'); ?>">
    <div class="panel_s">
        <div class="panel-body" style="padding: 5px;">
            <div class="widget-dragger"></div>
            <div class="row">
                <div class="col-md-12 mbot10">
                    <p
                        class="tw-font-semibold tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse tw-p-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="tw-w-6 tw-h-6 tw-text-neutral-500">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                        <span class="tw-text-neutral-700">
                            My Tickets
                        </span>
                    </p>

                    <hr class="-tw-mx-3 tw-mt-3 tw-mb-6">
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="_hidden_inputs _filters my_tickets_filters">
                <?php echo form_hidden('my_tickets', '1'); ?>
            </div>
            <?php
                $tableHtml = AdminTicketsTableStructure('my-tickets-table');
                $defaultTicketsOrder = get_table_last_order('tickets');
                $myTicketsOrder      = get_table_last_order('my-tickets');
                $tableHtml = str_replace('id="tickets"', 'id="my-tickets-table"', $tableHtml);
                $tableHtml = str_replace('data-to-table="tickets"', 'data-to-table="my-tickets-table"', $tableHtml);
                $tableHtml = str_replace('data-last-order-identifier="tickets"', 'data-last-order-identifier="my-tickets"', $tableHtml);
                $tableHtml = str_replace('data-default-order="' . $defaultTicketsOrder . '"', 'data-default-order="' . $myTicketsOrder . '"', $tableHtml);
                $tableHtml = str_replace('hidden-columns-table-tickets', 'hidden-columns-table-my-tickets-table', $tableHtml);
                echo $tableHtml;
            ?>
        </div>
    </div>
</div>
