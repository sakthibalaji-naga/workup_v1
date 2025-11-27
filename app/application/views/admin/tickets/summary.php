<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div>
    <?php
    $statuses = $this->tickets_model->get_ticket_status();
?>
    <div class="_filters _hidden_inputs hidden tickets_filters">
        <?php
  foreach ($statuses as $status) {
      $val = '';
      if ($chosen_ticket_status != '') {
          if ($chosen_ticket_status == $status['ticketstatusid']) {
              $val = $chosen_ticket_status;
          }
      } else {
          if (in_array($status['ticketstatusid'], $default_tickets_list_statuses)) {
              $val = 1;
          }
      }
      echo form_hidden('ticket_status_' . $status['ticketstatusid'], $val);
  } ?>
    </div>
    <div
        class="tw-mb-3 tw-flex  tw-flex-col tw-flex-wrap tw-gap-y-2 tw-order-1 sm:tw-flex-row sm:tw-gap-x-2 sm:-tw-order-none sm:tw-mr-2 md:tw-mb-0">
        <?php
    $where = [];
if (! is_admin()) {
    if (get_option('staff_access_only_assigned_departments') == 1) {
        $staff_deparments_ids = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
        $departments_ids      = [];
        if (count($staff_deparments_ids) == 0) {
            $departments = $this->departments_model->get();
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

            $where[] = 'AND (' . $deptWhere . ' OR ' . $assignedWhere . ' OR ' . $creatorWhere . ')';
        }
    }
}

foreach ($statuses as $status) {
    $_where = 'status=' . $status['ticketstatusid'];
    if (!empty($where)) {
        $_where .= ' ' . implode(' ', $where);
    }
    if (isset($project_id)) {
        $_where .= ' AND project_id=' . $project_id;
    }
    $_where .= ' AND merged_ticket_id IS NULL'; ?>
        <a href="#"
            data-cview="ticket_status_<?= e($status['ticketstatusid']); ?>"
            class="tw-bg-transparent tw-border tw-border-solid tw-border-neutral-300 tw-shadow-sm tw-py-1 tw-px-2 tw-rounded-lg tw-text-sm hover:tw-bg-neutral-200/60 tw-text-neutral-600 hover:tw-text-neutral-600 focus:tw-text-neutral-600"
            <?= ($hrefAttrs ?? null instanceof Closure) ? $hrefAttrs($status) : ''; ?>>
            <span class="tw-font-semibold tw-mr-1 rtl:tw-ml-1">
                <?= total_rows(db_prefix() . 'tickets', $_where); ?>
            </span>
            <span
                style="color:<?= e($status['statuscolor']); ?>">
                <?= e(ticket_status_translate($status['ticketstatusid'])); ?>
            </span>
        </a>
        <?php
}
        // Add Ticket Handler quick filter button
        $_where = 'EXISTS (SELECT 1 FROM ' . db_prefix() . 'ticket_handlers th WHERE th.ticketid = ' . db_prefix() . 'tickets.ticketid)';
        if (!empty($where)) {
            $_where .= ' ' . implode(' ', $where);
        }
        if (isset($project_id)) {
            $_where .= ' AND project_id=' . $project_id;
        }
        $_where .= ' AND merged_ticket_id IS NULL';
        ?>
        <a href="#"
            data-cview="ticket_has_handlers"
            class="tw-bg-transparent tw-border tw-border-solid tw-border-neutral-300 tw-shadow-sm tw-py-1 tw-px-2 tw-rounded-lg tw-text-sm hover:tw-bg-neutral-200/60 tw-text-neutral-600 hover:tw-text-neutral-600 focus:tw-text-neutral-600"
            <?= (isset($handlersHrefAttr) && $handlersHrefAttr instanceof Closure) ? $handlersHrefAttr() : ''; ?>>
            <span class="tw-font-semibold tw-mr-1 rtl:tw-ml-1">
                <?= total_rows(db_prefix() . 'tickets', $_where); ?>
            </span>
            <span>
                <?= _l('ticket_handler'); ?>
            </span>
        </a>

        <?php
        // Extra filter button: Tickets owned by current user (assigned to me)
        $_where = db_prefix() . 'tickets.assigned = ' . get_staff_user_id();
        if (!empty($where)) { $_where .= ' ' . implode(' ', $where); }
        if (isset($project_id)) { $_where .= ' AND project_id=' . $project_id; }
        $_where .= ' AND merged_ticket_id IS NULL';
        ?>
        <a href="#"
            data-cview="ticket_has_owner"
            class="tw-bg-transparent tw-border tw-border-solid tw-border-neutral-300 tw-shadow-sm tw-py-1 tw-px-2 tw-rounded-lg tw-text-sm hover:tw-bg-neutral-200/60 tw-text-neutral-600 hover:tw-text-neutral-600 focus:tw-text-neutral-600"
            <?= (isset($ownerHrefAttr) && $ownerHrefAttr instanceof Closure) ? $ownerHrefAttr() : ''; ?>>
            <span class="tw-font-semibold tw-mr-1 rtl:tw-ml-1">
                <?= total_rows(db_prefix() . 'tickets', $_where); ?>
            </span>
            <span>
                <?= _l('ticket_owner'); ?>
            </span>
        </a>
    </div>
</div>
