<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_ticket_handlers_table extends CI_Migration
{
    public function up()
    {
        $table = db_prefix() . 'ticket_handlers';

        if (! $this->db->table_exists($table)) {
            $this->dbforge->add_field([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'ticketid' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => false,
                ],
                'staffid' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => false,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->dbforge->add_key('id', true);
            $this->dbforge->add_key(['ticketid', 'staffid']);
            $this->dbforge->create_table($table, true);

            // Basic indexes to speed up lookups
            if (! $this->db->field_exists('ticketid', $table)) {
                // Safety guard; dbforge already added this field
            }
        }
    }

    public function down()
    {
        $table = db_prefix() . 'ticket_handlers';

        if ($this->db->table_exists($table)) {
            $this->dbforge->drop_table($table, true);
        }
    }
}
