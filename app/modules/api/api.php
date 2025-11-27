<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: API
Description: API module for ticket management
Version: 1.0.0
*/

define('API_MODULE_NAME', 'api');

$CI = &get_instance();

hooks()->add_action('admin_init', 'api_init_menu_items');
hooks()->add_filter('module_api_action_links', 'module_api_action_links');

/**
* Add additional settings for this module in the module list area
* @param  array $actions current actions
* @return array
*/
function module_api_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('api_settings') . '">' . _l('API Settings') . '</a>';

    return $actions;
}

/**
* Register activation module hook
*/
register_activation_hook(API_MODULE_NAME, 'api_activation_hook');

function api_activation_hook()
{
    // Include install file if needed
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(API_MODULE_NAME, [API_MODULE_NAME]);

/**
 * Init API module menu items in setup
 */
function api_init_menu_items()
{
    if (is_admin()) {
        $CI = &get_instance();

        // Add main API menu item
        $CI->app_menu->add_setup_menu_item('api', [
            'slug'     => 'api-settings',
            'name'     => 'API',
            'href'     => admin_url('api_settings'),
            'position' => 65,
        ]);

        // Add Internal API submenu item (existing API settings)
        $CI->app_menu->add_setup_children_item('api', [
            'slug'     => 'internal-api-settings',
            'name'     => 'Internal API',
            'href'     => admin_url('api_settings'),
            'position' => 5,
        ]);

        // Add External API submenu item (cron API settings)
        $CI->app_menu->add_setup_children_item('api', [
            'slug'     => 'external-api-settings',
            'name'     => 'External API',
            'href'     => admin_url('cron_api_settings'),
            'position' => 10,
        ]);

        // Add Flow Builder submenu item
        $CI->app_menu->add_setup_children_item('api', [
            'slug'     => 'flow-builder',
            'name'     => 'Flow Builder',
            'href'     => admin_url('flow_builder'),
            'position' => 15,
        ]);
    }
}
