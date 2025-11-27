<?php

namespace app\services;

class TicketsReportForGroupMembers extends TicketsReportByStaff
{
    protected $CI;
    private $teamStaffIds = [];

    public function __construct($teamStaffIds = null)
    {
        parent::__construct();
        $this->CI = &get_instance();

        if ($teamStaffIds === null) {
            // Get current user's groups as leader, get all members
            $this->CI =& get_instance();
            $groups = $this->CI->db->select('id')->where('leader_id', get_staff_user_id())->get(db_prefix() . 'groups')->result_array();
            $memberIds = [];
            foreach ($groups as $g) {
                $gMembers = $this->CI->db->select('member_id')->where('group_id', $g['id'])->get(db_prefix() . 'group_members')->result_array();
                $memberIds = array_merge($memberIds, array_column($gMembers, 'member_id'));
            }
            $this->teamStaffIds = array_unique($memberIds);
        } else {
            $this->teamStaffIds = $teamStaffIds;
        }
    }

    protected function query()
    {
        if (empty($this->teamStaffIds)) {
            $this->result = [];
            return;
        }

        $this->result = $this->CI->db
            ->select(implode(',', [
                    'staffid',
                    'firstname',
                    'lastname',
                    '(SELECT count(ticketid) from ' . db_prefix() . 'tickets where assigned = staffid ' . $this->modeWhere . ') as total_assigned',
                    '(SELECT count(ticketid) from ' . db_prefix() . 'tickets where assigned = staffid and status = 1 ' . $this->modeWhere . ') as total_open_tickets',
                    '(SELECT count(ticketid) from ' . db_prefix() . 'tickets where assigned = staffid and status = 5 ' . $this->modeWhere . ') as total_closed_tickets',
                    '(SELECT count(ticketid) from ' . db_prefix() . 'ticket_replies where ' . db_prefix() . 'ticket_replies.admin = staffid ' . str_replace('tickets',
                        'ticket_replies', $this->modeWhere) . ') as total_replies',
                ]))
            ->where_in('staffid', $this->teamStaffIds)
            ->where(db_prefix() . 'staff.active', 1)
            ->get('staff')
            ->result();
    }
}
