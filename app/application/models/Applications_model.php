<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Applications_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get application/s
     * @param  string $id    application id
     * @return mixed
     */
    public function get($id = '')
    {
        $applicationsTable = db_prefix() . 'applications';
        $hasPositionColumn = $this->db->field_exists('position', $applicationsTable);

        if ($hasPositionColumn) {
            $this->db->order_by('(CASE WHEN ' . $applicationsTable . '.position = 0 THEN 1 ELSE 0 END)', 'ASC', false);
            $this->db->order_by($applicationsTable . '.position', 'ASC');
        }

        $this->db->order_by($applicationsTable . '.name', 'ASC');
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get($applicationsTable)->row();
        }

        return $this->db->get($applicationsTable)->result_array();
    }
}
