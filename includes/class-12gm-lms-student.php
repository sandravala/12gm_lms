<?php

/**
 * Student dashboard functionality.
 *
 * @since      1.0.0
 */
class TwelveGM_LMS_Student
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
        // Add shortcodes
        add_shortcode('12gm_lms_dashboard', [$this, 'render_dashboard_shortcode']);
        add_shortcode('12gm_lms_course', [$this, 'render_course_shortcode']);
        add_shortcode('12gm_lms_lesson', [$this, 'render_lesson_shortcode']);

        // Add AJAX handlers
        add_action('wp_ajax_12gm_lms_mark_lesson_complete', [$this, 'ajax_mark_lesson_complete']);

        // Course and lesson template redirects
        add_filter('single_template', [$this, 'load_custom_templates']);

        // Add rewrite rules for pretty URLs
        add_action('init', [$this, 'add_rewrite_rules']);

        // Add admin bar menu for students
        add_action('admin_bar_menu', [$this, 'add_admin_bar_menu'], 90);

        // Add body classes
        add_filter('body_class', ['TwelveGM_LMS_Template_Loader', 'add_body_classes']);

        // Add theme support
        add_action('after_setup_theme', ['TwelveGM_LMS_Template_Loader', 'add_theme_support']);
    }

    /**
     * Add rewrite rules for pretty URLs.
     *
     * @since    1.0.0
     */
    public function add_rewrite_rules()
    {
        add_rewrite_rule(
            '^courses/([^/]+)/lesson/([^/]+)/?$',
            'index.php?12gm_course=$matches[1]&12gm_lesson=$matches[2]',
            'top'
        );

        add_rewrite_tag('%12gm_course%', '([^&]+)');
        add_rewrite_tag('%12gm_lesson%', '([^&]+)');
    }

    /**
     * Load custom templates for courses and lessons.
     *
     * @since    1.0.0
     * @param    string    $template    Current template path.
     * @return   string    Modified template path.
     */
    public function load_custom_templates($template)
    {
        global $post;

        if (is_singular('12gm_course')) {
            $custom_template = TWELVEGM_LMS_PLUGIN_DIR . 'templates/public/single-course.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        if (is_singular('12gm_lesson')) {
            // Check if the user has access to this lesson
            if (! $this->user_can_access_lesson($post->ID)) {
                // Redirect to access denied page or login
                wp_redirect(home_url('/my-account/'));
                exit;
            }

            $custom_template = TWELVEGM_LMS_PLUGIN_DIR . 'templates/public/single-lesson.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        return $template;
    }

    /**
     * Check if a user can access a lesson.
     *
     * @since    1.0.0
     * @param    int    $lesson_id    The lesson ID.
     * @return   bool   True if the user can access the lesson, false otherwise.
     */
    public function user_can_access_lesson($lesson_id)
    {
        // If user is admin or editor, always allow access
        if (current_user_can('edit_posts')) {
            return true;
        }

        // Get the course for this lesson
        $course_id = $this->get_course_for_lesson($lesson_id);

        if (! $course_id) {
            return false; // Lesson is not assigned to any course
        }

        // Check if user has access to the course
        $user_id = get_current_user_id();
        if (! $user_id) {
            return false; // User is not logged in
        }

        return $this->woocommerce->has_course_access($user_id, $course_id);
    }

    /**
     * Get the course ID for a lesson.
     *
     * @since    1.0.0
     * @param    int    $lesson_id    The lesson ID.
     * @return   int    The course ID or 0 if not found.
     */
    public function get_course_for_lesson($lesson_id)
    {
        $terms = wp_get_object_terms($lesson_id, '12gm_course_cat', ['fields' => 'slugs']);

        if (empty($terms)) {
            return 0;
        }

        // Extract course ID from term slug (format: 'course-ID')
        $term_slug = $terms[0];
        $course_id = str_replace('course-', '', $term_slug);

        return intval($course_id);
    }

    /**
     * Render the student dashboard.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string   HTML output.
     */
    public function render_dashboard_shortcode($atts)
    {
        $atts = shortcode_atts([
            'title' => __('My Courses', '12gm-lms'),
        ], $atts, '12gm_lms_dashboard');

        if (! is_user_logged_in()) {
            return $this->render_login_message();
        }

        $user_id          = get_current_user_id();
        $enrolled_courses = [];

        // If user is admin or editor, always allow access
        if (current_user_can('edit_posts')) {
            $all_courses = get_posts(['post_type' => '12gm_course']);
            foreach ($all_courses as $course) {
                $enrolled_courses[] += $course->ID;
            }
        } else {
            $enrolled_courses = get_user_meta($user_id, '12gm_lms_enrolled_courses', true);
        }

        if (! is_array($enrolled_courses) || empty($enrolled_courses)) {
            return $this->render_no_courses_message();
        }

        // Get course details
        $courses = [];
        foreach ($enrolled_courses as $course_id) {
            $course = get_post($course_id);
            if (! $course || $course->post_status !== 'publish') {
                continue;
            }

            // Get progress data
            $progress = $this->get_course_progress($user_id, $course_id);

            $courses[] = [
                'id'        => $course_id,
                'title'     => $course->post_title,
                'excerpt'   => $course->post_excerpt,
                'link'      => get_permalink($course_id),
                'thumbnail' => get_the_post_thumbnail_url($course_id, 'medium'),
                'progress'  => $progress,
            ];
        }

        return TwelveGM_LMS_Template_Loader::load_template('dashboard.php', [
            'courses' => $courses,
            'atts'    => $atts,
        ]);
    }

    /**
     * Render the login message.
     *
     * @since    1.0.0
     * @return   string   HTML output.
     */
    private function render_login_message()
    {
        ob_start();
?>
        <div class="12gm-lms-login-message">
            <h3><?php _e('Please Log In', '12gm-lms'); ?></h3>
            <p><?php _e('You need to be logged in to view your courses.', '12gm-lms'); ?></p>
            <p><a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="button"><?php _e('Log In', '12gm-lms'); ?></a></p>
        </div>
    <?php
        return ob_get_clean();
    }

    /**
     * Render the no courses message.
     *
     * @since    1.0.0
     * @return   string   HTML output.
     */
    private function render_no_courses_message()
    {
        ob_start();
    ?>
        <div class="12gm-lms-no-courses-message">
            <h3><?php _e('No Courses Found', '12gm-lms'); ?></h3>
            <p><?php _e('You are not enrolled in any courses yet.', '12gm-lms'); ?></p>
            <p><a href="<?php echo esc_url(home_url('/shop/')); ?>" class="button"><?php _e('Browse Courses', '12gm-lms'); ?></a></p>
        </div>
    <?php
        return ob_get_clean();
    }

    /**
     * Get course progress for a user.
     *
     * @since    1.0.0
     * @param    int    $user_id     The user ID.
     * @param    int    $course_id   The course ID.
     * @return   array  Course progress data.
     */
    public function get_course_progress($user_id, $course_id)
    {
        // Get all lessons for the course
        $lessons       = $this->get_course_lessons($course_id);
        $total_lessons = count($lessons);

        if ($total_lessons === 0) {
            return [
                'percentage' => 0,
                'completed'  => 0,
                'total'      => 0,
            ];
        }

        // Get completed lessons
        $completed_lessons = $this->get_completed_lessons($user_id, $course_id);
        $completed_count   = count($completed_lessons);

        // Calculate percentage
        $percentage = ($total_lessons > 0) ? floor(($completed_count / $total_lessons) * 100) : 0;

        return [
            'percentage' => $percentage,
            'completed'  => $completed_count,
            'total'      => $total_lessons,
        ];
    }

    /**
     * Get all lessons for a course.
     *
     * @since    1.0.0
     * @param    int    $course_id   The course ID.
     * @return   array  Array of lesson post objects.
     */
    public function get_course_lessons($course_id)
    {
        return get_posts([
            'post_type'   => '12gm_lesson',
            'numberposts' => -1,
            'orderby'     => 'menu_order',
            'order'       => 'ASC',
            'tax_query'   => [
                [
                    'taxonomy' => '12gm_course_cat',
                    'field'    => 'slug',
                    'terms'    => 'course-' . $course_id,
                ],
            ],
        ]);
    }

    /**
     * Get completed lessons for a user in a course.
     *
     * @since    1.0.0
     * @param    int    $user_id     The user ID.
     * @param    int    $course_id   The course ID.
     * @return   array  Array of completed lesson IDs.
     */
    public function get_completed_lessons($user_id, $course_id)
    {
        $completed = get_user_meta($user_id, '12gm_lms_completed_lessons_' . $course_id, true);

        if (! is_array($completed)) {
            return [];
        }

        return $completed;
    }

    /**
     * Render the course content.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string   HTML output.
     */
    public function render_course_shortcode($atts)
    {
        $atts = shortcode_atts([
            'id' => 0,
        ], $atts, '12gm_lms_course');

        if (! $atts['id']) {
            // Try to get the current course ID
            global $post;
            if ($post && $post->post_type === '12gm_course') {
                $atts['id'] = $post->ID;
            }
        }

        if (! $atts['id']) {
            return __('Course ID is required.', '12gm-lms');
        }

        $course_id = intval($atts['id']);
        $course    = get_post($course_id);

        if (! $course || $course->post_status !== 'publish') {
            return __('Course not found.', '12gm-lms');
        }

        $user_id = get_current_user_id();

        // Check if user has access
        // Check if user has access
        if (! current_user_can('edit_posts') && (! $user_id || ! $this->woocommerce->has_course_access($user_id, $course_id))) {
            return $this->render_course_access_denied($course_id);
        }

        // Get lessons
        $lessons = $this->get_course_lessons($course_id);

        // Get user progress
        $progress          = $this->get_course_progress($user_id, $course_id);
        $completed_lessons = $this->get_completed_lessons($user_id, $course_id);

        return TwelveGM_LMS_Template_Loader::load_template('course-content.php', [
            'course'            => $course,
            'lessons'           => $lessons,
            'progress'          => $progress,
            'completed_lessons' => $completed_lessons,
        ]);
    }

    /**
     * Render the course access denied message.
     *
     * @since    1.0.0
     * @param    int      $course_id   The course ID.
     * @return   string   HTML output.
     */
    private function render_course_access_denied($course_id)
    {
        // Check if this course is purchasable
        $products = get_posts([
            'post_type'   => 'product',
            'numberposts' => -1,
            'meta_query'  => [
                [
                    'key'     => '_12gm_lms_linked_courses',
                    'value'   => $course_id,
                    'compare' => 'LIKE',
                ],
            ],
        ]);

        ob_start();
    ?>
        <div class="12gm-lms-access-denied">
            <h3><?php _e('Access Denied', '12gm-lms'); ?></h3>
            <p><?php _e('You do not have access to this course.', '12gm-lms'); ?></p>

            <?php if (! empty($products)): ?>
                <p><?php _e('You can purchase this course by clicking one of the links below:', '12gm-lms'); ?></p>
                <ul class="12gm-lms-product-links">
                    <?php foreach ($products as $product): ?>
                        <li>
                            <a href="<?php echo esc_url(get_permalink($product->ID)); ?>" class="button">
                                <?php echo esc_html($product->post_title); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p><?php _e('Please contact the site administrator for access.', '12gm-lms'); ?></p>
            <?php endif; ?>
        </div>
<?php
        return ob_get_clean();
    }

    /**
     * Render the lesson content.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string   HTML output.
     */
    public function render_lesson_shortcode($atts)
    {
        $atts = shortcode_atts([
            'id' => 0,
        ], $atts, '12gm_lms_lesson');

        if (! $atts['id']) {
            // Try to get the current lesson ID
            global $post;
            if ($post && $post->post_type === '12gm_lesson') {
                $atts['id'] = $post->ID;
            }
        }

        if (! $atts['id']) {
            return __('Lesson ID is required.', '12gm-lms');
        }

        $lesson_id = intval($atts['id']);
        $lesson    = get_post($lesson_id);

        if (! $lesson || $lesson->post_status !== 'publish') {
            return __('Lesson not found.', '12gm-lms');
        }

        // Check if user can access this lesson
        if (! $this->user_can_access_lesson($lesson_id)) {
            return __('You do not have access to this lesson.', '12gm-lms');
        }

        // Get course information
        $course_id = $this->get_course_for_lesson($lesson_id);
        $course    = get_post($course_id);

        // Get all lessons in the course
        $course_lessons = $this->get_course_lessons($course_id);

// Initialize navigation variables
$current_position = 0;
$prev_lesson = null;
$next_lesson = null;

// Find current lesson index in the ordered lessons array
foreach ($course_lessons as $i => $course_lesson) {
    if ($course_lesson->ID == $lesson_id) {
        $current_position = $i;

        // Previous lesson (handles group transitions automatically)
        if ($i > 0) {
            $prev_lesson = $course_lessons[$i - 1];
        }

        // Next lesson (handles group transitions automatically)
        if ($i < count($course_lessons) - 1) {
            $next_lesson = $course_lessons[$i + 1];
        }

        break;
    }
}

// Add group transition information for template
$is_moving_to_new_group = false;
$next_group_name = '';

if ($next_lesson) {
    $current_lesson_groups = wp_get_post_terms($lesson_id, 'lesson_group');
    $next_lesson_groups = wp_get_post_terms($next_lesson->ID, 'lesson_group');
    
    $current_group_id = !empty($current_lesson_groups) ? $current_lesson_groups[0]->term_id : 'ungrouped';
    $next_group_id = !empty($next_lesson_groups) ? $next_lesson_groups[0]->term_id : 'ungrouped';
    
    if ($current_group_id !== $next_group_id) {
        $is_moving_to_new_group = true;
        $next_group_name = !empty($next_lesson_groups) ? $next_lesson_groups[0]->name : __('Other Lessons', '12gm-lms');
    }
}

        // Check if user has completed this lesson
        $user_id           = get_current_user_id();
        $completed_lessons = $this->get_completed_lessons($user_id, $course_id);
        $is_completed      = in_array($lesson_id, $completed_lessons);

return TwelveGM_LMS_Template_Loader::load_template('lesson-content.php', [
    'lesson'                 => $lesson,
    'course'                 => $course,
    'course_id'              => $course_id,
    'course_lessons'         => $course_lessons,
    'current_position'       => $current_position,
    'prev_lesson'            => $prev_lesson,
    'next_lesson'            => $next_lesson,
    'user_id'                => $user_id,
    'completed_lessons'      => $completed_lessons,
    'is_completed'           => $is_completed,
    'is_moving_to_new_group' => $is_moving_to_new_group,
    'next_group_name'        => $next_group_name,
]);
    }

    /**
     * AJAX handler for marking a lesson as complete.
     *
     * @since    1.0.0
     */
    public function ajax_mark_lesson_complete()
    {
        // Check nonce
        if (! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], '12gm_lms_lesson_complete')) {
            wp_send_json_error('Invalid nonce');
            wp_die();
        }

        // Check if user is logged in
        if (! is_user_logged_in()) {
            wp_send_json_error('You must be logged in');
            wp_die();
        }

        // Get lesson ID
        if (! isset($_POST['lesson_id'])) {
            wp_send_json_error('Lesson ID is required');
            wp_die();
        }

        $lesson_id = intval($_POST['lesson_id']);
        $user_id   = get_current_user_id();
        $course_id = $this->get_course_for_lesson($lesson_id);

        if (! $course_id) {
            wp_send_json_error('Lesson is not assigned to a course');
            wp_die();
        }

        // Check if user has access
        if (! $this->woocommerce->has_course_access($user_id, $course_id)) {
            wp_send_json_error('You do not have access to this course');
            wp_die();
        }

        // Mark lesson as complete
        $completed_lessons = $this->get_completed_lessons($user_id, $course_id);

        if (! in_array($lesson_id, $completed_lessons)) {
            $completed_lessons[] = $lesson_id;
            update_user_meta($user_id, '12gm_lms_completed_lessons_' . $course_id, $completed_lessons);

            // Record completion time
            update_user_meta($user_id, '12gm_lms_lesson_' . $lesson_id . '_completed', current_time('mysql'));

            // Update progress tracking
            $this->update_user_progress($user_id, $course_id);
        }

        // Get updated progress
        $progress = $this->get_course_progress($user_id, $course_id);

        wp_send_json_success([
            'message'  => __('Lesson marked as complete', '12gm-lms'),
            'progress' => $progress,
        ]);

        wp_die();
    }

    /**
     * Update the user progress tracking.
     *
     * @since    1.0.0
     * @param    int    $user_id     The user ID.
     * @param    int    $course_id   The course ID.
     */
    private function update_user_progress($user_id, $course_id)
    {
        // Get progress data
        $progress = $this->get_course_progress($user_id, $course_id);

        // Check if user has completed the course
        if ($progress['percentage'] === 100) {
            // Record course completion
            update_user_meta($user_id, '12gm_lms_course_' . $course_id . '_completed', current_time('mysql'));

            // Fire action for other integrations
            do_action('12gm_lms_course_completed', $user_id, $course_id);
        }
    }

    /**
     * Add a menu to the admin bar for students to access their courses.
     *
     * @since    1.0.0
     * @param    WP_Admin_Bar    $wp_admin_bar    Admin bar object.
     */
    public function add_admin_bar_menu($wp_admin_bar)
    {
        // Only show for logged-in users
        if (! is_user_logged_in()) {
            return;
        }

        $user_id          = get_current_user_id();
        $enrolled_courses = get_user_meta($user_id, '12gm_lms_enrolled_courses', true);

        // If user has no courses and isn't admin, don't show anything
        if (empty($enrolled_courses) && ! current_user_can('edit_posts')) {
            return;
        }

        // Get dashboard page URL
        $dashboard_url = get_permalink(get_option('12gm_lms_dashboard_page_id'));

        // Add the parent menu
        $wp_admin_bar->add_node([
            'id'    => '12gm-lms-courses',
            'title' => '<span class="ab-icon dashicons dashicons-welcome-learn-more"></span>' . __('My Courses', '12gm-lms'),
            'href'  => $dashboard_url,
        ]);

        // Add dashboard submenu
        $wp_admin_bar->add_node([
            'id'     => '12gm-lms-dashboard',
            'parent' => '12gm-lms-courses',
            'title'  => __('Learning Dashboard', '12gm-lms'),
            'href'   => $dashboard_url,
        ]);

        // Add courses as submenu items
        if (! empty($enrolled_courses) && is_array($enrolled_courses)) {
            $wp_admin_bar->add_node([
                'id'     => '12gm-lms-enrolled',
                'parent' => '12gm-lms-courses',
                'title'  => __('Enrolled Courses', '12gm-lms'),
                'href'   => $dashboard_url,
                'meta'   => [
                    'class' => '12gm-lms-menu-header',
                ],
            ]);

            // Get latest 5 courses that the user is enrolled in
            $courses_to_show = array_slice($enrolled_courses, 0, 5);

            foreach ($courses_to_show as $course_id) {
                $course = get_post($course_id);
                if (! $course || $course->post_status !== 'publish') {
                    continue;
                }

                // Get progress data
                $progress = $this->get_course_progress($user_id, $course_id);

                // Add course to menu
                $wp_admin_bar->add_node([
                    'id'     => '12gm-lms-course-' . $course_id,
                    'parent' => '12gm-lms-courses',
                    'title'  => esc_html($course->post_title) . ' <span class="12gm-lms-menu-progress">' . $progress['percentage'] . '%</span>',
                    'href'   => get_permalink($course_id),
                ]);
            }

            // If there are more courses, add a "View All" link
            if (count($enrolled_courses) > 5) {
                $wp_admin_bar->add_node([
                    'id'     => '12gm-lms-view-all',
                    'parent' => '12gm-lms-courses',
                    'title'  => __('View All Courses', '12gm-lms'),
                    'href'   => $dashboard_url,
                ]);
            }
        }

        // Add admin links for staff
        if (current_user_can('edit_posts')) {
            $wp_admin_bar->add_node([
                'id'     => '12gm-lms-admin',
                'parent' => '12gm-lms-courses',
                'title'  => __('LMS Admin', '12gm-lms'),
                'href'   => admin_url('admin.php?page=12gm-lms'),
                'meta'   => [
                    'class' => '12gm-lms-menu-header',
                ],
            ]);

            $wp_admin_bar->add_node([
                'id'     => '12gm-lms-admin-dashboard',
                'parent' => '12gm-lms-courses',
                'title'  => __('LMS Dashboard', '12gm-lms'),
                'href'   => admin_url('admin.php?page=12gm-lms'),
            ]);

            $wp_admin_bar->add_node([
                'id'     => '12gm-lms-admin-courses',
                'parent' => '12gm-lms-courses',
                'title'  => __('Manage Courses', '12gm-lms'),
                'href'   => admin_url('edit.php?post_type=12gm_course'),
            ]);

            $wp_admin_bar->add_node([
                'id'     => '12gm-lms-admin-lessons',
                'parent' => '12gm-lms-courses',
                'title'  => __('Manage Lessons', '12gm-lms'),
                'href'   => admin_url('edit.php?post_type=12gm_lesson'),
            ]);

            $wp_admin_bar->add_node([
                'id'     => '12gm-lms-admin-students',
                'parent' => '12gm-lms-courses',
                'title'  => __('Manage Students', '12gm-lms'),
                'href'   => admin_url('admin.php?page=12gm-lms-user-access'),
            ]);
        }
    }
}
