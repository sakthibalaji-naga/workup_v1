<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_302 extends CI_Migration
{
    public function up()
    {
        // Add welcome_popup_shown column to staff table
        // 0 means show popup, 1 means popup has been shown
        $this->dbforge->add_column(db_prefix() . 'staff', [
            'welcome_popup_shown' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => FALSE,
                'default' => 0,
                'after' => 'last_password_change',
            ],
        ]);
    }

    public function down()
    {
        // Remove welcome_popup_shown column from staff table
        $this->dbforge->drop_column(db_prefix() . 'staff', 'welcome_popup_shown');
    }
}
