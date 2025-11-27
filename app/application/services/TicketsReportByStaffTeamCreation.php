<?php

namespace app\services;

class TicketsReportByStaffTeamCreation extends TicketsReportByStaff
{
    /** @var array<int> */
    private array $teamStaffIds = [];

    public function __construct(?array $teamStaffIds = null)
    {
        parent::__construct();
        $this->CI          = &get_instance();
        $this->teamStaffIds = $teamStaffIds ?? get_staff_subordinate_ids(null, true, false);
    }

    protected function query()
    {
        if (empty($this->teamStaffIds)) {
            $this->result = [];
            return;
        }

        $staffTable   = db_prefix() . 'staff';
        $ticketsTable = db_prefix() . 'tickets';

        $idList = implode(',', array_map('intval', $this->teamStaffIds));

        $dateFilter = '';
        if ($this->modeWhere !== '') {
            $dateFilter = ' ' . str_replace($ticketsTable, 't', $this->modeWhere);
        }

        $ticketCountsSql = 'SELECT t.admin AS staff_id,'
            . ' COUNT(DISTINCT t.ticketid) AS total_tickets,'
            . ' COUNT(DISTINCT CASE WHEN t.status = 1 THEN t.ticketid END) AS total_open_tickets,'
            . ' COUNT(DISTINCT CASE WHEN t.status = 5 THEN t.ticketid END) AS total_closed_tickets'
            . ' FROM ' . $ticketsTable . ' t'
            . ' WHERE t.admin IN (' . $idList . ')' . $dateFilter
            . ' GROUP BY t.admin';

        $replyModeWhere = str_replace('tickets', 'ticket_replies', $this->modeWhere);

        $this->result = $this->CI->db
            ->select(implode(',', [
                $staffTable . '.staffid',
                $staffTable . '.firstname',
                $staffTable . '.lastname',
                'COALESCE(ticket_counts.total_tickets, 0) AS total_created',
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

    public function formatAverageReplyTime()
    {
        $this->result = collect($this->result)->map(function ($staffReport) {
            $staffReport->average_reply_time = '-';
            return $staffReport;
        })->toArray();
    }
}

