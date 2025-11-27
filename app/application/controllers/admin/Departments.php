<?php

use app\services\imap\Imap;
use app\services\imap\ConnectionErrorException;
use Ddeboer\Imap\Exception\MailboxDoesNotExistException;

defined('BASEPATH') or exit('No direct script access allowed');

class Departments extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('departments_model');

        if (!is_admin()) {
            access_denied('Departments');
        }
    }

    /* List all departments */
    public function index()
    {
        // Departments listing
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('departments');
        }
        $this->load->model('divisions_model');
        $data['email_exist_as_staff'] = $this->email_exist_as_staff();
        $data['departments']          = $this->departments_model->get();
        $data['divisions']            = $this->divisions_model->get();
        $data['title']                = _l('departments');
        $this->load->view('admin/departments/manage', $data);
    }

    /* Edit or add new department */
    public function department($id = '')
    {
        if ($this->input->post()) {
            $message          = '';
            $data             = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            // Normalize division to single-select and map to existing model expectations
            if (isset($data['divisionid']) && $data['divisionid'] !== '') {
                $data['divisions'] = [(int) $data['divisionid']];
                unset($data['divisionid']);
            }

            // Prevent duplicates: same department name within the same division
            $divisionForCheck = isset($data['divisions'][0]) ? (int) $data['divisions'][0] : 0;
            $nameForCheck     = isset($data['name']) ? trim(strtolower($data['name'])) : '';
            if ($nameForCheck !== '' && $divisionForCheck > 0 && $this->db->table_exists(db_prefix().'department_divisions')) {
                $this->db->select('COUNT(*) as total')
                    ->from(db_prefix().'departments d')
                    ->join(db_prefix().'department_divisions dd', 'dd.departmentid = d.departmentid', 'inner')
                    ->where('LOWER(TRIM(d.name))', $nameForCheck)
                    ->where('dd.divisionid', $divisionForCheck);
                if ($this->input->post('id')) {
                    $this->db->where('d.departmentid !=', (int) $this->input->post('id'));
                }
                $dup = $this->db->get()->row();
                if ($dup && (int) $dup->total > 0) {
                    $this->output->set_status_header(422);
                    echo json_encode([
                        'message' => 'A department with the same name already exists in the selected division.',
                    ]);
                    die;
                }
            }

            if (isset($data['fakeusernameremembered']) || isset($data['fakepasswordremembered'])) {
                unset($data['fakeusernameremembered']);
                unset($data['fakepasswordremembered']);
            }

            if (!$this->input->post('id')) {
                $id = $this->departments_model->add($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('department'));
                }
                echo json_encode([
                    'success'              => $success,
                    'message'              => $message,
                    'email_exist_as_staff' => $this->email_exist_as_staff(),
                ]);
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->departments_model->update($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('department'));
                }
                echo json_encode([
                    'success'              => $success,
                    'message'              => $message,
                    'email_exist_as_staff' => $this->email_exist_as_staff(),
                ]);
            }
            die;
        }
    }

    /* Delete department from database */
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('departments'));
        }
        $response = $this->departments_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('department_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('department')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('department_lowercase')));
        }
        redirect(admin_url('departments'));
    }

    public function email_exists()
    {
        // First we need to check if the email is the same
        $departmentid = $this->input->post('departmentid');
        if ($departmentid) {
            $this->db->where('departmentid', $departmentid);
            $_current_email = $this->db->get(db_prefix() . 'departments')->row();
            if ($_current_email->email == $this->input->post('email')) {
                echo json_encode(true);
                die();
            }
        }
        $exists = total_rows(db_prefix() . 'departments', [
            'email' => $this->input->post('email'),
        ]);
        if ($exists > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

    // AJAX: Return active staff for given divisions with optional emp code in the name
    public function staff_by_divisions()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $divisions = $this->input->post('divisions');
        if (is_string($divisions)) {
            $divisions = array_filter(explode(',', $divisions));
        }
        $divisions = array_values(array_filter((array) $divisions, function ($v) { return is_numeric($v) && (int)$v > 0; }));

        if (empty($divisions)) {
            echo json_encode([]);
            die;
        }

        // If staff.divisionid not present (older schema), return empty to avoid SQL errors
        if (! $this->db->field_exists('divisionid', db_prefix().'staff')) {
            echo json_encode([]);
            die;
        }

        // Build list of staff (HOD Name shows only staff full name)
        $this->db->select(db_prefix().'staff.staffid, '.db_prefix().'staff.firstname, '.db_prefix().'staff.lastname');
        $this->db->from(db_prefix().'staff');
        $this->db->where_in(db_prefix().'staff.divisionid', $divisions);
        $this->db->where(db_prefix().'staff.active', 1);
        if (get_option('access_tickets_to_none_staff_members') == 0) {
            $this->db->where(db_prefix().'staff.is_not_staff', 0);
        }
        $this->db->order_by(db_prefix().'staff.firstname', 'asc');
        $rows = $this->db->get()->result_array();

        $out = [];
        foreach ($rows as $s) {
            $out[] = [
                'staffid' => (int) $s['staffid'],
                'name'    => trim($s['firstname'].' '.$s['lastname']),
            ];
        }

        echo json_encode($out);
        die;
    }

    /**
     * Return child departments for a given parent department id (AJAX)
     */
    public function children()
    {
        if ($this->input->is_ajax_request()) {
            $parentId = $this->input->post('parent_id');
            $children = [];
            if (!empty($parentId)) {
                $this->db->select('departmentid, name');
                $this->db->where('parent_department', (int) $parentId);
                $children = $this->db->get(db_prefix() . 'departments')->result_array();
            }
            echo json_encode($children);
            die;
        }
        show_404();
    }

    public function folders()
    {
        app_check_imap_open_function();

        $imap = new Imap(
           $this->input->post('username') ? $this->input->post('username') : $this->input->post('email'),
           $this->input->post('password', false),
           $this->input->post('host'),
           $this->input->post('encryption')
        );

        try {
            echo json_encode($imap->getSelectableFolders());
        } catch (ConnectionErrorException $e) {
            echo json_encode([
                'alert_type' => 'warning',
                'message'    => $e->getMessage(),
            ]);
        }
    }

    public function test_imap_connection()
    {
        app_check_imap_open_function();

        $imap = new Imap(
           $this->input->post('username') ? $this->input->post('username') : $this->input->post('email'),
           $this->input->post('password', false),
           $this->input->post('host'),
           $this->input->post('encryption')
        );

        try {
            $connection = $imap->testConnection();

            try {
                $folder = $this->input->post('folder');

                $connection->getMailbox(empty($folder) ? 'INBOX' : $folder);
            } catch (MailboxDoesNotExistException $e) {
                echo json_encode([
                    'alert_type' => 'warning',
                    'message'    => $e->getMessage(),
                ]);
                die;
            }
            echo json_encode([
                'alert_type' => 'success',
                'message'    => _l('lead_email_connection_ok'),
            ]);
        } catch (ConnectionErrorException $e) {
            echo json_encode([
                'alert_type' => 'warning',
                'message'    => $e->getMessage(),
            ]);
        }
    }

    private function email_exist_as_staff()
    {
        return total_rows(db_prefix() . 'departments', 'email IN (SELECT email FROM ' . db_prefix() . 'staff)') > 0;
    }

    /* Bulk upload departments */
    public function bulk_upload()
    {
        if (!is_admin()) {
            access_denied('Departments');
        }

        if ($this->input->post()) {
            // Check if this is confirmed data from preview
            $confirmed_data = $this->input->post('confirmed_data');
            if ($confirmed_data) {
                // Process confirmed data from preview
                $result = $this->process_confirmed_data($confirmed_data);

                if ($result['success']) {
                    $message = 'Successfully processed departments';
                    if ($result['created'] > 0) {
                        $message .= ' - Created: ' . $result['created'];
                    }
                    if ($result['updated'] > 0) {
                        $message .= ' - Updated: ' . $result['updated'];
                    }
                    set_alert('success', $message);
                } else {
                    set_alert('danger', $result['error']);
                }

                redirect(admin_url('departments'));
            } else {
                // Handle file upload for preview - this should not happen in the current implementation
                // The preview is handled client-side, so this branch should not be reached
                redirect(admin_url('departments/bulk_upload'));
            }
        }

        $data['title'] = 'Bulk Upload Departments';
        $this->load->view('admin/departments/bulk_upload', $data);
    }

    private function process_confirmed_data($confirmed_data_json)
    {
        try {
            $confirmed_data = json_decode($confirmed_data_json, true);

            if (!$confirmed_data || !isset($confirmed_data['headers']) || !isset($confirmed_data['data'])) {
                return ['success' => false, 'error' => _l('invalid_data_format')];
            }

            $headers = $confirmed_data['headers'];
            $data = $confirmed_data['data'];
            $errors = [];
            $created = 0;
            $updated = 0;

            // Start transaction for data consistency
            $this->db->trans_start();

            // Step 1: Create missing departments (without parent relationships)
            $department_mappings = []; // Track name -> id mappings for parent relationships

            foreach ($data as $row_index => $row) {
                $department_data = $this->map_csv_row_to_department_data($row, $headers, false); // Don't include parent_department in creation

                if ($department_data) {
                    $department_name = $department_data['name'];

                    // Check if department already exists
                    $existing_id = $this->get_department_id_by_name($department_name);

                    if ($existing_id) {
                        // Department exists, store mapping for later parent relationship update
                        $department_mappings[$department_name] = $existing_id;
                    } else {
                        // Create new department
                        try {
                            $result = $this->departments_model->add($department_data);
                            if ($result) {
                                $department_mappings[$department_name] = $result;
                                $created++;
                            } else {
                                $errors[] = "Row " . ($row_index + 1) . ": " . _l('failed_to_create_department');
                            }
                        } catch (Exception $e) {
                            $errors[] = "Row " . ($row_index + 1) . ": " . $e->getMessage();
                            log_message('error', 'Department creation error: ' . $e->getMessage());
                        }
                    }
                } else {
                    $errors[] = "Row " . ($row_index + 1) . ": " . _l('invalid_data_format');
                }
            }

            // Step 2: Update parent department relationships
            if (empty($errors)) {
                foreach ($data as $row_index => $row) {
                    // Extract parent department name directly from CSV row
                    $parent_name = '';
                    $column_map = [
                        'parent_department' => null,
                        'parent_department_name' => null,
                    ];

                    // Map parent department column
                    if ($headers) {
                        foreach ($headers as $index => $column_name) {
                            $column_name = strtolower(trim($column_name));
                            if (array_key_exists($column_name, $column_map)) {
                                $column_map[$column_name] = $index;
                            }
                        }
                    }

                    if (isset($column_map['parent_department']) && isset($row[$column_map['parent_department']])) {
                        $parent_name = trim($row[$column_map['parent_department']]);
                    } elseif (isset($column_map['parent_department_name']) && isset($row[$column_map['parent_department_name']])) {
                        $parent_name = trim($row[$column_map['parent_department_name']]);
                    }

                    // Process parent relationship update for this department
                    $department_data = $this->map_csv_row_to_department_data($row, $headers, false);
                    $department_name = $department_data['name'];

                    if (isset($department_mappings[$department_name])) {
                        $department_id = $department_mappings[$department_name];

                        if (!empty($parent_name)) {
                            // Find parent department ID
                            $parent_id = isset($department_mappings[$parent_name])
                                ? $department_mappings[$parent_name]
                                : $this->get_department_id_by_name($parent_name);

                            if ($parent_id) {
                                try {
                                    $update_data = ['parent_department' => $parent_id];
                                    $result = $this->departments_model->update($update_data, $department_id);
                                    // Note: update() returns false if no changes were made, but that's not an error
                                    $updated++;
                                } catch (Exception $e) {
                                    $errors[] = "Row " . ($row_index + 1) . ": Failed to update parent relationship - " . $e->getMessage();
                                    log_message('error', 'Department parent update error: ' . $e->getMessage());
                                }
                            } else {
                                $errors[] = "Row " . ($row_index + 1) . ": Parent department '" . $parent_name . "' not found";
                            }
                        } else {
                            // Empty parent_name means no parent (set to NULL)
                            try {
                                $update_data = ['parent_department' => null];
                                $result = $this->departments_model->update($update_data, $department_id);
                                // Note: update() returns false if no changes were made, but that's not an error
                                $updated++;
                            } catch (Exception $e) {
                                $errors[] = "Row " . ($row_index + 1) . ": Failed to clear parent relationship - " . $e->getMessage();
                                log_message('error', 'Department parent clear error: ' . $e->getMessage());
                            }
                        }
                    }
                }
            }

            // Complete transaction
            if (empty($errors)) {
                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE) {
                    return ['success' => false, 'error' => _l('transaction_failed')];
                }
            } else {
                $this->db->trans_rollback();
                return ['success' => false, 'error' => implode('<br>', array_slice($errors, 0, 10))]; // Show first 10 errors
            }

        } catch (Exception $e) {
            log_message('error', 'Confirmed data processing error: ' . $e->getMessage());
            return ['success' => false, 'error' => _l('processing_error') . ': ' . $e->getMessage()];
        }

        return ['success' => true, 'created' => $created, 'updated' => $updated];
    }

    private function map_csv_row_to_department_data($row, $header, $include_parent = true)
    {
        // Expected CSV columns: name, division, parent_department
        $data = [];

        // Map by column names or positions
        $column_map = [
            'name' => null,
            'department_name' => null,
            'division' => null,
            'division_name' => null,
            'parent_department' => null,
            'parent_department_name' => null,
        ];

        // Try to map by header names first
        if ($header) {
            foreach ($header as $index => $column_name) {
                $column_name = strtolower(trim($column_name));
                if (array_key_exists($column_name, $column_map)) {
                    $column_map[$column_name] = $index;
                }
            }
        }

        // If header mapping failed, assume standard order
        if (count(array_filter($column_map)) < 1) { // At least name
            $column_map = [
                'name' => 0,
                'division' => 1,
                'parent_department' => 2,
            ];
        }

        // Extract data
        $name = isset($column_map['name']) && isset($row[$column_map['name']]) ? trim($row[$column_map['name']]) : '';
        if (empty($name) && isset($column_map['department_name']) && isset($row[$column_map['department_name']])) {
            $name = trim($row[$column_map['department_name']]);
        }

        // Validate required fields
        if (empty($name)) {
            return false;
        }

        // Build department data array
        $data = [
            'name' => $name,
            'hidefromclient' => 0, // Default to visible
        ];

        // Handle division
        $division_name = '';
        if (isset($column_map['division']) && isset($row[$column_map['division']])) {
            $division_name = trim($row[$column_map['division']]);
        } elseif (isset($column_map['division_name']) && isset($row[$column_map['division_name']])) {
            $division_name = trim($row[$column_map['division_name']]);
        }

        if (!empty($division_name)) {
            $division_id = $this->get_division_id_by_name($division_name);
            if ($division_id) {
                $data['divisions'] = [$division_id];
            }
        }

        // Handle parent department (only if requested)
        if ($include_parent) {
            $parent_name = '';
            if (isset($column_map['parent_department']) && isset($row[$column_map['parent_department']])) {
                $parent_name = trim($row[$column_map['parent_department']]);
            } elseif (isset($column_map['parent_department_name']) && isset($row[$column_map['parent_department_name']])) {
                $parent_name = trim($row[$column_map['parent_department_name']]);
            }

            if (!empty($parent_name)) {
                $data['parent_department_name'] = $parent_name; // Store name for later resolution
            }
        }

        return $data;
    }

    private function get_division_id_by_name($division_name)
    {
        $this->db->select('divisionid');
        $this->db->from(db_prefix() . 'divisions');
        $this->db->where('name', $division_name);
        $result = $this->db->get()->row();
        return $result ? $result->divisionid : null;
    }

    private function get_department_id_by_name($department_name)
    {
        $this->db->select('departmentid');
        $this->db->from(db_prefix() . 'departments');
        $this->db->where('name', $department_name);
        $result = $this->db->get()->row();
        return $result ? $result->departmentid : null;
    }
}
