<?php

defined('BASEPATH') or exit('No direct script access allowed');

 $has_permission_edit   = staff_can('edit', 'approval_flow');
 $has_permission_create = staff_can('create', 'approval_flow');
 $has_permission_delete = staff_can('delete', 'approval_flow');

 $CI     = &get_instance();
 // Prefer passed-in filter (from get_table_data) and fall back to query param to avoid undefined notices.
 $filter = isset($filter) && $filter !== '' ? $filter : ($CI->input->get('filter') ?: 'all');

 // Ensure these are always defined to prevent SQL syntax errors when building WHERE clauses.
 $current_user_id = (int) get_staff_user_id();
 $can_view_global = staff_can('view', 'approval_flow') ? true : false;

$aColumns = [
    db_prefix() . 'approval_flows.id',
    'name',
    '(SELECT COUNT(*) FROM ' . db_prefix() . 'approval_flow_steps WHERE approval_flow_id = ' . db_prefix() . 'approval_flows.id) as steps_count',
    'status',
    'created_by',
    'created_at',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'approval_flows';

$join = [
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'approval_flows.created_by',
];

$where = hooks()->apply_filters('approval_flow_table_sql_where', []);

// Filter for my influence - show only approval flows where current user is an approver
if ($filter === 'my_influence' && $current_user_id > 0) {
    $join[] = 'INNER JOIN ' . db_prefix() . 'approval_flow_steps ON ' . db_prefix() . 'approval_flow_steps.approval_flow_id = ' . db_prefix() . 'approval_flows.id';
    $where[] = 'AND ' . db_prefix() . 'approval_flow_steps.staff_id = ' . $current_user_id;
} elseif (! $can_view_global && $current_user_id > 0) {
    // Without global view permission, limit to flows created by the current user
    $where[] = 'AND ' . db_prefix() . 'approval_flows.created_by = ' . $current_user_id;
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'approval_flows.id',
    'firstname',
    'lastname',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Approval Flow ID
    $row[] = $aRow[db_prefix() . 'approval_flows.id'];

    // Approval Name
    $name = '<a href="' . admin_url('approval_flow/approval_flow/' . $aRow[db_prefix() . 'approval_flows.id']) . '">' . e($aRow['name']) . '</a>';
    $row[] = $name;

    // No of Approval Steps
    $steps_count = isset($aRow['steps_count']) ? (int)$aRow['steps_count'] : 0;
    $row[] = $steps_count;

    // Who created
    $created_by = e($aRow['firstname'] . ' ' . $aRow['lastname']);
    $row[] = $created_by;

    // When Created
    $row[] = e(_dt($aRow['created_at']));

    // Status (Active/Inactive) - only show for all approval flows
    if ($filter !== 'my_influence') {
        $status = $aRow['status'];
        $is_active = $status == 1;
        $status_class = $is_active ? 'success' : 'danger';
        $status_text = $is_active ? 'Active' : 'Inactive';
        $status_icon = $is_active ? 'fa-toggle-on' : 'fa-toggle-off';

        $status_toggle = '<span class="label label-' . $status_class . ' status-toggle" style="cursor: pointer; padding: 8px 12px; border-radius: 20px; font-size: 12px;" data-switch-url="' . admin_url('approval_flow/change_status/' . $aRow[db_prefix() . 'approval_flows.id']) . '" data-status="' . $status . '">
            <i class="fa ' . $status_icon . '"></i> ' . $status_text . '
        </span>';
        $row[] = $status_toggle;
    } else {
        // For my influence, just show the status as text
        $status = $aRow['status'];
        $is_active = $status == 1;
        $status_text = $is_active ? 'Active' : 'Inactive';
        $row[] = $status_text;
    }

    // Edit button - only show for all approval flows
    if ($filter !== 'my_influence') {
        $options = '';
        if ($has_permission_edit) {
            $options .= '<a href="' . admin_url('approval_flow/approval_flow/' . $aRow[db_prefix() . 'approval_flows.id']) . '" class="btn btn-default btn-icon" title="' . _l('edit') . '">';
            $options .= '<i class="fa-regular fa-pen-to-square"></i>';
            $options .= '</a>';
        }
        $row[] = $options;
    }

    $row['DT_RowClass'] = 'has-row-options';

    $row = hooks()->apply_filters('approval_flow_table_row', $row, $aRow);

    $output['aaData'][] = $row;
}
