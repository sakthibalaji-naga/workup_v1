<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_341 extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'ticket_reassignments';
        if ($CI->db->table_exists($table) && ! $CI->db->field_exists('decision_remarks', $table)) {
            $CI->db->query('ALTER TABLE `'.$table.'` ADD `decision_remarks` TEXT NULL AFTER `decision_at`;');
        }
    }
}

