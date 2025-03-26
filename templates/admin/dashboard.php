<?php
/**
 * Admin dashboard template.
 *
 * @since      1.0.0
 */
?>
<div class="wrap 12gm-lms-admin-dashboard">
    <h1><?php _e('LMS Dashboard', '12gm-lms'); ?></h1>
    
    <div class="12gm-lms-admin-cards">
        <div class="12gm-lms-admin-card">
            <div class="12gm-lms-admin-card-header">
                <h2><?php _e('Courses', '12gm-lms'); ?></h2>
            </div>
            <div class="12gm-lms-admin-card-content">
                <div class="12gm-lms-admin-card-stat">
                    <span class="12gm-lms-admin-card-number"><?php echo esc_html($course_count); ?></span>
                    <span class="12gm-lms-admin-card-label"><?php _e('Published Courses', '12gm-lms'); ?></span>
                </div>
            </div>
            <div class="12gm-lms-admin-card-footer">
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=12gm_course')); ?>" class="button"><?php _e('Manage Courses', '12gm-lms'); ?></a>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=12gm_course')); ?>" class="button button-primary"><?php _e('Add New Course', '12gm-lms'); ?></a>
            </div>
        </div>
        
        <div class="12gm-lms-admin-card">
            <div class="12gm-lms-admin-card-header">
                <h2><?php _e('Lessons', '12gm-lms'); ?></h2>
            </div>
            <div class="12gm-lms-admin-card-content">
                <div class="12gm-lms-admin-card-stat">
                    <span class="12gm-lms-admin-card-number"><?php echo esc_html($lesson_count); ?></span>
                    <span class="12gm-lms-admin-card-label"><?php _e('Published Lessons', '12gm-lms'); ?></span>
                </div>
            </div>
            <div class="12gm-lms-admin-card-footer">
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=12gm_lesson')); ?>" class="button"><?php _e('Manage Lessons', '12gm-lms'); ?></a>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=12gm_lesson')); ?>" class="button button-primary"><?php _e('Add New Lesson', '12gm-lms'); ?></a>
            </div>
        </div>
        
        <div class="12gm-lms-admin-card">
            <div class="12gm-lms-admin-card-header">
                <h2><?php _e('Students', '12gm-lms'); ?></h2>
            </div>
            <div class="12gm-lms-admin-card-content">
                <div class="12gm-lms-admin-card-stat">
                    <span class="12gm-lms-admin-card-number"><?php echo count($students_with_access); ?></span>
                    <span class="12gm-lms-admin-card-label"><?php _e('Enrolled Students', '12gm-lms'); ?></span>
                </div>
            </div>
            <div class="12gm-lms-admin-card-footer">
                <a href="<?php echo esc_url(admin_url('admin.php?page=12gm-lms-user-access')); ?>" class="button button-primary"><?php _e('Manage Student Access', '12gm-lms'); ?></a>
            </div>
        </div>
        
        <?php if (class_exists('WooCommerce')): ?>
            <div class="12gm-lms-admin-card">
                <div class="12gm-lms-admin-card-header">
                    <h2><?php _e('WooCommerce', '12gm-lms'); ?></h2>
                </div>
                <div class="12gm-lms-admin-card-content">
                    <p><?php _e('Link your courses to WooCommerce products to sell access to your courses.', '12gm-lms'); ?></p>
                </div>
                <div class="12gm-lms-admin-card-footer">
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=product')); ?>" class="button"><?php _e('Manage Products', '12gm-lms'); ?></a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="12gm-lms-admin-quick-links">
        <h2><?php _e('Quick Links', '12gm-lms'); ?></h2>
        <ul>
            <li><a href="<?php echo esc_url(admin_url('edit.php?post_type=12gm_course')); ?>"><?php _e('All Courses', '12gm-lms'); ?></a></li>
            <li><a href="<?php echo esc_url(admin_url('edit.php?post_type=12gm_lesson')); ?>"><?php _e('All Lessons', '12gm-lms'); ?></a></li>
            <li><a href="<?php echo esc_url(admin_url('admin.php?page=12gm-lms-user-access')); ?>"><?php _e('Manage Student Access', '12gm-lms'); ?></a></li>
            <?php if (class_exists('WooCommerce')): ?>
                <li><a href="<?php echo esc_url(admin_url('edit.php?post_type=product')); ?>"><?php _e('WooCommerce Products', '12gm-lms'); ?></a></li>
            <?php endif; ?>
            <li><a href="<?php echo esc_url(get_permalink(get_option('12gm_lms_dashboard_page_id'))); ?>" target="_blank"><?php _e('View Student Dashboard', '12gm-lms'); ?></a></li>
        </ul>
    </div>
    
    <div class="12gm-lms-admin-tools">
        <h2><?php _e('Tools', '12gm-lms'); ?></h2>
        <p><?php _e('If you are experiencing 404 errors when trying to view courses or lessons, try flushing the permalinks.', '12gm-lms'); ?></p>
        <p>
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=12gm-lms&action=flush_rules'), '12gm_lms_flush_rules')); ?>" class="button">
                <?php _e('Flush Permalinks', '12gm-lms'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('options-permalink.php')); ?>" class="button">
                <?php _e('WordPress Permalinks Settings', '12gm-lms'); ?>
            </a>
        </p>
    </div>
</div>