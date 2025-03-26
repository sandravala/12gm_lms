<?php
/**
 * Template for displaying a single lesson.
 *
 * @since      1.0.0
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        while (have_posts()) {
            the_post();
            
            echo do_shortcode('[12gm_lms_lesson id="' . get_the_ID() . '"]');
        }
        ?>
    </main>
</div>

<?php
get_sidebar();
get_footer();