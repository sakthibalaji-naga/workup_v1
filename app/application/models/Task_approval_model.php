<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Task_approval_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get task approvals by task id
     * @param  mixed $task_id
     * @return array
     */
    public function get_by_task_id($task_id)
    {
        $this->db->select('tbltask_approvals.*, tblstaff.firstname, tblstaff.lastname');
        $this->db->from('tbltask_approvals');
        $this->db->join('tblstaff', 'tblstaff.staffid = tbltask_approvals.staff_id');
        $this->db->where('task_id', $task_id);
        $this->db->order_by('step_order', 'asc');

        return $this->db->get()->result_array();
    }

    /**
     * Initialize task approvals when task is created
     * @param  mixed $task_id
     * @param  mixed $approval_flow_id
     * @return boolean
     */
    public function initialize_task_approvals($task_id, $approval_flow_id)
    {
        // Get approval flow steps
        $this->load->model('approval_flow_model');
        $approval_flow = $this->approval_flow_model->get($approval_flow_id);

        if (!$approval_flow || empty($approval_flow->steps)) {
            return false;
        }

        // Create approval records for each step
        foreach ($approval_flow->steps as $step) {
            $this->db->insert('tbltask_approvals', [
                'task_id' => $task_id,
                'staff_id' => $step['staff_id'],
                'step_order' => $step['step_order'],
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return true;
    }

    /**
     * Approve task step
     * @param  mixed $task_id
     * @param  mixed $staff_id
     * @param  string $comments
     * @return boolean
     */
    public function approve_step($task_id, $staff_id, $comments = '')
    {
        $this->db->where('task_id', $task_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->where('status', 'pending');

        $approval = $this->db->get('tbltask_approvals')->row();

        if (!$approval) {
            return false;
        }

        // Update approval status
        $this->db->where('id', $approval->id);
        $this->db->update('tbltask_approvals', [
            'status' => 'approved',
            'approved_at' => date('Y-m-d H:i:s'),
            'comments' => $comments,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($comments !== null && trim((string) $comments) !== '') {
            $this->log_remark_history($approval->id, $task_id, $staff_id, $comments, 'approve');
        }

        // Check if all approvals are complete
        $this->check_task_completion($task_id);

        return true;
    }

    /**
     * Reject task step
     * @param  mixed $task_id
     * @param  mixed $staff_id
     * @param  string $comments
     * @return boolean
     */
    public function reject_step($task_id, $staff_id, $comments = '')
    {
        $this->db->where('task_id', $task_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->where('status', 'pending');

        $approval = $this->db->get('tbltask_approvals')->row();

        if (!$approval) {
            return false;
        }

        // Update approval status
        $this->db->where('id', $approval->id);
        $this->db->update('tbltask_approvals', [
            'status' => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s'),
            'comments' => $comments,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($comments !== null && trim((string) $comments) !== '') {
            $this->log_remark_history($approval->id, $task_id, $staff_id, $comments, 'reject');
        }

        // Mark task as rejected
        $this->db->where('id', $task_id);
        $this->db->update('tbltasks', ['status' => 5]); // Cancelled status

        return true;
    }

    /**
     * Revert a completed approval step back to pending
     *
     * @param  int    $approval_id
     * @param  int    $actor_staff_id
     * @param  string $comments
     * @return bool
     */
    public function revert_step($approval_id, $actor_staff_id, $comments = '')
    {
        $approval = $this->db->where('id', (int) $approval_id)->get('tbltask_approvals')->row();

        if (! $approval || $approval->status === 'pending') {
            return false;
        }

        $this->db->where('id', (int) $approval_id);
        $this->db->update('tbltask_approvals', [
            'status' => 'pending',
            'approved_at' => null,
            'rejected_at' => null,
            'comments' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($this->db->affected_rows() <= 0) {
            return false;
        }

        if (! class_exists('Tasks_model')) {
            $this->load->model('tasks_model');
        }

        $this->db->where('id', (int) $approval->task_id);
        $this->db->update('tbltasks', ['status' => Tasks_model::STATUS_IN_PROGRESS]);

        $this->log_remark_history($approval->id, $approval->task_id, $actor_staff_id, $comments, 'revert');

        return true;
    }

    /**
     * Check if task is fully approved
     * @param  mixed $task_id
     * @return boolean
     */
    public function check_task_completion($task_id)
    {
        $this->db->where('task_id', $task_id);
        $approvals = $this->db->get('tbltask_approvals')->result_array();

        $all_approved = true;
        foreach ($approvals as $approval) {
            if ($approval['status'] !== 'approved') {
                $all_approved = false;
                break;
            }
        }

        if ($all_approved) {
            // Mark task as completed
            $this->db->where('id', $task_id);
            $this->db->update('tbltasks', ['status' => Tasks_model::STATUS_TESTING]); // Move to testing status
            return true;
        }

        return false;
    }

    /**
     * Get next pending approval for staff
     * @param  mixed $task_id
     * @param  mixed $staff_id
     * @return mixed
     */
    public function get_next_pending_approval($task_id, $staff_id)
    {
        // Get all approvals for this task
        $approvals = $this->get_by_task_id($task_id);

        // Find the first pending approval that belongs to this staff member
        foreach ($approvals as $approval) {
            if ($approval['staff_id'] == $staff_id && $approval['status'] == 'pending') {
                // Check if all previous steps are approved
                $previous_approved = true;
                foreach ($approvals as $prev_approval) {
                    if ($prev_approval['step_order'] < $approval['step_order'] && $prev_approval['status'] !== 'approved') {
                        $previous_approved = false;
                        break;
                    }
                }

                if ($previous_approved) {
                    return $approval;
                }
            }
        }

        return null;
    }

    /**
     * Save remarks (comments) for a specific approval step without changing status
     * @param  mixed $task_id
     * @param  mixed $staff_id
     * @param  string $comments
     * @return boolean
     */
    public function save_remarks($task_id, $staff_id, $comments = '')
    {
        $this->db->where('task_id', $task_id);
        $this->db->where('staff_id', $staff_id);

        $approval = $this->db->get('tbltask_approvals')->row();

        if (!$approval) {
            return false;
        }

        // Update remarks/comments
        $this->db->where('id', $approval->id);
        $this->db->update('tbltask_approvals', [
            'comments' => $comments,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $updated = $this->db->affected_rows() > 0;
        $historyLogged = false;

        if ($comments !== null && trim((string) $comments) !== '') {
            $historyLogged = $this->log_remark_history($approval->id, $task_id, $staff_id, $comments, 'remark');
        }

        return $updated || $historyLogged;
    }

    /**
     * Get a single approval row
     *
     * @param  int $approval_id
     * @return array|null
     */
    public function find($approval_id)
    {
        $this->db->select('tbltask_approvals.*, tblstaff.firstname, tblstaff.lastname');
        $this->db->from('tbltask_approvals');
        $this->db->join('tblstaff', 'tblstaff.staffid = tbltask_approvals.staff_id');
        $this->db->where('tbltask_approvals.id', (int) $approval_id);

        return $this->db->get()->row_array();
    }

    /**
     * Get remark history for a specific approval
     *
     * @param  int $task_approval_id
     * @return array
     */
    public function get_remark_history($task_approval_id)
    {
        if (! $task_approval_id) {
            return [];
        }

        $histories = $this->get_remark_history_for_approvals([$task_approval_id]);

        return $histories[$task_approval_id] ?? [];
    }

    /**
     * Get remark history for multiple approvals keyed by approval ID
     *
     * @param  array $approval_ids
     * @return array
     */
    public function get_remark_history_for_approvals($approval_ids)
    {
        $approval_ids = array_filter(array_map('intval', (array) $approval_ids));

        if (empty($approval_ids)) {
            return [];
        }

        $historyTable = db_prefix() . 'task_approval_remark_history';
        $staffTable   = db_prefix() . 'staff';

        $this->db->select($historyTable . '.*, ' . $staffTable . '.firstname, ' . $staffTable . '.lastname');
        $this->db->from($historyTable);
        $this->db->join($staffTable, $staffTable . '.staffid = ' . $historyTable . '.staff_id', 'left');
        $this->db->where_in($historyTable . '.task_approval_id', $approval_ids);
        $this->db->order_by($historyTable . '.created_at', 'desc');

        $results = $this->db->get()->result_array();

        $grouped = [];
        foreach ($results as $row) {
            $grouped[$row['task_approval_id']][] = $row;
        }

        return $grouped;
    }

    /**
     * Persist remark history entry
     *
     * @param  int    $approval_id
     * @param  int    $task_id
     * @param  int    $staff_id
     * @param  string $comments
     * @param  string $action_type
     * @return bool
     */
    private function log_remark_history($approval_id, $task_id, $staff_id, $comments, $action_type = 'remark')
    {
        $comments = trim((string) $comments);

        if ($comments === '' && $action_type === 'remark') {
            return false;
        }

        if ($comments === '') {
            $comments = _l('task_approval_history_default_note');
        }

        $this->db->insert(db_prefix() . 'task_approval_remark_history', [
            'task_id' => (int) $task_id,
            'task_approval_id' => (int) $approval_id,
            'staff_id' => (int) $staff_id,
            'action_type' => $action_type,
            'comments' => $comments,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Get approval status summary
     * @param  mixed $task_id
     * @return array
     */
    public function get_approval_summary($task_id)
    {
        $approvals = $this->get_by_task_id($task_id);

        $summary = [
            'total' => count($approvals),
            'approved' => 0,
            'rejected' => 0,
            'pending' => 0,
        ];

        foreach ($approvals as $approval) {
            $summary[$approval['status']]++;
        }

        return $summary;
    }
}
