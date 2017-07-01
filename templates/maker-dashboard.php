<?php
/**
 * Template Name: Maker Dashboard
 */

/*
todo:
*/
mfo_log(4, "maker-dashboard.php", "page load");
if ( !is_user_logged_in() ) {
   auth_redirect();
}
get_header(); ?>

		<div id="page-content">
			<div id="page-body">

			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<div class="container">
						<?php the_content(); ?>

						<?php edit_post_link( __( 'Edit', 'coraline' ), '<span class="edit-link">', '</span>' ); ?>
					</div><!-- .entry-content -->
				</div><!-- #post-## -->


			<?php endwhile; ?>

			</div><!-- #page-content -->

<?php get_footer(); ?>
<?php



?>

