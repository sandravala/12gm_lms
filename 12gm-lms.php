<?php
/**
 * Plugin Name: 12GM Learning Management System
 * Description: A simple LMS with WooCommerce integration, student & admin dashboards, and course management
 * Version: 1.0.0
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