<?php

defined('BASEPATH') or exit('No direct script access allowed');

$filter = isset($filter) ? $filter : 'all';

$table_data = [
    _l('approval_flow_id'),
    _l('approval_name'),
    _l('no_of_approval_steps'),
    _l('who_created'),
    _l('when_created'),
    _l('status'),
];

if ($filter !== 'my_influence') {
    $table_data[] = _l('edit');
}

$table_data = hooks()->apply_filters('approval_flow_table_columns', $table_data);

$table_identifier = isset($table_identifier) && $table_identifier !== ''
    ? $table_identifier
    : 'approval_flow_' . $filter;
$table_identifier = preg_replace('/[^A-Za-z0-9_\-]/', '_', $table_identifier);

$additional_data = [
    'data-last-order-identifier' => $table_identifier,
    'data-default-order'         => get_table_last_order($table_identifier),
    'id'                         => $table_identifier,
];

if ($filter === 'my_influence') {
    $additional_data['data-filter'] = 'my_influence';
}

$additional_classes = ['table-' . $table_identifier];

render_datatable($table_data, 'approval_flow', $additional_classes, $additional_data);
