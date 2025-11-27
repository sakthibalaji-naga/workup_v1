<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
    <title>
        <?php echo e(get_option('companyname')); ?> - <?php echo _l('admin_auth_login_heading'); ?>
    </title>
    <?php echo app_compile_css('admin-auth'); ?>
    <style>
    body,
    html {
        font-size: 16px;
    }

    body>* {
        font-size: 14px;
    }

    body {
        font-family: "Inter", sans-serif;
        color: #475569;
        margin: 0;
        padding: 0;
    }

    .password-field-wrapper {
        position: relative;
    }

    .password-field-wrapper .form-control {
        padding-right: 3rem;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 0.75rem;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: color .2s ease, background-color .2s ease;
    }

    .password-toggle:hover {
        color: #1f2937;
        background-color: #f8fafc;
    }

    .password-toggle:focus-visible {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }

    .password-toggle i {
        pointer-events: none;
        font-size: 1rem;
    }

    html[dir="rtl"] .password-field-wrapper .form-control {
        padding-right: 1rem;
        padding-left: 3rem;
    }

    html[dir="rtl"] .password-field-wrapper .password-toggle {
        right: auto;
        left: 0.75rem;
    }

    .company-logo {
        padding: 25px 10px;
        display: block;
    }

    .company-logo img {
        margin: 0 auto;
        display: block;
    }

    @media screen and (max-height: 575px),
    screen and (min-width: 992px) and (max-width:1199px) {

        #rc-imageselect,
        .g-recaptcha {
            transform: scale(0.83);
            -webkit-transform: scale(0.83);
            transform-origin: 0 0;
            -webkit-transform-origin: 0 0;
        }
    }
    </style>
    <?php if (show_recaptcha()) { ?>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <?php } ?>
    <?php if (file_exists(FCPATH . 'assets/css/custom.css')) { ?>
    <link href="<?php echo base_url('assets/css/custom.css'); ?>" rel="stylesheet" id="custom-css">
    <?php } ?>
    <?php hooks()->do_action('app_admin_authentication_head'); ?>
</head>
