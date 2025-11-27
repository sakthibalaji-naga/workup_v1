<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Divisions_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = false)
    {
        // Safety: ensure divisions table exists to prevent 500 on first usage
        if (!$this->db->table_exists(db_prefix() . 'divisions')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'divisions` (
                `divisionid` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(191) NOT NULL,
                PRIMARY KEY (`divisionid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }

        if (is_numeric($id)) {
            $this->db->where('divisionid', $id);
            return $this->db->get(db_prefix() . 'divisions')->row();
        }

        $divisions = $this->app_object_cache->get('divisions');
        if (!$divisions && !is_array($divisions)) {
            $divisions = $this->db->get(db_prefix() . 'divisions')->result_array();
            $this->app_object_cache->add('divisions', $divisions);
        }

        return $divisions;
    }

    public function add($data)
    {
        if (!$this->db->table_exists(db_prefix() . 'divisions')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'divisions` (
                `divisionid` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(191) NOT NULL,
                PRIMARY KEY (`divisionid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }
        $data = [
            'name' => trim($data['name'] ?? ''),
        ];
        if ($data['name'] === '') {
            return false;
        }
        $this->db->insert(db_prefix() . 'divisions', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->app_object_cache->delete('divisions');
            log_activity('New Division Added [' . $data['name'] . ', ID: ' . $insert_id . ']');
        }
        return $insert_id;
    }

    public function update($data, $id)
    {
        if (!$this->db->table_exists(db_prefix() . 'divisions')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'divisions` (
                `divisionid` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(191) NOT NULL,
                PRIMARY KEY (`divisionid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }
        $data = [
            'name' => trim($data['name'] ?? ''),
        ];
        if ($data['name'] === '') {
            return false;
        }
        $this->db->where('divisionid', $id);
        $this->db->update(db_prefix() . 'divisions', $data);
        if ($this->db->affected_rows() > 0) {
            $this->app_object_cache->delete('divisions');
            log_activity('Division Updated [Name: ' . $data['name'] . ', ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        if (!$this->db->table_exists(db_prefix() . 'divisions')) {
            // Nothing to delete
            return false;
        }
        $this->db->where('divisionid', $id);
        $this->db->delete(db_prefix() . 'divisions');
        if ($this->db->affected_rows() > 0) {
            $this->app_object_cache->delete('divisions');
            log_activity('Division Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }
}
