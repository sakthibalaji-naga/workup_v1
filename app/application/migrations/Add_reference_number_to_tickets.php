<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_reference_number_to_tickets extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        
        if (!$CI->db->field_exists('reference_number', db_prefix() . 'tickets')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'tickets` ADD `reference_number` VARCHAR(100) NULL AFTER `subject`');
        }
    }

    public function down()
    {
        $CI = &get_instance();
        
        if ($CI->db->field_exists('reference_number', db_prefix() . 'tickets')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'tickets` DROP COLUMN `reference_number`');
        }
    }
}

