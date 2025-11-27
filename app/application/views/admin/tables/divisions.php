<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'divisionid',
    'name',
];
$sIndexColumn = 'divisionid';
$sTable       = db_prefix() . 'divisions';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], []);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];

        if ($aColumns[$i] == 'name') {
            $_data = '<a href="#" class="tw-font-medium" onclick="edit_division(this,' . e($aRow['divisionid']) . '); return false" data-name="' . e($aRow['name']) . '">' . e($_data) . '</a>';
        }

        $row[] = $_data;
    }

    $options  = '<div class="tw-flex tw-items-center tw-space-x-2">';
    $options .= '<a href="' . admin_url('divisions/division/' . $aRow['divisionid']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700" ' . _attributes_to_string([
        'onclick' => 'edit_division(this,' . e($aRow['divisionid']) . '); return false', 'data-name' => e($aRow['name']),
    ]) . '>
        <i class="fa-regular fa-pen-to-square fa-lg"></i>
    </a>';

    $options .= '<a href="' . admin_url('divisions/delete/' . $aRow['divisionid']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
        <i class="fa-regular fa-trash-can fa-lg"></i>
    </a>';
    $options .= '</div>';

    $row[] = $options;

    $output['aaData'][] = $row;
}

