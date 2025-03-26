<?php
/**
 * Template for the course content page.
 *
 * @since      1.0.0
 */
?>
<div class="12gm-lms-course-container site-main entry-content">
    <div class="12gm-lms-course-header">
        <h1 class="12gm-lms-course-title entry-title"><?php echo esc_html($course->post_title); ?></h1>
        
        <?php if (!empty($course->post_excerpt)): ?>
            <div class="12gm-lms-course-excerpt">
                <?php echo wp_kses_post($course->post_excerpt); ?>
            </div>
        <?php endif; ?>
        
        <div class="12gm-lms-course-progress">
            <div class="12gm-lms-progress-bar">
                <div class="12gm-lms-progress-fill" style="width: <?php echo esc_attr($progress['percentage']); ?>%;"></div>
            </div>
            <div class="12gm-lms-progress-text">
                <?php echo sprintf(__('%d%% Complete (%d/%d lessons)', '12gm-lms'), 
                    $progress['percentage'], 
                    $progress['completed'], 
                    $progress['total']); ?>
            </div>
        </div>
    </div>
    
    <div class="12gm-lms-course-content">
        <?php echo apply_filters('the_content', $course->post_content); ?>
    </div>
    
    <div class="12gm-lms-course-lessons">
        <h3><?php _e('Lessons', '12gm-lms'); ?></h3>
        
        <?php if (empty($lessons)): ?>
            <p><?php _e('No lessons found for this course.', '12gm-lms'); ?></p>
        <?php else: ?>
            <div class="12gm-lms-lessons-list">
                <?php foreach ($lessons as $i => $lesson): ?>
                    <?php $is_completed = in_array($lesson->ID, $completed_lessons); ?>
                    
                    <div class="12gm-lms-lesson-item <?php echo $is_completed ? 'is-completed' : 'not-completed'; ?>">
                        <div class="12gm-lms-lesson-status">
                            <span class="12gm-lms-lesson-status-icon">
                                <?php if ($is_completed): ?>
                                    <span class="dashicons dashicons-yes-alt"></span>
                                <?php else: ?>
                                    <span class="dashicons dashicons-marker"></span>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="12gm-lms-lesson-content">
                            <h4 class="12gm-lms-lesson-title">
                                <a href="<?php echo esc_url(get_permalink($lesson->ID)); ?>">
                                    <?php echo sprintf(__('Lesson %d: %s', '12gm-lms'), $i + 1, esc_html($lesson->post_title)); ?>
                                </a>
                            </h4>
                            
                            <?php if (!empty($lesson->post_excerpt)): ?>
                                <div class="12gm-lms-lesson-excerpt">
                                    <?php echo wp_kses_post($lesson->post_excerpt); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="12gm-lms-lesson-actions">
                            <a href="<?php echo esc_url(get_permalink($lesson->ID)); ?>" class="button 12gm-lms-lesson-button">
                                <?php if ($is_completed): ?>
                                    <?php _e('Review Lesson', '12gm-lms'); ?>
                                <?php else: ?>
                                    <?php _e('Start Lesson', '12gm-lms'); ?>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="12gm-lms-course-navigation">
        <a href="<?php echo esc_url(get_permalink(get_option('12gm_lms_dashboard_page_id'))); ?>" class="button 12gm-lms-dashboard-button">
            <?php _e('Back to Dashboard', '12gm-lms'); ?>
        </a>
    </div>
</div>