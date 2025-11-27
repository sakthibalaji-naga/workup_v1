<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'departmentid',
    'name',
    'parent_department',
];
$sIndexColumn = 'departmentid';
$sTable       = db_prefix() . 'departments';

// Show all departments including parents
$where   = [];

// Build extra columns dynamically to avoid errors if schema differs
$extra = ['email', 'hidefromclient', 'host', 'encryption', 'password', 'delete_after_import', 'imap_username', 'folder', 'parent_department', 'calendar_id'];
if ($this->ci->db->field_exists('responsible_staff', db_prefix().'departments')) {
    $extra[] = 'responsible_staff';
}
$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, $extra);
$output  = $result['output'];
$rResult = $result['rResult'];

// Build a map of departmentid => name for displaying parent department names
$parentDepartmentsMap = [];
$parentsQuery = $this->ci->db->select('departmentid, name')->get(db_prefix() . 'departments')->result_array();
foreach ($parentsQuery as $pd) {
    $parentDepartmentsMap[$pd['departmentid']] = $pd['name'];
}

// Build a map of departmentid => csv division ids
$deptDivisionsMap = [];
$divQuery = $this->ci->db->select('departmentid, GROUP_CONCAT(divisionid) as divisions')
    ->from(db_prefix().'department_divisions')
    ->group_by('departmentid')
    ->get()->result_array();
foreach ($divQuery as $row) {
    $deptDivisionsMap[$row['departmentid']] = $row['divisions'];
}

// Map divisionid => name
$divisionNamesMap = [];
if ($this->ci->db->table_exists(db_prefix() . 'divisions')) {
    $divisionsAll = $this->ci->db->select('divisionid, name')->get(db_prefix() . 'divisions')->result_array();
    foreach ($divisionsAll as $d) {
        $divisionNamesMap[$d['divisionid']] = $d['name'];
    }
}

// Build staff id => name map for HOD name
$staffNamesMap = [];
$staffRows = $this->ci->db->select('staffid, firstname, lastname')->get(db_prefix().'staff')->result_array();
foreach ($staffRows as $s) {
    $staffNamesMap[(int)$s['staffid']] = trim($s['firstname'].' '.$s['lastname']);
}

foreach ($rResult as $aRow) {
    $row = [];

    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        $ps    = '';
        if (! empty($aRow['password'])) {
            $ps = $this->ci->encryption->decrypt($aRow['password']);
        }
        if ($aColumns[$i] == 'name') {
            $divisionsCsvRaw = isset($deptDivisionsMap[$aRow['departmentid']]) ? $deptDivisionsMap[$aRow['departmentid']] : '';
            $divisionsCsv    = e($divisionsCsvRaw);
            $primaryDivisionId = '';
            if (!empty($divisionsCsvRaw)) {
                $parts = explode(',', $divisionsCsvRaw);
                if (!empty($parts[0])) { $primaryDivisionId = (int) $parts[0]; }
            }
            $_data = '<a href="#" class="tw-font-medium" onclick="edit_department(this,' . e($aRow['departmentid']) . '); return false" data-name="' . e($aRow['name']) . '" data-calendar-id="' . e($aRow['calendar_id']) . '" data-email="' . e($aRow['email']) . '" data-hide-from-client="' . e($aRow['hidefromclient']) . '" data-host="' . e($aRow['host']) . '" data-password="' . $ps . '" data-folder="' . e($aRow['folder']) . '" data-imap_username="' . e($aRow['imap_username']) . '" data-encryption="' . e($aRow['encryption']) . '" data-delete-after-import="' . e($aRow['delete_after_import']) . '" data-parent-department="' . e($aRow['parent_department']) . '" data-divisions="' . $divisionsCsv . '"' . (!empty($primaryDivisionId) ? ' data-divisionid="' . e($primaryDivisionId) . '"' : '') . (isset($aRow['responsible_staff']) ? ' data-responsible-staff="' . e($aRow['responsible_staff']) . '"' : '') . '>' . e($_data) . '</a>';
        }
        if ($aColumns[$i] == 'parent_department') {
            // Convert parent department ID to name, show empty if none
            $parentId = $_data;
            $_data = '';
            if (!empty($parentId) && isset($parentDepartmentsMap[$parentId])) {
                $_data = e($parentDepartmentsMap[$parentId]);
            }
        }
        $row[] = $_data;
    }

    // Add HOD Name column (computed)
    $hodName = '';
    if (isset($aRow['responsible_staff']) && !empty($aRow['responsible_staff'])) {
        $sid = (int)$aRow['responsible_staff'];
        if (isset($staffNamesMap[$sid])) {
            $hodName = e($staffNamesMap[$sid]);
        }
    }
    $row[] = $hodName;

    // Add Divisions column (computed)
    $divNamesStr = '';
    if (isset($deptDivisionsMap[$aRow['departmentid']]) && $deptDivisionsMap[$aRow['departmentid']] !== null && $deptDivisionsMap[$aRow['departmentid']] !== '') {
        $ids = explode(',', $deptDivisionsMap[$aRow['departmentid']]);
        $names = [];
        foreach ($ids as $id) {
            $id = (int) $id;
            if (isset($divisionNamesMap[$id])) {
                $names[] = e($divisionNamesMap[$id]);
            }
        }
        $divNamesStr = implode(', ', $names);
    }
    $row[] = $divNamesStr;

    $options = '<div class="tw-flex tw-items-center tw-space-x-2">';
    $options .= '<a href="' . admin_url('departments/department/' . $aRow['departmentid']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700" ' . _attributes_to_string([
        'onclick' => 'edit_department(this,' . e($aRow['departmentid']) . '); return false', 'data-name' => e($aRow['name']), 'data-calendar-id' => e($aRow['calendar_id']), 'data-email' => e($aRow['email']), 'data-hide-from-client' => e($aRow['hidefromclient']), 'data-host' => e($aRow['host']), 'data-password' => $ps, 'data-encryption' => e($aRow['encryption']), 'data-folder' => e($aRow['folder']), 'data-imap_username' => e($aRow['imap_username']), 'data-delete-after-import' => e($aRow['delete_after_import']), 'data-parent-department' => e($aRow['parent_department']), 'data-divisions' => (isset($deptDivisionsMap[$aRow['departmentid']]) ? e($deptDivisionsMap[$aRow['departmentid']]) : ''), 'data-divisionid' => (isset($deptDivisionsMap[$aRow['departmentid']]) && !empty($deptDivisionsMap[$aRow['departmentid']]) ? (int) explode(',', $deptDivisionsMap[$aRow['departmentid']])[0] : ''),
    ]) . '>
        <i class="fa-regular fa-pen-to-square fa-lg"></i>
    </a>';

    $options .= '<a href="' . admin_url('departments/delete/' . $aRow['departmentid']) . '"
    class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
        <i class="fa-regular fa-trash-can fa-lg"></i>
    </a>';

    $options .= '</div>';

    $row[] = $options;

    $output['aaData'][] = $row;
}
