<?php

/**
 * Template for the course content page.
 *
 * @since      1.0.0
 */
?>
<div class="sv-lms-course-container">

    <div class="course-header">

        <div class="course-info">
        <div class="sv-lms-lesson-breadcrumbs breadcrumbs">
            <a href="<?php echo esc_url(get_permalink(get_option('12gm_lms_dashboard_page_id'))); ?>"><?php _e('Dashboard', '12gm-lms'); ?></a>
            &raquo;
            <a href="<?php echo esc_url(get_permalink($course->ID)); ?>"><?php echo esc_html($course->post_title); ?></a>
        </div>

            <h1><?php echo esc_html($course->post_title); ?></h1>
            <?php
            // Calculate total course duration from lesson minutes
            $total_minutes = 0;
            foreach ($lessons as $lesson) {
                $lesson_minutes = get_post_meta($lesson->ID, '_lesson_duration', true);
                if ($lesson_minutes && is_numeric($lesson_minutes)) {
                    $total_minutes += intval($lesson_minutes);
                }
            }

            // Format total duration for display
            $total_hours = floor($total_minutes / 60);
            $remaining_minutes = $total_minutes % 60;

            if ($total_hours > 0 && $remaining_minutes > 0) {
                $formatted_duration = $total_hours . __('h ', '12gm-lms') . $remaining_minutes . __('min', '12gm-lms');
            } elseif ($total_hours > 0) {
                $formatted_duration = $total_hours . ' ' . ($total_hours == 1 ? __('Hour', '12gm-lms') : __('Hours', '12gm-lms'));
            } else {
                $formatted_duration = $remaining_minutes . ' ' . __('Minutes', '12gm-lms');
            }


            // Calculate enrolled students for this specific course
            function get_course_enrollment_count($course_id)
            {
                global $wpdb;

                // Get all users who have this course in their enrolled courses meta
                $enrolled_count = $wpdb->get_var($wpdb->prepare("
                    SELECT COUNT(DISTINCT user_id) 
                    FROM {$wpdb->usermeta} 
                    WHERE meta_key = '12gm_lms_enrolled_courses' 
                    AND meta_value LIKE %s
                ", '%"' . $course_id . '"%'));

                return intval($enrolled_count);
            }

            // Get actual enrolled students for this course
            $actual_enrolled = get_course_enrollment_count($course->ID);
            $minimum_display = 0; // Your placeholder minimum
            $course_students = max($actual_enrolled, $minimum_display);
            ?>
            <div class="course-meta">
                <div class="course-lessons">📚 <?php printf(__('%d Lessons', '12gm-lms'), $progress['total']); ?></div>
                <div class="course-duration">⏱️ <?php printf(__('%s Total', '12gm-lms'), esc_html($formatted_duration)); ?></div>
                <?php if ($course_students > 10): ?>
                    <div class="course-students">👥 <?php printf(__('%d Students', '12gm-lms'), $course_students); ?></div>
                <?php endif; ?>
            </div>

            <p class="course-description"><?php echo wp_kses_post($course->post_excerpt); ?></p>
        </div>

        <div class="course-progress-container">
            <div class="progress-stats">
                <div class="stat-item">
                    <div class="stat-value"><?php echo esc_attr($progress['completed']); ?></div>
                    <div class="stat-label"><?php _e('Lessons Completed', '12gm-lms'); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo esc_attr($progress['total'] - $progress['completed']); ?></div>
                    <div class="stat-label"><?php _e('Lessons Remaining', '12gm-lms'); ?></div>
                </div>
                <!-- <div class="stat-item">
                    <div class="stat-value">4.5</div>
                    <div class="stat-label">Hours Spent</div>
                </div> -->
                <div class="stat-item">
                    <div class="stat-value"><?php echo esc_attr($progress['percentage']); ?>%</div>
                    <div class="stat-label"><?php _e('Course Completion', '12gm-lms'); ?></div>
                </div>
            </div>

            <div class="progress-bar">
                <div class="progress-fill" style="width:<?php echo esc_attr($progress['percentage']); ?>%"></div>
            </div>
            <div class="progress-text">
                <span><?php _e('Your Progress', '12gm-lms'); ?></span>
                <span class="progress-percentage"><?php printf(__('%d%% Complete', '12gm-lms'), $progress['percentage']); ?></span>
            </div>
        </div>

        <div class="lessons-container">
            <div class="lessons-list">
                <h2><?php _e('Course Lessons', '12gm-lms'); ?></h2>

                <?php if (empty($lessons)): ?>
                    <p><?php _e('No lessons found for this course.', '12gm-lms'); ?></p>
                <?php else: ?>
                    <?php
                    // Find the first incomplete lesson for "current" status
                    $first_incomplete_lesson_id = null;
                    foreach ($lessons as $l) {
                        if (!in_array($l->ID, $completed_lessons)) {
                            $first_incomplete_lesson_id = $l->ID;
                            break;
                        }
                    }

                    // Group all lessons (including ungrouped ones)
                    $grouped_lessons = array();
                    $has_groups = false;

                    foreach ($lessons as $lesson) {
                        $groups = wp_get_post_terms($lesson->ID, 'lesson_group');

                        if (!empty($groups) && !is_wp_error($groups)) {
                            $has_groups = true;
                            $group_key = $groups[0]->term_id;
                            $group_name = $groups[0]->name;
                            $group_description = $groups[0]->description;
                        } else {
                            // Treat ungrouped lessons as a special group
                            $group_key = 'ungrouped';
                            $group_name = __('Course Lessons', '12gm-lms');
                            $group_description = '';
                        }

                        if (!isset($grouped_lessons[$group_key])) {
                            $grouped_lessons[$group_key] = array(
                                'name' => $group_name,
                                'description' => $group_description,
                                'lessons' => array()
                            );
                        }
                        $grouped_lessons[$group_key]['lessons'][] = $lesson;
                    }

                    // Sort groups (ungrouped last if there are actual groups)
                    uksort($grouped_lessons, function ($a, $b) use (&$grouped_lessons, $has_groups) {
                        if ($has_groups) {
                            if ($a === 'ungrouped') return 1;
                            if ($b === 'ungrouped') return -1;
                        }
                        return strcmp($grouped_lessons[$a]['name'], $grouped_lessons[$b]['name']);
                    });

                    $lesson_counter = 0;
                    ?>

                    <?php foreach ($grouped_lessons as $group_key => $group_data): ?>
                        <div class="lesson-group <?php echo $has_groups ? '' : 'no-groups'; ?>">
                            <?php if ($has_groups || $group_key !== 'ungrouped'): ?>
                                <h3 class="lesson-group-title">
                                    <?php echo esc_html($group_data['name']); ?>
                                    <span class="lesson-count"><?php printf(__('(%d lessons)', '12gm-lms'), count($group_data['lessons'])); ?></span>
                                </h3>

                                <?php if (!empty($group_data['description'])): ?>
                                    <div class="lesson-group-description">
                                        <?php echo wp_kses_post($group_data['description']); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="lesson-group-content">
                                <?php 
                                $lesson_counter = 0;
                                 foreach ($group_data['lessons'] as $lesson):
                                    $lesson_counter++;
                                    $is_completed = in_array($lesson->ID, $completed_lessons);
                                    $is_current = (!$is_completed && $lesson->ID == $first_incomplete_lesson_id);
                                ?>
                                    <a href="<?php echo esc_url(get_permalink($lesson->ID)); ?>" class="lesson-card-link">
                                        <div class="lesson-card <?php echo $is_completed ? 'lesson-completed' : ''; ?>">
                                            <div class="lesson-icon">
                                                <?php if ($is_completed): ?>
                                                    ✓
                                                <?php else: ?>
                                                    ▷
                                                <?php endif; ?>
                                            </div>
                                            <div class="lesson-info">
                                                <div class="lesson-title"><?php echo $lesson_counter . '. ' . esc_html($lesson->post_title); ?></div>
                                                <div class="lesson-meta">
                                                    <?php
                                                    $lesson_type = get_post_meta($lesson->ID, '_lesson_type', true) ?: 'video';
                                                    $lesson_minutes = get_post_meta($lesson->ID, '_lesson_duration', true) ?: '30';
                                                    $lesson_duration = intval($lesson_minutes) . ' min';

                                                    $type_icons = [
                                                        'video' => '📹 ' . __('Video', '12gm-lms'),
                                                        'text' => '📄 ' . __('Text', '12gm-lms'),
                                                        'quiz' => '❓ ' . __('Quiz', '12gm-lms'),
                                                        'audio' => '🎵 ' . __('Audio', '12gm-lms')
                                                    ];
                                                    ?>
                                                    <div class="lesson-type"><?php echo esc_html($type_icons[$lesson_type] ?? '📹 Video'); ?></div>
                                                    <div class="lesson-duration"><?php echo esc_html($lesson_duration); ?></div>
                                                </div>
                                            </div>
                                            <div class="lesson-status <?php echo $is_completed ? 'completed' : ($is_current ? 'in-progress' : 'upcoming'); ?>">
                                                <?php
                                                if ($is_completed) {
                                                    echo __('Completed', '12gm-lms');
                                                } else {
                                                    echo __('Upcoming', '12gm-lms');
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
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