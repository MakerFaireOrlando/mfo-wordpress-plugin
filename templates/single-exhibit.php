<?php
/**
 * single-exhibit.php
 * Page Template for exhibit custom post type
 * todo: add check for approval status &  message
 * todo: add categories
 * todo: add additional photos / videos
 * todo: exhibit location (with check for setting)
 */

get_header(); ?>

<div id="page-content">
       <div id="page-body">
        <?php the_post();?>

		<?php
		  $exhibit_id = get_the_id();
		  $approval_status = get_post_meta($exhibit_id, 'wpcf-approval-status', true);
		  $maker_id = wpcf_pr_post_get_belongs($exhibit_id, 'maker');
		  $maker = get_post($maker_id);
		?>
                <div id="post-<?php the_ID(); ?>" <?php post_class('mfo-single-exhibit approval-status-'.$approval_status); ?>>
			 <?php if ( ($approval_status ==1 ) || (get_the_author_meta('ID') == get_current_user_id() )
                                           || current_user_can("edit_posts", get_the_ID() )):?>
                        <div class="container">
                        <div class="row">
			    <div class="content entry-page col-xs-12">
			      <div class="backlink"><a href="/makers"><i class="fa fa-arrow-left" aria-hidden="true"></i> Look for More Makers</a></div>



				<?php //Prior year warning block ?>
				<div class="page-header">
				<h1><?php 
					$approval_year = get_post_meta($exhibit_id, 'wpcf-approval-year', true);
					echo get_the_title() . ' (' . $approval_year . ' Exhibit)';?></h1>
				</div>
				<img class="img-responsive entry-image" src="<?php the_post_thumbnail_url( get_the_ID() )?>" alt="<?php the_title() ?>">
				<p class="lead"><?php echo get_post_meta(get_the_ID(), 'wpcf-long-description', 'true'); ?></p>

				<?php
				$website 	= get_post_meta($exhibit_id, 'wpcf-website', true);
				$email	 	= get_post_meta($exhibit_id, 'wpcf-public-email', true);
				$twitter 	= get_post_meta($exhibit_id, 'wpcf-twitter-url', true);
				$instagram 	= get_post_meta($exhibit_id, 'wpcf-instagram-url', true);
				$facebook 	= get_post_meta($exhibit_id, 'wpcf-facebook-url', true);
				$googleplus 	= get_post_meta($exhibit_id, 'wpcf-google-plus-url', true);
				$youtube 	= get_post_meta($exhibit_id, 'wpcf-youtube-url', true);
				?>
				<div class="row exhibit-social-row">

				<?php if ($website) echo '<div class="col-md-2 pull-left"><a href="'. get_post_meta($exhibit_id, 'wpcf-website', true) . '" class="btn btn-info" target="_blank">Project Website</a></div>'; ?>
				<div class="col-md-5">
				<ul class="list-inline">
				<?php if ($email) 	echo '<li><a href="mailto:' . $email	   . '" target="_blank"<i class="fa fa-2x fa-envelope" 	aria-hidden="true"></i></a></li>'; ?>
				<?php if ($twitter) 	echo '<li><a href="' . $twitter    . '" target="_blank"<i class="fa fa-2x fa-twitter" 	aria-hidden="true"></i></a></li>'; ?>
				<?php if ($instagram) 	echo '<li><a href="' . $instagram  . '" target="_blank"<i class="fa fa-2x fa-instagram" 	aria-hidden="true"></i></a></li>'; ?>
				<?php if ($facebok) 	echo '<li><a href="' . $facebook   . '" target="_blank"<i class="fa fa-2x fa-facebook" 	aria-hidden="true"></i></a></li>'; ?>
				<?php if ($googleplus) 	echo '<li><a href="' . $googleplus . '" target="_blank"<i class="fa fa-2x fa-googleplus" 	aria-hidden="true"></i></a></li>'; ?>
				<?php if ($youtube) 	echo '<li><a href="' . $youtube    . '" target="_blank"<i class="fa fa-2x fa-youtube" 	aria-hidden="true"></i></a></li>'; ?>
				</ul></div></div>

				<?php //Categories ?>
				<?php //Exhibit Location ?>
				<?php //Additional Pictures & videos & such ?>


				<?php //Maker Info 
				 global $post;
				 $post = get_post($maker_id);
				 setup_postdata ($post);
				?>
				<div class="page-header">
				<h1>Maker</h1></div>

				<div class="row center-block maker-profile-row">
				  <a href="<?php the_permalink() ?>"><img class="col-md-3 pull-left img-responsive" src="<?php the_post_thumbnail_url( $maker )?>" alt="<?php the_title() ?>"></a>
				  <div class="col-md-5">
				    <h3><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h3>
				    <p <?php the_excerpt() ?></p>
				  </div>
				</div>

				<?php wp_reset_postdata ($post); ?>


			    </div>
			 </div>
			</div>

			  <div class="container">

                       <?php //the_content(); ?>


                        </div><!-- .entry-content -->
                        <?php else: ?>
			<div style="height:200px">
			<div class="container"><h2>This exhibit has not been approved. If you are receiving this message in error, please email us - <?php echo mfo_support_email_link()?></h2></div>
			</div>
                        <?php endif?>

                </div><!-- #post-## -->


        </div><!-- #content -->

<?php get_footer(); ?>
