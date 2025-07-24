<?php
/**
 * Template for the student dashboard.
 *
 * @since      1.0.0
 */
?>
<div class="sv-lms-dashboard">
    <h2 class="sv-lms-title entry-title"><?php echo esc_html($atts['title']); ?></h2>
    
    <div class="sv-lms-courses-grid courses-grid">
        <?php foreach ($courses as $course): ?>
            <div class="sv-lms-course-card">
                <?php if (!empty($course['thumbnail'])): ?>
                    <div class="sv-lms-course-thumbnail">
                        <a href="<?php echo esc_url($course['link']); ?>">
                            <img src="<?php echo esc_url($course['thumbnail']); ?>" alt="<?php echo esc_attr($course['title']); ?>">
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="sv-lms-course-content">
                    <h3 class="sv-lms-course-title">
                        <a href="<?php echo esc_url($course['link']); ?>"><?php echo esc_html($course['title']); ?></a>
                    </h3>
                    
                    <?php if (!empty($course['excerpt'])): ?>
                        <div class="sv-lms-course-excerpt">
                            <?php echo wp_kses_post($course['excerpt']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="sv-lms-course-progress">
                        <div class="sv-lms-progress-bar">
                            <div class="sv-lms-progress-fill" style="width: <?php echo esc_attr($course['progress']['percentage']); ?>%;"></div>
                        </div>
                        <div class="sv-lms-progress-text">
                            <?php echo sprintf(__('%d%% Complete (%d/%d lessons)', '12gm-lms'), 
                                $course['progress']['percentage'], 
                                $course['progress']['completed'], 
                                $course['progress']['total']); ?>
                        </div>
                    </div>
                    
                    <div class="sv-lms-course-actions">
                        <a href="<?php echo esc_url($course['link']); ?>" class="button sv-lms-course-button">
                            <?php _e('Continue Learning', '12gm-lms'); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>