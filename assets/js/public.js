/**
 * Public JavaScript for the 12GM LMS plugin.
 *
 * @since      1.0.0
 */

(function($) {
    'use strict';

    /**
     * Lesson Page - Mark as Complete button
     */
    $(document).ready(function() {
        $('#12gm-lms-mark-complete-btn').on('click', function() {
            const button = $(this);
            const spinner = $('.12gm-lms-spinner');
            const successMsg = $('.12gm-lms-complete-success');
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
                        $('.12gm-lms-next-lesson-button').removeClass('disabled').prop('disabled', false);
                        
                        // Update progress bar if present
                        if (response.data.progress) {
                            const progress = response.data.progress;
                            
                            // Update progress bar
                            $('.12gm-lms-progress-fill').css('width', progress.percentage + '%');
                            
                            // Update progress text
                            $('.12gm-lms-progress-text').text(
                                progress.percentage + '% Complete (' + 
                                progress.completed + '/' + 
                                progress.total + ' lessons)'
                            );
                        }
                    } else {
                        // Show error message and re-enable button
                        alert(response.data || 'Error marking lesson as complete');
                        button.prop('disabled', false);
                    }
                },
                error: function() {
                    spinner.hide();
                    alert('Error marking lesson as complete. Please try again.');
                    button.prop('disabled', false);
                }
            });
        });
    });

})(jQuery);