<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flow_builder_model extends App_Model
{

    // Global variable storage for current flow execution
    private static $currentFlowVariables = [];

    /**
     * Clear current flow variables
     */
    private function clear_current_flow_variables()
    {
        self::$currentFlowVariables = [];
    }

    /**
     * Get current flow variables
     */
    private function get_current_flow_variables($variable_name = null)
    {
        if ($variable_name) {
            return self::$currentFlowVariables[$variable_name] ?? null;
        }
        return self::$currentFlowVariables;
    }

    /**
     * Set current flow variable
     */
    private function set_current_flow_variable($variable_name, $value)
    {
        self::$currentFlowVariables[$variable_name] = $value;
    }
    public function __construct()
    {
        parent::__construct();
        $this->ensure_flow_schema();
    }

    /**
     * Ensure flow tables contain the columns required for logging.
     */
    private function ensure_flow_schema()
    {
        // Log message fields have been removed

        if ($this->db->table_exists('tbl_flow_execution_logs') && !$this->db->field_exists('log_message', 'tbl_flow_execution_logs')) {
            $this->db->query("ALTER TABLE `tbl_flow_execution_logs` ADD `log_message` TEXT NULL AFTER `result`");
        }
    }

    /**
     * Resolve a value from the runtime context using dot/bracket notation or variable references.
     */
    private function get_value_from_context(array $context, $path)
    {
        if (!$path) {
            return null;
        }

        // Handle variable reference syntax {{variable_name.field}}
        if (is_string($path) && preg_match('/^\{\{([^}]+)\}\}$/', $path, $matches)) {
            $variable_path = $matches[1];
            return $this->resolve_variable_reference($context, $variable_path);
        }

        // Handle direct field path
        $segments = $this->split_path($path);
        if (empty($segments)) {
            return null;
        }

        $root = array_shift($segments);
        if (array_key_exists($root, $context)) {
            $current = $context[$root];
        } else {
            $current = $context['api_response'] ?? null;
            array_unshift($segments, $root);
        }

        foreach ($segments as $segment) {
            if (is_array($current) && array_key_exists($segment, $current)) {
                $current = $current[$segment];
                continue;
            }

            if (is_array($current) && ctype_digit((string) $segment) && array_key_exists((int) $segment, $current)) {
                $current = $current[(int) $segment];
                continue;
            }

            return null;
        }

        return $current;
    }

    /**
     * Resolve variable reference like {{variable_name.field.subfield}}
     */
    private function resolve_variable_reference(array $context, $variable_path)
    {
        if (!$variable_path) {
            return null;
        }

        $segments = $this->split_path($variable_path);
        if (empty($segments)) {
            return null;
        }

        $root = array_shift($segments);

        // Look for the variable in context
        if (array_key_exists($root, $context)) {
            $current = $context[$root];
        } else {
            // Fallback to api_response if variable not found directly
            $current = $context['api_response'] ?? null;
            array_unshift($segments, $root);
        }

        // Navigate through the path
        foreach ($segments as $segment) {
            if (is_array($current) && array_key_exists($segment, $current)) {
                $current = $current[$segment];
                continue;
            }

            if (is_array($current) && ctype_digit((string) $segment) && array_key_exists((int) $segment, $current)) {
                $current = $current[(int) $segment];
                continue;
            }

            return null;
        }

        return $current;
    }

    /**
     * Delete flow
     */
    public function delete_flow($id)
    {
        $this->db->where('id', $id)->delete('tbl_flows');
        $this->db->where('flow_id', $id)->delete('tbl_flow_execution_logs');
    }

    /**
     * Duplicate flow
     */
    public function duplicate_flow($id)
    {
        $original_flow = $this->get_flow($id);
        if (!$original_flow) {
            return false;
        }

        $new_flow_data = [
            'name' => $original_flow['name'] . ' (Copy)',
            'description' => $original_flow['description'],
            'flow_data' => $original_flow['flow_data'],
            'status' => $original_flow['status'],
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('tbl_flows', $new_flow_data);
        return $this->db->insert_id();
    }

    /**
     * Execute flow
     */
    public function execute_flow($id)
    {
        // Clear previous flow variables
        $this->clear_current_flow_variables();

        $flow = $this->get_flow($id);
        if (!$flow) {
            return ['success' => false, 'message' => 'Flow not found'];
        }

        $flow_data = json_decode($flow['flow_data'], true);

        $start_time = microtime(true);

        try {
            $execution = $this->process_flow($flow_data, []);

            $execution_time = microtime(true) - $start_time;

            $log = $execution['log'] ?? [];
            $context = $execution['context'] ?? [];

            $this->log_execution($id, 'success', $execution_time, [
                'log' => $log,
                'context' => $context,
                'variables' => $this->get_current_flow_variables()
            ]);

            return [
                'success' => true,
                'message' => 'Flow executed successfully',
                'result' => $log,
                'context' => $context,
                'execution_time' => $execution_time
            ];
        } catch (Exception $e) {
            $execution_time = microtime(true) - $start_time;

            $variables_snapshot = $this->get_current_flow_variables();
            $error_context = [
                'message' => $e->getMessage(),
                'code' => method_exists($e, 'getCode') ? $e->getCode() : 0,
                'type' => get_class($e)
            ];

            $error_result = [
                'error' => $error_context,
                'trace' => $e->getTraceAsString(),
                'variables' => $variables_snapshot
            ];

            $this->log_execution($id, 'error', $execution_time, $error_result);

            // Clear flow variables on error
            $this->clear_current_flow_variables();

            return [
                'success' => false,
                'message' => 'Flow execution failed',
                'execution_time' => $execution_time,
                'error' => $error_context,
                'result' => $error_result,
                'context' => $variables_snapshot
            ];
        }
    }





    /**
     * Process flow logic
     */
    private function process_flow($flow_data, array $context = [])
    {
        $nodes = $flow_data['nodes'] ?? [];
        $edges = $flow_data['edges'] ?? [];

        $start_node = null;
        foreach ($nodes as $node) {
            $node_type = $node['data']['type'] ?? ($node['type'] ?? '');
            if ($node_type === 'api_trigger') {
                $start_node = $node;
                break;
            }
        }

        if (!$start_node && !empty($nodes)) {
            $start_node = reset($nodes);
        }

        if (!$start_node) {
            throw new Exception('No trigger node found in flow');
        }

        return $this->execute_node($start_node, $nodes, $edges, $context);
    }

    /**
     * Execute a single node
     */
    private function execute_node($node, $all_nodes, $edges, array $context = [])
    {
        $node_id = $node['id'];
        $node_type = $node['data']['type'] ?? '';
        $log = [];

        switch ($node_type) {
            case 'api_trigger':
                $result = $this->execute_api_trigger($node, $context);
                $context = $result['context'];
                $log[] = $result['log'];
                break;

            case 'condition':
                $result = $this->execute_condition($node, $context);
                $context = $result['context'];
                $log[] = $result['log'];

                $next_edges = array_filter($edges, function ($edge) use ($node) {
                    return ($edge['source'] ?? null) === $node['id'];
                });

                foreach ($next_edges as $edge) {
                    $is_true_path = in_array($edge['sourceHandle'] ?? '', ['true', 'condition-true', 'true-output'], true);
                    if (($result['condition_met'] && $is_true_path) || (!$result['condition_met'] && !$is_true_path)) {
                        $next_node = $this->find_node_by_id($all_nodes, $edge['target'] ?? null);
                        if ($next_node) {
                            $child = $this->execute_node($next_node, $all_nodes, $edges, $context);
                            $context = $child['context'];
                            $log = array_merge($log, $child['log']);
                        }
                    }
                }

                return [
                    'context' => $context,
                    'log' => $log
                ];

            case 'staff_create':
                $result = $this->execute_staff_create($node, $context);
                $context = $result['context'];
                $log[] = $result['log'];
                break;

            case 'staff_update':
                $result = $this->execute_staff_update($node, $context);
                $context = $result['context'];
                $log[] = $result['log'];
                break;

            case 'ticket_create':
                $result = $this->execute_ticket_create($node, $context);
                $context = $result['context'];
                $log[] = $result['log'];
                break;

            case 'ticket_update':
                $result = $this->execute_ticket_update($node, $context);
                $context = $result['context'];
                $log[] = $result['log'];
                break;

            case 'division_create':
                $result = $this->execute_division_create($node, $context);
                $context = $result['context'];
                $log[] = $result['log'];
                break;

            case 'division_update':
                $result = $this->execute_division_update($node, $context);
                $context = $result['context'];
                $log[] = $result['log'];
                break;

            case 'department_create':
                $result = $this->execute_department_create($node, $context);
                $context = $result['context'];
                $log[] = $result['log'];
                break;

            case 'department_update':
                $result = $this->execute_department_update($node, $context);
                $context = $result['context'];
                $log[] = $result['log'];
                break;

            case 'sms_send':
                $result = $this->execute_sms_send($node, $context);
                $context = $result['context'];
                $log[] = $result['log'];
                break;

            case 'whatsapp_send':
                $result = $this->execute_whatsapp_send($node, $context);
                $context = $result['context'];
                $log[] = $result['log'];
                break;

            case 'email_send':
                $result = $this->execute_email_send($node, $context);
                $context = $result['context'];
                $log[] = $result['log'];
                break;

            default:
                throw new Exception("Unknown node type: $node_type");
        }

        $next_edges = array_filter($edges, function ($edge) use ($node_id) {
            return ($edge['source'] ?? null) === $node_id;
        });

        foreach ($next_edges as $edge) {
            $next_node = $this->find_node_by_id($all_nodes, $edge['target'] ?? null);
            if ($next_node) {
                $child = $this->execute_node($next_node, $all_nodes, $edges, $context);
                $context = $child['context'];
                $log = array_merge($log, $child['log']);
            }
        }

        return [
            'context' => $context,
            'log' => $log
        ];
    }

    /**
     * Execute API trigger node
     */
    private function execute_api_trigger($node, array $context)
    {
        $config = $node['data'] ?? [];
        $variable_name = !empty($config['variable_name']) ? trim($config['variable_name']) : 'api_response';
        $raw_response = null;

        if (empty($config['external_api_id'])) {
            throw new Exception('API trigger is not configured with an external API.');
        }

        $api_call = $this->call_external_api((int) $config['external_api_id']);
        if (!$api_call['success']) {
            $message = !empty($api_call['error']) ? $api_call['error'] : 'HTTP ' . ($api_call['http_code'] ?? '0') . ' - Unexpected response';
            if (empty($api_call['error']) && !empty($api_call['raw'])) {
                $snippet = substr($api_call['raw'], 0, 200);
                $message .= ' | Body: ' . $snippet;
            }
            throw new Exception('API trigger failed: ' . $message);
        }

        $raw_response = $api_call['raw'];
        $normalized_response = $this->normalize_api_response($api_call['decoded']);
        $this->store_api_response_mapping((int) $config['external_api_id'], $normalized_response);

        $context[$variable_name] = $normalized_response;
        $context['api_response'] = $normalized_response;
        $this->set_current_flow_variable($variable_name, $normalized_response);

        $raw_variable_name = $variable_name . '_raw';
        $context[$raw_variable_name] = $raw_response;
        $this->set_current_flow_variable($raw_variable_name, $raw_response);

        $http_code = $api_call['http_code'] ?? null;
        $status_variable_name = $variable_name . '_status';
        $context[$status_variable_name] = $http_code;
        $this->set_current_flow_variable($status_variable_name, $http_code);
        $context['http_status_code'] = $http_code;
        $this->set_current_flow_variable('http_status_code', $http_code);

        $http_status_route = $http_code !== null ? (string) $http_code : 'unknown';
        $configured_statuses = [];
        if (!empty($config['http_status_routing']['enabled'])) {
            $configured_statuses = array_map('strval', (array) ($config['http_status_routing']['statuses'] ?? []));
            if (!in_array($http_status_route, $configured_statuses, true)) {
                $http_status_route = 'default';
            }
        }
        $context['http_status_route'] = $http_status_route;
        $context['http_status_configured'] = $configured_statuses;
        $this->set_current_flow_variable('http_status_route', $http_status_route);
        $this->set_current_flow_variable('http_status_configured', $configured_statuses);

        $generated_variables = [];
        if (!empty($config['generated_variables']) && is_array($config['generated_variables'])) {
            foreach ($config['generated_variables'] as $variable_config) {
                $custom_name = trim($variable_config['name'] ?? '');
                $field_path = $variable_config['field_path'] ?? '';
                if ($custom_name === '' || $field_path === '') {
                    continue;
                }
                $value = $this->get_value_from_context($context, $field_path);
                $context[$custom_name] = $value;
                $this->set_current_flow_variable($custom_name, $value);
                $generated_variables[$custom_name] = $field_path;
            }
        }

        $log = [
            'node_id' => $node['id'],
            'type' => 'api_trigger',
            'external_api_id' => $config['external_api_id'],
            'variable' => $variable_name,
            'raw_variable' => $raw_variable_name,
            'status_variable' => $status_variable_name,
            'http_code' => $http_code,
            'http_status_route' => $http_status_route,
            'http_status_configured' => $configured_statuses,
            'generated_variables' => array_keys($generated_variables)
        ];

        return [
            'context' => $context,
            'log' => $log
        ];
    }

    /**
     * Store response field metadata for later use
     */
    private function store_api_response_mapping($external_api_id, $response)
    {
        if (!$external_api_id || !is_array($response)) {
            return;
        }

        $this->ensure_api_response_mappings_table();

        $fields = [];
        $this->collect_response_fields($response, '', $fields);

        if (empty($fields)) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->where('external_api_id', $external_api_id)->delete('tbl_api_response_mappings');

        foreach ($fields as $path => $field) {
            $data = [
                'external_api_id' => $external_api_id,
                'field_path'      => $path,
                'field_name'      => $field['name'],
                'field_type'      => $field['type'],
                'is_array_element'=> $field['is_array'] ? 1 : 0,
                'array_index'     => $field['index'],
                'parent_path'     => $field['parent'],
                'created_at'      => $now,
                'updated_at'      => $now,
            ];

            $this->db->insert('tbl_api_response_mappings', $data);
        }
    }

    /**
     * Recursively collect response field metadata
     */
    private function collect_response_fields($data, $parent_path, array &$result, $index = null)
    {
        if (is_array($data)) {
            $isAssoc = array_keys($data) !== range(0, count($data) - 1);
            if ($isAssoc) {
                foreach ($data as $key => $value) {
                    $path = $parent_path === '' ? $key : $parent_path . '.' . $key;
                    $this->collect_response_fields($value, $path, $result);
                }
            } else {
                foreach ($data as $idx => $value) {
                    $path = $parent_path . '[' . $idx . ']';
                    $this->collect_response_fields($value, $path, $result, $idx);
                }
            }
        } else {
            $fieldPath = ltrim($parent_path, '.');
            if ($fieldPath === '') {
                $fieldPath = 'value';
            }
            $fieldName = ucfirst(str_replace(['_', '.'], ' ', preg_replace('/\[.*?\]/', '', basename(str_replace('.', '/', $fieldPath)))));
            if (!isset($result[$fieldPath])) {
                $result[$fieldPath] = [
                    'name'    => $fieldName,
                    'type'    => $this->determine_field_type($data),
                    'is_array'=> $index !== null,
                    'index'   => $index,
                    'parent'  => $this->resolve_parent_path($fieldPath),
                ];
            }
        }
    }

    /**
     * Determine value type helper
     */
    private function determine_field_type($value)
    {
        if (is_bool($value)) {
            return 'boolean';
        }
        if (is_int($value)) {
            return 'integer';
        }
        if (is_float($value)) {
            return 'number';
        }
        if ($value === null) {
            return 'null';
        }
        if (is_array($value)) {
            return 'array';
        }
        return 'string';
    }

    /**
     * Resolve parent path helper
     */
    private function resolve_parent_path($path)
    {
        if (strpos($path, '.') === false && strpos($path, '[') === false) {
            return null;
        }

        $parent = preg_replace('/(\.[^.\\[]+|\[[0-9]+\])$/', '', $path);
        return $parent === '' ? null : $parent;
    }



    /**
     * Convert a dotted/bracket path into an array of segments.
     */
    private function split_path($path)
    {
        $path = trim((string) $path);
        if ($path === '') {
            return [];
        }

        $normalized = preg_replace('/\[(\d+)\]/', '.$1', $path);
        $normalized = preg_replace('/\.+/', '.', $normalized);
        $normalized = trim($normalized, '.');

        if ($normalized === '') {
            return [];
        }

        return explode('.', $normalized);
    }

    /**
     * Ensure external APIs table exists
     */
    private function ensure_external_apis_table()
    {
        if ($this->db->table_exists('tbl_external_apis')) {
            return;
        }

        $sql = "
        CREATE TABLE IF NOT EXISTS `tbl_external_apis` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL COMMENT 'Unique name for the external API',
          `api_url` text NOT NULL COMMENT 'Full URL of the external API endpoint',
          `request_method` varchar(10) NOT NULL DEFAULT 'GET' COMMENT 'HTTP method (GET, POST, PUT, DELETE)',
          `request_body` text DEFAULT NULL COMMENT 'JSON body for POST/PUT requests',
          `headers` text DEFAULT NULL COMMENT 'JSON format headers',
          `cron_schedule` varchar(100) NOT NULL COMMENT 'Cron expression for scheduling',
          `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether the external API is active',
          `created_at` datetime NOT NULL,
          `last_run` datetime DEFAULT NULL COMMENT 'Last execution time',
          `next_run` datetime DEFAULT NULL COMMENT 'Next scheduled execution time',
          PRIMARY KEY (`id`),
          UNIQUE KEY `name` (`name`),
          KEY `is_active` (`is_active`),
          KEY `next_run` (`next_run`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

        CREATE TABLE IF NOT EXISTS `tbl_external_api_logs` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `external_api_id` int(11) DEFAULT NULL COMMENT 'Reference to tbl_external_apis.id',
          `status_code` int(11) DEFAULT NULL COMMENT 'HTTP status code from API response',
          `response_body` longtext DEFAULT NULL COMMENT 'Full response from the external API',
          `error_message` text DEFAULT NULL COMMENT 'Error message if the API call failed',
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `external_api_id` (`external_api_id`),
          KEY `status_code` (`status_code`),
          KEY `created_at` (`created_at`),
          FOREIGN KEY (`external_api_id`) REFERENCES `tbl_external_apis` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ";

        $this->db->query($sql);
    }

    /**
     * Ensure API response mapping table exists
     */
    private function ensure_api_response_mappings_table()
    {
        if ($this->db->table_exists('tbl_api_response_mappings')) {
            return;
        }

        $sql_file = APPPATH . 'database/create_tbl_api_response_mappings.sql';
        if (!file_exists($sql_file)) {
            return;
        }

        $sql = file_get_contents($sql_file);
        $this->db->query($sql);
    }

    /**
     * Execute condition node
     */
    private function execute_condition($node, array $context)
    {
        $condition_data = $node['data']['config'] ?? $node['data'];
        $field = $condition_data['field'] ?? '';
        $operator = $condition_data['operator'] ?? 'equals';
        $expected_value = $condition_data['value'] ?? '';

        $actual_value = $this->get_value_from_context($context, $field);
        $condition_met = $this->evaluate_condition($actual_value, $operator, $expected_value);

        return [
            'context' => $context,
            'log' => [
                'node_id' => $node['id'],
                'type' => 'condition',
                'field' => $field,
                'operator' => $operator,
                'expected' => $expected_value,
                'actual' => $actual_value,
                'result' => $condition_met
            ],
            'condition_met' => $condition_met
        ];
    }

    /**
     * Execute staff create
     */
    private function execute_staff_create($node, array $context)
    {
        $config = $node['data']['config'] ?? $node['data'];
        $mapping = $config['mapping'] ?? [];

        $staff_data = [];
        foreach ($mapping as $map) {
            $source_field = $map['source_field'] ?? '';
            $target_field = $map['target_field'] ?? '';
            if (!$target_field) {
                continue;
            }
            $value_type = $map['value_type'] ?? 'field';

            if ($value_type === 'static') {
                $staff_data[$target_field] = $map['static_value'] ?? '';
            } else {
                $staff_data[$target_field] = $this->get_value_from_context($context, $source_field);
            }
        }

        if (!isset($staff_data['password']) || $staff_data['password'] === null) {
            $staff_data['password'] = 'temp_password_' . time();
        }

        $this->db->insert('tblstaff', $staff_data);
        $staff_id = $this->db->insert_id();

        $context['_last_staff_id'] = $staff_id;

        return [
            'context' => $context,
            'log' => [
                'node_id' => $node['id'],
                'type' => 'staff_create',
                'staff_id' => $staff_id,
                'fields' => array_keys($staff_data)
            ]
        ];
    }

    /**
     * Execute staff update
     */
    private function execute_staff_update($node, array $context)
    {
        $config = $node['data']['config'] ?? $node['data'];
        $mapping = $config['mapping'] ?? [];
        $staff_id_field = $config['staff_id_field'] ?? '';

        $staff_id = $this->get_value_from_context($context, $staff_id_field);
        if (!$staff_id) {
            throw new Exception('Staff ID not found for update operation');
        }

        $staff_data = [];
        foreach ($mapping as $map) {
            $source_field = $map['source_field'] ?? '';
            $target_field = $map['target_field'] ?? '';
            if (!$target_field) {
                continue;
            }
            $value_type = $map['value_type'] ?? 'field';

            if ($value_type === 'static') {
                $staff_data[$target_field] = $map['static_value'] ?? '';
            } else {
                $staff_data[$target_field] = $this->get_value_from_context($context, $source_field);
            }
        }

        if (!empty($staff_data)) {
            $this->db->where('staffid', $staff_id)->update('tblstaff', $staff_data);
        }

        return [
            'context' => $context,
            'log' => [
                'node_id' => $node['id'],
                'type' => 'staff_update',
                'staff_id' => $staff_id,
                'fields' => array_keys($staff_data)
            ]
        ];
    }

    /**
     * Execute ticket create
     */
    private function execute_ticket_create($node, array $context)
    {
        $config = $node['data']['config'] ?? $node['data'];
        $mapping = $config['mapping'] ?? [];

        $ticket_data = [];
        foreach ($mapping as $map) {
            $source_field = $map['source_field'] ?? '';
            $target_field = $map['target_field'] ?? '';
            if (!$target_field) {
                continue;
            }
            $value_type = $map['value_type'] ?? 'field';

            if ($value_type === 'static') {
                $ticket_data[$target_field] = $map['static_value'] ?? '';
            } else {
                $ticket_data[$target_field] = $this->get_value_from_context($context, $source_field);
            }
        }

        $this->db->insert('tbltickets', $ticket_data);
        $ticket_id = $this->db->insert_id();

        $context['_last_ticket_id'] = $ticket_id;

        return [
            'context' => $context,
            'log' => [
                'node_id' => $node['id'],
                'type' => 'ticket_create',
                'ticket_id' => $ticket_id,
                'fields' => array_keys($ticket_data)
            ]
        ];
    }

    /**
     * Execute ticket update
     */
    private function execute_ticket_update($node, array $context)
    {
        $config = $node['data']['config'] ?? $node['data'];
        $mapping = $config['mapping'] ?? [];
        $ticket_id_field = $config['ticket_id_field'] ?? '';

        $ticket_id = $this->get_value_from_context($context, $ticket_id_field);
        if (!$ticket_id) {
            throw new Exception('Ticket ID not found for update operation');
        }

        $ticket_data = [];
        foreach ($mapping as $map) {
            $source_field = $map['source_field'] ?? '';
            $target_field = $map['target_field'] ?? '';
            if (!$target_field) {
                continue;
            }
            $value_type = $map['value_type'] ?? 'field';

            if ($value_type === 'static') {
                $ticket_data[$target_field] = $map['static_value'] ?? '';
            } else {
                $ticket_data[$target_field] = $this->get_value_from_context($context, $source_field);
            }
        }

        if (!empty($ticket_data)) {
            $this->db->where('ticketid', $ticket_id)->update('tbltickets', $ticket_data);
        }

        return [
            'context' => $context,
            'log' => [
                'node_id' => $node['id'],
                'type' => 'ticket_update',
                'ticket_id' => $ticket_id,
                'fields' => array_keys($ticket_data)
            ]
        ];
    }

    /**
     * Execute division create
     */
    private function execute_division_create($node, array $context)
    {
        $config = $node['data']['config'] ?? $node['data'];
        $mapping = $config['mapping'] ?? [];

        $division_data = [];
        foreach ($mapping as $map) {
            $source_field = $map['source_field'] ?? '';
            $target_field = $map['target_field'] ?? '';
            if (!$target_field) {
                continue;
            }
            $value_type = $map['value_type'] ?? 'field';

            if ($value_type === 'static') {
                $division_data[$target_field] = $map['static_value'] ?? '';
            } else {
                $division_data[$target_field] = $this->get_value_from_context($context, $source_field);
            }
        }

        $this->db->insert('tbldivisions', $division_data);
        $division_id = $this->db->insert_id();
        $context['_last_division_id'] = $division_id;

        return [
            'context' => $context,
            'log' => [
                'node_id' => $node['id'],
                'type' => 'division_create',
                'division_id' => $division_id,
                'fields' => array_keys($division_data)
            ]
        ];
    }

    /**
     * Execute division update
     */
    private function execute_division_update($node, array $context)
    {
        $config = $node['data']['config'] ?? $node['data'];
        $mapping = $config['mapping'] ?? [];
        $division_id_field = $config['division_id_field'] ?? '';

        $division_id = $this->get_value_from_context($context, $division_id_field);
        if (!$division_id) {
            throw new Exception('Division ID not found for update operation');
        }

        $division_data = [];
        foreach ($mapping as $map) {
            $source_field = $map['source_field'] ?? '';
            $target_field = $map['target_field'] ?? '';
            if (!$target_field) {
                continue;
            }
            $value_type = $map['value_type'] ?? 'field';

            if ($value_type === 'static') {
                $division_data[$target_field] = $map['static_value'] ?? '';
            } else {
                $division_data[$target_field] = $this->get_value_from_context($context, $source_field);
            }
        }

        if (!empty($division_data)) {
            $this->db->where('divisionid', $division_id)->update('tbldivisions', $division_data);
        }

        return [
            'context' => $context,
            'log' => [
                'node_id' => $node['id'],
                'type' => 'division_update',
                'division_id' => $division_id,
                'fields' => array_keys($division_data)
            ]
        ];
    }

    /**
     * Execute department create
     */
    private function execute_department_create($node, array $context)
    {
        $config = $node['data']['config'] ?? $node['data'];
        $mapping = $config['mapping'] ?? [];

        $department_data = [];
        foreach ($mapping as $map) {
            $source_field = $map['source_field'] ?? '';
            $target_field = $map['target_field'] ?? '';
            if (!$target_field) {
                continue;
            }
            $value_type = $map['value_type'] ?? 'field';

            if ($value_type === 'static') {
                $department_data[$target_field] = $map['static_value'] ?? '';
            } else {
                $department_data[$target_field] = $this->get_value_from_context($context, $source_field);
            }
        }

        $this->db->insert('tbldepartments', $department_data);
        $department_id = $this->db->insert_id();
        $context['_last_department_id'] = $department_id;

        return [
            'context' => $context,
            'log' => [
                'node_id' => $node['id'],
                'type' => 'department_create',
                'department_id' => $department_id,
                'fields' => array_keys($department_data)
            ]
        ];
    }

    /**
     * Execute department update
     */
    private function execute_department_update($node, array $context)
    {
        $config = $node['data']['config'] ?? $node['data'];
        $mapping = $config['mapping'] ?? [];
        $department_id_field = $config['department_id_field'] ?? '';

        $department_id = $this->get_value_from_context($context, $department_id_field);
        if (!$department_id) {
            throw new Exception('Department ID not found for update operation');
        }

        $department_data = [];
        foreach ($mapping as $map) {
            $source_field = $map['source_field'] ?? '';
            $target_field = $map['target_field'] ?? '';
            if (!$target_field) {
                continue;
            }
            $value_type = $map['value_type'] ?? 'field';

            if ($value_type === 'static') {
                $department_data[$target_field] = $map['static_value'] ?? '';
            } else {
                $department_data[$target_field] = $this->get_value_from_context($context, $source_field);
            }
        }

        if (!empty($department_data)) {
            $this->db->where('departmentid', $department_id)->update('tbldepartments', $department_data);
        }

        return [
            'context' => $context,
            'log' => [
                'node_id' => $node['id'],
                'type' => 'department_update',
                'department_id' => $department_id,
                'fields' => array_keys($department_data)
            ]
        ];
    }

    /**
     * Execute SMS send
     */
    private function execute_sms_send($node, array $context)
    {
        $config = $node['data']['config'] ?? $node['data'];
        $phone_field = $config['phone_field'] ?? '';
        $message_field = $config['message_field'] ?? '';

        $phone = $this->get_value_from_context($context, $phone_field);
        $message = $this->get_value_from_context($context, $message_field);

        if (!$phone || !$message) {
            throw new Exception('Phone number or message not found for SMS');
        }

        log_message('info', "SMS would be sent to $phone: $message");

        return [
            'context' => $context,
            'log' => [
                'node_id' => $node['id'],
                'type' => 'sms_send',
                'phone' => $phone
            ]
        ];
    }

    /**
     * Execute WhatsApp send
     */
    private function execute_whatsapp_send($node, array $context)
    {
        $config = $node['data']['config'] ?? $node['data'];
        $phone_field = $config['phone_field'] ?? '';
        $message_field = $config['message_field'] ?? '';

        $phone = $this->get_value_from_context($context, $phone_field);
        $message = $this->get_value_from_context($context, $message_field);

        if (!$phone || !$message) {
            throw new Exception('Phone number or message not found for WhatsApp');
        }

        log_message('info', "WhatsApp message would be sent to $phone: $message");

        return [
            'context' => $context,
            'log' => [
                'node_id' => $node['id'],
                'type' => 'whatsapp_send',
                'phone' => $phone
            ]
        ];
    }

    /**
     * Execute email send
     */
    private function execute_email_send($node, array $context)
    {
        $config = $node['data']['config'] ?? $node['data'];
        $email_field = $config['email_field'] ?? '';
        $subject_field = $config['subject_field'] ?? '';
        $body_field = $config['body_field'] ?? '';

        $email = $this->get_value_from_context($context, $email_field);
        $subject = $this->get_value_from_context($context, $subject_field);
        $body = $this->get_value_from_context($context, $body_field);

        if (!$email || !$subject || !$body) {
            throw new Exception('Email, subject, or body not found for email sending');
        }

        log_message('info', "Email would be sent to $email with subject: $subject");

        return [
            'context' => $context,
            'log' => [
                'node_id' => $node['id'],
                'type' => 'email_send',
                'email' => $email
            ]
        ];
    }

    /**
     * Evaluate condition
     */
    private function evaluate_condition($actual_value, $operator, $expected_value)
    {
        switch ($operator) {
            case 'equals':
                return $actual_value == $expected_value;
            case 'not_equals':
                return $actual_value != $expected_value;
            case 'contains':
                return strpos($actual_value, $expected_value) !== false;
            case 'not_contains':
                return strpos($actual_value, $expected_value) === false;
            case 'greater_than':
                return $actual_value > $expected_value;
            case 'less_than':
                return $actual_value < $expected_value;
            default:
                return false;
        }
    }

    /**
     * Get API response (this would typically come from your API system)
     */
    private function get_api_response()
    {
        // This is a placeholder - in real implementation,
        // this would get the latest API response or a specific one
        return [
            'status' => 'success',
            'message' => 'Sample API response',
            'data' => [
                'user_id' => 1,
                'action' => 'create_staff'
            ]
        ];
    }

    /**
     * Log flow execution
     */
    private function log_execution($flow_id, $status, $execution_time, $result, $message = '')
    {
        $this->ensure_flow_schema();

        if (is_array($result) || is_object($result)) {
            $result = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } elseif (!is_string($result)) {
            $result = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $log_data = [
            'flow_id' => $flow_id,
            'status' => $status,
            'execution_time' => $execution_time,
            'result' => $result,
            'log_message' => is_string($message) ? trim($message) : '',
            'executed_at' => date('Y-m-d H:i:s'),
            'executed_by' => get_staff_user_id()
        ];

        $this->db->insert('tbl_flow_execution_logs', $log_data);
    }

    /**
     * Get specific flow by ID
     */
    public function get_flow($id)
    {
        $flow = $this->db->where('id', $id)->get('tbl_flows')->row_array();

        if ($flow && !empty($flow['flow_data'])) {
            // Ensure flow_data is properly decoded
            $decoded_flow_data = json_decode($flow['flow_data'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $flow['flow_data'] = $decoded_flow_data;
            } else {
                // If JSON decoding fails, keep the original data
                $flow['flow_data'] = $flow['flow_data'];
            }
        }

        return $flow;
    }

    /**
     * Test API connection and return response details using existing external API functionality
     */
    public function test_api_connection($api_id)
    {
        // Get API configuration from database
        $this->db->where('id', $api_id);
        $api_config = $this->db->get('tbl_external_apis')->row();

        if (!$api_config) {
            return [
                'success' => false,
                'message' => 'API configuration not found'
            ];
        }

        // Use the existing API calling logic directly
        $api_call = $this->call_external_api($api_id);

        if (!$api_call['success']) {
            $error_message = $api_call['error'] ?: 'HTTP ' . $api_call['http_code'];
            $raw_response = '';

            // Try to extract error details from response if available
            if (!empty($api_call['raw'])) {
                $raw_response = substr($api_call['raw'], 0, 1000);

                // Try to decode JSON error response
                $decoded_error = json_decode($api_call['raw'], true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded_error['Message'])) {
                    $error_message .= ' - ' . $decoded_error['Message'];
                }
            }

            return [
                'success' => false,
                'message' => 'API call failed: ' . $error_message,
                'http_code' => $api_call['http_code'],
                'error' => $api_call['error'],
                'raw_response' => $raw_response,
                'suggestions' => [
                    'Check API URL and ensure it is accessible',
                    'Verify API authentication credentials',
                    'Confirm request headers are correct',
                    'Ensure API endpoint exists and is active'
                ]
            ];
        }

        // Parse and structure the response for display
        $response_data = $this->normalize_api_response($api_call['decoded']);
        $response_structure = $this->build_response_structure($response_data);

        return [
            'success' => true,
            'message' => 'API call successful',
            'http_code' => $api_call['http_code'],
            'response_type' => $this->detect_response_type($response_data),
            'response_structure' => $response_structure,
            'raw_response' => substr($api_call['raw'], 0, 1000),
            'decoded_response' => $response_data
        ];
    }

    /**
     * Detect response type from parsed data
     */
    private function detect_response_type($data)
    {
        if (is_array($data)) {
            return 'object_array';
        }
        if (is_object($data)) {
            return 'object';
        }
        return 'primitive';
    }

    /**
     * Build response structure for display
     */
    private function build_response_structure($data, $display_prefix = '', $max_depth = 5, $current_depth = 0, $actual_prefix = '')
    {
        if ($current_depth >= $max_depth || !$data) {
            return [];
        }

        $structure = [];

        if (is_array($data) && !empty($data)) {
            // Check if it's a numeric array (list)
            $is_list = array_keys($data) === range(0, count($data) - 1);

            if ($is_list) {
                // Array of items
                $sample = $data[0] ?? $data;
                $display_name = $display_prefix ?: 'root_array';
                $current_path = $actual_prefix !== '' ? $actual_prefix : ($display_prefix ?: 'root_array');
                $structure[] = [
                    'type' => 'array',
                    'name' => $display_name,
                    'path' => $current_path,
                    'description' => 'Array of ' . count($data) . ' item(s)',
                    'sample_type' => gettype($sample),
                    'children' => is_array($sample) || is_object($sample) ?
                        $this->build_response_structure(
                            $sample,
                            'item',
                            $max_depth,
                            $current_depth + 1,
                            ($current_path !== '' ? $current_path : $display_name) . '[0]'
                        ) : []
                ];
            } else {
                // Associative array/object
                foreach ($data as $key => $value) {
                    $field_name = $display_prefix ? $display_prefix . '.' . $key : $key;
                    $field_path = $actual_prefix ? $actual_prefix . '.' . $key : $key;
                    $field_type = gettype($value);

                    $field = [
                        'type' => 'field',
                        'name' => $field_name,
                        'path' => $field_path,
                        'field_type' => $field_type,
                        'description' => ucfirst($field_type) . ' field: ' . $key,
                        'sample_value' => is_scalar($value) ? $value : null,
                        'children' => []
                    ];

                    // Add children for nested objects/arrays
                    if ((is_array($value) || is_object($value)) && $current_depth < $max_depth - 1) {
                        $field['children'] = $this->build_response_structure(
                            $value,
                            $field_name,
                            $max_depth,
                            $current_depth + 1,
                            $field_path
                        );
                    }

                    $structure[] = $field;
                }
            }
        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $field_name = $display_prefix ? $display_prefix . '.' . $key : $key;
                $field_path = $actual_prefix ? $actual_prefix . '.' . $key : $key;
                $field_type = gettype($value);

                $field = [
                    'type' => 'field',
                    'name' => $field_name,
                    'path' => $field_path,
                    'field_type' => $field_type,
                    'description' => ucfirst($field_type) . ' field: ' . $key,
                    'sample_value' => is_scalar($value) ? $value : null,
                    'children' => []
                ];

                // Add children for nested objects/arrays
                if ((is_array($value) || is_object($value)) && $current_depth < $max_depth - 1) {
                    $field['children'] = $this->build_response_structure(
                        $value,
                        $field_name,
                        $max_depth,
                        $current_depth + 1,
                        $field_path
                    );
                }

                $structure[] = $field;
            }
        } else {
            // Primitive value
            $structure[] = [
                'type' => 'field',
                'name' => $display_prefix ?: 'root_value',
                'path' => $actual_prefix !== '' ? $actual_prefix : ($display_prefix ?: 'root_value'),
                'field_type' => gettype($data),
                'description' => 'Primitive value',
                'sample_value' => $data,
                'children' => []
            ];
        }

        return $structure;
    }

    /**
     * Get all flows
     */
    public function get_all_flows()
    {
        return $this->db->get('tbl_flows')->result_array();
    }

    /**
     * Get execution logs
     */
    public function get_execution_logs($limit = 50)
    {
        return $this->db->order_by('executed_at', 'DESC')
                       ->limit($limit)
                       ->get('tbl_flow_execution_logs')
                       ->result_array();
    }

    /**
     * Save flow
     */
    public function save_flow($flow_data)
    {
        $id = isset($flow_data['id']) ? (int) $flow_data['id'] : null;
        $name = $flow_data['name'] ?? '';
        $description = $flow_data['description'] ?? '';
        $flow_json = $flow_data['flow_data'] ?? '';
        $status = isset($flow_data['status']) ? (int) $flow_data['status'] : 1;

        // Ensure flow_data is properly encoded as JSON
        $encoded_flow_data = '';
        if (is_string($flow_json)) {
            $encoded_flow_data = $flow_json;
        } elseif (is_array($flow_json)) {
            $encoded_flow_data = json_encode($flow_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } else {
            // Handle other cases
            $encoded_flow_data = json_encode($flow_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }

        $data = [
            'name' => $name,
            'description' => $description,
            'flow_data' => $encoded_flow_data,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($id) {
            // Update existing flow
            $this->db->where('id', $id)->update('tbl_flows', $data);
            return $id;
        } else {
            // Create new flow
            $data['created_by'] = get_staff_user_id();
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_flows', $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Call external API
     */
    private function call_external_api($api_id)
    {
        // Ensure external APIs table exists
        $this->ensure_external_apis_table();

        // Get API configuration from database
        $this->db->where('id', $api_id);
        $api_config = $this->db->get('tbl_external_apis')->row_array();

        if (!$api_config) {
            return [
                'success' => false,
                'error' => 'API configuration not found',
                'http_code' => 0
            ];
        }

        // Prepare API call
        $url = $api_config['api_url'];
        $method = strtoupper($api_config['request_method'] ?? 'GET');
        $headers = [];

        // Parse headers if available
        if (!empty($api_config['headers'])) {
            $header_lines = explode("\n", $api_config['headers']);
            foreach ($header_lines as $line) {
                $line = trim($line);
                if (!empty($line) && strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $headers[trim($key)] = trim($value);
                }
            }
        }

        // Initialize cURL
        $ch = curl_init();

        // Set URL and method
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // Set headers with proper JSON content-type for POST/PUT requests
        $header_array = [];

        // Always set default headers
        $header_array[] = 'Content-Type: application/json';
        $header_array[] = 'Accept: application/json';

        // Add custom headers if available
        if (!empty($headers)) {
            foreach ($headers as $key => $value) {
                // Don't override Content-Type if already set in custom headers
                if (strtolower($key) !== 'content-type') {
                    $header_array[] = "$key: $value";
                }
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array);

        // Set request body for POST/PUT requests
        if (in_array($method, ['POST', 'PUT', 'PATCH']) && !empty($api_config['request_body'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $api_config['request_body']);
        }

        // Execute request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $decoded = null;

        // Try to decode JSON response
        if ($response && $http_code >= 200 && $http_code < 300) {
            $decoded = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $decoded = $response; // Keep as string if not valid JSON
            }
        }

        curl_close($ch);

        return [
            'success' => $http_code >= 200 && $http_code < 300,
            'http_code' => $http_code,
            'raw' => $response,
            'decoded' => $decoded,
            'error' => $error
        ];
    }

    /**
     * Normalize API response structure
     */
    private function normalize_api_response($response)
    {
        if (!$response) {
            return [];
        }

        // If response is already an array, return as-is
        if (is_array($response)) {
            return $response;
        }

        // If response is a string, try to decode as JSON
        if (is_string($response)) {
            $decoded = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // If response is an object, convert to array
        if (is_object($response)) {
            return (array) $response;
        }

        // For primitive values, wrap in a data structure
        return [
            'value' => $response,
            'status' => 'success'
        ];
    }

    /**
     * Find node by ID
     */
    private function find_node_by_id($nodes, $node_id)
    {
        foreach ($nodes as $node) {
            if (($node['id'] ?? null) === $node_id) {
                return $node;
            }
        }
        return null;
    }
}
