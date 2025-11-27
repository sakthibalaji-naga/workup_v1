<?php

namespace app\services;

use Carbon\CarbonInterval;
use CI_Controller;
use InvalidArgumentException;

class TicketsReportByStaff
{
    /**
     * @var CI_Controller|object
     */
    protected $CI;

    /** @var string */
    protected string $modeWhere = '';

    /** @var array<int, object> */
    protected array $result = [];

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    /**
     * @param $mode string
     * @return array<int, object>
     */
    public function filterBy($mode)
    {
        $this->setModeWhere($mode);
        $this->query();
        $this->formatAverageReplyTime();
        return $this->result;
    }

    /**
     * @param  string  $mode
     * @return void
     */
    private function setModeWhere($mode)
    {
        $carbon = \Carbon\Carbon::now();
        switch ($mode) {
            case 'this_week':
                $this->modeWhere = 'and ' . db_prefix() . 'tickets.date between "' . $carbon->startOfWeek() . '" and "' . $carbon->endOfWeek() . '"';
                break;
            case 'last_week':
                $carbon->subWeek();
                $this->modeWhere = 'and ' . db_prefix() . 'tickets.date between "' . $carbon->startOfWeek() . '" and "' . $carbon->endOfWeek() . '"';
                break;
            case 'this_month':
                $this->modeWhere = 'and ' . db_prefix() . 'tickets.date between "' . $carbon->startOfMonth() . '" and "' . $carbon->endOfMonth() . '"';
                break;
            case 'last_month':
                $carbon->subMonth();
                $this->modeWhere = 'and ' . db_prefix() . 'tickets.date between "' . $carbon->startOfMonth() . '" and "' . $carbon->endOfMonth() . '"';
                break;
            case 'this_year':
                $this->modeWhere = 'and ' . db_prefix() . 'tickets.date between "' . $carbon->startOfYear() . '" and "' . $carbon->endOfYear() . '"';
                break;
            case 'last_year':
                $carbon->subYear();
                $this->modeWhere = 'and ' . db_prefix() . 'tickets.date between "' . $carbon->startOfYear() . '" and "' . $carbon->endOfYear() . '"';
                break;
            default:
                throw new InvalidArgumentException("Invalid Mode Provided");
        }
    }

    protected function query()
    {
        $staffTable      = db_prefix() . 'staff';
        $ticketCountsSql = $this->getTicketCountsSql();
        $replyModeWhere  = str_replace('tickets', 'ticket_replies', $this->modeWhere);

        $this->result = $this->CI->db
            ->select(implode(',', [
                $staffTable . '.staffid',
                $staffTable . '.firstname',
                $staffTable . '.lastname',
                'COALESCE(ticket_counts.total_tickets, 0) as total_assigned',
                'COALESCE(ticket_counts.total_open_tickets, 0) as total_open_tickets',
                'COALESCE(ticket_counts.total_closed_tickets, 0) as total_closed_tickets',
                '(SELECT count(ticketid) from ' . db_prefix() . 'ticket_replies where ' . db_prefix() . 'ticket_replies.admin = ' . $staffTable . '.staffid ' . $replyModeWhere . ') as total_replies',
            ]))
            ->from($staffTable)
            ->join('(' . $ticketCountsSql . ') ticket_counts', 'ticket_counts.staff_id = ' . $staffTable . '.staffid', 'left', false)
            ->where($staffTable . '.active', 1)
            ->get()
            ->result();
    }

    public function formatAverageReplyTime()
    {
        $this->result = collect($this->result)->map(function ($staffReport) {
            $staffReport->average_reply_time = $this->CI->db->select('(SELECT avg(response_seconds) FROM (SELECT time_to_sec(timediff(min(r.date), t.date)) AS response_seconds FROM ' . db_prefix() . 'tickets t JOIN ' . db_prefix() . 'ticket_replies r  ON t.ticketid = r.ticketid WHERE r.admin != 0 AND t.assigned = ' . $staffReport->staffid . ' ' . str_replace(db_prefix() . 'tickets', 't', $this->modeWhere) . ' GROUP BY t.ticketid) AS r) as average_reply_time')
                ->get('staff')->row()->average_reply_time;

            if ($staffReport->average_reply_time === null || $staffReport->average_reply_time < 60) {
                $staffReport->average_reply_time = '-';
            } else {
                $period = CarbonInterval::seconds($staffReport->average_reply_time);
                if ($period->totalHours < 1) {
                    $staffReport->average_reply_time = (int) $period->totalMinutes . ' ' . _l('minutes');
                } elseif ($period->totalDays <= 4) {
                    $staffReport->average_reply_time = (int) $period->totalHours . ' ' . _l('hours');
                } else {
                    $staffReport->average_reply_time = (int) $period->totalDays . ' ' . _l('days');
                }
            }
            return $staffReport;
        })->toArray();
    }

    /**
     * Build a reusable ticket counting subquery that considers assignments and handlers.
     *
     * @param  array<int>|null  $limitToStaffIds
     * @return string
     */
    protected function getTicketCountsSql(?array $limitToStaffIds = null): string
    {
        $ticketsTable  = db_prefix() . 'tickets';
        $handlersTable = db_prefix() . 'ticket_handlers';

        $assignedFilter = '';
        $handlerFilter  = '';

        if (is_array($limitToStaffIds)) {
            if (empty($limitToStaffIds)) {
                return 'SELECT NULL AS staff_id, 0 AS total_tickets, 0 AS total_open_tickets, 0 AS total_closed_tickets WHERE 1 = 0';
            }

            $idList        = implode(',', array_map('intval', $limitToStaffIds));
            $assignedFilter = ' AND t.assigned IN (' . $idList . ')';
            $handlerFilter  = ' AND th.staffid IN (' . $idList . ')';
        }

        $dateFilter = '';
        if ($this->modeWhere !== '') {
            $dateFilter = ' ' . str_replace($ticketsTable, 't', $this->modeWhere);
        }

        $ticketRoleQueries   = [];
        $ticketRoleQueries[] = 'SELECT t.ticketid, t.status, t.assigned AS staff_id '
            . 'FROM ' . $ticketsTable . ' t '
            . 'WHERE t.assigned != 0' . $assignedFilter . $dateFilter;

        if ($this->CI->db->table_exists($handlersTable)) {
            $ticketRoleQueries[] = 'SELECT t.ticketid, t.status, th.staffid AS staff_id '
                . 'FROM ' . $ticketsTable . ' t '
                . 'JOIN ' . $handlersTable . ' th ON th.ticketid = t.ticketid '
                . 'WHERE th.staffid IS NOT NULL' . $handlerFilter . $dateFilter;
        }

        $ticketRolesUnion = implode(' UNION ', $ticketRoleQueries);

        return 'SELECT staff_id,'
            . ' COUNT(DISTINCT ticketid) AS total_tickets,'
            . ' COUNT(DISTINCT CASE WHEN status = 1 THEN ticketid END) AS total_open_tickets,'
            . ' COUNT(DISTINCT CASE WHEN status = 5 THEN ticketid END) AS total_closed_tickets '
            . 'FROM (' . $ticketRolesUnion . ') ticket_roles '
            . 'GROUP BY staff_id';
    }
}
