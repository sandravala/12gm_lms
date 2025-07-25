/**
 * Public JavaScript for the 12GM LMS plugin.
 *
 * @since      1.0.0
 */

(function ($) {
  "use strict";

  $(document).ready(function () {
    
    /**
     * Expandable Sidebar Functionality
     */
    function initLessonSidebar() {
      const sidebar = $('#lessonSidebar');
      const overlay = $('#sidebarOverlay');
      const toggleBtn = $('#sidebarToggleBtn');
      const closeBtn = $('#sidebarCloseBtn');
      
      // Only initialize if sidebar elements exist (lesson pages)
      if (sidebar.length === 0) return;
      
      function openSidebar() {
        sidebar.addClass('sidebar-open');
        overlay.addClass('overlay-active');
        $('body').addClass('sidebar-open-body');
      }
      
      function closeSidebar() {
        sidebar.removeClass('sidebar-open');
        overlay.removeClass('overlay-active');
        $('body').removeClass('sidebar-open-body');
      }
      
      toggleBtn.on('click', openSidebar);
      closeBtn.on('click', closeSidebar);
      overlay.on('click', closeSidebar);
      
      // Close on escape key
      $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.hasClass('sidebar-open')) {
          closeSidebar();
        }
      });
      
      // Group expand/collapse functionality
      $('.sidebar-group-header').on('click', function() {
        const group = $(this).closest('.sidebar-lesson-group');
        const lessons = group.find('.sidebar-group-lessons');
        const icon = $(this).find('.group-expand-icon');
        
        if (lessons.hasClass('expanded')) {
          lessons.removeClass('expanded').addClass('collapsed');
          icon.removeClass('expanded');
        } else {
          lessons.removeClass('collapsed').addClass('expanded');
          icon.addClass('expanded');
        }
      });
    }

    /**
     * Lesson Page - Mark as Complete button (Enhanced for sidebar)
     */
    function initLessonComplete() {
      const markCompleteBtn = $("#sv-lms-mark-complete-btn, #12gm-lms-mark-complete-btn");
      
      if (markCompleteBtn.length === 0) return;
      
      markCompleteBtn.on("click", function () {
        const button = $(this);
        const spinner = $(".sv-lms-spinner, .12gm-lms-spinner");
        const successMsg = $(".sv-lms-complete-success, .12gm-lms-complete-success");
        const lessonId = button.data("lesson-id");
        const nonce = button.data("nonce");

        // Disable button and show spinner
        button.prop("disabled", true);
        spinner.show();

        // Send AJAX request
        $.ajax({
          url: twelvegm_lms_ajax.ajax_url,
          type: "POST",
          data: {
            action: "12gm_lms_mark_lesson_complete",
            lesson_id: lessonId,
            nonce: nonce,
          },
          success: function (response) {
            spinner.hide();

            if (response.success) {
              // Show success message
              button.hide();
              successMsg.show();

              // Update sidebar lesson status (if sidebar exists)
              $('.sidebar-lesson-item.current .lesson-status-icon').html('✓');
              $('.sidebar-lesson-item.current').addClass('completed');
              
              // Update progress bar and stats in sidebar (if sidebar exists)
              if (response.data.progress) {
                const progress = response.data.progress;
                
                // Sidebar progress updates
                $('.sv-lms-progress-fill').css('width', progress.percentage + '%');
                $('.progress-percentage').text(progress.percentage + '%');
                $('.progress-stats-compact .stat-value').eq(0).text(progress.completed);
                $('.progress-stats-compact .stat-value').eq(2).text(progress.total - progress.completed);
                
                // Main page progress updates
                $(".12gm-lms-progress-fill").css("width", progress.percentage + "%");
                $(".12gm-lms-progress-text").text(
                  progress.percentage +
                    (twelvegm_lms_ajax.i18n.progress_complete || "% Complete") +
                    " (" +
                    progress.completed +
                    "/" +
                    progress.total +
                    " " +
                    (twelvegm_lms_ajax.i18n.lessons_text || "lessons") +
                    ")"
                );
              }

              // Enable next lesson button
              $(".sv-lms-next-lesson-button, .12gm-lms-next-lesson-button")
                .removeClass("disabled")
                .prop("disabled", false);

              // Update lesson status icon
              $(".sv-lms-lesson-status-icon, .12gm-lms-lesson-status-icon").html(
                '<span class="dashicons dashicons-yes-alt"></span>'
              );
            } else {
              // Show error message and re-enable button
              alert(
                response.data || 
                "Klaida pažymint paskaitą kaip baigtą"
              );
              button.prop("disabled", false);
            }
          },
          error: function () {
            spinner.hide();
            alert(
              twelvegm_lms_ajax.i18n.error_try_again ||
              "Error marking lesson as complete. Please try again."
            );
            button.prop("disabled", false);
          },
        });
      });
    }

    /**
     * Course Page - Accordion functionality for lesson groups
     */
    function initCourseAccordion() {
      $(".accordion-header").on("click", function () {
        const groupId = $(this).data("group-id");
        const content = $(`#group-${groupId}`);
        const icon = $(this).find(".accordion-icon");

        // Toggle visibility and update icon
        if (content.is(":visible")) {
          content.slideUp();
          icon.text("+");
        } else {
          content.slideDown();
          icon.text("-");
        }
      });
    }

    // Initialize all functionality
    initLessonSidebar();
    initLessonComplete();
    initCourseAccordion();
  });
})(jQuery);