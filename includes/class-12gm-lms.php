<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 */
class TwelveGM_LMS {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      object    $post_types    Maintains the custom post types.
     */
    protected $post_types;

    /**
     * The WooCommerce integration class
     *
     * @since    1.0.0
     * @access   protected
     * @var      object    $woocommerce    Handles WooCommerce integration.
     */
    protected $woocommerce;

    /**
     * The admin class for dashboard functionality
     *
     * @since    1.0.0
     * @access   protected
     * @var      object    $admin    Handles admin dashboard.
     */
    protected $admin;

    /**
     * The student class for student dashboard functionality
     *
     * @since    1.0.0
     * @access   protected
     * @var      object    $student    Handles student dashboard.
     */
    protected $student;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        $this->post_types = new TwelveGM_LMS_Post_Types();
        $this->woocommerce = new TwelveGM_LMS_WooCommerce();
        $this->admin = new TwelveGM_LMS_Admin();
        $this->student = new TwelveGM_LMS_Student();
    }

    /**
     * Register all of the hooks related to the plugin functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_hooks() {
        // Add scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
        
        // Load text domain for translations
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    }

    /**
     * Run the plugin.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->post_types->run();
        $this->woocommerce->run();
        $this->admin->run();
        $this->student->run();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style('12gm-lms-admin', TWELVEGM_LMS_PLUGIN_URL . 'assets/css/admin.css', array(), TWELVEGM_LMS_VERSION, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_admin_scripts() {
        wp_enqueue_script('12gm-lms-admin', TWELVEGM_LMS_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), TWELVEGM_LMS_VERSION, false);
        
        // Add localized strings for admin JavaScript
        wp_localize_script('12gm-lms-admin', 'twelvegm_lms_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('12gm_lms_ajax_nonce'),
            'i18n' => array(
                'select_course' => __('-- Select a Course --', '12gm-lms'),
            )
        ));
    }

    /**
     * Register the stylesheets for the public area.
     *
     * @since    1.0.0
     */
    public function enqueue_public_styles() {
        wp_enqueue_style('12gm-lms-public', TWELVEGM_LMS_PLUGIN_URL . 'assets/css/public.css', array(), TWELVEGM_LMS_VERSION, 'all');
    }

    /**
     * Register the JavaScript for the public area.
     *
     * @since    1.0.0
     */
    public function enqueue_public_scripts() {
        wp_enqueue_script('12gm-lms-public', TWELVEGM_LMS_PLUGIN_URL . 'assets/js/public.js', array('jquery'), TWELVEGM_LMS_VERSION, true);
        
        // Add AJAX URL and nonce for JavaScript use
        wp_localize_script('12gm-lms-public', 'twelvegm_lms_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('12gm_lms_ajax_nonce'),
            'i18n' => array(
                'mark_complete' => __('Mark as Complete', '12gm-lms'),
                'completed' => __('Completed', '12gm-lms'),
                'error' => __('Error occurred. Please try again.', '12gm-lms'),
                'error_marking_complete' => __('Error marking lesson as complete', '12gm-lms'),
                'error_try_again' => __('Error marking lesson as complete. Please try again.', '12gm-lms'),
                'progress_complete' => __('% Complete', '12gm-lms'),
                'lessons_text' => __('lessons', '12gm-lms'),
            )
        ));
    }
    
    /**
     * Load plugin text domain for translations.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain('12gm-lms', false, dirname(plugin_basename(__FILE__)) . '/../languages');
    }
}