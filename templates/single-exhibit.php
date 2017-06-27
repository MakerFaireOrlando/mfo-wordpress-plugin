<?php
/**
 * single-exhibit.php
 * Page Template for exhibit custom post type
 * todo: add back in the not-approved message
 */

get_header(); ?>

<div id="page-content">
       <div id="page-body">
        <?php the_post();?>

                <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			 <?php if ( (get_the_author_meta('ID') == get_current_user_id() )
                                         || current_user_can("edit_posts", get_the_ID() )):?>
                        <div class="container">
                                <?php the_content(); ?>
                        </div><!-- .entry-content -->
                        <?php else: ?>
                        <div style="height: 200px; margin-top: 40px;"><strong>This Exhibit is not yet approved. If this is your Exhibit and are trying to view it, please 
                        <a href="/wp-login/">login</a>.</strong></div>
                        <?php endif ?>
                </div><!-- #post-## -->


        </div><!-- #content -->

<?php get_footer(); ?>
