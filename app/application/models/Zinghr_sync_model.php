<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Zinghr_sync_model extends App_Model
{
    private $endpoint = 'https://portal.zinghr.com/2015/route/EmployeeDetails/GetEmployeeMasterDetails';
    private $empCodeFieldId;
    private $dryRun = false;
    private $virtualIdSeed = -1;
    private $divisionCache = [];
    private $departmentCache = [];
    private $virtualDivisionIds = [];
    private $virtualDepartmentIds = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('staff_model');
        $this->ensure_schema();
    }

    public function get_settings()
    {
        $row = $this->get_settings_row();

        return [
            'subscription_name' => $row->subscription_name,
            'token'             => $row->token,
            'from_date'         => $row->from_date ?: date('Y-m-d', strtotime('-7 days')),
            'to_date'           => $row->to_date ?: date('Y-m-d'),
            'last_run'          => $row->last_run,
        ];
    }

    public function save_settings($data)
    {
        $row = $this->get_settings_row();
        $payload = [
            'subscription_name' => trim($data['subscription_name']),
            'token'             => trim($data['token']),
            'from_date'         => $this->normalize_date_input($data['from_date'] ?? null),
            'to_date'           => $this->normalize_date_input($data['to_date'] ?? null),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        $this->db->where('id', $row->id)->update('tblzinghr_settings', $payload);
    }

    public function sync($params, $options = [])
    {
        $this->dryRun               = !empty($options['dry_run']);
        $this->virtualIdSeed        = -1;
        $this->divisionCache        = [];
        $this->departmentCache      = [];
        $this->virtualDivisionIds   = [];
        $this->virtualDepartmentIds = [];

        $subscription = trim($params['subscription_name'] ?? '');
        $token        = trim($params['token'] ?? '');

        if ($subscription === '' || $token === '') {
            return [
                'success' => false,
                'message' => _l('staff_sync_error_credentials'),
            ];
        }

        $fromDate = $this->normalize_date_input($params['from_date'] ?? null);
        $toDate   = $this->normalize_date_input($params['to_date'] ?? null);

        if (!$fromDate || !$toDate) {
            return [
                'success' => false,
                'message' => _l('staff_sync_error_invalid_dates'),
            ];
        }

        $pageSize  = 200;
        $page      = 1;
        $hasMore   = true;
        $processed = 0;

        $stats = $this->initialize_stats();

        while ($hasMore) {
            $payload = [
                'SubscriptionName' => $subscription,
                'Token'            => $token,
                'EmployeeCode'     => '',
                'Fromdate'         => $this->format_date_for_api($fromDate),
                'Todate'           => $this->format_date_for_api($toDate),
                'EmpFlag'          => '',
                'SectionName'      => '',
                'PageSize'         => $pageSize,
                'PageNumber'       => $page,
            ];

            $response = $this->call_api($payload);
            if (!$response['success']) {
                return $response;
            }

            $employees = $response['data'];
            foreach ($employees as $employee) {
                $this->process_employee($employee, $stats);
            }

            $processed += count($employees);
            $total = $response['total'];
            $page++;
            $hasMore = $processed < $total && count($employees) === $pageSize;

            if ($total === 0 || count($employees) === 0) {
                break;
            }
        }

        if (!$this->dryRun) {
            $this->mark_last_run($fromDate, $toDate);
        }

        $mode = $this->dryRun ? 'preview' : 'execute';
        $this->dryRun = false;

        return [
            'success' => true,
            'message' => $mode === 'preview' ? _l('staff_sync_preview_ready') : _l('staff_sync_run_completed'),
            'stats'   => $stats,
            'mode'    => $mode,
        ];
    }

    private function process_employee(array $employee, array &$stats)
    {
        $attributes = isset($employee['Attributes']) && is_array($employee['Attributes']) ? $employee['Attributes'] : [];

        $divisionName     = $this->extract_attribute_value($attributes, 'Division');
        $parentDepartment = $this->extract_attribute_value($attributes, 'Department');
        $childDepartment  = $this->extract_attribute_value($attributes, 'Sub-Department');
        $targetDepartment = $childDepartment ?: $parentDepartment;

        $divisionId = $this->sync_division($divisionName, $stats);
        $departmentId = $this->sync_department_hierarchy($parentDepartment, $childDepartment, $divisionId, $stats);

        $this->sync_staff_record($employee, [
            'division_id'     => $divisionId,
            'division_name'   => $divisionName,
            'department_id'   => $departmentId,
            'department_name' => $targetDepartment,
        ], $stats);
    }

    private function sync_division($divisionName, array &$stats)
    {
        if (!$divisionName) {
            return null;
        }

        if (isset($this->divisionCache[$divisionName])) {
            return $this->divisionCache[$divisionName];
        }

        $row = $this->db->where('name', $divisionName)->get('tbldivisions')->row();
        if ($row) {
            $this->divisionCache[$divisionName] = (int) $row->divisionid;
            return (int) $row->divisionid;
        }

        $stats['divisions_created']++;
        $stats['divisions'][] = ['name' => $divisionName];

        if ($this->dryRun) {
            $virtualId = $this->generate_virtual_id();
            $this->divisionCache[$divisionName]      = $virtualId;
            $this->virtualDivisionIds[$divisionName] = $virtualId;
            return $virtualId;
        }

        $this->db->insert('tbldivisions', ['name' => $divisionName]);
        $divisionId = (int) $this->db->insert_id();
        $this->divisionCache[$divisionName] = $divisionId;

        return $divisionId;
    }

    private function sync_department_hierarchy($parentName, $childName, $divisionId, array &$stats)
    {
        $parentId = null;
        if ($parentName) {
            $parentId = $this->ensure_department($parentName, null, $divisionId, $stats);
        }

        $targetName = $childName ?: $parentName;
        if (!$targetName) {
            return null;
        }

        return $this->ensure_department($targetName, $childName && $parentId ? $parentId : null, $divisionId, $stats);
    }

    private function ensure_department($name, $parentId, $divisionId, array &$stats)
    {
        if (isset($this->departmentCache[$name])) {
            return $this->departmentCache[$name];
        }

        $dept = $this->db->where('name', $name)->get(db_prefix() . 'departments')->row();

        if (!$dept) {
            $stats['departments_created']++;
            $stats['department_creations'][] = [
                'name'         => $name,
                'parent_name'  => $this->lookup_department_name($parentId),
                'division'     => $this->lookup_division_name($divisionId),
            ];

            if ($this->dryRun) {
                $virtualId = $this->generate_virtual_id();
                $this->departmentCache[$name]      = $virtualId;
                $this->virtualDepartmentIds[$name] = $virtualId;
                return $virtualId;
            }

            $data = [
                'name'               => $name,
                'imap_username'      => null,
                'email'              => null,
                'email_from_header'  => 0,
                'host'               => null,
                'password'           => null,
                'encryption'         => '',
                'folder'             => 'INBOX',
                'delete_after_import'=> 0,
                'calendar_id'        => null,
                'hidefromclient'     => 1,
            ];

            if ($this->db->field_exists('parent_department', db_prefix() . 'departments')) {
                $data['parent_department'] = $parentId ?: null;
            }

            $this->db->insert(db_prefix() . 'departments', $data);
            $deptId = (int) $this->db->insert_id();
            $this->departmentCache[$name] = $deptId;
        } else {
            $deptId = (int) $dept->departmentid;
            $this->departmentCache[$name] = $deptId;

            if ($parentId && $this->db->field_exists('parent_department', db_prefix() . 'departments')) {
                if ((int) $dept->parent_department !== (int) $parentId) {
                    $stats['departments_updated']++;
                    $stats['department_updates'][] = [
                        'name'       => $name,
                        'old_parent' => $this->lookup_department_name($dept->parent_department),
                        'new_parent' => $this->lookup_department_name($parentId),
                    ];

                    if (!$this->dryRun) {
                        $this->db->where('departmentid', $deptId)->update(db_prefix() . 'departments', ['parent_department' => $parentId]);
                    }
                }
            }
        }

        if ($divisionId && is_int($deptId)) {
            $exists = $this->db->where('departmentid', $deptId)->where('divisionid', $divisionId)->get(db_prefix() . 'department_divisions')->row();
            if (!$exists && !$this->dryRun) {
                $this->db->insert(db_prefix() . 'department_divisions', [
                    'departmentid' => $deptId,
                    'divisionid'   => $divisionId,
                ]);
            }
        }

        return $deptId;
    }

    private function sync_staff_record(array $employee, array $context, array &$stats)
    {
        $empCode = trim($employee['EmployeeCode'] ?? '');
        if ($empCode === '') {
            return;
        }

        $current = $this->find_staff_by_empcode($empCode);
        $isActive = $this->determine_active_status($employee);

        $firstName = trim($employee['FirstName'] ?? '') ?: trim($employee['EmployeeName'] ?? '');
        $lastName  = trim($employee['LastName'] ?? '');

        if ($firstName === '' && $lastName === '') {
            $firstName = $empCode;
        }

        $email = trim($employee['Email'] ?? '');
        if ($email === '') {
            $email = strtolower($empCode) . '@zinghr-sync.local';
        }

        $baseData = [
            'firstname'   => $firstName,
            'lastname'    => $lastName,
            'email'       => $email,
            'phonenumber' => trim($employee['Mobile'] ?? ''),
            'active'      => $isActive ? 1 : 0,
        ];

        if (isset($context['division_id']) && $context['division_id'] && $this->db->field_exists('divisionid', db_prefix() . 'staff')) {
            $baseData['divisionid'] = $context['division_id'];
        }

        $managerId = null;
        $managerCode = trim($employee['ReportingManagerCode'] ?? '');
        if ($managerCode !== '' && strcasecmp($managerCode, $empCode) !== 0) {
            $manager = $this->find_staff_by_empcode($managerCode);
            if ($manager) {
                $managerId = (int) $manager->staffid;
            }
        }
        if ($managerId && $this->db->field_exists('reporting_manager', db_prefix() . 'staff')) {
            $baseData['reporting_manager'] = $managerId;
        }

        $staffLabel = trim($firstName . ' ' . $lastName);
        $staffLabel = $staffLabel !== '' ? $staffLabel : $empCode;

        if (!$current) {
            if ($this->dryRun) {
                $stats['staff_created']++;
                $stats['staff_creations'][] = $this->format_staff_log($empCode, $staffLabel, $email, $context);
                return;
            }

            $baseData['password']    = $this->generate_password();
            $baseData['departments'] = isset($context['department_id']) && $context['department_id'] ? [$context['department_id']] : [];

            $staffId = $this->staff_model->add($baseData);
            if ($staffId) {
                $stats['staff_created']++;
                $stats['staff_creations'][] = $this->format_staff_log($empCode, $staffLabel, $email, $context);
                $this->save_emp_code($staffId, $empCode);
                $this->assign_department($staffId, $context['department_id'] ?? null);
            } else {
                $stats['errors'][] = _l('staff_sync_error_create', $empCode);
            }
            return;
        }

        [$needsUpdate, $changes] = $this->get_staff_changes($current, $baseData);

        if ($needsUpdate) {
            if ($this->dryRun) {
                $stats['staff_updated']++;
                $stats['staff_updates'][] = $this->format_staff_log($empCode, $staffLabel, $email, $context, $changes);
            } else {
                $result = $this->staff_model->update($baseData, $current->staffid);
                if ($result) {
                    $stats['staff_updated']++;
                    $stats['staff_updates'][] = $this->format_staff_log($empCode, $staffLabel, $email, $context, $changes);
                }
            }
        }

        $statusChanged = false;
        if ($current->active == 1 && !$isActive) {
            $stats['staff_inactivated']++;
            $stats['staff_inactivations'][] = $this->format_staff_log($empCode, $staffLabel, $email, $context);
            $statusChanged = true;
        } elseif ($current->active == 0 && $isActive) {
            $stats['staff_reactivated']++;
            $stats['staff_reactivations'][] = $this->format_staff_log($empCode, $staffLabel, $email, $context);
            $statusChanged = true;
        }

        if ($statusChanged && !$this->dryRun) {
            $this->db->where('staffid', $current->staffid)->update(db_prefix() . 'staff', ['active' => $baseData['active']]);
        }

        if (!$this->dryRun) {
            $this->assign_department($current->staffid, $context['department_id'] ?? null);
            $this->save_emp_code($current->staffid, $empCode);
        }
    }

    private function assign_department($staffId, $departmentId)
    {
        if (!$staffId || !$departmentId || $this->dryRun) {
            return;
        }
        $exists = $this->db->where('staffid', $staffId)->where('departmentid', $departmentId)->get(db_prefix() . 'staff_departments')->row();
        if (!$exists) {
            $this->db->insert(db_prefix() . 'staff_departments', [
                'staffid'      => $staffId,
                'departmentid' => $departmentId,
            ]);
        }
    }

    private function find_staff_by_empcode($code)
    {
        $fieldId = $this->get_emp_code_field_id();
        if (!$fieldId) {
            return null;
        }

        return $this->db->select('s.*')
            ->from(db_prefix() . 'staff s')
            ->join(db_prefix() . 'customfieldsvalues cfv', 'cfv.relid = s.staffid AND cfv.fieldid = ' . (int) $fieldId, 'left')
            ->where('cfv.value', $code)
            ->get()
            ->row();
    }

    private function save_emp_code($staffId, $empCode)
    {
        if (!$staffId || !$empCode || $this->dryRun) {
            return;
        }
        $fieldId = $this->get_emp_code_field_id();
        if (!$fieldId) {
            return;
        }

        $exists = $this->db->where('fieldid', $fieldId)->where('relid', $staffId)->where('fieldto', 'staff')->get(db_prefix() . 'customfieldsvalues')->row();
        if ($exists) {
            if ($exists->value !== $empCode) {
                $this->db->where('id', $exists->id)->update(db_prefix() . 'customfieldsvalues', ['value' => $empCode]);
            }
        } else {
            $this->db->insert(db_prefix() . 'customfieldsvalues', [
                'relid'  => $staffId,
                'fieldid'=> $fieldId,
                'fieldto'=> 'staff',
                'value'  => $empCode,
            ]);
        }
    }

    private function get_staff_changes($current, array $data)
    {
        $changes = [];
        foreach ($data as $key => $value) {
            if ($key === 'password' || $key === 'departments') {
                continue;
            }
            if (!property_exists($current, $key)) {
                continue;
            }
            if ((string) $current->$key !== (string) $value) {
                $changes[$key] = [
                    'old' => (string) $current->$key,
                    'new' => (string) $value,
                ];
            }
        }

        return [count($changes) > 0, $changes];
    }

    private function determine_active_status(array $employee)
    {
        $status = strtolower(trim($employee['EmploymentStatus'] ?? ''));
        $empFlag = strtoupper(trim($employee['EmpFlag'] ?? ''));

        if ($empFlag === 'DELETE') {
            return false;
        }

        if ($status === 'active' || $status === 'existing') {
            return true;
        }

        return false;
    }

    private function call_api(array $payload)
    {
        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $responseBody = curl_exec($ch);
        $error        = curl_error($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseBody === false) {
            return [
                'success' => false,
                'message' => _l('staff_sync_error_api', $error ?: 'cURL error'),
            ];
        }

        $decoded = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => _l('staff_sync_error_api', 'Invalid JSON response'),
            ];
        }

        if ($httpCode !== 200) {
            $message = isset($decoded['Message']) ? $decoded['Message'] : 'HTTP ' . $httpCode;
            return [
                'success' => false,
                'message' => _l('staff_sync_error_api', $message),
            ];
        }

        if (isset($decoded['Code']) && (int) $decoded['Code'] !== 1) {
            $message = isset($decoded['Message']) ? $decoded['Message'] : 'Unexpected API status';
            return [
                'success' => false,
                'message' => _l('staff_sync_error_api', $message),
            ];
        }

        $employees = $decoded['Employees'] ?? [];
        $total     = isset($decoded['TotalEmployeeCount']) ? (int) $decoded['TotalEmployeeCount'] : count($employees);

        return [
            'success' => true,
            'data'    => $employees,
            'total'   => $total,
        ];
    }

    private function extract_attribute_value(array $attributes, $label)
    {
        foreach ($attributes as $attribute) {
            $desc = isset($attribute['AttributeTypeDesc']) ? strtolower($attribute['AttributeTypeDesc']) : '';
            $code = isset($attribute['AttributeTypeCode']) ? strtolower($attribute['AttributeTypeCode']) : '';
            $target = strtolower($label);
            if ($desc === $target || $code === $target) {
                return trim($attribute['AttributeTypeUnitDesc'] ?? $attribute['AttributeTypeUnitCode'] ?? '');
            }
        }

        return null;
    }

    private function format_date_for_api($date)
    {
        $time = strtotime($date);
        return $time ? date('d-m-Y', $time) : '';
    }

    private function normalize_date_input($date)
    {
        if (!$date) {
            return null;
        }
        $time = strtotime($date);
        if (!$time) {
            return null;
        }

        return date('Y-m-d', $time);
    }

    private function generate_password()
    {
        return substr(strtoupper(bin2hex(random_bytes(6))), 0, 12);
    }

    private function get_emp_code_field_id()
    {
        if ($this->empCodeFieldId !== null) {
            return $this->empCodeFieldId;
        }

        $row = $this->db->select('id')
            ->where(['slug' => 'staff_emp_code', 'fieldto' => 'staff'])
            ->get(db_prefix() . 'customfields')
            ->row();

        $this->empCodeFieldId = $row ? (int) $row->id : null;

        return $this->empCodeFieldId;
    }

    private function ensure_schema()
    {
        if (!$this->db->table_exists('tbldivisions')) {
            $this->db->query('CREATE TABLE `tbldivisions` (
                `divisionid` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(191) NOT NULL,
                PRIMARY KEY (`divisionid`),
                UNIQUE KEY `uniq_division_name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }

        if (!$this->db->table_exists(db_prefix() . 'department_divisions')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'department_divisions` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `departmentid` INT(11) NOT NULL,
                `divisionid` INT(11) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_dep_div` (`departmentid`,`divisionid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }

        if (!$this->db->field_exists('divisionid', db_prefix() . 'staff')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'staff` ADD `divisionid` INT(11) NULL DEFAULT NULL AFTER `role`;');
        }

        if (!$this->db->field_exists('reporting_manager', db_prefix() . 'staff')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'staff` ADD `reporting_manager` INT(11) NULL DEFAULT NULL AFTER `divisionid`;');
        }

        if (!$this->db->field_exists('parent_department', db_prefix() . 'departments')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'departments` ADD `parent_department` INT(11) NULL DEFAULT NULL AFTER `delete_after_import`;');
        }

        if (!$this->db->table_exists('tblzinghr_settings')) {
            $this->db->query('CREATE TABLE `tblzinghr_settings` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `subscription_name` VARCHAR(50) DEFAULT NULL,
                `token` TEXT,
                `from_date` DATE DEFAULT NULL,
                `to_date` DATE DEFAULT NULL,
                `last_run` DATETIME DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }
    }

    private function get_settings_row()
    {
        $row = $this->db->limit(1)->get('tblzinghr_settings')->row();
        if ($row) {
            return $row;
        }

        $record = [
            'subscription_name' => '',
            'token'             => '',
            'from_date'         => date('Y-m-d', strtotime('-7 days')),
            'to_date'           => date('Y-m-d'),
            'last_run'          => null,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('tblzinghr_settings', $record);
        $record['id'] = $this->db->insert_id();

        return (object) $record;
    }

    private function mark_last_run($fromDate, $toDate)
    {
        $row = $this->get_settings_row();
        $this->db->where('id', $row->id)->update('tblzinghr_settings', [
            'last_run'  => date('Y-m-d H:i:s'),
            'from_date' => $fromDate,
            'to_date'   => $toDate,
            'updated_at'=> date('Y-m-d H:i:s'),
        ]);
    }

    private function initialize_stats()
    {
        return [
            'divisions_created'    => 0,
            'divisions'            => [],
            'departments_created'  => 0,
            'department_creations' => [],
            'departments_updated'  => 0,
            'department_updates'   => [],
            'staff_created'        => 0,
            'staff_creations'      => [],
            'staff_updated'        => 0,
            'staff_updates'        => [],
            'staff_inactivated'    => 0,
            'staff_inactivations'  => [],
            'staff_reactivated'    => 0,
            'staff_reactivations'  => [],
            'errors'               => [],
        ];
    }

    private function format_staff_log($empCode, $name, $email, array $context, $changes = [])
    {
        return [
            'emp_code'        => $empCode,
            'name'            => $name,
            'email'           => $email,
            'division'        => $context['division_name'] ?? '',
            'department'      => $context['department_name'] ?? '',
            'changes'         => $changes,
        ];
    }

    private function lookup_department_name($departmentId)
    {
        if (!$departmentId) {
            return null;
        }
        foreach ($this->departmentCache as $name => $id) {
            if ((int) $id === (int) $departmentId) {
                return $name;
            }
        }
        $row = $this->db->select('name')->where('departmentid', $departmentId)->get(db_prefix() . 'departments')->row();
        return $row ? $row->name : null;
    }

    private function lookup_division_name($divisionId)
    {
        if (!$divisionId) {
            return null;
        }
        foreach ($this->divisionCache as $name => $id) {
            if ((int) $id === (int) $divisionId) {
                return $name;
            }
        }
        $row = $this->db->select('name')->where('divisionid', $divisionId)->get('tbldivisions')->row();
        return $row ? $row->name : null;
    }

    private function generate_virtual_id()
    {
        return $this->virtualIdSeed--;
    }
}
