<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Internal Mail Menu Hook
 * This file adds the Internal Mail menu item to the admin sidebar
 */

/**
 * Register internal mail menu
 * Hook: admin_init
 */
hooks()->add_action('admin_init', 'internal_mail_add_menu_items');

function internal_mail_add_menu_items()
{
    $CI = &get_instance();
    
    if (is_staff_logged_in()) {
        // Load the model to get unread count
        if (!isset($CI->internal_mail_model)) {
            $CI->load->model('internal_mail_model');
        }
        
        $unread_count = $CI->internal_mail_model->get_unread_count(get_staff_user_id());
        
        // Build menu item
        $menu_item = [
            'slug'     => 'internal-mail',
            'name'     => 'internal_mail',
            'icon'     => 'fa fa-envelope',
            'href'     => admin_url('internal_mail/inbox'),
            'position' => 40, // Position in menu (adjust as needed)
        ];
        
        // Add unread badge if there are unread messages
        if ($unread_count > 0) {
            $menu_item['badge'] = [
                'value' => $unread_count,
                'type'  => 'danger',
            ];
        }
        
        // Add submenu items
        $menu_item['children'] = [];

        $menu_item['children'][] = [
            'slug' => 'internal-mail-inbox',
            'name' => 'internal_mail_inbox',
            'icon' => 'fa fa-inbox',
            'href' => admin_url('internal_mail/inbox'),
        ];

        $menu_item['children'][] = [
            'slug' => 'internal-mail-sent',
            'name' => 'internal_mail_sent',
            'icon' => 'fa fa-paper-plane',
            'href' => admin_url('internal_mail/sent'),
        ];

        $menu_item['children'][] = [
            'slug' => 'internal-mail-drafts',
            'name' => 'internal_mail_drafts',
            'icon' => 'fa fa-file-text',
            'href' => admin_url('internal_mail/drafts'),
        ];

        $menu_item['children'][] = [
            'slug' => 'internal-mail-trash',
            'name' => 'internal_mail_trash',
            'icon' => 'fa fa-trash',
            'href' => admin_url('internal_mail/trash'),
        ];
        
        // Add to sidebar menu
        add_sidebar_menu_item('internal_mail', $menu_item);
    }
}

/**
 * Helper function to add sidebar menu item
 * If your system doesn't have this function, this will register it via hook
 */
if (!function_exists('add_sidebar_menu_item')) {
    function add_sidebar_menu_item($key, $item)
    {
        hooks()->add_filter('sidebar_menu_items', function($items) use ($key, $item) {
            $items[$key] = $item;
            return $items;
        });
    }
}
