<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Zinghr_sync_model extends App_Model
{
    private $endpoint = 'https://portal.zinghr.com/2015/route/EmployeeDetails/GetEmployeeMasterDetails';
    private $empCodeFieldId;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('staff_model');
        $this->ensure_schema();
    }

    public function get_settings()
    {
        $defaultFrom = date('Y-m-d', strtotime('-7 days'));
        $defaultTo   = date('Y-m-d');

        return [
            'subscription_name' => get_option('zinghr_subscription_name') ?: '',
            'token'             => get_option('zinghr_token') ?: '',
            'from_date'         => get_option('zinghr_from_date') ?: $defaultFrom,
            'to_date'           => get_option('zinghr_to_date') ?: $defaultTo,
            'last_run'          => get_option('zinghr_last_sync_at') ?: null,
        ];
    }

    public function save_settings($data)
    {
        update_option('zinghr_subscription_name', trim($data['subscription_name']));
        update_option('zinghr_token', trim($data['token']));
        update_option('zinghr_from_date', $this->normalize_date_input($data['from_date']));
        update_option('zinghr_to_date', $this->normalize_date_input($data['to_date']));
    }

    public function sync($params)
    {
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

        $stats = [
            'divisions_created'   => 0,
            'departments_created' => 0,
            'departments_updated' => 0,
            'staff_created'       => 0,
            'staff_updated'       => 0,
            'staff_inactivated'   => 0,
            'staff_reactivated'   => 0,
            'errors'              => [],
        ];

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

        update_option('zinghr_last_sync_at', date('Y-m-d H:i:s'));
        update_option('zinghr_from_date', $fromDate);
        update_option('zinghr_to_date', $toDate);

        return [
            'success' => true,
            'message' => _l('staff_sync_run_completed'),
            'stats'   => $stats,
        ];
    }

    private function process_employee(array $employee, array &$stats)
    {
        $attributes = isset($employee['Attributes']) && is_array($employee['Attributes']) ? $employee['Attributes'] : [];

        $divisionName      = $this->extract_attribute_value($attributes, 'Division');
        $parentDepartment  = $this->extract_attribute_value($attributes, 'Department');
        $childDepartment   = $this->extract_attribute_value($attributes, 'Sub-Department');

        $divisionId = $this->sync_division($divisionName, $stats);
        $departmentId = $this->sync_department_hierarchy($parentDepartment, $childDepartment, $divisionId, $stats);

        $this->sync_staff_record($employee, $divisionId, $departmentId, $stats);
    }

    private function sync_division($divisionName, array &$stats)
    {
        if (!$divisionName) {
            return null;
        }

        $row = $this->db->where('name', $divisionName)->get('tbldivisions')->row();
        if ($row) {
            return (int) $row->divisionid;
        }

        $this->db->insert('tbldivisions', ['name' => $divisionName]);
        $stats['divisions_created']++;

        return (int) $this->db->insert_id();
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
        $dept = $this->db->where('name', $name)->get(db_prefix() . 'departments')->row();

        if (!$dept) {
            $data = [
                'name'              => $name,
                'imap_username'     => null,
                'email'             => null,
                'email_from_header' => 0,
                'host'              => null,
                'password'          => null,
                'encryption'        => '',
                'folder'            => 'INBOX',
                'delete_after_import' => 0,
                'calendar_id'       => null,
                'hidefromclient'    => 1,
            ];

            if ($this->db->field_exists('parent_department', db_prefix() . 'departments')) {
                $data['parent_department'] = $parentId ?: null;
            }

            $this->db->insert(db_prefix() . 'departments', $data);
            $deptId = (int) $this->db->insert_id();
            $stats['departments_created']++;
        } else {
            $deptId = (int) $dept->departmentid;
            if ($parentId && $this->db->field_exists('parent_department', db_prefix() . 'departments')) {
                if ((int) $dept->parent_department !== (int) $parentId) {
                    $this->db->where('departmentid', $deptId)->update(db_prefix() . 'departments', ['parent_department' => $parentId]);
                    $stats['departments_updated']++;
                }
            }
        }

        if ($divisionId) {
            $exists = $this->db->where('departmentid', $deptId)->where('divisionid', $divisionId)->get(db_prefix() . 'department_divisions')->row();
            if (!$exists) {
                $this->db->insert(db_prefix() . 'department_divisions', [
                    'departmentid' => $deptId,
                    'divisionid'   => $divisionId,
                ]);
            }
        }

        return $deptId;
    }

    private function sync_staff_record(array $employee, $divisionId, $departmentId, array &$stats)
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

        if ($divisionId && $this->db->field_exists('divisionid', db_prefix() . 'staff')) {
            $baseData['divisionid'] = $divisionId;
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

        if (!$current) {
            $baseData['password'] = $this->generate_password();
            $baseData['departments'] = $departmentId ? [$departmentId] : [];
            $staffId = $this->staff_model->add($baseData);
            if ($staffId) {
                $stats['staff_created']++;
                $this->save_emp_code($staffId, $empCode);
                $this->assign_department($staffId, $departmentId);
            } else {
                $stats['errors'][] = _l('staff_sync_error_create', $empCode);
            }
            return;
        }

        $staffId = (int) $current->staffid;
        $needsUpdate = $this->needs_staff_update($current, $baseData);

        if ($needsUpdate) {
            $result = $this->staff_model->update($baseData, $staffId);
            if ($result) {
                $stats['staff_updated']++;
            }
        } else {
            // Even if no other data changed, still update status manually if staff_model skipped due to empty update
            if (isset($baseData['active']) && (int) $current->active !== (int) $baseData['active']) {
                $this->db->where('staffid', $staffId)->update(db_prefix() . 'staff', ['active' => (int) $baseData['active']]);
            }
        }

        if ($current->active == 1 && !$isActive) {
            $stats['staff_inactivated']++;
        } elseif ($current->active == 0 && $isActive) {
            $stats['staff_reactivated']++;
        }

        $this->assign_department($staffId, $departmentId);
        $this->save_emp_code($staffId, $empCode);
    }

    private function assign_department($staffId, $departmentId)
    {
        if (!$staffId || !$departmentId) {
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
        if (!$staffId || !$empCode) {
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

    private function needs_staff_update($current, array $data)
    {
        foreach ($data as $key => $value) {
            if ($key === 'password' || $key === 'departments') {
                continue;
            }
            if (!property_exists($current, $key)) {
                continue;
            }
            if ((string) $current->$key !== (string) $value) {
                return true;
            }
        }

        return false;
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
    }
}
