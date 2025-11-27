<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('group_model');

$aColumns = [
    'id',
    'name',
    'leader_id',
    'division_id',
    'department_id',
    'sub_department_id',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'groups';

$join = [];

$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $group_id = $aRow['id'];
    $name = $aRow['name'];
    $leader_id = $aRow['leader_id'];
    $division_id = $aRow['division_id'];
    $department_id = $aRow['department_id'];
    $sub_department_id = $aRow['sub_department_id'];
    $members = $CI->group_model->get_group_members($group_id, true); // array of ids

    // Get leader name
    $CI->db->select('firstname,lastname');
    $CI->db->from(db_prefix().'staff');
    $CI->db->where('staffid', $leader_id);
    $leader = $CI->db->get()->row();
    $leader_name = $leader ? $leader->firstname . ' ' . $leader->lastname : '';

    // Get division name
    $division_name = '';
    if (!empty($division_id)) {
        $CI->db->select('name');
        $CI->db->from(db_prefix().'divisions');
        $CI->db->where('divisionid', $division_id);
        $division = $CI->db->get()->row();
        $division_name = $division ? $division->name : '';
    }

    // Get department/sub-department names from leader assignments
    $deptInfo = $CI->group_model->get_leader_department_info($leader_id);
    $department_names = !empty($deptInfo['departments']) ? implode(', ', $deptInfo['departments']) : '';
    $sub_department_names = !empty($deptInfo['sub_departments']) ? implode(', ', $deptInfo['sub_departments']) : '';

    // Get members names
    if (!empty($members)) {
        $CI->db->select('firstname,lastname');
        if ($CI->db->field_exists('employee_code', db_prefix() . 'staff')) {
            $CI->db->select('employee_code');
        }
        $CI->db->from(db_prefix().'staff');
        $CI->db->where_in('staffid', $members);
        $members_rows = $CI->db->get()->result_array();
        $members_list = [];
        foreach ($members_rows as $m) {
            $label = trim(($m['firstname'] ?? '') . ' ' . ($m['lastname'] ?? ''));
            if (!empty($m['employee_code'])) {
                $label .= ' - ' . $m['employee_code'];
            }
            $members_list[] = $label;
        }
        $members_str = implode(', ', array_slice($members_list, 0, 3)); // Show first 3
        if (count($members_list) > 3) {
            $members_str .= '...';
        }
    } else {
        $members_str = '';
    }

    $options = '<div class="tw-flex tw-items-center tw-space-x-2">';
    $options .= '<a href="#" onclick="edit_group(this,' . e($group_id) . '); return false" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">';
    $options .= '<i class="fa-regular fa-pen-to-square fa-lg"></i>';
    $options .= '</a>';
    $options .= '<a href="' . admin_url('groups/delete/' . $group_id) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">';
    $options .= '<i class="fa-regular fa-trash-can fa-lg"></i>';
    $options .= '</a>';
    $options .= '</div>';

    $row[] = $group_id;
    $row[] = '<span class="group-name">' . $name . '</span>';
    $row[] = $leader_name;
    $row[] = $division_name;
    $row[] = $department_names;
    $row[] = $sub_department_names;
    $row[] = $members_str . ' (' . count($members) . ')';
    $row[] = $options;

    $output['aaData'][] = $row;
}
