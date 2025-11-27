<?php

defined('BASEPATH') or exit('No direct script access allowed');

$this->ci->load->model('tickets_model');
$statuses = $this->ci->tickets_model->get_ticket_status();
$this->ci->load->model('departments_model');

$rules = [
    App_table_filter::new('subject', 'TextRule')->label(_l('ticket_dt_subject')),
    App_table_filter::new('department', 'SelectRule')->label(_l('ticket_dt_department'))->options(function ($ci) {
        return collect($ci->departments_model->get())->map(fn ($dep) => [
            'value' => $dep['departmentid'],
            'label' => $dep['name'],
        ])->all();
    })->isVisible(fn () => is_admin()),
    App_table_filter::new('status', 'MultiSelectRule')->label(_l('ticket_dt_status'))->options(function ($ci) use ($statuses) {
        return collect($statuses)->map(fn ($status) => [
            'value' => $status['ticketstatusid'],
            'label' => ticket_status_translate($status['ticketstatusid']),
        ])->all();
    }),
    App_table_filter::new('priority', 'SelectRule')->label(_l('ticket_dt_priority'))->options(function ($ci) {
        return collect($ci->tickets_model->get_priority())->map(fn ($priority) => [
            'value' => $priority['priorityid'],
            'label' => ticket_priority_translate($priority['priorityid']),
        ])->all();
    }),
    
    App_table_filter::new('merged', 'BooleanRule')->label(_l('merged'))->raw(function ($value) {
        return $value == '1' ? 'merged_ticket_id IS NOT NULL' : 'merged_ticket_id IS NULL';
    }),
    App_table_filter::new('my_tickets', 'BooleanRule')->label(_l('my_tickets'))->raw(function ($value) {
        // Show only tickets created by the current user or assigned to them
        if ($value != '1') {
            return '(admin != ' . get_staff_user_id() . ' AND assigned != ' . get_staff_user_id() . ')';
        }
        $table = db_prefix() . 'tickets';
        return '(' . $table . '.admin = ' . get_staff_user_id() . ' OR ' . $table . '.assigned = ' . get_staff_user_id() . ')';
    }),
    App_table_filter::new('my_team_tickets', 'BooleanRule')->label(_l('my_team_tickets'))->raw(function ($value) {
        $CI         = &get_instance();
        $ticketsTbl = db_prefix() . 'tickets';

        $subordinateIds = get_staff_subordinate_ids(null, true, false);

        if (empty($subordinateIds)) {
            return $value == '1' ? '1=0' : '';
        }

        $idList = implode(',', array_map('intval', $subordinateIds));

        $clauses = [
            $ticketsTbl . '.admin IN (' . $idList . ')',
            $ticketsTbl . '.assigned IN (' . $idList . ')',
        ];

        $handlersTbl = db_prefix() . 'ticket_handlers';
        if ($CI->db->table_exists($handlersTbl)) {
            $clauses[] = 'EXISTS (SELECT 1 FROM ' . $handlersTbl . ' th WHERE th.ticketid = ' . $ticketsTbl . '.ticketid AND th.staffid IN (' . $idList . '))';
        }

        $reassignTbl = db_prefix() . 'ticket_reassignments';
        if ($CI->db->table_exists($reassignTbl)) {
            $clauses[] = 'EXISTS (SELECT 1 FROM ' . $reassignTbl . ' tr WHERE tr.ticketid = ' . $ticketsTbl . '.ticketid AND tr.status = "pending" AND (tr.to_assigned IN (' . $idList . ') OR tr.from_assigned IN (' . $idList . ')))';
        }

        $condition = '(' . implode(' OR ', $clauses) . ')';

        if ($value == '1') {
            return $condition;
        }

        return 'NOT (' . $condition . ')';
    }),
    App_table_filter::new('my_team_creation_tickets', 'BooleanRule')->label(_l('my_team_creation_tickets'))->raw(function ($value) {
        $ticketsTbl     = db_prefix() . 'tickets';
        $subordinateIds = get_staff_subordinate_ids(null, true, false);

        if (empty($subordinateIds)) {
            return $value == '1' ? '1=0' : '';
        }

        $idList    = implode(',', array_map('intval', $subordinateIds));
        $condition = $ticketsTbl . '.admin IN (' . $idList . ')';

        if ($value == '1') {
            return $condition;
        }

        return 'NOT (' . $condition . ')';
    }),
    App_table_filter::new('ticket_handlers', 'BooleanRule')->label(_l('ticket_handler'))->raw(function ($value) {
        if ($value == '1') {
            // Show only tickets that have handlers
            return 'EXISTS (SELECT 1 FROM ' . db_prefix() . 'ticket_handlers th WHERE th.ticketid = ' . db_prefix() . 'tickets.ticketid)';
        } else {
            // No filter when disabled
            return '';
        }
    }),
];

$rules[] = App_table_filter::new('assigned', 'SelectRule')->label(_l('ticket_assigned'))
    ->withEmptyOperators()
    ->emptyOperatorValue(0)
    ->isVisible(fn () => is_admin())
    ->options(function ($ci) {
        $staff = $ci->staff_model->get('', ['active' => 1]);

        return collect($staff)->map(function ($staff) {
            return [
                'value' => $staff['staffid'],
                'label' => $staff['firstname'] . ' ' . $staff['lastname'],
            ];
        })->all();
    });

return App_table::find('tickets')
    ->outputUsing(function ($params) {
        extract($params);

        $divisionAlias = 'creator_division_name';
        $divisionSelect = "'' as $divisionAlias";
        $needsDivisionJoin = false;

        // Use ticket creator's division and prefer their department for display
        if ($this->ci->db->field_exists('divisionid', db_prefix() . 'tickets') && $this->ci->db->table_exists(db_prefix() . 'divisions') &&
            $this->ci->db->table_exists(db_prefix() . 'staff_departments') && $this->ci->db->table_exists(db_prefix() . 'departments') &&
            $this->ci->db->table_exists(db_prefix() . 'department_divisions')) {
            $divisionSelect    = "COALESCE(creator_div.name, '') as $divisionAlias";
            $needsDivisionJoin = true;
        }

        $departmentAlias = 'department_name';
        $departmentSelect = db_prefix() . 'departments.name as ' . $departmentAlias;

        if ($needsDivisionJoin) {
            $departmentSelect = sprintf(
                "COALESCE(creator_parent_dept.name, creator_dept.name, %sdepartments.name, '') as %s",
                db_prefix(),
                $departmentAlias
            );
        }

        $ageAlias            = 'ticket_age_days';
        $closeTimestampAlias = 'close_timestamp';

        $aColumns = [
            '1', // bulk actions
            'ticketid',
            'ticket_number',
            db_prefix() . 'tickets.date',
            'subject',
            "CONCAT(screator.firstname, ' ', screator.lastname) as created_by",
            $divisionSelect,
            '(SELECT GROUP_CONCAT(CONCAT(s.firstname, " ", s.lastname) SEPARATOR ", ") FROM ' . db_prefix() . 'ticket_handlers th JOIN ' . db_prefix() . 'staff s ON s.staffid = th.staffid WHERE th.ticketid = ' . db_prefix() . 'tickets.ticketid) as ticket_handlers',
            'CONCAT(sassigned.firstname, " ", sassigned.lastname) as assigned_name',
            'approx_resolution_time',
            "'' as $ageAlias",
            'status',
            // Keep columns index-aligned with the visible table header. Tags are not
            // fetched for performance; use an empty string placeholder to preserve indexing.
            "'' as tags",
            "CONCAT(" . db_prefix() . "contacts.firstname, ' ', " . db_prefix() . "contacts.lastname) as contact_full_name",
            $departmentSelect,
            'lastreply',
        ];

        // Contact column index with the placeholder Tags column present
        $contactColumn = 13;

        $additionalSelect = [
            'adminread',
            'ticketkey',
            db_prefix() . 'tickets.userid',
            'statuscolor',
            db_prefix() . 'tickets.name as ticket_opened_by_name',
            db_prefix() . 'tickets.email',
            'assigned',
            'priority',
            db_prefix() . 'tickets.admin as admin_id',
            db_prefix() . 'clients.company',
            'approx_response_time',
            'approx_resolution_time',
            '(SELECT CONCAT(LEFT(message, 100), CASE WHEN LENGTH(message) > 100 THEN "..." ELSE "" END) FROM ' . db_prefix() . 'ticket_replies WHERE ticketid = ' . db_prefix() . 'tickets.ticketid ORDER BY date DESC LIMIT 1) as last_reply_preview',
            '(SELECT CASE WHEN r.admin IS NOT NULL THEN CONCAT(s.firstname, " ", s.lastname) WHEN r.contactid != 0 THEN CONCAT(c.firstname, " ", c.lastname) ELSE r.name END FROM ' . db_prefix() . 'ticket_replies r LEFT JOIN ' . db_prefix() . 'staff s ON s.staffid = r.admin LEFT JOIN ' . db_prefix() . 'contacts c ON c.id = r.contactid WHERE r.ticketid = ' . db_prefix() . 'tickets.ticketid ORDER BY r.date DESC LIMIT 1) as last_replier',
        ];

        if ($this->ci->db->table_exists(db_prefix() . 'ticket_logs')) {
            $closeTimestampExpr = '(SELECT MAX(tl.timestamp) FROM ' . db_prefix() . 'ticket_logs tl WHERE tl.ticketid = ' . db_prefix() . 'tickets.ticketid AND (tl.log_type IN (\'close_request_auto_approved\', \'close_request_approved\') OR (tl.log_type = \'status_change\' AND LOWER(tl.log_details) LIKE \'%\"new_status\":\"%close%\"%\'))) as ' . $closeTimestampAlias;
            $additionalSelect[] = $closeTimestampExpr;
        } else {
            $additionalSelect[] = "NULL as $closeTimestampAlias";
        }

        // Include SLA component fields only if they exist in the DB
        $ticketsTable = db_prefix() . 'tickets';
        if ($this->ci->db->field_exists('response_time_value', $ticketsTable)) {
            $additionalSelect[] = 'response_time_value';
        }
        if ($this->ci->db->field_exists('response_time_unit', $ticketsTable)) {
            $additionalSelect[] = 'response_time_unit';
        }
        if ($this->ci->db->field_exists('resolution_time_value', $ticketsTable)) {
            $additionalSelect[] = 'resolution_time_value';
        }
        if ($this->ci->db->field_exists('resolution_time_unit', $ticketsTable)) {
            $additionalSelect[] = 'resolution_time_unit';
        }

        // Include SLA component fields from services if they exist; use aliases to avoid name clashes
        $servicesTable = db_prefix() . 'services';
        if ($this->ci->db->table_exists($servicesTable)) {
            if ($this->ci->db->field_exists('response_time_value', $servicesTable)) {
                $additionalSelect[] = db_prefix() . 'services.response_time_value as svc_response_time_value';
            }
            if ($this->ci->db->field_exists('response_time_unit', $servicesTable)) {
                $additionalSelect[] = db_prefix() . 'services.response_time_unit as svc_response_time_unit';
            }
            if ($this->ci->db->field_exists('resolution_time_value', $servicesTable)) {
                $additionalSelect[] = db_prefix() . 'services.resolution_time_value as svc_resolution_time_value';
            }
            if ($this->ci->db->field_exists('resolution_time_unit', $servicesTable)) {
                $additionalSelect[] = db_prefix() . 'services.resolution_time_unit as svc_resolution_time_unit';
            }
        }

        // No pending reassignments info in list view (approval shown in single ticket view)

        $join = [
            'LEFT JOIN ' . db_prefix() . 'contacts ON ' . db_prefix() . 'contacts.id = ' . db_prefix() . 'tickets.contactid',
            
            'LEFT JOIN ' . db_prefix() . 'departments ON ' . db_prefix() . 'departments.departmentid = ' . db_prefix() . 'tickets.department',
            'LEFT JOIN ' . db_prefix() . 'tickets_status ON ' . db_prefix() . 'tickets_status.ticketstatusid = ' . db_prefix() . 'tickets.status',
            'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'tickets.userid',
            'LEFT JOIN ' . db_prefix() . 'tickets_priorities ON ' . db_prefix() . 'tickets_priorities.priorityid = ' . db_prefix() . 'tickets.priority',
            'LEFT JOIN ' . db_prefix() . 'staff as sassigned ON sassigned.staffid = ' . db_prefix() . 'tickets.assigned',
            'LEFT JOIN ' . db_prefix() . 'staff as screator ON screator.staffid = ' . db_prefix() . 'tickets.admin',
        ];

        if ($needsDivisionJoin) {
            // Join a collapsed view of staff_departments to avoid duplicate rows when creators belong to multiple departments
            $staffDepartmentsTable = db_prefix() . 'staff_departments';
            $creatorDeptJoin       = 'LEFT JOIN (SELECT staffid, MIN(departmentid) as departmentid FROM ' . $staffDepartmentsTable . ' GROUP BY staffid) as creator_sd ON creator_sd.staffid = ' . db_prefix() . 'tickets.admin';
            $join[] = $creatorDeptJoin;
            $join[] = 'LEFT JOIN ' . db_prefix() . 'departments creator_dept ON creator_dept.departmentid = creator_sd.departmentid';
            $join[] = 'LEFT JOIN ' . db_prefix() . 'departments creator_parent_dept ON creator_parent_dept.departmentid = creator_dept.parent_department';
            $join[] = 'LEFT JOIN ' . db_prefix() . 'department_divisions creator_dd ON creator_dd.departmentid = creator_sd.departmentid';
            $join[] = 'LEFT JOIN ' . db_prefix() . 'divisions creator_div ON creator_div.divisionid = creator_dd.divisionid';
        }

        // Join services to access SLA values per ticket service (if table exists)
        if ($this->ci->db->table_exists(db_prefix() . 'services')) {
            $join[] = 'LEFT JOIN ' . db_prefix() . 'services ON ' . db_prefix() . 'services.serviceid = ' . db_prefix() . 'tickets.service';
        }

        $custom_fields = get_table_custom_fields('tickets');
        // Initialize helper arrays/indices used below
        $customFieldsColumns = [];
        // No tags column in this table layout; set to an invalid index to avoid notices
        $tagsColumns = -1;

        foreach ($custom_fields as $key => $field) {
            $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
            array_push($customFieldsColumns, $selectAs);
            array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
            array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'tickets.ticketid = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
        }

        $where = [];

        if ($filtersWhere = $this->getWhereFromRules()) {
            $where[] = $filtersWhere;
        } else {
            // Default: exclude closed tickets (status 5) when no status filter is selected
            // But include tickets with pending reopen requests
            $where[] = 'AND (status != 5 OR EXISTS (SELECT 1 FROM ' . db_prefix() . 'ticket_reopen_requests trr WHERE trr.ticketid = ' . db_prefix() . 'tickets.ticketid AND trr.status = "pending"))';
        }

        if (isset($userid) && $userid != '') {
            array_push($where, 'AND ' . db_prefix() . 'tickets.userid = ' . $this->ci->db->escape_str($userid));
        } elseif (isset($by_email)) {
            array_push($where, 'AND ' . db_prefix() . 'tickets.email = "' . $this->ci->db->escape_str($by_email) . '"');
        }

        if (isset($via_ticket)) {
            array_push($where, 'AND ' . db_prefix() . 'tickets.ticketid != ' . $this->ci->db->escape_str($via_ticket));
        }

        if ($project_id = $this->ci->input->post('project_id')) {
            array_push($where, 'AND project_id = ' . $this->ci->db->escape_str($project_id));
        }

        // If userid is set, the the view is in client profile, should be shown all tickets
        if (! is_admin()) {
            if (get_option('staff_access_only_assigned_departments') == 1) {
                $staff_deparments_ids = $this->ci->departments_model->get_staff_departments(get_staff_user_id(), true);
                $departments_ids      = [];
                if (count($staff_deparments_ids) == 0) {
                    $departments = $this->ci->departments_model->get();

                    foreach ($departments as $department) {
                        array_push($departments_ids, $department['departmentid']);
                    }
                } else {
                    $departments_ids = $staff_deparments_ids;
                }
                if (count($departments_ids) > 0) {
                    $tickets = db_prefix() . 'tickets';
                    $deptWhere = 'department IN (SELECT departmentid FROM ' . db_prefix() . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")';
                    // Always allow tickets assigned to the current user (even if not in department)
                    $assignedWhere = $tickets . '.assigned = ' . get_staff_user_id();
                    // Always allow tickets created by the current user
                    $creatorWhere  = $tickets . '.admin = ' . get_staff_user_id();

                    $clauses = [$deptWhere, $assignedWhere, $creatorWhere];

                    // Allow pending reassignments for current user to be visible even if not in department
                    $reassign = db_prefix() . 'ticket_reassignments';
                    if ($this->ci->db->table_exists($reassign)) {
                        $pendingWhere = 'EXISTS (SELECT 1 FROM ' . $reassign . ' tr WHERE tr.ticketid = ' . $tickets . '.ticketid AND tr.status = "pending" AND tr.to_assigned = ' . get_staff_user_id() . ')';
                        $clauses[] = $pendingWhere;
                    }

                    // Allow tickets where user is a registered handler
                    $handlersTable = db_prefix() . 'ticket_handlers';
                    if ($this->ci->db->table_exists($handlersTable)) {
                        $handlerWhere = 'EXISTS (SELECT 1 FROM ' . $handlersTable . ' th WHERE th.ticketid = ' . $tickets . '.ticketid AND th.staffid = ' . get_staff_user_id() . ')';
                        $clauses[] = $handlerWhere;
                    }

                    array_push($where, 'AND (' . implode(' OR ', $clauses) . ')');
                }
            }
        }

        $sIndexColumn = 'ticketid';
        $sTable       = db_prefix() . 'tickets';

        // Fix for big queries. Some hosting have max_join_limit
        if (count($custom_fields) > 4) {
            @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
        }

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);

        $output  = $result['output'];
        $rResult = $result['rResult'];

        $nowTimestamp = time();

        foreach ($rResult as $aRow) {
            $row = [];
            $createdColumnKey      = db_prefix() . 'tickets.date';
            $ticketCreatedRaw      = $aRow[$createdColumnKey] ?? null;
            $closeTimestampRaw     = $aRow[$closeTimestampAlias] ?? null;
            $approxResolutionRaw   = $aRow['approx_resolution_time'] ?? null;
            $isClosedStatus        = isset($aRow['status']) ? $this->ci->tickets_model->is_close_status((int) $aRow['status']) : false;
            $ticketAgeDisplay      = '-';
            $ticketAgeOrderValue   = -1;
            $startTs               = (!empty($ticketCreatedRaw) && $ticketCreatedRaw !== '0000-00-00 00:00:00') ? strtotime($ticketCreatedRaw) : false;
            $closeTs               = (!empty($closeTimestampRaw) && $closeTimestampRaw !== '0000-00-00 00:00:00') ? strtotime($closeTimestampRaw) : false;
            $lastReplyRaw          = $aRow['lastreply'] ?? null;
            $lastReplyTs           = (!empty($lastReplyRaw) && $lastReplyRaw !== '0000-00-00 00:00:00') ? strtotime($lastReplyRaw) : false;
            $ageEndTs              = $nowTimestamp;

            if ($isClosedStatus) {
                if ($closeTs !== false) {
                    $ageEndTs = $closeTs;
                } elseif ($lastReplyTs !== false) {
                    $ageEndTs = $lastReplyTs;
                }
            }

            if ($startTs !== false) {
                if ($ageEndTs === false) {
                    $ageEndTs = $nowTimestamp;
                }
                if ($ageEndTs < $startTs) {
                    $ageEndTs = $startTs;
                }
                $ageSeconds = $ageEndTs - $startTs;
                if ($ageSeconds < 0) {
                    $ageSeconds = 0;
                }
                $ticketAgeOrderValue = (int) floor($ageSeconds / 86400);
                $ticketAgeDisplay    = (string) $ticketAgeOrderValue;

                if ($isClosedStatus && $ticketAgeOrderValue >= 0) {
                    $ticketAgeDisplay .= ' (Closed)';
                }
            }

            $approxResolutionTs = (!empty($approxResolutionRaw) && $approxResolutionRaw !== '0000-00-00 00:00:00') ? strtotime($approxResolutionRaw) : false;
            $isOverdue          = $approxResolutionTs !== false && $approxResolutionTs < $nowTimestamp && ! $isClosedStatus;
            $remainingDays      = null;
            $overdueDays        = null;
            if ($approxResolutionTs !== false) {
                if ($isOverdue) {
                    $overdueDays = max(1, (int) ceil(($nowTimestamp - $approxResolutionTs) / 86400));
                } elseif (! $isClosedStatus) {
                    $remainingDays = max(0, (int) ceil(($approxResolutionTs - $nowTimestamp) / 86400));
                }
            }

            for ($i = 0; $i < count($aColumns); $i++) {
                if (strpos($aColumns[$i], 'as') !== false && ! isset($aRow[$aColumns[$i]])) {
                    $_data = $aRow[strafter($aColumns[$i], 'as ')];
                } else {
                    $_data = $aRow[$aColumns[$i]];
                }

                if ($aColumns[$i] == '1') {
                    $_data = '<div class="checkbox"><input type="checkbox" value="' . $aRow['ticketid'] . '" data-name="' . e($aRow['subject']) . '" data-status="' . $aRow['status'] . '"><label></label></div>';
                } elseif ($aColumns[$i] == 'lastreply') {
                    if ($aRow[$aColumns[$i]] == null) {
                        $_data = _l('ticket_no_reply_yet');
                    } else {
                        $_data = e(_dt($aRow[$aColumns[$i]]));
                    }
                } elseif ($aColumns[$i] == 'subject' || $aColumns[$i] == 'ticketid') {
                    // Ticket is assigned
                    if ($aRow['assigned'] != 0) {
                        if ($aColumns[$i] != 'ticketid') {
                            $_data .= '<a href="' . admin_url('profile/' . $aRow['assigned']) . '" data-toggle="tooltip" title="' . e(get_staff_full_name($aRow['assigned'])) . '" class="pull-left mright5">' . staff_profile_image($aRow['assigned'], [
                                'staff-profile-image-xs',
                            ]) . '</a>';
                        } else {
                            $_data = e($_data);
                        }
                    } else {
                        $_data = e($_data);
                    }

    $url   = admin_url('tickets/ticket/' . ($aRow['ticket_number'] ?: $aRow['ticketid']));
    $tooltip = htmlspecialchars_decode($aRow['subject']);
    if (!empty($aRow['last_reply_preview'])) {
        $date = !empty($aRow['lastreply']) ? date('M j, Y g:i A', strtotime($aRow['lastreply'])) : '';
        $last_reply_clean = strip_tags(html_entity_decode($aRow['last_reply_preview']));
        $tooltip .= "\nLast Reply by " . (!empty($aRow['last_replier']) ? $aRow['last_replier'] : 'Unknown') . " on " . $date . ":\n" . $last_reply_clean;
    }
    $_data = '<a href="' . $url . '" class="valign tw-truncate tw-max-w-xs tw-block tw-min-w-0 tw-font-medium" title="' . htmlspecialchars($tooltip) . '">' . $_data . '</a>';
                    if ($aColumns[$i] == 'subject') {
                        $_data .= '<div class="row-options">';
                        $_data .= '<a href="' . $url . '">' . _l('view') . '</a>';
                        $_data .= ' | <a href="' . $url . '?tab=settings">' . _l('edit') . '</a>';
                        // Reassignment approval UI is shown only on the single ticket page
                        $_data .= '</div>';
                    }
                } elseif ($i == $tagsColumns) {
                    $_data = render_tags($_data);
                } elseif ($i == $contactColumn) {
                    if ($aRow['userid'] != 0) {
                        $_data = '<a href="' . admin_url('clients/client/' . $aRow['userid'] . '?group=contacts') . '">' . e($aRow['contact_full_name']);
                        if (! empty($aRow['company'])) {
                            $_data .= ' (' . e($aRow['company']) . ')';
                        }
                        $_data .= '</a>';
                    } else {
                        $_data = e($aRow['ticket_opened_by_name']);
                    }
                } elseif (strpos($aColumns[$i], 'as creator_division_name') !== false) {
                    $divisionName   = trim((string) ($aRow['creator_division_name'] ?? ''));
                    $departmentName = trim((string) ($aRow['department_name'] ?? ''));
                    $parts          = array_filter([$divisionName, $departmentName], function ($value) {
                        return $value !== '';
                    });
                    $_data = !empty($parts) ? e(implode(' - ', $parts)) : '-';
                } elseif (strpos($aColumns[$i], $ageAlias) !== false) {
                    if ($ticketAgeOrderValue >= 0) {
                        $_data = '<span data-order="' . (int) $ticketAgeOrderValue . '">' . e($ticketAgeDisplay) . '</span>';
                    } else {
                        $_data = '-';
                    }
                } elseif ($aColumns[$i] == 'status') {
                    // Check for pending requests that should override the status display
                    $ticketId = $aRow['ticketid'];
                    $pendingCloseRequest = $this->ci->tickets_model->get_pending_close_request($ticketId);
                    $pendingReopenRequest = $this->ci->tickets_model->get_pending_reopen_request($ticketId);
                    $pendingReassignRequest = $this->ci->tickets_model->get_pending_reassign($ticketId);

                    $statusText = '';
                    $statusClass = 'ticket-status-' . $aRow['status'];
                    $statusStyle = 'border:1px solid ' . adjust_hex_brightness($aRow['statuscolor'], 0.4) . '; color:' . $aRow['statuscolor'] . ';background: ' . adjust_hex_brightness($aRow['statuscolor'], 0.04) . ';';

                    if ($pendingCloseRequest) {
                        $statusText = _l('ticket_status_waiting_for_close');
                        $statusClass = 'ticket-status-pending-close';
                        $statusStyle = 'border:1px solid #ffc107; color:#856404; background:#fff3cd;';
                    } elseif ($pendingReopenRequest) {
                        $statusText = _l('ticket_status_waiting_for_reopen');
                        $statusClass = 'ticket-status-pending-reopen';
                        $statusStyle = 'border:1px solid #17a2b8; color:#0c5460; background:#d1ecf1;';
                    } elseif ($pendingReassignRequest) {
                        $statusText = _l('ticket_status_waiting_for_reassign');
                        $statusClass = 'ticket-status-pending-reassign';
                        $statusStyle = 'border:1px solid #6f42c1; color:#4c2c74; background:#e2d6f7;';
                    } else {
                        $statusText = ticket_status_translate($aRow['status']);
                    }

                    $_data = '<span class="label ' . $statusClass . '" style="' . $statusStyle . '">' . e($statusText) . '</span>';
                } elseif ($aColumns[$i] == 'ticket_number') {
                    $_data = '<a href="' . admin_url('tickets/ticket/' . ($aRow['ticket_number'] ?: $aRow['ticketid'])) . '" class="tw-text-neutral-600 hover:tw-text-neutral-800">' . e($aRow['ticket_number']) . '</a>';
                } elseif ($aColumns[$i] == db_prefix() . 'tickets.date') {
                    if (!empty($_data) && $_data != '0000-00-00 00:00:00') {
                        $_data = date('d/m/Y', strtotime($_data));
                    } else {
                        $_data = '';
                    }

                } elseif (strpos($aColumns[$i], 'assigned_name') !== false) {
                    $_data = ($aRow['assigned'] != 0 && !empty($_data)) ? e($_data) : '-';
                } elseif (strpos($aColumns[$i], 'as created_by') !== false) {
                    // Render creator name only
                    $_data = ($aRow['admin_id'] != 0 && !empty($aRow['created_by'])) ? e($aRow['created_by']) : _l('ticket_created_by_client');
                } elseif ($aColumns[$i] == '(SELECT GROUP_CONCAT(CONCAT(s.firstname, " ", s.lastname) SEPARATOR ", ") FROM ' . db_prefix() . 'ticket_handlers th JOIN ' . db_prefix() . 'staff s ON s.staffid = th.staffid WHERE th.ticketid = ' . db_prefix() . 'tickets.ticketid) as ticket_handlers') {
                    $_data = e($aRow['ticket_handlers']) ?: '-';
                } elseif ($aColumns[$i] == 'approx_resolution_time') {
                    if (!empty($_data) && $_data != '0000-00-00 00:00:00') {
                        $formattedDate = date('d/m/Y', strtotime($_data));
                        $badgeHtml     = '';

                        if ($isOverdue && $overdueDays !== null) {
                            $overdueLabel = $overdueDays === 1 ? '1 day overdue' : ($overdueDays . ' days overdue');
                            $badgeHtml    = ' <span class="label label-danger ticket-overdue-indicator">' . e($overdueLabel) . '</span>';
                        } elseif (! $isClosedStatus && $remainingDays !== null) {
                            if ($remainingDays === 0) {
                                $remainingLabel = 'Due today';
                            } else {
                                $remainingLabel = $remainingDays === 1 ? '1 day left' : ($remainingDays . ' days left');
                            }
                            $badgeHtml = ' <span class="label label-info ticket-resolution-countdown">' . e($remainingLabel) . '</span>';
                        }

                        $_data = $formattedDate . $badgeHtml;
                    } else {
                        $_data = '-';
                    }
                }
                $row[] = $_data;

                if ($aRow['adminread'] == 0) {
                    $row['DT_RowClass'] = 'text-danger';
                }
            }

            if (isset($row['DT_RowClass'])) {
                if ($isOverdue) {
                    $row['DT_RowClass'] .= ' ticket-row-overdue';
                }
            } elseif ($isOverdue) {
                $row['DT_RowClass'] = 'ticket-row-overdue';
            }

            if ($ticketAgeOrderValue >= 0) {
                if (!isset($row['DT_RowAttr'])) {
                    $row['DT_RowAttr'] = [];
                }
                $row['DT_RowAttr']['data-ticket-age-days'] = (int) $ticketAgeOrderValue;
            }

            if ($isOverdue) {
                if (!isset($row['DT_RowAttr'])) {
                    $row['DT_RowAttr'] = [];
                }
                $row['DT_RowAttr']['data-ticket-overdue'] = '1';
            }

            if (isset($row['DT_RowClass'])) {
                $row['DT_RowClass'] .= ' has-row-options';
            } else {
                $row['DT_RowClass'] = 'has-row-options';
            }

            $row                = hooks()->apply_filters('admin_tickets_table_row_data', $row, $aRow);
            $output['aaData'][] = $row;
        }

        return $output;
    })->setRules($rules);
