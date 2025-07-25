<?php
/**
 * Enhanced WooCommerce integration with secure guest course purchase handling.
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
     * Process completed orders to grant access to courses.
     * Now handles both logged-in users and guests.
     *
     * @since    1.0.0
     * @param    int    $order_id    The order ID.
     */
    public function process_completed_order($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        $customer_email = $order->get_billing_email();
        
        // Validate we have at least an email
        if (!$customer_email) {
            error_log('12GM LMS: No customer email found for order #' . $order_id);
            return;
        }
        
        $course_products = $this->get_course_products_from_order($order);
        
        if (empty($course_products)) {
            return; // No course products in this order
        }
        
        if ($user_id) {
            // Existing user - grant access immediately
            $this->process_user_course_access($user_id, $course_products);
        } else {
            // Guest purchase - create account and grant access
            $user_id = $this->create_account_for_guest_purchase($customer_email, $order);
            if ($user_id && !is_wp_error($user_id)) {
                // Update the order to associate it with the new user
                $order->set_customer_id($user_id);
                $order->save();
                
                // Grant course access
                $this->process_user_course_access($user_id, $course_products);
                
                // Send welcome email with password reset link
                $this->send_welcome_email_with_password_reset($user_id, $course_products, $order_id);
                
                // Log the account creation
                error_log('12GM LMS: Auto-created account for guest purchase. User ID: ' . $user_id . ', Email: ' . $customer_email);
            } else {
                error_log('12GM LMS: Failed to create account for guest: ' . $customer_email . ' - ' . (is_wp_error($user_id) ? $user_id->get_error_message() : 'Unknown error'));
            }
        }
    }
    
    /**
     * Get course products from an order.
     *
     * @param WC_Order $order
     * @return array Array of course IDs grouped by product
     */
    private function get_course_products_from_order($order) {
        $course_products = array();
        
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();

            $linked_courses = get_post_meta($product_id, '_12gm_lms_linked_courses', true);
            
            if (is_array($linked_courses) && !empty($linked_courses)) {
                $course_products[$product_id] = array(
                    'product_name' => $item->get_name(),
                    'courses' => $linked_courses
                );
            }
        }
        
        return $course_products;
    }
    
    /**
     * Process course access for existing users.
     *
     * @param int $user_id
     * @param array $course_products
     */
    private function process_user_course_access($user_id, $course_products) {
        foreach ($course_products as $product_data) {
            foreach ($product_data['courses'] as $course_id) {
                $this->grant_course_access($user_id, $course_id);
            }
        }
    }
    
    /**
     * Create a user account for a guest purchase.
     *
     * @param string $email Customer email
     * @param WC_Order $order The order object
     * @return int|WP_Error User ID on success, WP_Error on failure
     */
    private function create_account_for_guest_purchase($email, $order) {
        // Check if user already exists
        if (email_exists($email)) {
            // User exists but didn't log in for purchase
            // Grant access to existing user
            $user = get_user_by('email', $email);
            return $user->ID;
        }
        
        // Generate username from email
        $username = $this->generate_unique_username($email);
        
        // Generate a random password (user will reset it)
        $password = wp_generate_password(12, true, true);
        
        // Get customer name from order
        $first_name = $order->get_billing_first_name();
        $last_name = $order->get_billing_last_name();
        $display_name = trim($first_name . ' ' . $last_name);
        if (empty($display_name)) {
            $display_name = $username;
        }
        
        // Create user
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        // Set additional user data
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $display_name,
        ));
        
        // Set user role (customer if WooCommerce is active, subscriber otherwise)
        $user = new WP_User($user_id);
        if (class_exists('WooCommerce')) {
            $user->set_role('customer');
        } else {
            $user->set_role('subscriber');
        }
        
        // Add meta to track this was auto-created for course purchase
        update_user_meta($user_id, '12gm_lms_auto_created', true);
        update_user_meta($user_id, '12gm_lms_auto_created_date', current_time('mysql'));
        update_user_meta($user_id, '12gm_lms_auto_created_order', $order->get_id());
        
        return $user_id;
    }
    
    /**
     * Generate a unique username from email.
     *
     * @param string $email
     * @return string
     */
    private function generate_unique_username($email) {
        // Get the part before @ symbol
        $username = sanitize_user(current(explode('@', $email)), true);
        
        // Remove any remaining dots or special characters
        $username = str_replace('.', '', $username);
        $username = preg_replace('/[^a-zA-Z0-9]/', '', $username);
        
        // Ensure it's not empty
        if (empty($username)) {
            $username = 'user';
        }
        
        // Ensure username is unique
        $original_username = $username;
        $counter = 1;
        while (username_exists($username)) {
            $username = $original_username . $counter;
            $counter++;
        }
        
        return $username;
    }
    
    /**
     * Send welcome email with password reset link.
     *
     * @param int $user_id
     * @param array $course_products
     * @param int $order_id
     */
        private function send_welcome_email_with_password_reset($user_id, $course_products, $order_id) {
        $user = get_user_by('id', $user_id);
        
        if (!$user) {
            return;
        }
        
        // Generate password reset key
        $reset_key = get_password_reset_key($user);
        if (is_wp_error($reset_key)) {
            error_log('12GM LMS: Failed to generate password reset key for user ' . $user_id);
            return;
        }
        
        // Build course list for email
        $course_list = '<ul>';
        $course_count = 0;
        foreach ($course_products as $product_data) {
            $course_list .= '<li>' . esc_html($product_data['product_name']) . '</li>';
            $course_count += count($product_data['courses']);
        }
        $course_list .= '</ul>';
        
        // Create password reset URL
        $reset_url = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login), 'login');
        
        // Get dashboard URL
        $dashboard_url = get_permalink(get_option('12gm_lms_dashboard_page_id'));
        
        $subject = 'Valio!!! Tavo 12GM paskyra sukurta!';

        $reset_button = '<a href="' . esc_url($reset_url) . '" style="display:inline-block;padding:10px 20px;background-color:#2196F3;color:#ffffff;text-decoration:none;border-radius:4px;">Nustatyti slaptažodį</a>';
        $dashboard_button = '<a href="' . esc_url($dashboard_url) . '" style="display:inline-block;padding:10px 20px;background-color:#4CAF50;color:#ffffff;text-decoration:none;border-radius:4px;">Atidaryti skydą</a>';

        $message = sprintf(
            'Labas %s,<br><br>' .
            'Ačiū už pirkimą! Tavo įsigyti produktai:<br><br>' .
            '%s<br><br>' .
            'Susikurk slaptažodį, kad galėtum prisijungti ir peržiūrėti savo kursus:<br><br>' .
            '%s<br><br>' .
            '%s<br><br>' .
            'Tavo vartotojo vardas: %s<br>' .
            'Tavo el. paštas: %s<br><br>' .
            'SVARBU: slaptažodžio sukūrimo nuoroda galioja 24 valandas. Jei reikia naujos nuorodos, galite ją susigeneruoti čia: <a href="%s">%s</a><br><br>' .
            'Iki pasimatymo,<br>12GM komanda',
            $user->display_name,
            $course_list,
            $reset_button,
            $dashboard_button,
            $user->user_login,
            $user->user_email,
            wp_lostpassword_url(),
            wp_lostpassword_url()
        );
        
        // Use WordPress/WooCommerce mailer
        if (class_exists('WC_Emails')) {
            $mailer = WC()->mailer();
            $wrapped_message = $mailer->wrap_message($subject, $message);
            $sent = $mailer->send($user->user_email, $subject, $wrapped_message);
        } else {
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $sent = wp_mail($user->user_email, $subject, $message, $headers);
        }
        
        if ($sent) {
            // Log successful email
            update_user_meta($user_id, '12gm_lms_welcome_email_sent', current_time('mysql'));
        } else {
            error_log('12GM LMS: Failed to send welcome email to ' . $user->user_email);
        }
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

        echo '<p>Pasirinkite kursus, prie kurių suteikti prieigą, kai šis produktas bus nupirktas:</p>';
        
        if (empty($courses)) {
            echo '<p>Kursų nerasta.</p>';
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