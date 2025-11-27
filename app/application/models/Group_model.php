<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Group_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  integer ID (optional)
     * @return mixed
     * Get group object based on passed id if not passed id return array of all groups
     */
    public function get($id = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            $group = $this->db->get(db_prefix() . 'groups')->row();

            if ($group) {
                $group->members = $this->get_group_members($id, true);
                // Load names for display
                $group->division_name = '';
                if ($group->division_id) {
                    $this->load->model('divisions_model');
                    $division = $this->divisions_model->get($group->division_id);
                    $group->division_name = $division->name ?? '';
                }

                $deptInfo = $this->get_leader_department_info($group->leader_id);
                $departmentNames = $deptInfo['departments'];
                $subDepartmentNames = $deptInfo['sub_departments'];

                if (!empty($departmentNames)) {
                    $group->department_name = implode(', ', $departmentNames);
                    $group->department_id = $deptInfo['department_ids'][0];
                } else {
                    $group->department_name = '';
                    $group->department_id = 0;
                }

                if (!empty($subDepartmentNames)) {
                    $group->sub_department_name = implode(', ', $subDepartmentNames);
                    $group->sub_department_id = $deptInfo['sub_department_ids'][0];
                } else {
                    $group->sub_department_name = '';
                    $group->sub_department_id = null;
                }
            }

            return $group;
        }

        $groups = $this->db->get(db_prefix() . 'groups')->result_array();

        foreach ($groups as &$group) {
            $group['members'] = $this->get_group_members($group['id'], true);
            // Load names for display
            $group['division_name'] = '';
            if ($group['division_id']) {
                $this->load->model('divisions_model');
                $division = $this->divisions_model->get($group['division_id']);
                $group['division_name'] = $division->name ?? '';
            }

            $deptInfo = $this->get_leader_department_info($group['leader_id']);
            $departmentNames = $deptInfo['departments'];
            $subDepartmentNames = $deptInfo['sub_departments'];

            $group['department_name'] = !empty($departmentNames) ? implode(', ', $departmentNames) : '';
            $group['sub_department_name'] = !empty($subDepartmentNames) ? implode(', ', $subDepartmentNames) : '';
        }

        return $groups;
    }

    /**
     * @param array $_POST data
     * @return integer
     * Add new group
     */
    public function add($data)
    {
        $members = [];
        if (isset($data['members'])) {
            $members = array_filter(array_map('intval', (array) $data['members']));
            unset($data['members']);
        }

        $data = hooks()->apply_filters('before_group_added', $data);
        $this->db->insert(db_prefix() . 'groups', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            // Save members relations
            if (!empty($members)) {
                foreach ($members as $member_id) {
                    $this->db->insert(db_prefix() . 'group_members', [
                        'group_id'   => $insert_id,
                        'member_id' => $member_id,
                    ]);
                }
            }
            hooks()->do_action('after_group_added', $insert_id);
            log_activity('New Group Added [' . $data['name'] . ', ID: ' . $insert_id . ']');
        }

        return $insert_id;
    }

    /**
     * @param  array $_POST data
     * @param  integer ID
     * @return boolean
     * Update group to database
     */
    public function update($data, $id)
    {
        $members = null;
        if (isset($data['members'])) {
            $members = array_filter(array_map('intval', (array) $data['members']));
            unset($data['members']);
        }

        $data = hooks()->apply_filters('before_group_updated', $data, $id);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'groups', $data);
        $baseUpdated = $this->db->affected_rows() > 0;
        if ($baseUpdated) {
            log_activity('Group Updated [Name: ' . $data['name'] . ', ID: ' . $id . ']');
        }

        $membersChanged = false;
        // Even if no changes in group table, still sync members if provided
        if (is_array($members)) {
            // Fetch existing
            $existing = $this->db->select('member_id')->where('group_id', $id)->get(db_prefix().'group_members')->result_array();
            $existingIds = array_map(function($r){return (int)$r['member_id'];}, $existing);
            $toInsert = array_diff($members, $existingIds);
            $toDelete = array_diff($existingIds, $members);
            if (!empty($toDelete)) {
                $this->db->where('group_id', $id);
                $this->db->where_in('member_id', $toDelete);
                $this->db->delete(db_prefix().'group_members');
                $membersChanged = true;
            }
            foreach ($toInsert as $member_id) {
                $this->db->insert(db_prefix().'group_members', [
                    'group_id'   => $id,
                    'member_id' => $member_id,
                ]);
                $membersChanged = true;
            }
        }

        return $baseUpdated || $membersChanged;
    }

    /**
     * @param  integer ID
     * @return mixed
     * Delete group from database, if used return array with key referenced
     */
    public function delete($id)
    {
        if (is_reference_in_table('group_id', db_prefix() . 'group_members', $id)) {
            return [
                'referenced' => true,
            ];
        }

        hooks()->do_action('before_delete_group', $id);

        // Delete members first
        $this->db->where('group_id', $id);
        $this->db->delete(db_prefix() . 'group_members');

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'groups');
        if ($this->db->affected_rows() > 0) {
            log_activity('Group Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * @param  integer $group_id
     * @param  boolean $only_ids
     * @return array
     * Get group members
     */
    public function get_group_members($group_id, $only_ids = false)
    {
        $this->db->where('group_id', $group_id);
        if ($only_ids) {
            $this->db->select('member_id');
            return array_column($this->db->get(db_prefix() . 'group_members')->result_array(), 'member_id');
        }
        $this->db->select('*');
        $this->db->from(db_prefix() . 'group_members gm');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = gm.member_id');
        return $this->db->get()->result_array();
    }

    /**
     * Retrieve department and sub-department details for a leader
     * based on the staff_departments assignments.
     *
     * @param  int $leader_id
     * @return array
     */
    public function get_leader_department_info($leader_id)
    {
        $result = [
            'department_ids' => [],
            'departments' => [],
            'sub_department_ids' => [],
            'sub_departments' => [],
        ];

        $leader_id = (int) $leader_id;
        if ($leader_id <= 0) {
            return $result;
        }

        $rows = $this->db->select([
                db_prefix() . 'departments.departmentid as child_id',
                db_prefix() . 'departments.name as child_name',
                db_prefix() . 'departments.parent_department as parent_id',
                'parent_d.departmentid as parent_department_id',
                'parent_d.name as parent_name',
            ])
            ->from(db_prefix() . 'staff_departments')
            ->join(db_prefix() . 'departments', db_prefix() . 'departments.departmentid = ' . db_prefix() . 'staff_departments.departmentid', 'left')
            ->join(db_prefix() . 'departments parent_d', 'parent_d.departmentid = ' . db_prefix() . 'departments.parent_department', 'left')
            ->where(db_prefix() . 'staff_departments.staffid', $leader_id)
            ->get()
            ->result_array();

        if (!$rows) {
            return $result;
        }

        foreach ($rows as $row) {
            if (empty($row['child_id'])) {
                continue;
            }
            $parentId = $row['parent_id'] ?? null;
            $childId  = (int) $row['child_id'];
            $childName = $row['child_name'] ?? '';

            if (!empty($parentId)) {
                $parentId = (int) $parentId;
                $parentName = $row['parent_name'] ?? '';
                if ($parentName !== '') {
                    $result['departments'][$parentId] = $parentName;
                    $result['department_ids'][$parentId] = $parentId;
                }
                if ($childName !== '') {
                    $result['sub_departments'][$childId] = $childName;
                    $result['sub_department_ids'][$childId] = $childId;
                }
            } else {
                if ($childName !== '') {
                    $result['departments'][$childId] = $childName;
                    $result['department_ids'][$childId] = $childId;
                }
            }
        }

        $result['department_ids']     = array_values(array_unique($result['department_ids']));
        $result['departments']        = array_values(array_filter($result['departments']));
        $result['sub_department_ids'] = array_values(array_unique($result['sub_department_ids']));
        $result['sub_departments']    = array_values(array_filter($result['sub_departments']));

        return $result;
    }

        /**
     * @param  integer $staff_id
     * @return array
     * Get groups by leader
     */
    public function get_groups_by_leader($staff_id)
    {
        $this->db->where('leader_id', $staff_id);
        return $this->db->get(db_prefix() . 'groups')->result_array();
    }
}
