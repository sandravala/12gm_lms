<?php
/**
 * Template for the student dashboard.
 *
 * @since      1.0.0
 */
?>
<div class="sv-lms-dashboard">
    
    <?php if (!empty($user_courses)): ?>
        <div class="sv-lms-section">
            <?php if ($is_admin): ?>
                <h2 class="sv-lms-section-title"><?php _e('Mano kursai', '12gm-lms'); ?></h2>
                <p class="sv-lms-section-description"><?php _e('Kursai, kuriuose esu užsiregistravęs', '12gm-lms'); ?></p>
            <?php endif; ?>
            
            <div class="sv-lms-courses-grid courses-grid enrolled-courses">
                <?php foreach ($user_courses as $course): ?>
                    <div class="sv-lms-course-card enrolled-course">
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
                                    <span><?php echo sprintf(__('Peržiūrėta: %d%% ', '12gm-lms'), 
                                        $course['progress']['percentage']); ?>
                                        </span>
                                        <span><?php echo sprintf(__('Paskaitų: %d iš %d', '12gm-lms'), 
                                        $course['progress']['completed'], 
                                        $course['progress']['total']); ?>
                                        </span>
                                </div>
                            </div>
                            
                            <div class="sv-lms-course-actions">
                                <a href="<?php echo esc_url($course['link']); ?>" class="button sv-lms-course-button">
                                    <?php _e('Peržiūrėti', '12gm-lms'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <?php if ($is_admin): ?>
                            <div class="course-badge enrolled-badge">
                                <?php _e('Užsiregistravęs', '12gm-lms'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($is_admin && !empty($admin_courses)): ?>
        <div class="sv-lms-section admin-section">
            <h2 class="sv-lms-section-title"><?php _e('Visi kiti kursai', '12gm-lms'); ?></h2>
            <p class="sv-lms-section-description"><?php _e('Kursai, kuriuos galiu peržiūrėti kaip administratorius', '12gm-lms'); ?></p>
            
            <div class="sv-lms-courses-grid courses-grid admin-courses">
                <?php foreach ($admin_courses as $course): ?>
                    <div class="sv-lms-course-card admin-course">
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
                            
                            <!-- Admin courses show basic info instead of progress -->
                            <div class="sv-lms-course-meta">
                                <div class="course-meta-item">
                                    <span class="meta-label"><?php _e('Paskaitų:', '12gm-lms'); ?></span>
                                    <span class="meta-value"><?php echo $course['progress']['total']; ?></span>
                                </div>
                            </div>
                            
                            <div class="sv-lms-course-actions">
                                <a href="<?php echo esc_url($course['link']); ?>" class="button sv-lms-course-button admin-button">
                                    <?php _e('Peržiūrėti kaip admin', '12gm-lms'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="course-badge admin-badge">
                            <?php _e('Admin prieiga', '12gm-lms'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>