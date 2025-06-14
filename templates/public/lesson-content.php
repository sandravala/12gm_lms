<?php
/**
 * Template for the lesson content page.
 *
 * @since      1.0.0
 */
?>
<div class="sv-lms-lesson-container site-main entry-content">
    <div class="sv-lms-lesson-header">
        <div class="sv-lms-lesson-breadcrumbs breadcrumbs">
            <a href="<?php echo esc_url(get_permalink(get_option('12gm_lms_dashboard_page_id'))); ?>"><?php _e('Dashboard', 'sv-lms'); ?></a>
            &raquo;
            <a href="<?php echo esc_url(get_permalink($course->ID)); ?>"><?php echo esc_html($course->post_title); ?></a>
            &raquo;
            <span><?php echo esc_html($lesson->post_title); ?></span>
        </div>
        
        <h1 class="sv-lms-lesson-title"><?php echo esc_html($lesson->post_title); ?></h1>
        
        <?php if (!empty($lesson->post_excerpt)): ?>
            <div class="sv-lms-lesson-excerpt">
                <?php echo wp_kses_post($lesson->post_excerpt); ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="sv-lms-lesson-content">
        <?php echo apply_filters('the_content', $lesson->post_content); ?>
    </div>
    
    <div class="sv-lms-lesson-footer">
        <?php if (!$is_completed): ?>
            <div class="sv-lms-lesson-complete-form">
                <button id="sv-lms-mark-complete-btn" class="button button-primary" data-lesson-id="<?php echo esc_attr($lesson->ID); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('12gm_lms_lesson_complete')); ?>">
                    <?php _e('Mark as Complete', 'sv-lms'); ?>
                </button>
                <span class="sv-lms-spinner" style="display: none;"></span>
                <span class="sv-lms-complete-success" style="display: none; color: green;">
                    <span class="dashicons dashicons-yes-alt"></span> <?php _e('Lesson completed', 'sv-lms'); ?>
                </span>
            </div>
        <?php else: ?>
            <div class="sv-lms-lesson-completed-message">
                <span class="dashicons dashicons-yes-alt"></span> <?php _e('You have completed this lesson', 'sv-lms'); ?>
            </div>
        <?php endif; ?>
        
        <div class="sv-lms-lesson-navigation">
            <?php if ($prev_lesson): ?>
                <a href="<?php echo esc_url(get_permalink($prev_lesson->ID)); ?>" class="button sv-lms-prev-lesson-button">
                    <span class="dashicons dashicons-arrow-left-alt"></span>
                    <?php _e('Previous Lesson', 'sv-lms'); ?>
                </a>
            <?php endif; ?>
            
            <a href="<?php echo esc_url(get_permalink($course->ID)); ?>" class="button sv-lms-course-button">
                <?php _e('Back to Course', 'sv-lms'); ?>
            </a>
            
            <?php if ($next_lesson): ?>
                <a href="<?php echo esc_url(get_permalink($next_lesson->ID)); ?>" class="button sv-lms-next-lesson-button <?php echo $is_completed ? '' : 'disabled'; ?>" <?php echo $is_completed ? '' : 'disabled'; ?>>
                    <?php _e('Next Lesson', 'sv-lms'); ?>
                    <span class="dashicons dashicons-arrow-right-alt"></span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#sv-lms-mark-complete-btn').on('click', function() {
        const button = $(this);
        const spinner = $('.sv-lms-spinner');
        const successMsg = $('.sv-lms-complete-success');
        const lessonId = button.data('lesson-id');
        const nonce = button.data('nonce');
        
        // Disable button and show spinner
        button.prop('disabled', true);
        spinner.show();
        
        // Send AJAX request
        $.ajax({
            url: twelvegm_lms_ajax.ajax_url,
            type: 'POST',
            data: {
                action: '12gm_lms_mark_lesson_complete',
                lesson_id: lessonId,
                nonce: nonce
            },
            success: function(response) {
                spinner.hide();
                
                if (response.success) {
                    // Show success message
                    button.hide();
                    successMsg.show();
                    
                    // Enable next lesson button
                    $('.sv-lms-next-lesson-button').removeClass('disabled').prop('disabled', false);
                    
                    // Update lesson status icon
                    $('.sv-lms-lesson-status-icon').html('<span class="dashicons dashicons-yes-alt"></span>');
                } else {
                    // Show error message and re-enable button
                    alert(response.data);
                    button.prop('disabled', false);
                }
            },
            error: function() {
                spinner.hide();
                alert(twelvegm_lms_ajax.i18n.error || '<?php _e('Error marking lesson as complete. Please try again.', 'sv-lms'); ?>');
                button.prop('disabled', false);
            }
        });
    });
});
</script>