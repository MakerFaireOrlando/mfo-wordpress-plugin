<?php
/**
 * single-maker.php
 * Page Template for maker custom post type
 */

get_header(); ?>

<div id="content-container">
       <div id="content" role="main">
        <?php the_post();?>

                <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php if ( (get_the_author_meta('ID') == get_current_user_id() )
					 || current_user_can("edit_posts", the_ID() )){?>
                        <div class="entry-content">
                                <?php the_content(); ?>
                        </div><!-- .entry-content -->
			<?php }?>
                </div><!-- #post-## -->


        </div><!-- #content -->

<?php get_footer(); ?>
