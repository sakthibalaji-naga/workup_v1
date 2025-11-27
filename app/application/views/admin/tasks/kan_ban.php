<?php defined('BASEPATH') or exit('No direct script access allowed');

$where = [];
if ($this->input->get('project_id')) {
    $where['rel_id']   = $this->input->get('project_id');
    $where['rel_type'] = 'project';
}

$todayDate       = date('Y-m-d');
$specialTaskPool = [
    'today'   => [],
    'overdue' => [],
];
$kanbanColumns = [];

foreach ($task_statuses as $status) {
    $kanBan = new app\services\tasks\TasksKanban($status['id']);
    $kanBan->search($this->input->get('search'))
        ->sortBy($this->input->get('sort_by'), $this->input->get('sort'))
        ->forProject($this->input->get('project_id'));

    if ($this->input->get('refresh')) {
        $kanBan->refresh($this->input->get('refresh')[$status['id']] ?? null);
    }

    $tasks       = $kanBan->get();
    $total_tasks = count($tasks);
    $total_pages = $kanBan->totalPages();

    foreach ($tasks as $task) {
        $isComplete = (int) $task['status'] === Tasks_model::STATUS_COMPLETE;
        if ($isComplete) {
            continue;
        }

        $dueDate   = ! empty($task['duedate']) ? date('Y-m-d', strtotime($task['duedate'])) : null;
        $startDate = ! empty($task['startdate']) ? date('Y-m-d', strtotime($task['startdate'])) : null;
        $bucket    = null;

        if ($dueDate) {
            if ($dueDate === $todayDate) {
                $bucket = 'today';
            } elseif ($dueDate < $todayDate) {
                $bucket = 'overdue';
            }
        } elseif ($startDate) {
            if ($startDate === $todayDate) {
                $bucket = 'today';
            } elseif ($startDate < $todayDate) {
                $bucket = 'overdue';
            }
        }

        if ($bucket) {
            $specialTaskPool[$bucket][$task['id']] = $task;
        }
    }

    $kanbanColumns[] = [
        'status'       => $status,
        'tasks'        => $tasks,
        'total_tasks'  => $total_tasks,
        'total_pages'  => $total_pages,
        'count_all'    => $kanBan->countAll(),
        'current_page' => $kanBan->getPage(),
    ];
}

$specialColumns = [
    [
        'status' => 'today',
        'tasks'  => array_values($specialTaskPool['today']),
    ],
    [
        'status' => 'overdue',
        'tasks'  => array_values($specialTaskPool['overdue']),
    ],
];

foreach ($specialColumns as $specialColumn) {
    $isToday    = $specialColumn['status'] === 'today';
    $columnTitle = $isToday ? _l('my_tasks_today_task') : _l('my_tasks_overdue_task');
    $count       = count($specialColumn['tasks']);
    $countLabel  = $count === 1 ? _l('my_tasks_status_single_task', $count) : _l('my_tasks_status_tasks', $count);
    $color       = $isToday ? '#f59e0b' : '#dc2626'; ?>
<ul class="kan-ban-col tasks-kanban special-kanban"
    data-col-status-id="<?= e($specialColumn['status']); ?>"
    data-total-pages="1"
    data-total="<?= e($count); ?>">
    <li class="kan-ban-col-wrapper">
        <div class="border-right panel_s">
            <div class="panel-heading tw-font-medium"
                style="background:<?= e($color); ?>;border-color:<?= e($color); ?>;color:#fff;"
                data-status-id="<?= e($specialColumn['status']); ?>">
                <span class="tw-flex tw-items-center">
                    <i class="fa fa-<?= $isToday ? 'calendar-day' : 'exclamation-triangle'; ?> tw-mr-2"></i>
                    <?= e($columnTitle); ?> -
                    <span class="tw-text-sm tw-ml-1"><?= e($countLabel); ?></span>
                </span>
            </div>
            <div class="kan-ban-content-wrapper">
                <div class="kan-ban-content">
                    <ul class="kanban-special-list relative"
                        data-task-status-id="<?= e($specialColumn['status']); ?>">
                        <?php if (empty($specialColumn['tasks'])) { ?>
                        <li class="text-center not-sortable mtop30 kanban-empty">
                            <h4>
                                <i class="fa-solid fa-circle-notch" aria-hidden="true"></i><br /><br />
                                <?= $isToday ? _l('my_tasks_empty_today') : _l('my_tasks_empty_overdue'); ?>
                            </h4>
                        </li>
                        <?php } else { ?>
                        <?php foreach ($specialColumn['tasks'] as $task) { ?>
                        <?php $this->load->view('admin/tasks/_kan_ban_card', ['task' => $task, 'status' => $task['status']]); ?>
                        <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
            </div>
    </li>
</ul>
<?php }

foreach ($kanbanColumns as $column) {
    $status      = $column['status'];
    $tasks       = $column['tasks'];
    $total_tasks = $column['total_tasks'];
    $total_pages = $column['total_pages']; ?>
<ul class="kan-ban-col tasks-kanban"
    data-col-status-id="<?= e($status['id']); ?>"
    data-total-pages="<?= e($total_pages); ?>"
    data-total="<?= e($total_tasks); ?>">
    <li class="kan-ban-col-wrapper">
        <div class="border-right panel_s">
            <div class="panel-heading tw-font-medium"
                style="background:<?= e($status['color']); ?>;border-color:<?= e($status['color']); ?>;color:#fff; ?>"
                data-status-id="<?= e($status['id']); ?>">

                <?= format_task_status($status['id'], false, true); ?>
                -
                <span class="tw-text-sm">
                    <?= $column['count_all'] . ' ' . _l('tasks') ?>
                </span>

            </div>
            <div class="kan-ban-content-wrapper">
                <div class="kan-ban-content">
                    <ul class="status tasks-status sortable relative"
                        data-task-status-id="<?= e($status['id']); ?>">
                        <?php
              foreach ($tasks as $task) {
                  if ($task['status'] == $status['id']) {
                      $this->load->view('admin/tasks/_kan_ban_card', ['task' => $task, 'status' => $status['id']]);
                  }
              } ?>
                        <?php if ($total_tasks > 0) { ?>
                        <li class="text-center not-sortable kanban-load-more"
                            data-load-status="<?= e($status['id']); ?>">
                            <a href="#"
                                class="btn btn-default btn-block<?php if ($total_pages <= 1 || $column['current_page'] == $total_pages) {
                                    echo ' disabled';
                                } ?>"
                                data-page="<?= $column['current_page']; ?>"
                                onclick="kanban_load_more(<?= e($status['id']); ?>,this,'tasks/tasks_kanban_load_more',265,360); return false;"
                                ;><?= _l('load_more'); ?></a>
                        </li>
                        <?php } ?>
                        <li class="text-center not-sortable mtop30 kanban-empty<?php if ($total_tasks > 0) {
                            echo ' hide';
                        } ?>">
                            <h4>
                                <i class="fa-solid fa-circle-notch" aria-hidden="true"></i><br /><br />
                                <?= _l('no_tasks_found'); ?>
                            </h4>
                        </li>
                    </ul>
                </div>
            </div>
    </li>
</ul>
<?php } ?>
