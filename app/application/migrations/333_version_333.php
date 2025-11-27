<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_333 extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();

        // Add sub_department to tickets if not exists
        if (!$CI->db->field_exists('sub_department', db_prefix() . 'tickets')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'tickets` ADD `sub_department` INT(11) NULL DEFAULT NULL AFTER `department`;');
        }
    }
}

