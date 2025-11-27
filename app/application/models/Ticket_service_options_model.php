<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ticket_service_options_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_options($type)
    {
        $this->db->where('type', $type);
        return $this->db->get('tblticketserviceoptions')->result_array();
    }

    public function add_option($data)
    {
        $this->db->insert('tblticketserviceoptions', $data);
        return $this->db->insert_id();
    }

    public function update_option($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tblticketserviceoptions', $data);
    }

    public function delete_option($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('tblticketserviceoptions');
    }
}
