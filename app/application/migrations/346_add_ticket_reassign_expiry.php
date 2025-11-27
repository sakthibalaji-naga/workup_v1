<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_ticket_reassign_expiry extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'ticket_reassignments';

        if ($CI->db->table_exists($table) && ! $CI->db->field_exists('expires_at', $table)) {
            $CI->db->query('ALTER TABLE `'.$table.'` ADD `expires_at` DATETIME NULL AFTER `created_at`;');
            $CI->db->query('ALTER TABLE `'.$table.'` ADD INDEX `expires_at` (`expires_at`);');
        }
    }

    public function down()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'ticket_reassignments';

        if ($CI->db->table_exists($table) && $CI->db->field_exists('expires_at', $table)) {
            $CI->db->query('ALTER TABLE `'.$table.'` DROP COLUMN `expires_at`;');
        }
    }
}
