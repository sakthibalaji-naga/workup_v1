<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_337 extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();
        // Add divisionid to services if not exists
        if (!$CI->db->field_exists('divisionid', db_prefix() . 'services')) {
            $CI->db->query('ALTER TABLE `'.db_prefix().'services` ADD `divisionid` INT(11) NULL DEFAULT NULL AFTER `name`;');
        }
        // Ensure departmentid exists (migration 332 may have added); if not, add
        if (!$CI->db->field_exists('departmentid', db_prefix() . 'services')) {
            $CI->db->query('ALTER TABLE `'.db_prefix().'services` ADD `departmentid` INT(11) NULL DEFAULT NULL AFTER `divisionid`;');
        }
        // Add sub_department
        if (!$CI->db->field_exists('sub_department', db_prefix() . 'services')) {
            $CI->db->query('ALTER TABLE `'.db_prefix().'services` ADD `sub_department` INT(11) NULL DEFAULT NULL AFTER `departmentid`;');
        }
    }
}

