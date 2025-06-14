<?php
/**
 * Register all custom post types for the plugin.
 *
 * @since      1.0.0
 */
class TwelveGM_LMS_Post_Types {

    /**
     * Register the custom post types.
     *
     * @since    1.0.0
     */
    public function run() {
        add_action('init', array($this, 'register_course_post_type'));
        add_action('init', array($this, 'register_lesson_post_type'));
        add_action('init', array($this, 'register_progress_post_type'));
        
        // Make sure to flush rewrite rules when needed
        add_action('init', array($this, 'maybe_flush_rewrite_rules'));
    }

    /**
     * Register the Course post type.
     *
     * @since    1.0.0
     */
    public function register_course_post_type() {
        $labels = array(
            'name'                  => _x('Courses', 'Post Type General Name', '12gm-lms'),
            'singular_name'         => _x('Course', 'Post Type Singular Name', '12gm-lms'),
            'menu_name'             => __('Courses', '12gm-lms'),
            'name_admin_bar'        => __('Course', '12gm-lms'),
            'archives'              => __('Course Archives', '12gm-lms'),
            'attributes'            => __('Course Attributes', '12gm-lms'),
            'parent_item_colon'     => __('Parent Course:', '12gm-lms'),
            'all_items'             => __('All Courses', '12gm-lms'),
            'add_new_item'          => __('Add New Course', '12gm-lms'),
            'add_new'               => __('Add New', '12gm-lms'),
            'new_item'              => __('New Course', '12gm-lms'),
            'edit_item'             => __('Edit Course', '12gm-lms'),
            'update_item'           => __('Update Course', '12gm-lms'),
            'view_item'             => __('View Course', '12gm-lms'),
            'view_items'            => __('View Courses', '12gm-lms'),
            'search_items'          => __('Search Course', '12gm-lms'),
            'not_found'             => __('Not found', '12gm-lms'),
            'not_found_in_trash'    => __('Not found in Trash', '12gm-lms'),
            'featured_image'        => __('Featured Image', '12gm-lms'),
            'set_featured_image'    => __('Set featured image', '12gm-lms'),
            'remove_featured_image' => __('Remove featured image', '12gm-lms'),
            'use_featured_image'    => __('Use as featured image', '12gm-lms'),
            'insert_into_item'      => __('Insert into course', '12gm-lms'),
            'uploaded_to_this_item' => __('Uploaded to this course', '12gm-lms'),
            'items_list'            => __('Courses list', '12gm-lms'),
            'items_list_navigation' => __('Courses list navigation', '12gm-lms'),
            'filter_items_list'     => __('Filter courses list', '12gm-lms'),
        );
        
        $args = array(
            'label'                 => __('Course', '12gm-lms'),
            'description'           => __('Course Description', '12gm-lms'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=12gm_course',
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-welcome-learn-more',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true, // Enable Gutenberg editor
            'rest_base'             => '12gm-courses',
        );
        
        register_post_type('12gm_course', $args);
    }

    /**
     * Register the Lesson post type.
     *
     * @since    1.0.0
     */
    public function register_lesson_post_type() {
        $labels = array(
            'name'                  => _x('Lessons', 'Post Type General Name', '12gm-lms'),
            'singular_name'         => _x('Lesson', 'Post Type Singular Name', '12gm-lms'),
            'menu_name'             => __('Lessons', '12gm-lms'),
            'name_admin_bar'        => __('Lesson', '12gm-lms'),
            'archives'              => __('Lesson Archives', '12gm-lms'),
            'attributes'            => __('Lesson Attributes', '12gm-lms'),
            'parent_item_colon'     => __('Parent Lesson:', '12gm-lms'),
            'all_items'             => __('All Lessons', '12gm-lms'),
            'add_new_item'          => __('Add New Lesson', '12gm-lms'),
            'add_new'               => __('Add New', '12gm-lms'),
            'new_item'              => __('New Lesson', '12gm-lms'),
            'edit_item'             => __('Edit Lesson', '12gm-lms'),
            'update_item'           => __('Update Lesson', '12gm-lms'),
            'view_item'             => __('View Lesson', '12gm-lms'),
            'view_items'            => __('View Lessons', '12gm-lms'),
            'search_items'          => __('Search Lesson', '12gm-lms'),
            'not_found'             => __('Not found', '12gm-lms'),
            'not_found_in_trash'    => __('Not found in Trash', '12gm-lms'),
            'featured_image'        => __('Featured Image', '12gm-lms'),
            'set_featured_image'    => __('Set featured image', '12gm-lms'),
            'remove_featured_image' => __('Remove featured image', '12gm-lms'),
            'use_featured_image'    => __('Use as featured image', '12gm-lms'),
            'insert_into_item'      => __('Insert into lesson', '12gm-lms'),
            'uploaded_to_this_item' => __('Uploaded to this lesson', '12gm-lms'),
            'items_list'            => __('Lessons list', '12gm-lms'),
            'items_list_navigation' => __('Lessons list navigation', '12gm-lms'),
            'filter_items_list'     => __('Filter lessons list', '12gm-lms'),
        );
        
        $args = array(
            'label'                 => __('Lesson', '12gm-lms'),
            'description'           => __('Lesson Description', '12gm-lms'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=12gm_course',
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-media-document',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true, // Enable Gutenberg editor
            'rest_base'             => '12gm-lessons',
        );
        
        register_post_type('12gm_lesson', $args);

        // Add course-lesson relationship through taxonomy
        $taxonomy_labels = array(
            'name'              => _x('Course Categories', 'taxonomy general name', '12gm-lms'),
            'singular_name'     => _x('Course Category', 'taxonomy singular name', '12gm-lms'),
            'search_items'      => __('Search Course Categories', '12gm-lms'),
            'all_items'         => __('All Course Categories', '12gm-lms'),
            'parent_item'       => __('Parent Course Category', '12gm-lms'),
            'parent_item_colon' => __('Parent Course Category:', '12gm-lms'),
            'edit_item'         => __('Edit Course Category', '12gm-lms'),
            'update_item'       => __('Update Course Category', '12gm-lms'),
            'add_new_item'      => __('Add New Course Category', '12gm-lms'),
            'new_item_name'     => __('New Course Category Name', '12gm-lms'),
            'menu_name'         => __('Course Categories', '12gm-lms'),
        );

        $taxonomy_args = array(
            'labels'            => $taxonomy_labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => false,
            'show_in_rest'      => true,
        );

        register_taxonomy('12gm_course_cat', array('12gm_lesson'), $taxonomy_args);
    }

    /**
     * Register the Progress post type (used to track student progress).
     *
     * @since    1.0.0
     */
    public function register_progress_post_type() {
        $labels = array(
            'name'                  => _x('Student Progress', 'Post Type General Name', '12gm-lms'),
            'singular_name'         => _x('Progress', 'Post Type Singular Name', '12gm-lms'),
            'menu_name'             => __('Student Progress', '12gm-lms'),
            'name_admin_bar'        => __('Progress', '12gm-lms'),
            'archives'              => __('Progress Archives', '12gm-lms'),
            'attributes'            => __('Progress Attributes', '12gm-lms'),
            'parent_item_colon'     => __('Parent Progress:', '12gm-lms'),
            'all_items'             => __('All Progress', '12gm-lms'),
            'add_new_item'          => __('Add New Progress', '12gm-lms'),
            'add_new'               => __('Add New', '12gm-lms'),
            'new_item'              => __('New Progress', '12gm-lms'),
            'edit_item'             => __('Edit Progress', '12gm-lms'),
            'update_item'           => __('Update Progress', '12gm-lms'),
            'view_item'             => __('View Progress', '12gm-lms'),
            'view_items'            => __('View Progress', '12gm-lms'),
            'search_items'          => __('Search Progress', '12gm-lms'),
            'not_found'             => __('Not found', '12gm-lms'),
            'not_found_in_trash'    => __('Not found in Trash', '12gm-lms'),
        );
        
        $args = array(
            'label'                 => __('Progress', '12gm-lms'),
            'description'           => __('Student Progress Tracking', '12gm-lms'),
            'labels'                => $labels,
            'supports'              => array('title'),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=12gm_course',
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-chart-line',
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'post',
            'show_in_rest'          => false,
        );
        
        register_post_type('12gm_progress', $args);
    }
    
    /**
     * Check if we need to flush rewrite rules.
     * 
     * @since    1.0.0
     */
    public function maybe_flush_rewrite_rules() {
        // If our rewrite rules haven't been flushed yet, do it now
        if (get_option('12gm_lms_flush_rewrite_rules', false)) {
            flush_rewrite_rules();
            delete_option('12gm_lms_flush_rewrite_rules');
        }
    }
}