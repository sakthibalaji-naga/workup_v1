<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_ticket_logs_table extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field([
            'id INT(11) PRIMARY KEY AUTO_INCREMENT',
            'ticketid INT(11) NOT NULL',
            'timestamp DATETIME NOT NULL',
            'log_type VARCHAR(255) NOT NULL',
            'log_details TEXT',
            'user_id INT(11) NOT NULL',
            'user_type VARCHAR(50) NOT NULL',
        ]);
        $this->dbforge->create_table(db_prefix() . 'ticket_logs', true);
    }

    public function down()
    {
        $this->dbforge->drop_table(db_prefix() . 'ticket_logs', true);
    }
}
