<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_task_approval_remark_history extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'task_approval_remark_history';

        if ($CI->db->table_exists($table)) {
            return;
        }

        $tasksTable     = db_prefix() . 'tasks';
        $approvalsTable = db_prefix() . 'task_approvals';
        $staffTable     = db_prefix() . 'staff';

        $CI->db->query(
            'CREATE TABLE `' . $table . '` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `task_id` INT(11) NOT NULL,
                `task_approval_id` INT(11) NOT NULL,
                `staff_id` INT(11) NOT NULL,
                `action_type` VARCHAR(30) NOT NULL DEFAULT \'remark\',
                `comments` TEXT NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `task_id` (`task_id`),
                KEY `task_approval_id` (`task_approval_id`),
                KEY `staff_id` (`staff_id`),
                CONSTRAINT `fk_task_approval_history_task` FOREIGN KEY (`task_id`) REFERENCES `' . $tasksTable . '` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_task_approval_history_approval` FOREIGN KEY (`task_approval_id`) REFERENCES `' . $approvalsTable . '` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_task_approval_history_staff` FOREIGN KEY (`staff_id`) REFERENCES `' . $staffTable . '` (`staffid`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
    }

    public function down()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'task_approval_remark_history';

        if ($CI->db->table_exists($table)) {
            $CI->db->query('DROP TABLE `' . $table . '`;');
        }
    }
}
