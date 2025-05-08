<?php
/**
 * Template for the course content page.
 *
 * @since      1.0.0
 */
?>
<div class="sv-lms-course-container site-main entry-content">

    <div class="course-header">
            <div class="course-thumbnail">
                <img src="/api/placeholder/400/400" alt="Web Development Fundamentals">
            </div>
            
            <div class="course-info">
                <span class="course-category">TBD</span>
                <h1><?php echo esc_html($course->post_title); ?></h1>
                
                <div class="course-meta">
                    <div class="course-lessons">📚 <?php echo esc_attr($progress['total']); ?> Lessons</div>
                    <div class="course-duration">⏱️ 6 Hours Total</div>
                    <div class="course-students">👥 243 Students</div>
                </div>
                
                <p class="course-description"><?php echo wp_kses_post($course->post_excerpt); ?></p>
            </div>

            <div class="course-progress-container">
            <div class="progress-stats">
                <div class="stat-item">
                    <div class="stat-value"><?php echo esc_attr($progress['completed']); ?></div>
                    <div class="stat-label">Lessons Completed</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo esc_attr($progress['total'] - $progress['completed']); ?></div>
                    <div class="stat-label">Lessons Remaining</div>
                </div>
                <!-- <div class="stat-item">
                    <div class="stat-value">4.5</div>
                    <div class="stat-label">Hours Spent</div>
                </div> -->
                <div class="stat-item">
                    <div class="stat-value"><?php echo esc_attr($progress['percentage']); ?>%</div>
                    <div class="stat-label">Course Completion</div>
                </div>
            </div>
            
            <div class="progress-bar">
                <div class="progress-fill" style="width:<?php echo esc_attr($progress['percentage']); ?>%"></div>
            </div>
            <div class="progress-text">
                <span>Your Progress</span>
                <span class="progress-percentage"><?php echo esc_attr($progress['percentage']); ?>% Complete</span>
            </div>
        </div>

        <div class="lessons-container">
            <div class="lessons-list">
                <h2>Course Lessons</h2>
                
                <?php if (empty($lessons)): ?>
                    <p><?php _e('No lessons found for this course.', '12gm-lms'); ?></p>
                <?php else: ?>
                    <?php foreach ($lessons as $i => $lesson): 
                        $is_completed = in_array($lesson->ID, $completed_lessons);
                        $is_current = (!$is_completed && $i === array_search(false, array_map(function($l) use ($completed_lessons) {
                            return in_array($l->ID, $completed_lessons);
                        }, $lessons)));
                    ?>
                        <a href="<?php echo esc_url(get_permalink($lesson->ID)); ?>" class="lesson-card-link">
                            <div class="lesson-card <?php echo $is_completed ? 'lesson-completed' : ''; ?>">
                                <div class="lesson-icon">
                                    <?php if ($is_completed): ?>
                                        ✓
                                    <?php elseif ($is_current): ?>
                                        ▶️
                                    <?php else: ?>
                                        <?php echo $i + 1; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="lesson-info">
                                    <div class="lesson-title"><?php echo ($i + 1) . '. ' . esc_html($lesson->post_title); ?></div>
                                    <div class="lesson-meta">
                                        <div class="lesson-type">📹 Video</div>
                                        <div class="lesson-duration">30 min</div>
                                    </div>
                                </div>
                                <div class="lesson-status <?php 
                                    echo $is_completed ? 'completed' : ($is_current ? 'in-progress' : 'upcoming');
                                ?>">
                                    <?php 
                                    if ($is_completed) {
                                        echo 'Completed';
                                    } elseif ($is_current) {
                                        echo 'In Progress';
                                    } else {
                                        echo 'Upcoming';
                                    }
                                    ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <div class="sv-lms-course-navigation">
        <a href="<?php echo esc_url(get_permalink(get_option('12gm_lms_dashboard_page_id'))); ?>" class="sv-lms-back-button">
            <?php _e('Back to Dashboard', '12gm-lms'); ?>
        </a>
    </div>
</div>