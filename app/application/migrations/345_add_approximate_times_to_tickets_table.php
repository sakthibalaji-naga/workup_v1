<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_approximate_times_to_tickets_table extends CI_Migration
{
    public function up()
    {
        $fields = [
            'approx_response_time' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'approx_resolution_time' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
        ];
        $this->dbforge->add_column('tickets', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('tickets', 'approx_response_time');
        $this->dbforge->drop_column('tickets', 'approx_resolution_time');
    }
}
