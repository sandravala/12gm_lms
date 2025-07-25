<?php

/**
 * Register all custom post types for the plugin.
 *
 * @since      1.0.0
 */
class TwelveGM_LMS_Post_Types
{

    /**
     * Register the custom post types.
     *
     * @since    1.0.0
     */
    public function run()
    {
        add_action('init', array($this, 'register_course_post_type'));
        add_action('init', array($this, 'register_lesson_post_type'));
        add_action('init', array($this, 'register_progress_post_type'));
        add_action('init', array($this, 'register_lesson_group_taxonomy'));
        add_filter('post_row_actions', array($this, 'add_duplicate_link'), 10, 2);
        add_action('admin_action_duplicate_lesson', array($this, 'handle_duplicate_action'));
        // Make sure to flush rewrite rules when needed
        add_action('init', array($this, 'maybe_flush_rewrite_rules'));
    }

    /**
     * Register the Course post type.
     *
     * @since    1.0.0
     */
    public function register_course_post_type()
    {
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
            'rewrite'               => array(
                'slug'       => 'turiu-laiko',
                'with_front' => false
            ),
        );

        register_post_type('12gm_course', $args);
    }

    /**
     * Register the Lesson post type.
     *
     * @since    1.0.0
     */
    public function register_lesson_post_type()
    {
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
            'rewrite'               => false, // We'll use the existing courses/lesson pattern
        );

        register_post_type('12gm_lesson', $args);

        add_filter('post_type_link', array($this, 'lesson_permalink'), 10, 2);



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
            'show_ui'           => false,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => false,
            'show_in_rest'      => true,
        );

        register_taxonomy('12gm_course_cat', array('12gm_lesson'), $taxonomy_args);
    }




    /**
     * Replace %12gm_course_name% with the actual course slug in lesson permalinks.
     */
    public function lesson_permalink($permalink, $post)
    {
        if ($post->post_type !== '12gm_lesson') {
            return $permalink;
        }


        // Get the course post ID from post meta
        $course_id = get_post_meta($post->ID, '_12gm_course_id', true);
        if ($course_id) {
            $course = get_post($course_id);
            if ($course && $course->post_status === 'publish') {
                $course_slug = $course->post_name; // This is the course post slug
            } else {
                $course_slug = 'kursas';
            }
        } else {
            $course_slug = 'kursas';
        }

        return home_url('/' . $course_slug . '/' . $post->post_name . '/');
    }



    /**
     * Register the Progress post type (used to track student progress).
     *
     * @since    1.0.0
     */
    public function register_progress_post_type()
    {
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
     * Register the Lesson Group taxonomy.
     *
     * @since 1.0.0
     */
    public function register_lesson_group_taxonomy()
    {
        $taxonomy_labels = array(
            'name'              => _x('Lesson Groups', 'taxonomy general name', '12gm-lms'),
            'singular_name'     => _x('Lesson Group', 'taxonomy singular name', '12gm-lms'),
            'search_items'      => __('Search Lesson Groups', '12gm-lms'),
            'all_items'         => __('All Lesson Groups', '12gm-lms'),
            'parent_item'       => __('Parent Lesson Group', '12gm-lms'),
            'parent_item_colon' => __('Parent Lesson Group:', '12gm-lms'),
            'edit_item'         => __('Edit Lesson Group', '12gm-lms'),
            'update_item'       => __('Update Lesson Group', '12gm-lms'),
            'add_new_item'      => __('Add New Lesson Group', '12gm-lms'),
            'new_item_name'     => __('New Lesson Group Name', '12gm-lms'),
            'menu_name'         => __('Lesson Groups', '12gm-lms'),
        );

        $taxonomy_args = array(
            'labels'            => $taxonomy_labels,
            'hierarchical'      => true, // Category-like behavior
            'public'            => true,
            'show_ui'           => true, // Enables admin interface
            'show_admin_column' => true, // Adds taxonomy column in post list
            'show_in_nav_menus' => true,
            'show_tagcloud'     => false,
            'show_in_rest'      => true, // Enables Gutenberg editor support
        );

        register_taxonomy('lesson_group', array('12gm_lesson'), $taxonomy_args);
    }

    /**
     * Duplicate a lesson post.
     *
     * @param int $lesson_id The ID of the lesson to duplicate.
     * @return int|WP_Error The ID of the new post or a WP_Error object on failure.
     */
    function duplicate_lesson($lesson_id)
    {
        // Get the original lesson post
        $original_post = get_post($lesson_id);

        if (!$original_post || $original_post->post_type !== '12gm_lesson') {
            return new WP_Error('invalid_post', __('Invalid lesson post.', '12gm-lms'));
        }

        // Create a new post array
        $new_post = array(
            'post_title'   => $original_post->post_title . ' (Copy)',
            'post_content' => $original_post->post_content,
            'post_status'  => 'draft',
            'post_type'    => $original_post->post_type,
        );

        // Insert the new post
        $new_post_id = wp_insert_post($new_post);

        if (is_wp_error($new_post_id)) {
            return $new_post_id;
        }

        // Copy metadata
        $meta_data = get_post_meta($lesson_id);
        foreach ($meta_data as $key => $values) {
            foreach ($values as $value) {
                add_post_meta($new_post_id, $key, $value);
            }
        }

        // Copy taxonomy terms
        $taxonomies = get_object_taxonomies($original_post->post_type);
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_post_terms($lesson_id, $taxonomy, array('fields' => 'ids'));
            wp_set_post_terms($new_post_id, $terms, $taxonomy);
        }

        return $new_post_id;
    }

    /**
     * Add a "Duplicate" link to the lesson post row actions.
     *
     * @param array $actions The existing row actions.
     * @param WP_Post $post The current post object.
     * @return array The modified row actions.
     */
    public function add_duplicate_link($actions, $post)
    {
        if ($post->post_type === '12gm_lesson') {
            $duplicate_url = admin_url('admin.php?action=duplicate_lesson&post=' . $post->ID);
            $actions['duplicate'] = '<a href="' . esc_url($duplicate_url) . '">' . __('Duplicate', '12gm-lms') . '</a>';
        }
        return $actions;
    }

    /**
     * Handle the "Duplicate" action for lessons.
     */
    public function handle_duplicate_action()
    {
        if (!isset($_GET['post']) || !current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to duplicate this lesson.', '12gm-lms'));
        }

        $lesson_id = intval($_GET['post']);
        $new_post_id = $this->duplicate_lesson($lesson_id);

        if (is_wp_error($new_post_id)) {
            wp_die($new_post_id->get_error_message());
        }

        wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
        exit;
    }

    /**
     * Check if we need to flush rewrite rules.
     * 
     * @since    1.0.0
     */
    public function maybe_flush_rewrite_rules()
    {
        // If our rewrite rules haven't been flushed yet, do it now
        if (get_option('12gm_lms_flush_rewrite_rules', false)) {
            flush_rewrite_rules();
            delete_option('12gm_lms_flush_rewrite_rules');
        }
    }
}
