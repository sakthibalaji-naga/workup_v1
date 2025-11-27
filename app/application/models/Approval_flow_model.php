<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Approval_flow_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get approval flow by id
     * @param  mixed $id approval flow id
     * @return object
     */
    public function get($id)
    {
        $this->db->where('id', $id);
        $approval_flow = $this->db->get(db_prefix() . 'approval_flows')->row();

        if ($approval_flow) {
            $approval_flow->steps = $this->get_approval_steps($id);
        }

        return $approval_flow;
    }

    /**
     * Check if approval flow is used in active tasks
     */
    public function is_used_in_tasks($flow_id)
    {
        $this->db->where('rel_type', 'approval');
        $this->db->where('rel_id', $flow_id);
        $this->db->where('status !=', 5); // Exclude completed tasks (status 5 = completed)
        $count = $this->db->count_all_results(db_prefix() . 'tasks');

        return $count > 0;
    }

    /**
     * Get active tasks that are using the approval flow
     */
    public function get_active_tasks_using_flow($flow_id)
    {
        $this->db->select('id, name, status');
        $this->db->from(db_prefix() . 'tasks');
        $this->db->where('rel_type', 'approval');
        $this->db->where('rel_id', $flow_id);
        $this->db->where('status !=', 5); // Exclude completed tasks (status 5 = completed)
        $this->db->order_by('id', 'desc');

        return $this->db->get()->result();
    }

    /**
     * Add new approval flow
     * @param array $data approval flow data
     * @return mixed
     */
    public function add($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');

        // Handle approval steps
        $steps = [];
        if (isset($data['steps'])) {
            $steps = $data['steps'];
            unset($data['steps']);
        }

        $this->db->insert(db_prefix() . 'approval_flows', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            // Add approval steps
            if (!empty($steps)) {
                foreach ($steps as $step_order => $step) {
                    if (!empty($step['staff_id'])) {
                        $this->db->insert(db_prefix() . 'approval_flow_steps', [
                            'approval_flow_id' => $insert_id,
                            'staff_id' => $step['staff_id'],
                            'step_order' => $step_order + 1,
                            'step_name' => isset($step['step_name']) ? $step['step_name'] : 'Step ' . ($step_order + 1),
                        ]);
                    }
                }
            }

            log_activity('New Approval Flow Added [ID:' . $insert_id . ', Name: ' . $data['name'] . ']');
            return $insert_id;
        }

        return false;
    }

    /**
     * Update approval flow
     * @param array $data approval flow data
     * @param mixed $id approval flow id
     * @return boolean
     */
    public function update($data, $id)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Handle approval steps
        $steps = [];
        if (isset($data['steps'])) {
            $steps = $data['steps'];
            unset($data['steps']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'approval_flows', $data);

        if ($this->db->affected_rows() > 0) {
            // Update approval steps - first delete existing
            $this->db->where('approval_flow_id', $id);
            $this->db->delete(db_prefix() . 'approval_flow_steps');

            // Add new steps
            if (!empty($steps)) {
                foreach ($steps as $step_order => $step) {
                    if (!empty($step['staff_id'])) {
                        $this->db->insert(db_prefix() . 'approval_flow_steps', [
                            'approval_flow_id' => $id,
                            'staff_id' => $step['staff_id'],
                            'step_order' => $step_order + 1,
                            'step_name' => isset($step['step_name']) ? $step['step_name'] : 'Step ' . ($step_order + 1),
                        ]);
                    }
                }
            }

            log_activity('Approval Flow Updated [ID:' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Delete approval flow
     * @param mixed $id approval flow id
     * @return boolean
     */
    public function delete($id)
    {
        // Delete approval steps first
        $this->db->where('approval_flow_id', $id);
        $this->db->delete(db_prefix() . 'approval_flow_steps');

        // Delete the approval flow
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'approval_flows');

        if ($this->db->affected_rows() > 0) {
            log_activity('Approval Flow Deleted [ID:' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Get approval steps for a flow
     * @param mixed $flow_id
     * @return array
     */
    public function get_approval_steps($flow_id)
    {
        $this->db->select(db_prefix() . 'approval_flow_steps.*, ' . db_prefix() . 'staff.firstname, ' . db_prefix() . 'staff.lastname');
        $this->db->from(db_prefix() . 'approval_flow_steps');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'approval_flow_steps.staff_id');
        $this->db->where('approval_flow_id', $flow_id);
        $this->db->order_by('step_order', 'asc');

        return $this->db->get()->result_array();
    }

    /**
     * Get all active approval flows (id + name only)
     * @return array
     */
    public function get_active_flows($createdBy = null)
    {
        $this->db->select('id, name')
            ->from(db_prefix() . 'approval_flows')
            ->where('status', 1);

        if ($createdBy !== null) {
            $this->db->where('created_by', (int) $createdBy);
        }

        return $this->db
            ->order_by('name', 'asc')
            ->get()
            ->result_array();
    }

    /**
     * Change approval flow status
     * @param mixed $id approval flow id
     * @return boolean
     */
    public function change_status($id)
    {
        $this->db->where('id', $id);
        $flow = $this->db->get(db_prefix() . 'approval_flows')->row();

        if ($flow) {
            $status = $flow->status == 1 ? 0 : 1;

            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'approval_flows', ['status' => $status]);

            if ($this->db->affected_rows() > 0) {
                log_activity('Approval Flow Status Changed [ID:' . $id . ', Status: ' . ($status ? 'Active' : 'Inactive') . ']');
                return true;
            } else {
                // Check if the status is actually different
                $this->db->where('id', $id);
                $updated_flow = $this->db->get(db_prefix() . 'approval_flows')->row();
                if ($updated_flow && $updated_flow->status == $status) {
                    // Status was already the desired value, consider it successful
                    return true;
                }
            }
        }

        return false;
    }


}
