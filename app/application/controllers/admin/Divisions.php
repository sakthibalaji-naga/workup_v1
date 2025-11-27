<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Divisions extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('divisions_model');

        if (!is_admin()) {
            access_denied('Divisions');
        }
    }

    public function index()
    {
        // Ensure table exists to avoid 500s on first load before migration runs
        if (!$this->db->table_exists(db_prefix() . 'divisions')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'divisions` (
                `divisionid` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(191) NOT NULL,
                PRIMARY KEY (`divisionid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('divisions');
        }

        // No need to prefetch divisions here; DataTables will load via AJAX
        $data['divisions'] = [];
        $data['title']     = 'Divisions';
        $this->load->view('admin/divisions/manage', $data);
    }

    public function division($id = '')
    {
        if ($this->input->post()) {
            $message = '';
            $data    = $this->input->post();

            if (!$this->input->post('id')) {
                $id = $this->divisions_model->add($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', 'Division');
                } else {
                    $success = false;
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->divisions_model->update($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', 'Division');
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
            die;
        }
    }

    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('divisions'));
        }

        $response = $this->divisions_model->delete($id);
        if ($response === true) {
            set_alert('success', _l('deleted', 'Division'));
        } else {
            set_alert('warning', _l('problem_deleting', 'division'));
        }
        redirect(admin_url('divisions'));
    }

    /* Bulk upload divisions */
    public function bulk_upload()
    {
        if (!is_admin()) {
            access_denied('Divisions');
        }

        if ($this->input->post()) {
            // Check if this is confirmed data from preview
            $confirmed_data = $this->input->post('confirmed_data');
            if ($confirmed_data) {
                // Process confirmed data from preview
                $result = $this->process_confirmed_data($confirmed_data);

                if ($result['success']) {
                    set_alert('success', 'Successfully imported ' . $result['imported'] . ' divisions');
                } else {
                    set_alert('danger', $result['error']);
                }

                redirect(admin_url('divisions'));
            } else {
                // Handle file upload for preview - this should not happen in the current implementation
                // The preview is handled client-side, so this branch should not be reached
                redirect(admin_url('divisions/bulk_upload'));
            }
        }

        $data['title'] = 'Bulk Upload Divisions';
        $this->load->view('admin/divisions/bulk_upload', $data);
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
            $imported = 0;

            // Start transaction for data consistency
            $this->db->trans_start();

            foreach ($data as $row_index => $row) {
                $division_data = $this->map_csv_row_to_division_data($row, $headers);

                if ($division_data) {
                    try {
                        $result = $this->divisions_model->add($division_data);
                        if ($result) {
                            $imported++;
                        } else {
                            $errors[] = "Row " . ($row_index + 1) . ": " . _l('failed_to_create_division');
                        }
                    } catch (Exception $e) {
                        $errors[] = "Row " . ($row_index + 1) . ": " . $e->getMessage();
                        log_message('error', 'Division creation error: ' . $e->getMessage());
                    }
                } else {
                    $errors[] = "Row " . ($row_index + 1) . ": " . _l('invalid_data_format');
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

        return ['success' => true, 'imported' => $imported];
    }

    private function map_csv_row_to_division_data($row, $header)
    {
        // Expected CSV columns: name
        $data = [];

        // Map by column names or positions
        $column_map = [
            'name' => null,
            'division_name' => null,
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
            ];
        }

        // Extract data
        $name = isset($column_map['name']) && isset($row[$column_map['name']]) ? trim($row[$column_map['name']]) : '';
        if (empty($name) && isset($column_map['division_name']) && isset($row[$column_map['division_name']])) {
            $name = trim($row[$column_map['division_name']]);
        }

        // Validate required fields
        if (empty($name)) {
            return false;
        }

        // Build division data array
        $data = [
            'name' => $name,
        ];

        return $data;
    }
}
