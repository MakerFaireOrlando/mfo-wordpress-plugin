<?php
/**
 * single-maker.php
 * Page Template for maker custom post type
 */

get_header(); ?>

<div id="page-content">
       <div id="page-body">
	<?php
	$cred_form = get_query_var('cred-edit-form');
	$cred_id   = get_query_var('id');

	  if ($cred_form):
		//echo '$cred_id=' . $cred_id;
		//echo '$cred_form=' . $cred_form;
		echo '<div class="container"><row>';
		cred_form($cred_form, $cred_id);
		echo '</row></div>';
	 else:
		?>

        <?php the_post();?>
		<?php
		  $maker_id = get_the_id();
		  $approval_status = get_post_meta($maker_id, 'wpcf-has-approved-children', true);
		?>
                <div id="post-<?php the_ID(); ?>" <?php post_class('mfo-single-maker approval-status-'.$approval_status); ?>>
			 <?php if ( ($approval_status ==1 ) || (get_the_author_meta('ID') == get_current_user_id() )
                                           || current_user_can("edit_posts", get_the_ID() )):?>
                        <div class="container">
                        <div class="row">
			    <div class="content entry-page col-xs-12">
			      <div class="backlink"><a href="/makers"><i class="fa fa-arrow-left" aria-hidden="true"></i> Look for More Makers</a></div>



				<?php //Prior year warning block ?>
				<div class="page-header">
				<h1><?php
					echo get_the_title();?></h1>
				</div>
				<img class="img-responsive entry-image" src="<?php the_post_thumbnail_url( get_the_ID(), 'large' )?>" alt="<?php the_title() ?>">
				<p class="lead"><?php the_excerpt(); ?></p>

				<?php
				$website 	= get_post_meta($maker_id, 'wpcf-website', true);
				$email	 	= get_post_meta($maker_id, 'wpcf-public-email', true);
				$twitter 	= get_post_meta($maker_id, 'wpcf-twitter-url', true);
				$instagram 	= get_post_meta($maker_id, 'wpcf-instagram-url', true);
				$facebook 	= get_post_meta($maker_id, 'wpcf-facebook-url', true);
				$youtube 	= get_post_meta($maker_id, 'wpcf-youtube-url', true);
				?>
				<div class="row maker-social-row">

				<?php if ($website) echo '<div class="col-md-2 pull-left"><a href="'. get_post_meta($maker_id, 'wpcf-website', true) . '" class="btn btn-info" target="_blank">Website</a></div>'; ?>
				<div class="col-md-7">
				<ul class="list-inline">
				<?php if ($email) 	echo '<li><a href="mailto:' . $email	   . '" target="_blank"<i class="fa fa-2x fa-envelope" 	aria-hidden="true"></i></a></li>'; ?>
				<?php if ($twitter) 	echo '<li><a href="' . $twitter    . '" target="_blank"<i class="fa fa-2x fa-twitter" 	aria-hidden="true"></i></a></li>'; ?>
				<?php if ($instagram) 	echo '<li><a href="' . $instagram  . '" target="_blank"<i class="fa fa-2x fa-instagram" 	aria-hidden="true"></i></a></li>'; ?>
				<?php if ($facebook) 	echo '<li><a href="' . $facebook   . '" target="_blank"<i class="fa fa-2x fa-facebook" 	aria-hidden="true"></i></a></li>'; ?>
				<?php if ($youtube) 	echo '<li><a href="' . $youtube    . '" target="_blank"<i class="fa fa-2x fa-youtube" 	aria-hidden="true"></i></a></li>'; ?>
				</ul></div></div>

				<div class="container page-template-blog">
				<div class="row"><h3>Exhibits</h3></div>
				<?php
					$childargs = array(
						'post_type' => 'exhibit',
						'numberposts' => -1,
						'meta_query' => array(
							array(
								'key' => '_wpcf_belongs_maker_id',
								'value' => $maker_id
							),
							array(
								'key' => 'wpcf-approval-status',
								'value' => '1'
							)

							)
						);
					$child_posts = get_posts($childargs);

				//echo json_encode($child_posts);

				foreach ($child_posts as $child_post) {
					//adding this div to pickup the existing css format
					 echo '<div class="recent-post-post first-post col-xs-12">';
					 echo '<article class="recent-post-inner">';
 					 echo '<a href="' . get_permalink($child_post->ID) . '">';
					 echo '<div class="recent-post-img" style="background-image: url(' . get_the_post_thumbnail_url ($child_post->ID, 'medium-large') . ');"></div>';
					 //note description is misspelled on the next line to match the MM CSS
					 echo '<div class="recent-post-text"><h4>' . $child_post->post_title . '</h4>';
					 $exhibit_year = get_post_meta($child_post->ID,"wpcf-approval-year", true);
					 echo '<p class="recent-post-date">' . $exhibit_year . ' Exhibit</p><p class="recent-post-descripton">';
					 echo $child_post->post_excerpt;
 					 echo '</p></a></div></div>';
				} ?>

				<?php wp_reset_postdata ($post); ?>
	
			    </div>
			    </div>
			 </div>
			</div>
			<div class="container">
                        </div><!-- .entry-content -->
                        <?php else: ?>
			<div style="height:200px">
			<div class="container"><h2>This maker has not been approved. If you are receiving this message in error, please email us - <?php echo mfo_support_email_link()?></h2></div>
			</div>
                        <?php endif?>

                </div><!-- #post-## -->

          <?php endif; //end cred edit test ?>

        </div><!-- #content -->

<?php get_footer(); ?>
