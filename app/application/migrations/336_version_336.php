<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_336 extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();
        if (!$CI->db->field_exists('divisionid', db_prefix() . 'staff')) {
            $CI->db->query('ALTER TABLE `'. db_prefix() .'staff` ADD `divisionid` INT(11) NULL DEFAULT NULL AFTER `role`;');
        }
    }
}

