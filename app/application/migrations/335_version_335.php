<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_335 extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'department_divisions';
        if (!$CI->db->table_exists($table)) {
            $CI->db->query('CREATE TABLE `'.$table.'` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `departmentid` INT(11) NOT NULL,
                `divisionid` INT(11) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `departmentid_idx` (`departmentid`),
                KEY `divisionid_idx` (`divisionid`),
                UNIQUE KEY `uniq_dep_div` (`departmentid`,`divisionid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }
    }
}

