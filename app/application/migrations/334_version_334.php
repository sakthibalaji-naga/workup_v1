<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_334 extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . 'divisions')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'divisions` (
                `divisionid` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(191) NOT NULL,
                PRIMARY KEY (`divisionid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }
    }
}

