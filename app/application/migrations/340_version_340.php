<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_340 extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();

        if (! $CI->db->table_exists(db_prefix() . 'ticket_reassignments')) {
            $CI->db->query('CREATE TABLE `'.db_prefix().'ticket_reassignments` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `ticketid` INT(11) NOT NULL,
                `divisionid` INT(11) NULL,
                `department` INT(11) NULL,
                `sub_department` INT(11) NULL,
                `service` INT(11) NULL,
                `from_assigned` INT(11) NULL,
                `to_assigned` INT(11) NOT NULL,
                `status` VARCHAR(20) NOT NULL DEFAULT "pending",
                `created_by` INT(11) NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `decision_by` INT(11) NULL,
                `decision_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `ticketid` (`ticketid`),
                KEY `to_assigned` (`to_assigned`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }
    }
}

