<?php
/**
 * Template loader to allow themes to customize plugin templates.
 *
 * @since      1.0.0
 */
class TwelveGM_LMS_Template_Loader {

    /**
     * Get the template path for a given template.
     *
     * @since    1.0.0
     * @param    string    $template    Template name.
     * @return   string    Template path.
     */
    public static function get_template_path($template) {
        // Look for template in theme/child theme
        $theme_template = locate_template(array(
            '12gm-lms/' . $template,                // Theme or child theme
            'templates/12gm-lms/' . $template       // Alternative location
        ));
        
        // If theme template exists, use it
        if ($theme_template) {
            return $theme_template;
        }
        
        // Otherwise, use plugin template
        return TWELVEGM_LMS_PLUGIN_DIR . 'templates/public/' . $template;
    }
    
    /**
     * Load a template, allowing themes to override it.
     *
     * @since    1.0.0
     * @param    string    $template    Template name.
     * @param    array     $args        Template arguments.
     * @return   string    Rendered template.
     */
    public static function load_template($template, $args = array()) {
        $template_path = self::get_template_path($template);
        
        if (file_exists($template_path)) {
            // Make variables accessible to the template
            if (!empty($args) && is_array($args)) {
                extract($args);
            }
            
            ob_start();
            include $template_path;
            return ob_get_clean();
        }
        
        return '';
    }
    
    /**
     * Add theme support for plugin features.
     *
     * @since    1.0.0
     */
    public static function add_theme_support() {
        // Add post thumbnail support for courses and lessons
        add_theme_support('post-thumbnails');
        
        // Register custom image sizes if needed
        add_image_size('12gm-course-thumbnail', 600, 400, true);
        add_image_size('12gm-lesson-thumbnail', 300, 200, true);
    }
    
    /**
     * Add body classes for plugin templates.
     *
     * @since    1.0.0
     * @param    array    $classes    Array of body classes.
     * @return   array    Modified array of body classes.
     */
    public static function add_body_classes($classes) {
        if (is_singular('12gm_course')) {
            $classes[] = '12gm-lms-course-page';
        } elseif (is_singular('12gm_lesson')) {
            $classes[] = '12gm-lms-lesson-page';
        }
        
        return $classes;
    }
}