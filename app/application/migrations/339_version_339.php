<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_339 extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();
        // Add divisionid to tickets if not exists
        if (!$CI->db->field_exists('divisionid', db_prefix() . 'tickets')) {
            $CI->db->query('ALTER TABLE `'.db_prefix().'tickets` ADD `divisionid` INT(11) NULL DEFAULT NULL AFTER `department`;');
        }
    }
}

