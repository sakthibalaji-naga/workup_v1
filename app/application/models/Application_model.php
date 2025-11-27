<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Application_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        $applicationsTable = db_prefix() . 'applications';
        $hasPositionColumn = $this->db->field_exists('position', $applicationsTable);

        if ($hasPositionColumn) {
            $this->db->order_by('(CASE WHEN ' . $applicationsTable . '.position = 0 THEN 1 ELSE 0 END)', 'ASC', false);
            $this->db->order_by($applicationsTable . '.position', 'ASC');
        }

        $this->db->order_by($applicationsTable . '.name', 'ASC');

        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get($applicationsTable)->row();
        }

        return $this->db->get($applicationsTable)->result_array();
    }

    public function add($data)
    {
        $this->db->insert(db_prefix() . 'applications', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Application Added [ID: ' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    public function update($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'applications', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Application Updated [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'applications');
        if ($this->db->affected_rows() > 0) {
            log_activity('Application Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Toggle application active/inactive status
     */
    public function toggle_status($id, $status)
    {
        // Check if active column exists
        if (!$this->db->field_exists('active', db_prefix() . 'applications')) {
            // If column doesn't exist, return false (migration not run)
            return false;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'applications', ['active' => $status]);

        if ($this->db->affected_rows() > 0) {
            $action = $status ? 'Activated' : 'Deactivated';
            log_activity('Application ' . $action . ' [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Get linked services for an application
     */
    public function get_linked_services($application_id)
    {
        if (!$this->db->field_exists('applicationid', db_prefix() . 'services')) {
            log_message('error', 'tblservices.applicationid column missing when fetching linked services for application ID: ' . $application_id);

            return [];
        }

        $fields = [
            'serviceid',
            'name as service_name',
        ];

        $hasActiveColumn = $this->db->field_exists('active', db_prefix() . 'services');
        if ($hasActiveColumn) {
            $fields[] = 'active';
        }

        $this->db->select(implode(', ', $fields));
        $this->db->from(db_prefix() . 'services');
        $this->db->where('applicationid', $application_id);
        $this->db->order_by('name', 'ASC');

        $services = $this->db->get()->result_array();

        if ($hasActiveColumn) {
            foreach ($services as &$service) {
                $service['active'] = (int) $service['active'];
            }
            unset($service);
        }

        return $services ?: [];
    }

    /**
     * Check if application has linked services
     */
    public function has_linked_services($application_id)
    {
        if (!$this->db->field_exists('applicationid', db_prefix() . 'services')) {
            return false;
        }

        $this->db->where('applicationid', $application_id);

        return (int) $this->db->count_all_results(db_prefix() . 'services') > 0;
    }

    /**
     * Count how many linked services are active (if active column exists)
     */
    public function count_active_linked_services($application_id)
    {
        if (
            !$this->db->field_exists('applicationid', db_prefix() . 'services')
            || !$this->db->field_exists('active', db_prefix() . 'services')
        ) {
            return 0;
        }

        $this->db->where('applicationid', $application_id);
        $this->db->where('active', 1);

        return (int) $this->db->count_all_results(db_prefix() . 'services');
    }

    /**
     * Mark linked services as inactive (if active column exists)
     */
    public function deactivate_linked_services($application_id)
    {
        if (
            !$this->db->field_exists('applicationid', db_prefix() . 'services')
            || !$this->db->field_exists('active', db_prefix() . 'services')
        ) {
            return 0;
        }

        $this->db->where('applicationid', $application_id);
        $this->db->where('active', 1);
        $this->db->update(db_prefix() . 'services', ['active' => 0]);

        $affected = $this->db->affected_rows();

        if ($affected > 0) {
            log_activity('Linked services deactivated [' . $affected . ' services][Application ID: ' . $application_id . ']');
        }

        return $affected;
    }
}
