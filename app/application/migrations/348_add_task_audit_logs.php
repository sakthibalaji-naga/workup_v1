<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_task_audit_logs extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'task_audit_logs';

        if ($CI->db->table_exists($table)) {
            return;
        }

        $tasksTable = db_prefix() . 'tasks';
        $staffTable = db_prefix() . 'staff';

        $CI->db->query(
            'CREATE TABLE `' . $table . '` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `task_id` INT(11) NOT NULL,
                `staff_id` INT(11) NOT NULL,
                `action` VARCHAR(100) NOT NULL,
                `field_name` VARCHAR(100) NULL,
                `old_value` TEXT NULL,
                `new_value` TEXT NULL,
                `description` TEXT NULL,
                `ip_address` VARCHAR(45) NULL,
                `user_agent` TEXT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `task_id` (`task_id`),
                KEY `staff_id` (`staff_id`),
                KEY `action` (`action`),
                KEY `created_at` (`created_at`),
                CONSTRAINT `fk_task_audit_logs_task` FOREIGN KEY (`task_id`) REFERENCES `' . $tasksTable . '` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_task_audit_logs_staff` FOREIGN KEY (`staff_id`) REFERENCES `' . $staffTable . '` (`staffid`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
    }

    public function down()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'task_audit_logs';

        if ($CI->db->table_exists($table)) {
            $CI->db->query('DROP TABLE `' . $table . '`;');
        }
    }
}
