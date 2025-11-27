<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_applications_table extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'incharge_department' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'service_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key('id', true);
    $this->dbforge->create_table(db_prefix() . 'applications');
    }

    public function down()
    {
    $this->dbforge->drop_table(db_prefix() . 'applications');
    }
}