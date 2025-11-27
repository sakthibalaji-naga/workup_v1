<?php

namespace app\services;

use Carbon\CarbonInterval;

class TicketsReportByStaffTeam extends TicketsReportByStaff
{
    protected $CI;
    private $teamStaffIds = [];

    public function __construct($teamStaffIds = null)
    {
        parent::__construct();
        $this->CI = &get_instance();

        if ($teamStaffIds === null) {
            $this->teamStaffIds = get_staff_subordinate_ids(null, true, false);
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

        $staffTable      = db_prefix() . 'staff';
        $idList          = implode(',', array_map('intval', $this->teamStaffIds));
        $ticketCountsSql = $this->getTicketCountsSql($this->teamStaffIds);
        $replyModeWhere  = str_replace('tickets', 'ticket_replies', $this->modeWhere);

        $this->result = $this->CI->db
            ->select(implode(',', [
                $staffTable . '.staffid',
                $staffTable . '.firstname',
                $staffTable . '.lastname',
                'COALESCE(ticket_counts.total_tickets, 0) AS total_assigned',
                'COALESCE(ticket_counts.total_open_tickets, 0) AS total_open_tickets',
                'COALESCE(ticket_counts.total_closed_tickets, 0) AS total_closed_tickets',
                '(SELECT COUNT(ticketid) FROM ' . db_prefix() . 'ticket_replies WHERE '
                    . db_prefix() . 'ticket_replies.admin = ' . $staffTable . '.staffid '
                    . $replyModeWhere . ') AS total_replies',
            ]))
            ->from($staffTable)
            ->join('(' . $ticketCountsSql . ') ticket_counts', 'ticket_counts.staff_id = ' . $staffTable . '.staffid', 'left', false)
            ->where($staffTable . '.staffid IN (' . $idList . ')')
            ->where($staffTable . '.active', 1)
            ->get()
            ->result();
    }
}
