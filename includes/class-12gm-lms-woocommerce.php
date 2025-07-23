<?php
/**
 * WooCommerce integration for the plugin.
 *
 * @since      1.0.0
 */
class TwelveGM_LMS_WooCommerce {

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function run() {
        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            return;
        }

        // Add metabox to WooCommerce products to link courses
        add_action('add_meta_boxes', array($this, 'add_course_product_metabox'));
        add_action('save_post', array($this, 'save_course_product_metabox'), 10, 2);
        
        // Grant access to courses when products are purchased
        add_action('woocommerce_order_status_completed', array($this, 'process_completed_order'));
    }

    /**
     * Check if WooCommerce is active.
     *
     * @since    1.0.0
     * @return   bool    True if WooCommerce is active, false otherwise.
     */
    private function is_woocommerce_active() {
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }

    /**
     * Add metabox to WooCommerce products to link courses.
     *
     * @since    1.0.0
     */
    public function add_course_product_metabox() {
        add_meta_box(
            '12gm_lms_course_product',
            __('LMS Course Access', '12gm-lms'),
            array($this, 'render_course_product_metabox'),
            'product',
            'side',
            'default'
        );
    }

    /**
     * Render the metabox content.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_course_product_metabox($post) {
        // Add nonce for security
        wp_nonce_field('12gm_lms_course_product', '12gm_lms_course_product_nonce');

        // Get the current value
        $linked_courses = get_post_meta($post->ID, '_12gm_lms_linked_courses', true);
        if (!is_array($linked_courses)) {
            $linked_courses = array();
        }

        // Get all courses
        $courses = get_posts(array(
            'post_type' => '12gm_course',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        echo '<p>' . __('Select courses to grant access when this product is purchased:', '12gm-lms') . '</p>';
        
        if (empty($courses)) {
            echo '<p>' . __('No courses found.', '12gm-lms') . '</p>';
            return;
        }

        echo '<div style="max-height: 200px; overflow-y: auto;">';
        foreach ($courses as $course) {
            $checked = in_array($course->ID, $linked_courses) ? 'checked="checked"' : '';
            echo '<label style="display: block; margin-bottom: 5px;">';
            echo '<input type="checkbox" name="12gm_lms_linked_courses[]" value="' . esc_attr($course->ID) . '" ' . $checked . '>';
            echo esc_html($course->post_title);
            echo '</label>';
        }
        echo '</div>';
    }

    /**
     * Save the metabox data.
     *
     * @since    1.0.0
     * @param    int        $post_id    The post ID.
     * @param    WP_Post    $post       The post object.
     */
    public function save_course_product_metabox($post_id, $post) {
        // Check if our nonce is set.
        if (!isset($_POST['12gm_lms_course_product_nonce'])) {
            return;
        }

        // Verify the nonce.
        if (!wp_verify_nonce($_POST['12gm_lms_course_product_nonce'], '12gm_lms_course_product')) {
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

        // Save the linked courses.
        $linked_courses = isset($_POST['12gm_lms_linked_courses']) ? array_map('intval', $_POST['12gm_lms_linked_courses']) : array();
        update_post_meta($post_id, '_12gm_lms_linked_courses', $linked_courses);
    }

    /**
     * Process completed orders to grant access to courses.
     *
     * @since    1.0.0
     * @param    int    $order_id    The order ID.
     */
    public function process_completed_order($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        
        // If no user is associated with the order, exit
        if (!$user_id) {
            return;
        }
        
        // Loop through order items
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_id();
            $linked_courses = get_post_meta($product_id, '_12gm_lms_linked_courses', true);
            
            if (is_array($linked_courses) && !empty($linked_courses)) {
                foreach ($linked_courses as $course_id) {
                    // Grant access to the course
                    $this->grant_course_access($user_id, $course_id);
                }
            }
        }
    }
    
    /**
     * Grant access to a course for a user.
     *
     * @since    1.0.0
     * @param    int    $user_id     The user ID.
     * @param    int    $course_id   The course ID.
     */
    public function grant_course_access($user_id, $course_id) {
        // Get current user enrollments
        $enrollments = get_user_meta($user_id, '12gm_lms_enrolled_courses', true);
        if (!is_array($enrollments)) {
            $enrollments = array();
        }
        
        // Add course if not already enrolled
        if (!in_array($course_id, $enrollments)) {
            $enrollments[] = $course_id;
            update_user_meta($user_id, '12gm_lms_enrolled_courses', $enrollments);
            
            // Record the enrollment date
            add_user_meta($user_id, '12gm_lms_enrolled_' . $course_id, current_time('mysql'), true);
            
            // Log the enrollment (optional, for debugging)
            error_log('User #' . $user_id . ' enrolled in course #' . $course_id);
            
            // Fire action for other integrations
            do_action('12gm_lms_user_enrolled', $user_id, $course_id);
        }
    }
    
    /**
     * Revoke access to a course for a user.
     *
     * @since    1.0.0
     * @param    int    $user_id     The user ID.
     * @param    int    $course_id   The course ID.
     */
    public function revoke_course_access($user_id, $course_id) {
        // Get current user enrollments
        $enrollments = get_user_meta($user_id, '12gm_lms_enrolled_courses', true);
        if (!is_array($enrollments)) {
            return;
        }
        
        // Remove the course if enrolled
        if (($key = array_search($course_id, $enrollments)) !== false) {
            unset($enrollments[$key]);
            update_user_meta($user_id, '12gm_lms_enrolled_courses', array_values($enrollments));
            
            // Remove the enrollment date
            delete_user_meta($user_id, '12gm_lms_enrolled_' . $course_id);
            
            // Fire action for other integrations
            do_action('12gm_lms_user_unenrolled', $user_id, $course_id);
        }
    }
    
    /**
     * Check if a user has access to a course.
     *
     * @since    1.0.0
     * @param    int    $user_id     The user ID.
     * @param    int    $course_id   The course ID.
     * @return   bool   True if the user has access, false otherwise.
     */
    public function has_course_access($user_id, $course_id) {
        $enrollments = get_user_meta($user_id, '12gm_lms_enrolled_courses', true);
        if (!is_array($enrollments)) {
            return false;
        }
        
        return in_array($course_id, $enrollments);
    }
}