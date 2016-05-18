<?php
/**
 * @package WordPress
 * @subpackage Coraline
 * @since Coraline 1.0
 * Template Name: Load-in Report
 */

/*
todo:
 - Output Location->Slot->Qty Max; Qty Used; Qty Remain->Exhibits

*/

get_header(); ?>

		<div id="content-container">
			<div id="content" role="main">

			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<div class="entry-content">
						<?php the_content(); ?>


<?php

	//get the current count for each slot

	$approved_exhibits_args = array(
  	'post_type' => 'exhibit',
  	'post_status' => 'publish',
  	'posts_per_page' => -1, // all
  	'orderby' => 'title',
  	'order' => 'ASC',
  	'meta_query' => array(array('key' => 'wpcf-approval-status', 'value' => '1'))
	);

	$exhibits = get_posts($approved_exhibits_args);
	//echo "approved-exhibits: " . count($exhibits);

	$slot_count[""]=0; //need it declared since we test before setting
	foreach ($exhibits as $e) {
		$e_slot = get_post_meta( $e->ID, 'wpcf-exhibit-loadin-slot', true );
		if (array_key_exists ($e_slot, $slot_count)) {
			$slot_count[$e_slot]++;
		}
		else {
		$slot_count[$e_slot] = 1;
		}
	} //end foreach exhibit

	ksort($slot_count);

	$noselect = intval($slot_count[""]);
	$numexhibits = count($exhibits);
	$select = $numexhibits-$noselect;
	$n_percent = ($noselect / $numexhibits) * 100;
	$s_percent = ($select / $numexhibits) * 100;

	echo '<h3>' . $numexhibits   . ' Approved Exhibits</h3>';
	echo '<h3>' . $noselect . ' (' . number_format($n_percent, 1, '.', '') . '%)' . ' Exhibits have not selected a Load-in </h3>';
	echo '<h3>' . $select   . ' (' . number_format($s_percent, 1, '.', '') . '%)' . ' Exhibits have selected a Load-in </h3>';
	echo '<hr>';
	//echo "<pre>";
	//print_r($slot_count);
	//echo "</pre>";




	$args = array(
  	'post_type' => 'loadin-location',
  	'post_status' => 'publish',
  	'posts_per_page' => -1, // all
  	'orderby' => 'title',
  	'order' => 'ASC',
 	// 'meta_query' => array(array('key' => 'wpcf-approval-status', 'value' => '1'))
	);

	$locations_array = get_posts($args);
	//echo "<pre>"
	//print_r($locations_array);
	//echo "</pre>";

	foreach ($locations_array as $location) {
		echo '<div style="margin-bottom:20px;">';
		echo '<h2>' . $location->post_title . '</h2>';
		//echo '<div>';
		//echo '</div>';

		$args2 = array(
        	'post_type' => 'loadin-slot',
        	'post_status' => 'publish',
        	'posts_per_page' => -1, // all
        	'orderby' => 'title',
        	'order' => 'ASC',
         	'meta_query' => array(array('key' => '_wpcf_belongs_loadin-location_id', 'value' => $location->ID))
        	);

		$slots_array = get_posts($args2);

		//pull dates
		foreach ($slots_array as $slot) {
			$dates[]=get_post_meta( $slot->ID, 'wpcf-loadin-date', true);
			$dates = array_unique($dates);
		} //end foreach slot
		//echo "<pre>";
        	//print_r($dates);
        	//echo "</pre>";

		foreach ($dates as $key => $value) {
			$accordion_id = $location->ID . '-' .$value;
			$accordions[] = $accordion_id;
			echo '<div style="background-color:#f2f2f2; padding:10px">';
			echo '<h3>' . date("l, F j, Y",$value) . '</h3>';
			echo '<div>';

			foreach ($slots_array as $slot) {
				if (get_post_meta( $slot->ID, 'wpcf-loadin-date', true) == $value) {
					$max = intval(get_post_meta ( $slot->ID, 'wpcf-loadin-max-quantity', true));
					$used = intval($slot_count[$slot->post_title]);
					$remain = $max-$used;

					echo '<div style="padding-left:10px; margin-bottom:10px;">';
					//echo $slot->post_title . ':&nbsp&nbsp&nbsp';
					echo get_post_meta( $slot->ID, 'wpcf-loadin-start-time', true) . "&nbsp&nbsp&nbsp";
					echo 'Max: ' . $max .'&nbsp&nbsp&nbspUsed: ' . $used .'&nbsp&nbsp&nbsp';
					echo 'Remaining: ' . $remain;

					foreach ($exhibits as $e) {
                				$e_slot = get_post_meta( $e->ID, 'wpcf-exhibit-loadin-slot', true );
                				if ($e_slot == $slot->post_title) {
							echo '<div style="margin-left:10px;">';
							echo '<a href="' . get_permalink($e->ID) .'" target="_blank">';
							echo $e->ID . ': ' . $e->post_title . '</a>';
							echo '</div>';
                				}
        				} //end foreach exhibit

					echo '</div>';

				} //end if is the right date

			} //end foreach slot

			echo '</div></div>'; //close the date accordion

		} //end foreach date

		echo '</div>';

	}//end foreach location



?>


						<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'coraline' ), 'after' => '</div>' ) ); ?>
						<?php edit_post_link( __( 'Edit', 'coraline' ), '<span class="edit-link">', '</span>' ); ?>
					</div><!-- .entry-content -->
				</div><!-- #post-## -->

				<?php comments_template( '', true ); ?>

			<?php endwhile; ?>

			</div><!-- #content -->
		</div><!-- #content-container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
