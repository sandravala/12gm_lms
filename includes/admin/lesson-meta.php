<?php

/**
 * Lesson meta functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class TwelveGM_LMS_Lesson_Meta
{

    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta'));
    }

    public function add_meta_boxes()
    {
        add_meta_box(
            'lesson_meta',
            __('Lesson Details', '12gm-lms'),
            array($this, 'meta_box_callback'),
            '12gm_lesson',
            'normal',
            'high'
        );
    }

    public function meta_box_callback($post)
    {
        wp_nonce_field('lesson_meta_nonce', 'lesson_meta_nonce');

        $lesson_type = get_post_meta($post->ID, '_lesson_type', true);
        $lesson_duration = get_post_meta($post->ID, '_lesson_duration', true);

?>
        <table class="form-table">
            <tr>
                <th><label for="lesson_type"><?php _e('Lesson Type', '12gm-lms'); ?></label></th>
                <td>
                    <select name="lesson_type" id="lesson_type">
                        <option value="video" <?php selected($lesson_type, 'video'); ?>>📹 <?php _e('Video', '12gm-lms'); ?></option>
                        <option value="text" <?php selected($lesson_type, 'text'); ?>>📄 <?php _e('Text', '12gm-lms'); ?></option>
                        <option value="quiz" <?php selected($lesson_type, 'quiz'); ?>>❓ <?php _e('Quiz', '12gm-lms'); ?></option>
                        <option value="audio" <?php selected($lesson_type, 'audio'); ?>>🎵 <?php _e('Audio', '12gm-lms'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>;
                <th><label for="lesson_duration"><?php _e('Duration (minutes)', '12gm-lms'); ?></label></th>
                <td><input type="number" name="lesson_duration" id="lesson_duration" value="<?php echo esc_attr($lesson_duration); ?>" placeholder="30" min="1" /></td>
            </tr>
        </table>
<?php
    }

    public function save_meta($post_id)
    {
        if (!isset($_POST['lesson_meta_nonce']) || !wp_verify_nonce($_POST['lesson_meta_nonce'], 'lesson_meta_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (get_post_type($post_id) !== '12gm_lesson') {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['lesson_type'])) {
            update_post_meta($post_id, '_lesson_type', sanitize_text_field($_POST['lesson_type']));
        }

        if (isset($_POST['lesson_duration'])) {
            update_post_meta($post_id, '_lesson_duration', sanitize_text_field($_POST['lesson_duration']));
        }
    }
}

// Initialize the class
new TwelveGM_LMS_Lesson_Meta();
