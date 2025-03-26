<?php
/**
 * Admin user access management template.
 *
 * @since      1.0.0
 */
?>
<div class="wrap 12gm-lms-admin-user-access">
    <h1><?php _e('Student Access Management', '12gm-lms'); ?></h1>
    
    <div class="12gm-lms-admin-form">
        <h2><?php _e('Grant Course Access', '12gm-lms'); ?></h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('12gm_lms_user_access', '12gm_lms_user_access_nonce'); ?>
            <input type="hidden" name="action" value="grant">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="user_id"><?php _e('Student', '12gm-lms'); ?></label></th>
                    <td>
                        <select name="user_id" id="user_id" required>
                            <option value=""><?php _e('-- Select Student --', '12gm-lms'); ?></option>
                            <?php foreach ($student_users as $user): ?>
                                <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')'; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="course_id"><?php _e('Course', '12gm-lms'); ?></label></th>
                    <td>
                        <select name="course_id" id="course_id" required>
                            <option value=""><?php _e('-- Select Course --', '12gm-lms'); ?></option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo esc_attr($course->ID); ?>"><?php echo esc_html($course->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Grant Access', '12gm-lms'); ?>">
            </p>
        </form>
    </div>
    
    <div class="12gm-lms-admin-form">
        <h2><?php _e('Revoke Course Access', '12gm-lms'); ?></h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('12gm_lms_user_access', '12gm_lms_user_access_nonce'); ?>
            <input type="hidden" name="action" value="revoke">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="user_id_revoke"><?php _e('Student', '12gm-lms'); ?></label></th>
                    <td>
                        <select name="user_id" id="user_id_revoke" required>
                            <option value=""><?php _e('-- Select Student --', '12gm-lms'); ?></option>
                            <?php foreach ($student_users as $user): 
                                $enrolled_courses = get_user_meta($user->ID, '12gm_lms_enrolled_courses', true);
                                if (!empty($enrolled_courses)):
                            ?>
                                <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')'; ?></option>
                            <?php endif; endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="course_id_revoke"><?php _e('Course', '12gm-lms'); ?></label></th>
                    <td>
                        <select name="course_id" id="course_id_revoke" required>
                            <option value=""><?php _e('-- Select Course --', '12gm-lms'); ?></option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo esc_attr($course->ID); ?>"><?php echo esc_html($course->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit" id="submit_revoke" class="button button-secondary" value="<?php _e('Revoke Access', '12gm-lms'); ?>">
            </p>
        </form>
    </div>
    
    <div class="12gm-lms-admin-student-list">
        <h2><?php _e('Enrolled Students', '12gm-lms'); ?></h2>
        
        <?php 
        // Get students with course enrollments
        $enrolled_students = array();
        
        // Initialize a student class for progress tracking
        $student_obj = new TwelveGM_LMS_Student();
                
        foreach ($student_users as $user) {
            $enrolled_courses = get_user_meta($user->ID, '12gm_lms_enrolled_courses', true);
            
            if (!empty($enrolled_courses) && is_array($enrolled_courses)) {
                $student_data = array(
                    'id' => $user->ID,
                    'name' => $user->display_name,
                    'email' => $user->user_email,
                    'courses' => array(),
                );
                
                foreach ($enrolled_courses as $course_id) {
                    $course = get_post($course_id);
                    if ($course && $course->post_status === 'publish') {
                        $progress = $student_obj->get_course_progress($user->ID, $course_id);
                        $student_data['courses'][] = array(
                            'id' => $course_id,
                            'title' => $course->post_title,
                            'progress' => $progress,
                        );
                    }
                }
                
                $enrolled_students[] = $student_data;
            }
        }
        ?>
        
        <?php if (empty($enrolled_students)): ?>
            <p><?php _e('No students are currently enrolled in any courses.', '12gm-lms'); ?></p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Student', '12gm-lms'); ?></th>
                        <th><?php _e('Email', '12gm-lms'); ?></th>
                        <th><?php _e('Enrolled Courses', '12gm-lms'); ?></th>
                        <th><?php _e('Progress', '12gm-lms'); ?></th>
                        <th><?php _e('Actions', '12gm-lms'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrolled_students as $student): ?>
                        <tr>
                            <td><?php echo esc_html($student['name']); ?></td>
                            <td><?php echo esc_html($student['email']); ?></td>
                            <td>
                                <?php foreach ($student['courses'] as $course): ?>
                                    <div>
                                        <a href="<?php echo esc_url(get_permalink($course['id'])); ?>" target="_blank">
                                            <?php echo esc_html($course['title']); ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php foreach ($student['courses'] as $course): ?>
                                    <div>
                                        <?php echo sprintf(__('%d%% (%d/%d lessons)', '12gm-lms'), 
                                            $course['progress']['percentage'], 
                                            $course['progress']['completed'], 
                                            $course['progress']['total']); ?>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php foreach ($student['courses'] as $course): ?>
                                    <div>
                                        <a href="<?php echo esc_url(add_query_arg(array(
                                            'action' => 'revoke',
                                            'user_id' => $student['id'],
                                            'course_id' => $course['id'],
                                            '12gm_lms_user_access_nonce' => wp_create_nonce('12gm_lms_user_access'),
                                        ), admin_url('admin.php?page=12gm-lms-user-access'))); ?>" class="button button-small">
                                            <?php _e('Revoke Access', '12gm-lms'); ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Add Ajax functionality for student enrollments if needed
});
</script>