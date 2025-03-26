/**
 * Admin JavaScript for the 12GM LMS plugin.
 *
 * @since      1.0.0
 */

(function($) {
    'use strict';

    /**
     * Student Access Management Page
     */
    $(document).ready(function() {
        // Student Access Management Page - Dynamic dropdowns
        if ($('.12gm-lms-admin-user-access').length) {
            // User select change for revoking access
            $('#user_id_revoke').on('change', function() {
                const userId = $(this).val();
                
                if (!userId) {
                    return;
                }
                
                // Disable course dropdown until we get data
                $('#course_id_revoke').prop('disabled', true);
                
                // AJAX request to get user courses
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: '12gm_lms_get_user_courses',
                        user_id: userId,
                        nonce: window.twelvegm_lms_ajax.nonce
                    },
                    success: function(response) {
                        // Reset and enable course dropdown
                        $('#course_id_revoke').prop('disabled', false);
                        $('#course_id_revoke').html('<option value="">' + window.twelvegm_lms_ajax.select_course + '</option>');
                        
                        if (response.success && response.data.courses) {
                            // Add courses to dropdown
                            $.each(response.data.courses, function(i, course) {
                                $('#course_id_revoke').append(
                                    $('<option></option>').val(course.id).text(course.title)
                                );
                            });
                        }
                    },
                    error: function() {
                        $('#course_id_revoke').prop('disabled', false);
                    }
                });
            });
        }
        
        /**
         * Course Edit Page 
         */
        if ($('#12gm_lms_course_lessons').length) {
            // Make lesson list sortable
            if ($.fn.sortable) {
                $('.12gm-lms-lessons-list table tbody').sortable({
                    items: 'tr',
                    cursor: 'move',
                    axis: 'y',
                    handle: 'td:first',
                    placeholder: 'ui-state-highlight',
                    start: function(e, ui) {
                        ui.placeholder.height(ui.item.height());
                    },
                    update: function(e, ui) {
                        // Update order numbers
                        $('.12gm-lms-lessons-list table tbody tr').each(function(index) {
                            $(this).find('input[name^="12gm_lms_lesson_order"]').val(index + 1);
                        });
                    }
                });
            }
        }
    });

})(jQuery);