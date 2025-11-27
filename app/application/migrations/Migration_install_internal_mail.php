<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Internal Mail System - Database Migration
 * Run this file once to create all required database tables
 */

class Migration_install_internal_mail extends App_module_migration
{
    public function up()
    {
        $CI = &amp;get_instance();
        
        // Read SQL file
        $sql = file_get_contents(APPPATH . 'database/create_internal_mail_tables.sql');
        
        // Split by semicolon to execute each statement separately
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            if (trim($statement) != '') {
                try {
                    $CI->db->query($statement);
                } catch (Exception $e) {
                    log_message('error', 'Internal Mail Migration Error: ' . $e->getMessage());
                }
            }
        }
        
        // Create upload directory
        $upload_dir = FCPATH . 'uploads/internal_mail';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        log_message('info', 'Internal Mail System tables created successfully');
    }
    
    public function down()
    {
        $CI = &amp;get_instance();
        
        // Drop tables in reverse order (due to foreign keys)
        $tables = [
            'tblinternal_mail_tracking',
            'tblinternal_mail_attachments',
            'tblinternal_mail_recipients',
            'tblinternal_mail',
        ];
        
        foreach ($tables as $table) {
            if ($CI->db->table_exists($table)) {
                $CI->db->query("DROP TABLE IF EXISTS `$table`");
            }
        }
        
        log_message('info', 'Internal Mail System tables dropped');
    }
}
