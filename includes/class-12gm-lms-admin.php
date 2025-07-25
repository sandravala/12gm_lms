<?php

/**
 * Admin dashboard functionality.
 *
 * @since      1.0.0
 */
class TwelveGM_LMS_Admin
{

    /**
     * WooCommerce instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      TwelveGM_LMS_WooCommerce    $woocommerce    WooCommerce integration instance.
     */
    private $woocommerce;

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        $this->woocommerce = new TwelveGM_LMS_WooCommerce();
    }

    /**
     * Register the hooks.
     *
     * @since    1.0.0
     */
    public function run()
    {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Add metaboxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'), 10, 2);

        // Add course assignment metabox to lessons
        add_action('add_meta_boxes', array($this, 'add_lesson_course_metabox'));
        add_action('save_post', array($this, 'save_lesson_course_metabox'), 10, 2);

        // Add user access management page
        add_action('admin_menu', array($this, 'add_user_access_page'));

        // Handle AJAX for enrollment management
        add_action('wp_ajax_12gm_lms_enroll_user', array($this, 'ajax_enroll_user'));
        add_action('wp_ajax_12gm_lms_unenroll_user', array($this, 'ajax_unenroll_user'));

        // Add flush permalinks option to main LMS settings
        add_action('admin_init', array($this, 'handle_flush_rewrite_rules'));

        add_action('set_object_terms', function ($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids) {
            if ($taxonomy === '12gm_course_cat' && !empty($terms)) {
                // $terms can be an array of term IDs or slugs
                $term = is_array($terms) ? $terms[0] : $terms;
                if (is_numeric($term)) {
                    $term_obj = get_term($term, '12gm_course_cat');
                    $term_slug = $term_obj ? $term_obj->slug : '';
                } else {
                    $term_slug = $term;
                }
                if (strpos($term_slug, 'course-') === 0) {
                    $course_id = intval(str_replace('course-', '', $term_slug));
                    update_post_meta($object_id, '_12gm_course_id', $course_id);
                }
            }
        }, 10, 6);
    }

    /**
     * Add admin menu items.
     *
     * @since    1.0.0
     */
    public function add_admin_menu()
    {
        // Main menu item
        add_menu_page(
            __('LMS Dashboard', '12gm-lms'),
            __('LMS', '12gm-lms'),
            'manage_options',
            '12gm-lms',
            array($this, 'display_dashboard_page'),
            'dashicons-welcome-learn-more',
            30
        );

        // Add submenus
        add_submenu_page(
            '12gm-lms',
            __('Dashboard', '12gm-lms'),
            __('Dashboard', '12gm-lms'),
            'manage_options',
            '12gm-lms',
            array($this, 'display_dashboard_page')
        );

        add_submenu_page(
            '12gm-lms',
            __('All Courses', '12gm-lms'),
            __('All Courses', '12gm-lms'),
            'manage_options',
            'edit.php?post_type=12gm_course'
        );

        add_submenu_page(
            '12gm-lms',
            __('Add New Course', '12gm-lms'),
            __('Add New Course', '12gm-lms'),
            'manage_options',
            'post-new.php?post_type=12gm_course'
        );

        add_submenu_page(
            '12gm-lms',
            __('All Lessons', '12gm-lms'),
            __('All Lessons', '12gm-lms'),
            'manage_options',
            'edit.php?post_type=12gm_lesson'
        );

        add_submenu_page(
            '12gm-lms',
            __('Add New Lesson', '12gm-lms'),
            __('Add New Lesson', '12gm-lms'),
            'manage_options',
            'post-new.php?post_type=12gm_lesson'
        );

        add_submenu_page(
            '12gm-lms', // Parent menu slug
            __('Lesson Groups', '12gm-lms'), // Page title
            __('Lesson Groups', '12gm-lms'), // Menu title
            'manage_categories', // Capability required
            'edit-tags.php?taxonomy=lesson_group&post_type=12gm_lesson' // URL for the taxonomy management page
        );
    }

    /**
     * Display the admin dashboard page.
     *
     * @since    1.0.0
     */
    public function display_dashboard_page()
    {
        // Course stats
        $course_count = wp_count_posts('12gm_course')->publish;
        $lesson_count = wp_count_posts('12gm_lesson')->publish;

        // Student stats
        $students_with_access = $this->get_students_with_course_access();


        include TWELVEGM_LMS_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }


    /**
     * Add metaboxes to the course edit screen.
     *
     * @since    1.0.0
     */
    public function add_meta_boxes()
    {
        add_meta_box(
            '12gm_lms_course_lessons',
            __('Course Lessons', '12gm-lms'),
            array($this, 'render_course_lessons_metabox'),
            '12gm_course',
            'normal',
            'high'
        );

        add_meta_box(
            '12gm_lms_course_access',
            __('Course Access', '12gm-lms'),
            array($this, 'render_course_access_metabox'),
            '12gm_course',
            'side',
            'default'
        );
    }


    /**
     * Render the course lessons metabox.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_course_lessons_metabox($post)
    {
        wp_nonce_field('12gm_lms_course_lessons', '12gm_lms_course_lessons_nonce');

        // Get all lessons assigned to this course
        $course_lessons = get_posts(array(
            'post_type' => '12gm_lesson',
            'numberposts' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'tax_query' => array(
                array(
                    'taxonomy' => '12gm_course_cat',
                    'field' => 'slug',
                    'terms' => 'course-' . $post->ID,
                )
            )
        ));

        // Get all lesson groups
        $lesson_groups = get_terms(array(
            'taxonomy' => 'lesson_group',
            'hide_empty' => false,
        ));

        // Group lessons by their group
        $grouped_lessons = array();
        foreach ($course_lessons as $lesson) {
            $lesson_groups_assigned = wp_get_post_terms($lesson->ID, 'lesson_group');
            if (!empty($lesson_groups_assigned)) {
                $group_id = $lesson_groups_assigned[0]->term_id;
                $group_name = $lesson_groups_assigned[0]->name;
            } else {
                $group_id = 'ungrouped';
                $group_name = __('No Group', '12gm-lms');
            }

            if (!isset($grouped_lessons[$group_id])) {
                $grouped_lessons[$group_id] = array(
                    'name' => $group_name,
                    'lessons' => array()
                );
            }
            $grouped_lessons[$group_id]['lessons'][] = $lesson;
        }

        // Display current lessons grouped
        echo '<div class="12gm-lms-lessons-list">';
        echo '<h3>' . __('Current Lessons in this Course', '12gm-lms') . '</h3>';

        if (empty($course_lessons)) {
            echo '<p>' . __('No lessons assigned to this course yet.', '12gm-lms') . '</p>';
        } else {
            foreach ($grouped_lessons as $group_id => $group_data) {
                echo '<div style="margin-bottom: 30px; border: 1px solid #ddd; padding: 15px;">';
                echo '<h4 style="margin-top: 0; background: #f5f5f5; padding: 10px; margin: -15px -15px 15px -15px;">' . esc_html($group_data['name']) . '</h4>';

                echo '<table class="widefat">';
                echo '<thead><tr>';
                echo '<th style="width: 80px;">' . __('Order in Group', '12gm-lms') . '</th>';
                echo '<th>' . __('Lesson Title', '12gm-lms') . '</th>';
                echo '<th style="width: 150px;">' . __('Change Group', '12gm-lms') . '</th>';
                echo '<th style="width: 120px;">' . __('Actions', '12gm-lms') . '</th>';
                echo '</tr></thead><tbody>';

                foreach ($group_data['lessons'] as $index => $lesson) {
                    // Get current group for this lesson
                    $current_groups = wp_get_post_terms($lesson->ID, 'lesson_group');
                    $current_group_id = !empty($current_groups) ? $current_groups[0]->term_id : '';

                    echo '<tr>';
                    // Order within the group (1, 2, 3, etc.)
                    echo '<td><input type="number" name="12gm_lms_lesson_group_order[' . esc_attr($lesson->ID) . ']" value="' . ($index + 1) . '" min="1" style="width: 60px;"></td>';
                    echo '<td><a href="' . get_edit_post_link($lesson->ID) . '">' . esc_html($lesson->post_title) . '</a></td>';

                    // Group dropdown
                    echo '<td>';
                    echo '<select name="12gm_lms_lesson_groups[' . esc_attr($lesson->ID) . ']" style="width: 100%;">';
                    echo '<option value=""' . ($group_id === 'ungrouped' ? ' selected' : '') . '>' . __('No Group', '12gm-lms') . '</option>';

                    if (!empty($lesson_groups)) {
                        foreach ($lesson_groups as $group) {
                            $selected = ($group->term_id == $current_group_id) ? 'selected' : '';
                            echo '<option value="' . esc_attr($group->term_id) . '" ' . $selected . '>' . esc_html($group->name) . '</option>';
                        }
                    }
                    echo '</select>';
                    echo '</td>';

                    echo '<td>';
                    echo '<a href="' . get_edit_post_link($lesson->ID) . '" class="button">' . __('Edit', '12gm-lms') . '</a> ';
                    echo '<a href="' . get_permalink($lesson->ID) . '" class="button" target="_blank">' . __('View', '12gm-lms') . '</a>';
                    echo '</td>';
                    echo '</tr>';
                }

                echo '</tbody></table>';
                echo '</div>';
            }
        }
        echo '</div>';

        // Add new lessons form (existing code)
        echo '<div class="12gm-lms-lessons-add">';
        echo '<h3>' . __('Add Lessons to this Course', '12gm-lms') . '</h3>';

        if (empty($all_lessons)) {
            echo '<p>' . __('No lessons found. Create lessons first.', '12gm-lms') . '</p>';
        } else {
            echo '<p>' . __('Select lessons to add to this course:', '12gm-lms') . '</p>';
            echo '<div style="max-height: 200px; overflow-y: auto;">';

            // Filter out already assigned lessons
            $assigned_ids = wp_list_pluck($course_lessons, 'ID');

            foreach ($all_lessons as $lesson) {
                if (in_array($lesson->ID, $assigned_ids)) {
                    continue;
                }

                echo '<label style="display: block; margin-bottom: 5px;">';
                echo '<input type="checkbox" name="12gm_lms_lessons_to_add[]" value="' . esc_attr($lesson->ID) . '">';
                echo esc_html($lesson->post_title);
                echo '</label>';
            }

            echo '</div>';
            echo '<p><input type="submit" class="button button-primary" value="' . __('Update Course Lessons', '12gm-lms') . '"></p>';
        }
        echo '</div>';

        // Add "create new lesson" link
        echo '<p><a href="' . admin_url('post-new.php?post_type=12gm_lesson') . '" class="button">' . __('Create New Lesson', '12gm-lms') . '</a></p>';

        // Show note about creating groups
        if (empty($lesson_groups)) {
            echo '<p><em>' . __('No lesson groups found. Create lesson groups in the Lessons → Lesson Groups section.', '12gm-lms') . '</em></p>';
        }
    }

    /**
     * Render the course access metabox.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_course_access_metabox($post)
    {
        wp_nonce_field('12gm_lms_course_access', '12gm_lms_course_access_nonce');

        // Get WooCommerce products linked to this course
        $products = get_posts(array(
            'post_type' => 'product',
            'numberposts' => -1,
            'meta_query' => array(
                array(
                    'key' => '_12gm_lms_linked_courses',
                    'value' => $post->ID,
                    'compare' => 'LIKE',
                )
            )
        ));

        // Display linked products
        echo '<div class="12gm-lms-product-links">';
        echo '<h4>' . __('Linked WooCommerce Products', '12gm-lms') . '</h4>';

        if (!empty($products)) {
            echo '<ul>';
            foreach ($products as $product) {
                echo '<li><a href="' . get_edit_post_link($product->ID) . '">' . esc_html($product->post_title) . '</a></li>';
            }
            echo '</ul>';
        } else {
            echo '<p>' . __('No products linked to this course.', '12gm-lms') . '</p>';
        }

        if (!empty($products)) {
            echo '<p>' . __('Purchase of these products will grant access to this course.', '12gm-lms') . '</p>';
        }

        echo '<p><a href="' . admin_url('edit.php?post_type=product') . '" class="button">' . __('Manage Products', '12gm-lms') . '</a></p>';
        echo '</div>';

        // Manual enrollment
        echo '<div class="12gm-lms-manual-enrollment">';
        echo '<h4>' . __('Manual Enrollment', '12gm-lms') . '</h4>';
        echo '<p>' . __('To manually enroll or manage student access, use the Student Access page.', '12gm-lms') . '</p>';
        echo '<p><a href="' . admin_url('admin.php?page=12gm-lms-user-access') . '" class="button">' . __('Manage Student Access', '12gm-lms') . '</a></p>';
        echo '</div>';
    }

    /**
     * Save the course meta box data.
     *
     * @since    1.0.0
     * @param    int        $post_id    The post ID.
     * @param    WP_Post    $post       The post object.
     */
    public function save_meta_boxes($post_id, $post)
    {
        // Check if we're saving a course
        if ($post->post_type !== '12gm_course') {
            return;
        }

        // Check for course lessons nonce
        if (!isset($_POST['12gm_lms_course_lessons_nonce']) || !wp_verify_nonce($_POST['12gm_lms_course_lessons_nonce'], '12gm_lms_course_lessons')) {
            return;
        }

        // Check for access nonce
        if (!isset($_POST['12gm_lms_course_access_nonce']) || !wp_verify_nonce($_POST['12gm_lms_course_access_nonce'], '12gm_lms_course_access')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Process course lessons
        // Get current lessons
        $current_lessons = get_posts(array(
            'post_type' => '12gm_lesson',
            'numberposts' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => '12gm_course_cat',
                    'field' => 'slug',
                    'terms' => 'course-' . $post_id,
                )
            )
        ));

        // Setup course term if it doesn't exist
        $term_slug = 'course-' . $post_id;
        $term = get_term_by('slug', $term_slug, '12gm_course_cat');

        if (!$term) {
            wp_insert_term(
                get_the_title($post_id),
                '12gm_course_cat',
                array(
                    'slug' => $term_slug,
                    'description' => sprintf(__('Lessons for course: %s', '12gm-lms'), get_the_title($post_id)),
                )
            );
        }

        // Update lesson group orders
        if (isset($_POST['12gm_lms_lesson_group_order']) && is_array($_POST['12gm_lms_lesson_group_order'])) {
            foreach ($_POST['12gm_lms_lesson_group_order'] as $lesson_id => $group_order) {
                // Get the lesson's group
                $lesson_groups = wp_get_post_terms($lesson_id, 'lesson_group');
                $group_id = !empty($lesson_groups) ? $lesson_groups[0]->term_id : 'ungrouped';

                // Store group order as meta
                update_post_meta($lesson_id, '_lesson_group_order', intval($group_order));

                // Calculate global menu_order based on group and group order
                // This ensures proper ordering in queries
                $global_order = $this->calculate_global_lesson_order($lesson_id, $group_id, intval($group_order));

                wp_update_post(array(
                    'ID' => $lesson_id,
                    'menu_order' => $global_order,
                ));
            }
        }

        // Update lesson groups
        if (isset($_POST['12gm_lms_lesson_groups']) && is_array($_POST['12gm_lms_lesson_groups'])) {
            foreach ($_POST['12gm_lms_lesson_groups'] as $lesson_id => $group_id) {
                if (empty($group_id)) {
                    // Remove group assignment
                    wp_set_post_terms($lesson_id, array(), 'lesson_group');
                } else {
                    // Set the selected group
                    wp_set_post_terms($lesson_id, array(intval($group_id)), 'lesson_group');
                }
            }
        }

        // Add new lessons to the course
        if (isset($_POST['12gm_lms_lessons_to_add']) && is_array($_POST['12gm_lms_lessons_to_add'])) {
            $order = count($current_lessons) + 1;

            foreach ($_POST['12gm_lms_lessons_to_add'] as $lesson_id) {
                wp_set_object_terms($lesson_id, $term_slug, '12gm_course_cat', true);

                // Set the menu order
                wp_update_post(array(
                    'ID' => $lesson_id,
                    'menu_order' => $order++,
                ));
            }
        }
    }

    /**
     * Calculate global lesson order based on group and group order.
     *
     * @param int $lesson_id Lesson ID
     * @param mixed $group_id Group ID or 'ungrouped'
     * @param int $group_order Order within the group
     * @return int Global menu order
     */
    private function calculate_global_lesson_order($lesson_id, $group_id, $group_order)
    {
        // Get all lesson groups and sort them
        $lesson_groups = get_terms(array(
            'taxonomy' => 'lesson_group',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ));

        $group_index = 0;
        $lessons_per_group = 1000; // Spacing between groups

        if ($group_id === 'ungrouped') {
            // Ungrouped lessons come last
            $group_index = count($lesson_groups);
        } else {
            // Find group index
            foreach ($lesson_groups as $index => $group) {
                if ($group->term_id == $group_id) {
                    $group_index = $index;
                    break;
                }
            }
        }

        // Calculate global order: (group_index * 1000) + group_order
        return ($group_index * $lessons_per_group) + $group_order;
    }

    /**
     * Add lesson-course assignment metabox.
     *
     * @since    1.0.0
     */
    public function add_lesson_course_metabox()
    {
        add_meta_box(
            '12gm_lms_lesson_course',
            __('Assign to Course', '12gm-lms'),
            array($this, 'render_lesson_course_metabox'),
            '12gm_lesson',
            'side',
            'default'
        );
    }

    /**
     * Render the lesson-course assignment metabox.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_lesson_course_metabox($post)
    {
        wp_nonce_field('12gm_lms_lesson_course', '12gm_lms_lesson_course_nonce');

        // Get all course terms
        $course_terms = get_terms(array(
            'taxonomy' => '12gm_course_cat',
            'hide_empty' => false,
        ));

        // Get current term
        $current_terms = wp_get_object_terms($post->ID, '12gm_course_cat', array('fields' => 'slugs'));

        echo '<p>' . __('Assign this lesson to a course:', '12gm-lms') . '</p>';

        if (empty($course_terms)) {
            echo '<p>' . __('No courses found. Create a course first.', '12gm-lms') . '</p>';
            echo '<p><a href="' . admin_url('post-new.php?post_type=12gm_course') . '" class="button">' . __('Create New Course', '12gm-lms') . '</a></p>';
            return;
        }

        echo '<select name="12gm_lms_lesson_course" style="width:100%;">';
        echo '<option value="">' . __('-- Select a Course --', '12gm-lms') . '</option>';

        foreach ($course_terms as $term) {
            $selected = in_array($term->slug, $current_terms) ? 'selected="selected"' : '';
            echo '<option value="' . esc_attr($term->slug) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
        }

        echo '</select>';
    }

    /**
     * Save the lesson-course assignment.
     *
     * @since    1.0.0
     * @param    int        $post_id    The post ID.
     * @param    WP_Post    $post       The post object.
     */
    public function save_lesson_course_metabox($post_id, $post)
    {
        // Check if we're saving a lesson
        if ($post->post_type !== '12gm_lesson') {
            return;
        }

        // Check for nonce
        if (!isset($_POST['12gm_lms_lesson_course_nonce']) || !wp_verify_nonce($_POST['12gm_lms_lesson_course_nonce'], '12gm_lms_lesson_course')) {
            return;
        }

        // If this is an autosave, our form has not been submitted
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Store current menu order BEFORE making any changes
        $current_menu_order = get_post_field('menu_order', $post_id);

        // Save the course assignment
        if (isset($_POST['12gm_lms_lesson_course'])) {
            $course_term = sanitize_text_field($_POST['12gm_lms_lesson_course']);

            // Get current course assignment
            $current_terms = wp_get_object_terms($post_id, '12gm_course_cat', array('fields' => 'slugs'));
            $current_course = !empty($current_terms) ? $current_terms[0] : '';

            // Only update menu_order if the course assignment actually changed
            if ($current_course !== $course_term) {
                if (empty($course_term)) {
                    wp_set_object_terms($post_id, array(), '12gm_course_cat');
                    // Don't update menu_order when removing from course
                    // Remove course ID meta as well
                    delete_post_meta($post_id, '_12gm_course_id');
                } else {
                    wp_set_object_terms($post_id, $course_term, '12gm_course_cat');

                    // Only set menu order for newly assigned lessons (lessons without existing order)
                    if (empty($current_menu_order) || $current_menu_order == 0) {
                        // Get the last menu order of lessons in this course
                        $course_lessons = get_posts(array(
                            'post_type' => '12gm_lesson',
                            'numberposts' => -1,
                            'orderby' => 'menu_order',
                            'order' => 'DESC',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => '12gm_course_cat',
                                    'field' => 'slug',
                                    'terms' => $course_term,
                                )
                            )
                        ));

                        // Set menu order only for newly assigned lessons
                        if (empty($course_lessons)) {
                            $menu_order = 1;
                        } else {
                            $menu_order = $course_lessons[0]->menu_order + 1;
                        }

                        wp_update_post(array(
                            'ID' => $post_id,
                            'menu_order' => $menu_order,
                        ));
                    } else {
                        // Preserve existing menu order for lessons that already have an order
                        wp_update_post(array(
                            'ID' => $post_id,
                            'menu_order' => $current_menu_order,
                        ));
                    }
                    // Store parent course post ID as meta for pretty permalinks
                    if (strpos($course_term, 'course-') === 0) {
                        $course_id = intval(str_replace('course-', '', $course_term));
                        update_post_meta($post_id, '_12gm_course_id', $course_id);
                    }
                }
            } else {
                // Course assignment didn't change, preserve existing menu order
                if ($current_menu_order > 0) {
                    wp_update_post(array(
                        'ID' => $post_id,
                        'menu_order' => $current_menu_order,
                    ));
                }
            }
        }
    }

    /**
     * Add user access management page.
     *
     * @since    1.0.0
     */
    public function add_user_access_page()
    {
        add_submenu_page(
            '12gm-lms',
            __('Student Access', '12gm-lms'),
            __('Student Access', '12gm-lms'),
            'manage_options',
            '12gm-lms-user-access',
            array($this, 'display_user_access_page')
        );
    }

    /**
     * Display the user access page.
     *
     * @since    1.0.0
     */
    public function display_user_access_page()
    {
        // Process form submissions
        if (isset($_POST['12gm_lms_user_access_nonce']) && wp_verify_nonce($_POST['12gm_lms_user_access_nonce'], '12gm_lms_user_access')) {
            if (isset($_POST['action']) && $_POST['action'] === 'grant' && isset($_POST['user_id']) && isset($_POST['course_id'])) {
                $user_id = intval($_POST['user_id']);
                $course_id = intval($_POST['course_id']);

                $this->woocommerce->grant_course_access($user_id, $course_id);

                echo '<div class="notice notice-success is-dismissible"><p>' . __('Access granted successfully!', '12gm-lms') . '</p></div>';
            } elseif (isset($_POST['action']) && $_POST['action'] === 'revoke' && isset($_POST['user_id']) && isset($_POST['course_id'])) {
                $user_id = intval($_POST['user_id']);
                $course_id = intval($_POST['course_id']);

                $this->woocommerce->revoke_course_access($user_id, $course_id);

                echo '<div class="notice notice-success is-dismissible"><p>' . __('Access revoked successfully!', '12gm-lms') . '</p></div>';
            }
        }

        // Get all users with the student role
        $student_users = get_users(array(
            'role__in' => array('subscriber', 'customer', 'administrator'),
        ));

        // Get all courses
        $courses = get_posts(array(
            'post_type' => '12gm_course',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        // Display the page content
        include TWELVEGM_LMS_PLUGIN_DIR . 'templates/admin/user-access.php';
    }

    /**
     * AJAX handler for enrolling a user.
     *
     * @since    1.0.0
     */
    public function ajax_enroll_user()
    {
        // Check for nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], '12gm_lms_ajax_nonce')) {
            wp_send_json_error('Invalid nonce');
            wp_die();
        }

        // Check for required fields
        if (!isset($_POST['user_id']) || !isset($_POST['course_id'])) {
            wp_send_json_error('Missing required fields');
            wp_die();
        }

        $user_id = intval($_POST['user_id']);
        $course_id = intval($_POST['course_id']);

        // Enroll the user
        $this->woocommerce->grant_course_access($user_id, $course_id);

        wp_send_json_success('User enrolled successfully');
        wp_die();
    }

    /**
     * AJAX handler for unenrolling a user.
     *
     * @since    1.0.0
     */
    public function ajax_unenroll_user()
    {
        // Check for nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], '12gm_lms_ajax_nonce')) {
            wp_send_json_error('Invalid nonce');
            wp_die();
        }

        // Check for required fields
        if (!isset($_POST['user_id']) || !isset($_POST['course_id'])) {
            wp_send_json_error('Missing required fields');
            wp_die();
        }

        $user_id = intval($_POST['user_id']);
        $course_id = intval($_POST['course_id']);

        // Unenroll the user
        $this->woocommerce->revoke_course_access($user_id, $course_id);

        wp_send_json_success('User unenrolled successfully');
        wp_die();
    }

    /**
     * Get all students who have access to at least one course.
     *
     * @since    1.0.0
     * @return   array    Array of user IDs who have access to at least one course.
     */
    private function get_students_with_course_access()
    {
        global $wpdb;

        $meta_key = '12gm_lms_enrolled_courses';

        $results = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s",
                $meta_key
            )
        );

        return $results;
    }

    /**
     * Handle the flush rewrite rules request.
     *
     * @since    1.0.0
     */
    public function handle_flush_rewrite_rules()
    {
        if (isset($_GET['page']) && $_GET['page'] === '12gm-lms' && isset($_GET['action']) && $_GET['action'] === 'flush_rules') {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.', '12gm-lms'));
            }

            // Check nonce
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], '12gm_lms_flush_rules')) {
                wp_die(__('Security check failed.', '12gm-lms'));
            }

            // Set the flag to flush rewrite rules
            update_option('12gm_lms_flush_rewrite_rules', true);

            // Redirect back to dashboard
            wp_redirect(admin_url('admin.php?page=12gm-lms&flushed=1'));
            exit;
        }

        // Show admin notice after flushing rewrite rules
        if (isset($_GET['page']) && $_GET['page'] === '12gm-lms' && isset($_GET['flushed'])) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Permalinks have been flushed successfully.', '12gm-lms') . '</p></div>';
            });
        }
    }
}
