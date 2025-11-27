<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_332 extends App_module_migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `tblservices` ADD `departmentid` INT(11) NOT NULL DEFAULT '0' AFTER `name`;");
    }
}
