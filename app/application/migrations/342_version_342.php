<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_add_ticket_logs_table extends CI_Migration
{
    public function up()
    {
        \$this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'ticketid' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'timestamp' => [
                'type' => 'DATETIME',
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'log_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'log_details' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        \$this->dbforge->add_key('id', true);
        \$this->dbforge->create_table('ticket_logs');
    }

    public function down()
    {
        \$this->dbforge->drop_table('ticket_logs');
    }
}