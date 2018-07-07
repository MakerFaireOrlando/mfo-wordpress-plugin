<?php
/**
 * single-exhibit.php
 * Page Template for exhibit custom post type
 * todo: add categories
 * todo: exhibit location (with check for setting)
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
				<?php if ($facebook) 	echo '<li><a href="' . $facebook   . '" target="_blank"<i class="fa fa-2x fa-facebook" 	aria-hidden="true"></i></a></li>'; ?>
				<?php if ($googleplus) 	echo '<li><a href="' . $googleplus . '" target="_blank"<i class="fa fa-2x fa-googleplus" 	aria-hidden="true"></i></a></li>'; ?>
				<?php if ($youtube) 	echo '<li><a href="' . $youtube    . '" target="_blank"<i class="fa fa-2x fa-youtube" 	aria-hidden="true"></i></a></li>'; ?>
				</ul></div></div>

				<?php //Categories ?>
				<?php //Exhibit Location ?>
				<?php //Additional Pictures & videos & such
				      //https://stackoverflow.com/questions/5487444/wordpress-image-size-based-on-url
				      //https://wp-types.com/documentation/customizing-sites-using-php/displaying-repeating-fields-one-kind/
					$addl_photos = get_post_meta($exhibit_id, 'wpcf-additional-photos');
					$num_photos = count(array_filter($addl_photos));
					if ($num_photos > 1):
					?>
					<section class="exhibitPhotos num-photos-<?php echo $num_photos?>">
					<div id="exhibitPhotoCarousel" class="carousel slide" data-ride="carousel">
					<!-- Indicators -->
					<ol class="carousel-indicators">
					<?php
					$counter = 0;
					foreach ($addl_photos as $photo) {
						$active = ""; if ($counter==0) $active='class = "active"';
						echo '<li data-target="exhibitPhotoCarousel" data-slide-to="' . $counter . '"' .$active.'></li>';
						$counter++;
					}?>
					</ol>

					<!-- Wrapper for slides -->
  					<div class="carousel-inner">
					<?php
					$counter = 0;
					foreach ($addl_photos as $photo) {
						$active = ""; if ($counter==0) $active=" active";
						$photo_id = mfo_get_attachment_id_by_url($photo);
						$photo_imgtag = wp_get_attachment_image($photo_id, 'large');
						echo '<div class="item' . $active .'">';
						echo $photo_imgtag . '</div>';
						$counter++;
					}
				 	?>

					<!-- Left and right controls -->
 					<a class="left carousel-control" href="#exhibitPhotoCarousel"  data-slide="prev">
    					<span class="glyphicon glyphicon-chevron-left"></span>
    					<span class="sr-only">Previous</span>
  					</a>
  					<a class="right carousel-control" href="#exhibitPhotoCarousel" data-slide="next">
    					<span class="glyphicon glyphicon-chevron-right"></span>
    					<span class="sr-only">Next</span>
  					</a>

					</div></section>
					<?php
					elseif ($num_photos == 1):
						$photo_id = mfo_get_attachment_id_by_url($addl_photos[0]);
                                                $photo_imgtag = wp_get_attachment_image($photo_id, 'large');
						echo '<section class="exhibitPhotos"><div class="single-image">';
						echo $photo_imgtag;
						echo '</div></section>';
					endif;
					?>

					<?php
					$embeds = get_post_meta($exhibit_id, 'wpcf-embeddable-media');
					if (count(array_filter($embeds)) > 0) {
						echo '<div class="exhibit-embeds num-embeds-' . count($embeds) . '">';
						foreach ($embeds as $embed) {
					  	echo '<div class="exhibit-oembed">';
					  	echo wp_oembed_get($embed, array('width'=>700));
					  	echo '</div>';
						}
					  	echo '</div>';
					}?>

				<?php //Maker Info 
				 global $post;
				 $post = get_post($maker_id);
				 setup_postdata ($post);
				?>
				<div class="page-header">
				<h1>Maker</h1></div>

				<div class="row center-block maker-profile-row">
				  <a href="<?php the_permalink() ?>"><img class="col-md-3 pull-left img-responsive" src="<?php echo get_the_post_thumbnail_url( $maker, 'medium' )?>" alt="<?php the_title() ?>"></a>
				  <div class="col-md-7">
				    <h3><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h3>
				    <p <?php the_excerpt() ?></p>
				  </div>
				</div>

				<?php wp_reset_postdata ($post); ?>


			    </div>
			 </div>
			</div>

			<div style="margin-top:20px;margin-bottom:20px;height:15px;background-color:#00597E"></div>
			<div class="container">
			<div class="row">
			<div class="col-sm-6"><a href="/makers"><img class="aligncenter size-full"
					src="<?php echo get_template_directory_uri() . '/images/explore-the-exhibits.png'?>" />

			</a></div>
			<div class="col-sm-6"><a href="/makers"><img class="aligncenter size-full"
					src="<?php echo get_template_directory_uri() . '/images/meet-the-makers.png'?>" />
			</a></div>
			</div>
			</div>
			  <div class="container">



                        </div><!-- .entry-content -->
                        <?php else: ?>
			<div style="height:200px">
			<div class="container"><h2>This exhibit has not been approved. If you are receiving this message in error, please email us - <?php echo mfo_support_email_link()?></h2></div>
			</div>
                        <?php endif?>

                </div><!-- #post-## -->

          <?php endif; //end cred edit test ?>

        </div><!-- #content -->

<?php get_footer(); ?>
