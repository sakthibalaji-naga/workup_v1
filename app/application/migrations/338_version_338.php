<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_338 extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'services';

        if (!$CI->db->field_exists('response_time_value', $table)) {
            $CI->db->query('ALTER TABLE `'.$table.'` ADD `response_time_value` INT(11) NULL DEFAULT NULL AFTER `sub_department`;');
        }
        if (!$CI->db->field_exists('response_time_unit', $table)) {
            $CI->db->query('ALTER TABLE `'.$table.'` ADD `response_time_unit` VARCHAR(20) NULL DEFAULT NULL AFTER `response_time_value`;');
        }
        if (!$CI->db->field_exists('resolution_time_value', $table)) {
            $CI->db->query('ALTER TABLE `'.$table.'` ADD `resolution_time_value` INT(11) NULL DEFAULT NULL AFTER `response_time_unit`;');
        }
        if (!$CI->db->field_exists('resolution_time_unit', $table)) {
            $CI->db->query('ALTER TABLE `'.$table.'` ADD `resolution_time_unit` VARCHAR(20) NULL DEFAULT NULL AFTER `resolution_time_value`;');
        }
    }
}

