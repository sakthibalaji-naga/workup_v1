<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_application_id_to_tickets_table extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('tickets', [
            'application_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->dbforge->drop_column('tickets', 'application_id');
    }
}
