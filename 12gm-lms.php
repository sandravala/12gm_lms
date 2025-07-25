<?php
/**
 * Plugin Name: 12GM Learning Management System
 * Description: A simple LMS with WooCommerce integration, student & admin dashboards, and course management
 * Version: 1.0.1
 * Author: 12GM
 * Text Domain: 12gm-lms
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('TWELVEGM_LMS_VERSION', '1.0.0');
define('TWELVEGM_LMS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TWELVEGM_LMS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once TWELVEGM_LMS_PLUGIN_DIR . 'includes/class-12gm-lms.php';
require_once TWELVEGM_LMS_PLUGIN_DIR . 'includes/class-12gm-lms-post-types.php';
require_once TWELVEGM_LMS_PLUGIN_DIR . 'includes/class-12gm-lms-woocommerce.php';
require_once TWELVEGM_LMS_PLUGIN_DIR . 'includes/class-12gm-lms-admin.php';
require_once TWELVEGM_LMS_PLUGIN_DIR . 'includes/class-12gm-lms-student.php';
require_once TWELVEGM_LMS_PLUGIN_DIR . 'includes/class-12gm-lms-template-loader.php';
require_once TWELVEGM_LMS_PLUGIN_DIR . 'includes/admin/lesson-meta.php';

// Activation hook
register_activation_hook(__FILE__, 'twelvegm_lms_activate');
function twelvegm_lms_activate() {
    // Create required database tables and default options
    require_once TWELVEGM_LMS_PLUGIN_DIR . 'includes/class-12gm-lms-activator.php';
    TwelveGM_LMS_Activator::activate();
    
    // Flush rewrite rules after creating custom post types
    flush_rewrite_rules();
}

// Register lesson groups taxonomy
function register_lesson_groups_taxonomy() {
    register_taxonomy('lesson_group', '12gm_lesson', array(
        'labels' => array(
            'name' => __('Lesson Groups', '12gm-lms'),
            'singular_name' => __('Lesson Group', '12gm-lms'),
            'menu_name' => __('Lesson Groups', '12gm-lms'),
            'all_items' => __('All Groups', '12gm-lms'),
            'add_new_item' => __('Add New Group', '12gm-lms'),
            'edit_item' => __('Edit Group', '12gm-lms'),
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'lesson-group'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'register_lesson_groups_taxonomy');

// Deactivation hook
register_deactivation_hook(__FILE__, 'twelvegm_lms_deactivate');
function twelvegm_lms_deactivate() {
    flush_rewrite_rules();
}

// Initialize the plugin
function twelvegm_lms_init() {
    $plugin = new TwelveGM_LMS();
    $plugin->run();
}
add_action('plugins_loaded', 'twelvegm_lms_init');

// Track user logins (simple version)
function track_user_login($user_login, $user) {
    update_user_meta($user->ID, 'last_login', current_time('mysql'));
}
add_action('wp_login', 'track_user_login', 10, 2);

// Add dashboard widget for guest accounts (hook it properly)
function add_lms_guest_accounts_dashboard_widget() {
    // Only add for users who can manage options
    if (current_user_can('manage_options')) {
        wp_add_dashboard_widget(
            'lms_guest_accounts_widget',
            'Paskutinės LMS svečių paskyros',
            'display_lms_guest_accounts_widget'
        );
    }
}

function display_lms_guest_accounts_widget() {
    $recent_users = get_users(array(
        'meta_key' => '12gm_lms_auto_created',
        'meta_value' => true,
        'orderby' => 'registered',
        'order' => 'DESC',
        'number' => 5,
    ));
    
    if (empty($recent_users)) {
        echo '<p>Paskutinių automatiškai sukurtų paskyrų nėra.</p>';
        return;
    }
    
    echo '<table style="width: 100%;">';
    echo '<thead><tr><th>Vartotojas</th><th>Data</th><th>Būsena</th></tr></thead>';
    echo '<tbody>';
    
    foreach ($recent_users as $user) {
        $last_login = get_user_meta($user->ID, 'last_login', true);
        $status = $last_login ? 'Aktyvi' : 'Laukia';
        $status_color = $last_login ? 'green' : 'orange';
        
        echo '<tr>';
        echo '<td><a href="' . admin_url('user-edit.php?user_id=' . $user->ID) . '">' . esc_html($user->display_name) . '</a></td>';
        echo '<td>' . esc_html(date('M j, Y', strtotime($user->user_registered))) . '</td>';
        echo '<td><span style="color: ' . $status_color . ';">' . $status . '</span></td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    echo '<p><a href="' . admin_url('admin.php?page=12gm-lms-guest-accounts') . '">Žiūrėti visas →</a></p>';
}

// Hook the dashboard widget at the right time
add_action('wp_dashboard_setup', 'add_lms_guest_accounts_dashboard_widget');