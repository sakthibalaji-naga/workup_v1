<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (file_exists(APPPATH . 'hooks/internal_mail_menu_hook.php')) {
    require_once(APPPATH . 'hooks/internal_mail_menu_hook.php');
}
