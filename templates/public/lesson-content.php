<?php
/**
 * Template for the lesson content page with expandable sidebar.
 *
 * @since      1.0.0
 */
?>
<div class="sv-lms-lesson-container">
    <!-- Sidebar toggle button -->
    <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="<?php _e('Toggle course outline', '12gm-lms'); ?>">
        <span class="toggle-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <line x1="9" y1="9" x2="15" y2="9"/>
                <line x1="9" y1="15" x2="15" y2="15"/>
            </svg>
        </span>
        <span class="toggle-text"><?php _e('Visos paskaitos', '12gm-lms'); ?></span>
    </button>

    <!-- Main lesson content -->
    <div class="sv-lms-lesson-main-content">
        <div class="sv-lms-lesson-header">
            <div class="sv-lms-lesson-breadcrumbs breadcrumbs">
                <a href="<?php echo esc_url(get_permalink(get_option('12gm_lms_dashboard_page_id'))); ?>"><?php _e('Mano kursai', '12gm-lms'); ?></a>
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
                        <?php _e('Žymėti kaip baigtą', '12gm-lms'); ?>
                    </button>
                    <span class="sv-lms-spinner" style="display: none;"></span>
                    <span class="sv-lms-complete-success" style="display: none; color: green;">
                        <span class="dashicons dashicons-yes-alt"></span> <?php _e('Paskaita užbaigta!', '12gm-lms'); ?>
                    </span>
                </div>
            <?php else: ?>
                <div class="sv-lms-lesson-completed-message">
                    <span class="dashicons dashicons-yes-alt"></span> <?php _e('Sėkmingai įveikei šią temą!', '12gm-lms'); ?>
                </div>
            <?php endif; ?>
            
            <div class="sv-lms-lesson-navigation">
                <?php if ($prev_lesson): ?>
                    <a href="<?php echo esc_url(get_permalink($prev_lesson->ID)); ?>" class="button sv-lms-prev-lesson-button">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                        <?php _e('Ankstesnė paskaita', '12gm-lms'); ?>
                    </a>
                <?php endif; ?>
                
                <a href="<?php echo esc_url(get_permalink($course->ID)); ?>" class="button sv-lms-course-button">
                    <?php _e('Atgal į kursą', '12gm-lms'); ?>
                </a>
                
                <?php if ($next_lesson): ?>
                    <a href="<?php echo esc_url(get_permalink($next_lesson->ID)); ?>" 
                       class="button sv-lms-next-lesson-button <?php echo $is_completed ? '' : 'disabled'; ?>" 
                       <?php echo $is_completed ? '' : 'onclick="return false;"'; ?>>
                        <?php if (!empty($next_group_name)): ?>
                            <?php _e('Kitas skyrius: ', '12gm-lms'); ?> <?php echo esc_html($next_group_name); ?>
                        <?php else: ?>
                            <?php _e('Kita paskaita: ', '12gm-lms'); ?>
                        <?php endif; ?>
                        <span class="dashicons dashicons-arrow-right-alt"></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Expandable sidebar overlay -->
    <div class="sv-lms-lesson-sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Expandable sidebar -->
    <div class="sv-lms-lesson-sidebar" id="lessonSidebar">
        <!-- Sidebar header -->
        <div class="sidebar-header">
            <h3 class="sidebar-title"><?php echo esc_html($course->post_title); ?></h3>
            <button class="sidebar-close-btn" id="sidebarCloseBtn" title="<?php _e('Close', '12gm-lms'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        
        <div class="sidebar-content">
            <!-- Progress overview -->
            <div class="course-progress-overview">
                <?php 
                $student_obj = new TwelveGM_LMS_Student();
                $progress = $student_obj->get_course_progress($user_id, $course_id);
                ?>
                
                <?php if($progress['percentage'] < 100): ?>
                
                <div class="sv-lms-progress-bar">
                    <div class="sv-lms-progress-fill" style="width: <?php echo $progress['percentage']; ?>%;"></div>
                </div>
                
                <div class="progress-stats-compact">
                    <div class="stat-compact">
                        <span class="stat-value"><?php echo $progress['completed']; ?></span>
                        <span class="stat-label"><?php _e('Užbaigta', '12gm-lms'); ?></span>
                    </div>
                    <div class="stat-compact">
                        <span class="stat-value"><?php echo $progress['total'] - $progress['completed']; ?></span>
                        <span class="stat-label"><?php _e('Liko', '12gm-lms'); ?></span>
                    </div>
                    <div class="stat-compact">
                        <span class="stat-value"><?php echo $progress['percentage']; ?></span>
                        <span class="stat-label"><?php _e('%', '12gm-lms'); ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Lessons list -->
            <div class="sidebar-lessons-list">
                <?php
                // Group lessons by their group (reuse logic from course-content.php)
                $grouped_lessons = array();
                $has_groups = false;

                foreach ($course_lessons as $course_lesson) {
                    $groups = wp_get_post_terms($course_lesson->ID, 'lesson_group');

                    if (!empty($groups) && !is_wp_error($groups)) {
                        $has_groups = true;
                        $group_key = $groups[0]->term_id;
                        $group_name = $groups[0]->name;
                    } else {
                        $group_key = 'ungrouped';
                        $group_name = __('Other Lessons', '12gm-lms');
                    }

                    if (!isset($grouped_lessons[$group_key])) {
                        $grouped_lessons[$group_key] = array(
                            'name' => $group_name,
                            'lessons' => array()
                        );
                    }
                    $grouped_lessons[$group_key]['lessons'][] = $course_lesson;
                }

                // Sort groups
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
                    <?php
                    // Check if current lesson is in this group
                    $current_lesson_in_group = false;
                    $group_has_completed = false;
                    $group_total = count($group_data['lessons']);
                    $group_completed = 0;
                    
                    foreach ($group_data['lessons'] as $group_lesson) {
                        if ($group_lesson->ID == $lesson->ID) {
                            $current_lesson_in_group = true;
                        }
                        if (in_array($group_lesson->ID, $completed_lessons)) {
                            $group_completed++;
                        }
                    }
                    
                    $group_has_completed = $group_completed > 0;
                    ?>
                    
                    <div class="sidebar-lesson-group <?php echo $current_lesson_in_group ? 'current-group' : ''; ?> <?php echo ($group_completed == $group_total) ? 'group-completed' : ''; ?>">
                        <?php if ($has_groups): ?>
                            <div class="sidebar-group-header" data-group="<?php echo $group_key; ?>">
                                <div class="group-title-wrapper">
                                    <h4 class="sidebar-group-title">
                                        <span class="group-expand-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="6,9 12,15 18,9"/>
                                            </svg>
                                        </span>
                                        <?php echo esc_html($group_data['name']); ?>
                                    </h4>
                                    <div class="group-progress">
                                        <span class="group-progress-text"><?php echo $group_completed; ?>/<?php echo $group_total; ?></span>
                                        <?php if ($group_completed == $group_total): ?>
                                            <span class="group-complete-badge">✓</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="sidebar-group-lessons <?php echo $current_lesson_in_group ? 'expanded' : 'collapsed'; ?>">
                            <?php
                            $group_lesson_counter = 0;
                            foreach ($group_data['lessons'] as $group_lesson):
                                $group_lesson_counter++;
                                $is_completed = in_array($group_lesson->ID, $completed_lessons);
                                $is_current = ($group_lesson->ID == $lesson->ID);
                            ?>
                                <div class="sidebar-lesson-item <?php echo $is_completed ? 'completed' : ''; ?> <?php echo $is_current ? 'current' : ''; ?>">
                                    <div class="lesson-status-icon">
                                        <?php if ($is_current): ?>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polygon points="5,3 19,12 5,21"/>
                                            </svg>
                                        <?php elseif ($is_completed): ?>
                                            ✓
                                        <?php else: ?>
                                            <?php echo $group_lesson_counter; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="lesson-content-wrapper">
                                        <?php if ($is_current): ?>
                                            <span class="lesson-title-current"><?php echo esc_html($group_lesson->post_title); ?></span>
                                        <?php else: ?>
                                            <a href="<?php echo esc_url(get_permalink($group_lesson->ID)); ?>" class="lesson-title-link">
                                                <?php echo esc_html($group_lesson->post_title); ?>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php 
                                        $lesson_duration = get_post_meta($group_lesson->ID, '_lesson_duration', true);
                                        if ($lesson_duration): 
                                        ?>
                                            <span class="lesson-duration"><?php echo intval($lesson_duration); ?> min</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- -->