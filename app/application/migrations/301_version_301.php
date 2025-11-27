<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_301 extends CI_Migration
{
    public function up()
    {
        // Create ticket_logs table if it doesn't exist
        if (!$this->db->table_exists(db_prefix() . 'ticket_logs')) {
            $this->dbforge->add_field([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ],
                'ticketid' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'null' => FALSE
                ],
                'timestamp' => [
                    'type' => 'DATETIME',
                    'null' => FALSE
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'null' => FALSE
                ],
                'user_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => FALSE,
                    'default' => 'staff'
                ],
                'log_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => FALSE
                ],
                'log_details' => [
                    'type' => 'TEXT',
                    'null' => TRUE
                ]
            ]);

            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('ticketid');
            $this->dbforge->add_key('user_id');
            $this->dbforge->add_key('log_type');

            $this->dbforge->create_table(db_prefix() . 'ticket_logs');

            // Add index for better performance
            $this->db->query('ALTER TABLE `' . db_prefix() . 'ticket_logs` ADD INDEX `timestamp` (`timestamp`)');
        }
    }

    public function down()
    {
        // Drop ticket_logs table
        $this->dbforge->drop_table(db_prefix() . 'ticket_logs');
    }
}
