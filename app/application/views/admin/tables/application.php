<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'name',
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'applications';

// Check if active column exists, if not, don't include it
$CI_temp = &get_instance();
$CI_temp->load->database();
$active_column_exists = $CI_temp->db->field_exists('active', db_prefix() . 'applications');
$position_column_exists = $CI_temp->db->field_exists('position', db_prefix() . 'applications');

if ($active_column_exists) {
    $aColumns[] = 'active';
}
if ($position_column_exists) {
    $aColumns[] = 'position';
}

$additional_select = ['id'];
if ($active_column_exists) {
    $additional_select[] = 'active';
}
if ($position_column_exists) {
    $additional_select[] = 'position';
}

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], $additional_select);
$output  = $result['output'];
$rResult = $result['rResult'];

$CI = &get_instance();
$CI->load->model('departments_model');
$CI->load->model('tickets_model');
$departments = $CI->departments_model->get();
$services = $CI->tickets_model->get_service();

$department_map = [];
foreach ($departments as $department) {
    $department_map[$department['departmentid']] = $department['name'];
}

$service_map = [];
foreach ($services as $service) {
    $service_map[$service['serviceid']] = $service['name'];
}

foreach ($rResult as $aRow) {
    $row = [];

    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'name') {
            $_data = '<a href="#" onclick="edit_application(this,' . $aRow['id'] . '); return false" data-name="' . $aRow['name'] . '" data-position="' . (isset($aRow['position']) ? $aRow['position'] : 0) . '" >' . $_data . '</a>';
        } elseif ($aColumns[$i] == 'active') {
            $_data = isset($aRow['active']) && $aRow['active'] ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';
        } elseif ($aColumns[$i] == 'position') {
            $_data = isset($aRow['position']) && $aRow['position'] > 0 ? $aRow['position'] : 'Last';
        }
        $row[] = $_data;
    }

    $options = '<div class="tw-flex tw-items-center tw-space-x-2">';
    $options .= '<a href="#" onclick="edit_application(this,' . $aRow['id'] . '); return false" data-name="' . $aRow['name'] . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
        <i class="fa-regular fa-pen-to-square fa-lg"></i>
    </a>';

    // Toggle active/inactive button - only show if active column exists
    if ($active_column_exists) {
        $toggle_class = isset($aRow['active']) && $aRow['active'] ? 'btn-success' : 'btn-danger';
        $toggle_text = isset($aRow['active']) && $aRow['active'] ? 'Active' : 'Inactive';
        $toggle_icon = isset($aRow['active']) && $aRow['active'] ? 'fa-toggle-on' : 'fa-toggle-off';

        $options .= '<button type="button" onclick="toggle_application_status(' . $aRow['id'] . ')" class="btn ' . $toggle_class . ' btn-sm">
            <i class="fa ' . $toggle_icon . '"></i> ' . $toggle_text . '
        </button>';
    } else {
        $options .= '<span class="text-muted">Run DB migration first</span>';
    }

    $options .= '</div>';

    $row[] = $options;

    $output['aaData'][] = $row;
}
