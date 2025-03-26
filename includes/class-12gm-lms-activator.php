<?php
/**
 * Plugin activation functionality.
 *
 * @since      1.0.0
 */
class TwelveGM_LMS_Activator {

    /**
     * Run activation tasks.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Create necessary database tables
        self::create_tables();
        
        // Create default pages
        self::create_pages();
        
        // Set default options
        self::set_default_options();
        
        // Set flag to flush rewrite rules on next init
        update_option('12gm_lms_flush_rewrite_rules', true);
    }
    
    /**
     * Create necessary database tables.
     *
     * @since    1.0.0
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create progress tracking table (if needed)
        $table_name = $wpdb->prefix . '12gm_lms_progress';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                course_id bigint(20) NOT NULL,
                lesson_id bigint(20) NOT NULL,
                completed tinyint(1) NOT NULL DEFAULT 0,
                completion_date datetime DEFAULT NULL,
                updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_course (user_id, course_id),
                KEY user_lesson (user_id, lesson_id)
            ) $charset_collate;";
            
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
    }
    
    /**
     * Create default pages.
     *
     * @since    1.0.0
     */
    private static function create_pages() {
        // Create student dashboard page
        $dashboard_page_id = wp_insert_post(array(
            'post_title'     => __('My Learning Dashboard', '12gm-lms'),
            'post_content'   => '[12gm_lms_dashboard]',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'comment_status' => 'closed',
        ));
        
        // Save page ID to options
        update_option('12gm_lms_dashboard_page_id', $dashboard_page_id);
    }
    
    /**
     * Set default options.
     *
     * @since    1.0.0
     */
    private static function set_default_options() {
        // Plugin version
        update_option('12gm_lms_version', TWELVEGM_LMS_VERSION);
        
        // Default settings
        if (!get_option('12gm_lms_settings')) {
            $default_settings = array(
                'course_slug' => 'course',
                'lesson_slug' => 'lesson',
                'enable_progress_tracking' => 1,
                'require_lesson_completion' => 1,
                'show_course_progress' => 1,
            );
            
            update_option('12gm_lms_settings', $default_settings);
        }
    }
}