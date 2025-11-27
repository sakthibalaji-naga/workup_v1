<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Render admin tickets table
 * @param string  $name        table name
 * @param boolean $bulk_action include checkboxes on the left side for bulk actions
 */
function AdminTicketsTableStructure($name = '', $bulk_action = false)
{
    $table = '<table class="table customizable-table number-index-' . ($bulk_action ? '2' : '1') . ' dt-table-loading ' . ($name == '' ? 'tickets-table' : $name) . ' table-tickets" id="tickets" data-last-order-identifier="tickets" data-default-order="' . get_table_last_order('tickets') . '">';
    $table .= '<thead>';
    $table .= '<tr>'; 

    $table .= '<th class="' . ($bulk_action == true ? '' : 'not_visible') . '">';
    $table .= '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="tickets"><label></label></div>';
    $table .= '</th>';

    $table .= '<th class="toggleable not_visible" id="th-number">' . _l('the_number_sign') . '</th>';
    $table .= '<th class="toggleable" id="th-ticket-number">' . _l('ticket_number') . '</th>';
    $table .= '<th class="toggleable ticket_created_column" id="th-created">' . _l('ticket_date_created') . '</th>';
    $table .= '<th class="toggleable" id="th-subject">' . _l('ticket_dt_subject') . '</th>';
    $table .= '<th class="toggleable" id="th-created-by">' . _l('ticket_owner') . '</th>';
    $table .= '<th class="toggleable" id="th-client-division">Div - Dept</th>';
    $table .= '<th class="toggleable" id="th-ticket-handler">' . _l('ticket_handler') . '</th>';
    $table .= '<th class="toggleable" id="th-assigned-to">' . _l('ticket_assigned') . '</th>';
    $table .= '<th class="toggleable" id="th-resolution-date">Resolution Time</th>';
    $table .= '<th class="toggleable" id="th-ticket-age">Aging</th>';
    $table .= '<th class="toggleable" id="th-status">' . _l('ticket_dt_status') . '</th>';
    // Hide the Tags column by default but keep it toggleable for consistency
    $table .= '<th class="toggleable not_visible" id="th-tags">' . _l('tags') . '</th>';

    // Hide Contact/Submitter column by default
    $table .= '<th class="toggleable not_visible" id="th-submitter">' . _l('ticket_dt_submitter') . '</th>';
    $table .= '<th class="toggleable not_visible" id="th-department">' . _l('ticket_dt_department') . '</th>';
    $table .= '<th class="toggleable not_visible" id="th-last-reply">' . _l('ticket_dt_last_reply') . '</th>';

    $custom_fields = get_table_custom_fields('tickets');

    foreach ($custom_fields as $field) {
        $table .= '<th>' . $field['name'] . '</th>';
    }

    $table .= '</tr>';
    $table .= '</thead>';
    $table .= '<tbody></tbody>';
    $table .= '</table>';

    $table .= '<script id="hidden-columns-table-tickets" type="text/json">';
    $table .= get_staff_meta(get_staff_user_id(), 'hidden-columns-table-tickets');
    $table .= '</script>';

    return $table;
}

/**
 * Function to translate ticket status
 * The app offers ability to translate ticket status no matter if they are stored in database
 * @param  mixed $id
 * @return string
 */
function ticket_status_translate($id)
{
    if ($id == '' || is_null($id)) {
        return '';
    }

    $line = _l('ticket_status_db_' . $id, '', false);

    if ($line == 'db_translate_not_found') {
        $CI = & get_instance();
        $CI->db->where('ticketstatusid', $id);
        $status = $CI->db->get(db_prefix() . 'tickets_status')->row();

        return !$status ? '' : $status->name;
    }

    return $line;
}

/**
 * Function to translate ticket priority
 * The apps offers ability to translate ticket priority no matter if they are stored in database
 * @param  mixed $id
 * @return string
 */
function ticket_priority_translate($id)
{
    if ($id == '' || is_null($id)) {
        return '';
    }

    $line = _l('ticket_priority_db_' . $id, '', false);

    if ($line == 'db_translate_not_found') {
        $CI = & get_instance();
        $CI->db->where('priorityid', $id);
        $priority = $CI->db->get(db_prefix() . 'tickets_priorities')->row();

        return !$priority ? '' : $priority->name;
    }

    return $line;
}

/**
 * When ticket will be opened automatically set to open
 * @param integer  $current Current status
 * @param integer  $id      ticketid
 * @param boolean $admin   Admin opened or client opened
 */
function set_ticket_open($current, $id, $admin = true)
{
    if ($current == 1) {
        return;
    }

    $field = ($admin == false ? 'clientread' : 'adminread');

    $CI = & get_instance();
    $CI->db->where('ticketid', $id);

    $CI->db->update(db_prefix() . 'tickets', [
        $field => 1,
    ]);
}

/**
 * Check whether to show ticket submitter on clients area table based on applied settings and contact
 * @since  2.3.2
 * @return boolean
 */
function show_ticket_submitter_on_clients_area_table()
{
    $show_submitter_on_table = true;
    if (!can_logged_in_contact_view_all_tickets()) {
        $show_submitter_on_table = false;
    }

    return hooks()->apply_filters('show_ticket_submitter_on_clients_area_table', $show_submitter_on_table);
}

/**
 * Check whether the logged in contact can view all tickets in customers area
 * @since  2.3.2
 * @return boolean
 */
function can_logged_in_contact_view_all_tickets()
{
    return !(!is_primary_contact() && get_option('only_show_contact_tickets') == 1);
}

/**
 * Get clients area ticket summary statuses data
 * @since  2.3.2
 * @param  array $statuses  current statuses
 * @return array
 */
function get_clients_area_tickets_summary($statuses)
{
    foreach ($statuses as $key => $status) {
        $where = ['userid' => get_client_user_id(), 'status' => $status['ticketstatusid']];
        if (!can_logged_in_contact_view_all_tickets()) {
            $where[db_prefix() . 'tickets.contactid'] = get_contact_user_id();
        }
        $statuses[$key]['total_tickets']   = total_rows(db_prefix() . 'tickets', $where);
        $statuses[$key]['translated_name'] = ticket_status_translate($status['ticketstatusid']);
        $statuses[$key]['url']             = site_url('clients/tickets/' . $status['ticketstatusid']);
    }

    return hooks()->apply_filters('clients_area_tickets_summary', $statuses);
}

/**
 * Check whether contact can change the ticket status (single ticket) in clients area
 * @since  2.3.2
 * @param  mixed $status  the status id, if not passed, will first check from settings
 * @return boolean
 */
function can_change_ticket_status_in_clients_area($status = null)
{
    $option = get_option('allow_customer_to_change_ticket_status');

    if (is_null($status)) {
        return $option == 1;
    }

    /*
    *   For all cases check the option too again, because if the option is set to No, no status changes on any status is allowed
     */
    if ($option == 0) {
        return false;
    }

    $forbidden = hooks()->apply_filters('forbidden_ticket_statuses_to_change_in_clients_area', [3, 4]);

    if (in_array($status, $forbidden)) {
        return false;
    }

    return true;
}

/**
 * For html5 form accepted attributes
 * This function is used for the tickets form attachments
 * @return string
 */
function get_ticket_form_accepted_mimes()
{
    $ticket_allowed_extensions = get_option('ticket_attachments_file_extensions');

    $_ticket_allowed_extensions = array_map(function ($ext) {
        return trim($ext);
    }, explode(',', $ticket_allowed_extensions));

    $all_form_ext = str_replace([' '], '', $ticket_allowed_extensions);

    if (is_array($_ticket_allowed_extensions)) {
        foreach ($_ticket_allowed_extensions as $ext) {
            $all_form_ext .= ',' . get_mime_by_extension($ext);
        }
    }

    return $all_form_ext;
}

function ticket_message_save_as_predefined_reply_javascript()
{
    if (!is_admin() && get_option('staff_members_save_tickets_predefined_replies') == '0') {
        return false;
    } ?>
<div class="modal fade" id="savePredefinedReplyFromMessageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('predefined_replies_dt_name'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo render_input('name', 'predefined_reply_add_edit_name'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-primary"
                    id="saveTicketMessagePredefinedReply"><?php echo _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
$(function() {
    var editorMessage = tinymce.get('message');
    if (typeof(editorMessage) != 'undefined') {
        editorMessage.on('change', function() {
            if (editorMessage.getContent().trim() != '') {
                if ($('#savePredefinedReplyFromMessage').length == 0) {
                    $('[app-field-wrapper="message"] [role="menubar"]:first')
                        .append(
                            "<button id=\"savePredefinedReplyFromMessage\" data-toggle=\"modal\" type=\"button\" data-target=\"#savePredefinedReplyFromMessageModal\" class=\"tox-mbtn save_predefined_reply_from_message pointer\" href=\"#\"><?php echo _l('save_message_as_predefined_reply'); ?></button>"
                        );
                }
                // For open is handled on contact select
                if ($('#single-ticket-form').length > 0) {
                    var contactIDSelect = $('#contactid');
                    if (contactIDSelect.data('no-contact') == undefined && contactIDSelect.data(
                            'ticket-emails') == '0') {
                        show_ticket_no_contact_email_warning($('input[name="userid"]').val(),
                            contactIDSelect.val());
                    } else {
                        clear_ticket_no_contact_email_warning();
                    }
                }
            } else {
                $('#savePredefinedReplyFromMessage').remove();
                clear_ticket_no_contact_email_warning();
            }
        });
    }
    $('body').on('click', '#saveTicketMessagePredefinedReply', function(e) {
        e.preventDefault();
        var data = {}
        data.message = editorMessage.getContent();
        data.name = $('#savePredefinedReplyFromMessageModal #name').val();
        data.ticket_area = true;
        $.post(admin_url + 'tickets/predefined_reply', data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                var predefined_reply_select = $('#insert_predefined_reply');
                predefined_reply_select.find('option:first').after('<option value="' + response
                    .id + '">' + data.name + '</option>');
                predefined_reply_select.selectpicker('refresh');
            }
            $('#savePredefinedReplyFromMessageModal').modal('hide');
        });
    });
});
</script>
<?php
}

function get_ticket_public_url($ticket)
{
    if (is_array($ticket)) {
        $ticket = array_to_object($ticket);
    }

    $CI = &get_instance();

    if (!$ticket->ticketkey) {
        $CI->db->where('ticketid', $ticket->ticketid);
        $CI->db->update('tickets', ['ticketkey' => $key = app_generate_hash()]);
    } else {
        $key = $ticket->ticketkey;
    }

    return site_url('forms/tickets/' . $key);
}

function can_staff_delete_ticket_reply()
{
    return can_staff_delete_ticket();
}

function can_staff_delete_ticket()
{
    return false;
}

function can_staff_edit_ticket_message()
{
    if(is_admin()) {
        return true;
    }

    if(!is_staff_member() && get_option('access_tickets_to_none_staff_members') == '0') {
        return false;
    }

    return get_option('allow_non_admin_members_to_edit_ticket_messages') == '1';
}

/**
 * Get staff role label and color for ticket history
 * @param int $staff_id Staff ID
 * @param object $ticket Ticket object
 * @return array Array with 'label' and 'class' keys
 */
function get_staff_role_info($staff_id, $ticket = null)
{
    $role_info = [
        'label' => _l('ticket_staff_string'),
        'class' => 'label-default'
    ];

    if (is_admin($staff_id)) {
        $role_info['label'] = _l('ticket_staff_role_admin');
        $role_info['class'] = 'label-danger'; // Red for admin
        return $role_info;
    }

    if ($ticket) {
        // Check if staff is the creator
        if (isset($ticket->admin) && (int)$ticket->admin === (int)$staff_id) {
            $role_info['label'] = _l('ticket_staff_role_creator');
            $role_info['class'] = 'label-success'; // Green for creator
            return $role_info;
        }

        // Check if staff is assigned
        if (isset($ticket->assigned) && (int)$ticket->assigned === (int)$staff_id) {
            $role_info['label'] = _l('ticket_staff_role_assigned');
            $role_info['class'] = 'label-info'; // Blue for assigned
            return $role_info;
        }

        // Check if staff is a handler
        $CI = &get_instance();
        if ($CI->db->table_exists(db_prefix() . 'ticket_handlers')) {
            $CI->db->where('ticketid', $ticket->ticketid);
            $CI->db->where('staffid', $staff_id);
            if ($CI->db->count_all_results(db_prefix() . 'ticket_handlers') > 0) {
                $role_info['label'] = _l('ticket_staff_role_handler');
                $role_info['class'] = 'label-warning'; // Orange for handler
                return $role_info;
            }
        }
    }

    // Default to staff
    return $role_info;
}

/**
 * Get staff role label for ticket history (backward compatibility)
 * @param int $staff_id Staff ID
 * @param object $ticket Ticket object
 * @return string Role label
 */
function get_staff_role_label($staff_id, $ticket = null)
{
    $role_info = get_staff_role_info($staff_id, $ticket);
    return $role_info['label'];
}

/**
 * Add ticket log entry
 * @param int $ticketid Ticket ID
 * @param string $log_type Type of log (e.g., 'status_change', 'ticket_settings_updated')
 * @param array $log_details Additional details for the log
 */
function add_ticket_log($ticketid, $log_type, $log_details = [])
{
    $CI = &get_instance();

    // Be defensive in case the custom table is not present in this installation
    $table = db_prefix() . 'ticket_logs';
    if (!$CI->db->table_exists($table)) {
        return false;
    }

    $data = [
        'ticketid'   => (int) $ticketid,
        'timestamp'  => date('Y-m-d H:i:s'),
        'user_id'    => (int) get_staff_user_id(),
        'user_type'  => 'staff',
        'log_type'   => (string) $log_type,
        'log_details'=> json_encode($log_details),
    ];

    $CI->db->insert($table, $data);
    return $CI->db->affected_rows() > 0;
}

function ticket_public_form_customers_footer()
{
    // Create new listeners for the public_form
    // removes the one from clients.js (if loaded) and using new ones
    ?>
<style>
.single-ticket-project-area {
    display: none !important;
}
</style>
<script>
$(function() {
    setTimeout(function() {
        $('#ticket-reply').appFormValidator();

        $('.toggle-change-ticket-status').off('click');
        $('.toggle-change-ticket-status').on('click', function() {
            $('.ticket-status,.ticket-status-inline').toggleClass('hide');
        });

        $('#ticket_status_single').off('change');
        $('#ticket_status_single').on('change', function() {
            data = {};
            data.status_id = $(this).val();
            data.ticket_id = $('input[name="ticket_id"]').val();
            $.post(site_url + 'clients/change_ticket_status/', data).done(function() {
                window.location.reload();
            });
        });
    }, 2000)
})
</script>
<?php
}
