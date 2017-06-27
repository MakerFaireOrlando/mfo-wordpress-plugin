<?php
/**
 * @package WordPress
 * @subpackage Coraline
 * @since Coraline 1.0
 * Template Name: Load-in
 */

/*
todo:
 - color the timeslot buttons by availablity?
 - add more to the description of each location - picture, etc.
 - TEST, TEST, TEST
 - check if current location no longer matches load-in location
 - add confirmation dialog - http://jsfiddle.net/taditdash/vvjj8/
 - email when someone releases their slot
*/

get_header(); ?>

		<div id="content-container">
			<div id="content" role="main">

			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<div class="entry-content">
						<?php the_content(); ?>

<form id="slotform" action="" method="post">
  <input type="hidden" name="slot-id"/>
</form>

<?php
//  <input type="submit" name="SubmitButton"/>

$exhibit = intval(get_query_var ("li-exhibit",0));
$exhibit_author = get_post_field( 'post_author', $exhibit);


$curuser = wp_get_current_user();

//echo $curuser->ID . " " . $curuser->display_name . "<BR>";
//echo $exhibit_author . "<BR>";
//echo 'The post type is: ' . get_post_type($exhibit) . '<br>';
//echo "exhibit: ".$exhibit."<br>";

if (get_post_type($exhibit) != "exhibit") {
	echo '<h3>System Error: invalid post type.</h3>Please return to Maker Dashboard and report this issue.';
	}

elseif ($curuser->ID != $exhibit_author AND !current_user_can( 'manage_options' )) {
	echo '<h3>System Error</h3>Please return to Maker Dashboard and report this issue.';
	}
	//TODO: Also test if current user is has the rights to edit the exhibit...
else { 

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
 
	//echo "<pre>";
	//print_r($slot_count);
	//echo "</pre>";



	//is this a form post?

	if (isset($_POST['slot-id']))
	{

		//echo "<pre>slot-id:";
		//print_r ($_POST);
		//echo "</pre>";

		$slot_id = $_POST['slot-id'];

		//echo "<pre>slot_id:";
		//print_r ($slot_id);
		//echo "</pre>";

		if ($slot_id == 1) {
			//this is a request to unset
			$update = update_post_meta( $exhibit, 'wpcf-exhibit-loadin-slot', "");
		}
		elseif ($slot_id >1) {
			$slot_name = get_the_title($slot_id);
			$slot_max_qty = get_post_meta ( $slot_id, 'wpcf-loadin-max-quantity', true);

			if ($slot_max_qty > $slot_count[$slot_name]) {

			$exhibit_slot_title = get_post_meta( $exhibit, 'wpcf-exhibit-loadin-slot', true );
			if ($exhibit_slot_title != $slot_name) { //same slot selected
				$update = update_post_meta( $exhibit, 'wpcf-exhibit-loadin-slot', $slot_name );
				if ($update) {
					echo '<div style="color:white; background-color:green; margin-bottom:20px;">';
					echo 'Exhibit Load-in updated.';
					echo '</div>';
					$headers = 'From: '. mfo_event_name() . '<' . mfo_support_email() . '>' . "\r\n";
					$subject = 'Load-in selected for ';
					$subject = $subject . get_the_title($exhibit);
					$message = $subject . ' (' . $exhibit  . ')' . ' - ' . $slot_name . "\r\n\r\n";
					$message = $message . 'Username: ' . $current_user->user_login . "\r\n";
					$message = $message . 'User email: ' . $current_user->user_email . "\r\n";
					$from = mfo-support-email();
					//$from = "ian.cole@gmail.com";
					wp_mail($from, $subject, $message, $headers);

				}
				else {
					echo '<div style="color:white; background-color:red; margin-bottom:20px;">';
					echo 'Update error: Please report this error.';
					echo '</div>';
				}
			} // end if update worked
			else {
				echo '<div style="color:white; background-color:blue; margin-bottom:20px;">';
				echo 'Same load-in slot selected, no change made.';
				echo '</div>';
			} //end if same slot
			} //end if slot qty ok
			else {
				echo '<div style="color:white; background-color:red; margin-bottom:20px;">';
				echo 'Error: Slot no longer available.';
				echo '</div>';
			}

		}
		else {
			echo '<div style="color:white; background-color:red; margin-bottom:20px;">';
			echo 'Form Error: No slot id. Please report this error.';
			echo '</div>';
		}


	} //end is set post




	$exhibit_locations = wp_get_post_terms($exhibit, "exhibit-location");
	//echo "<pre>terms for ". $exhibit . ":";
	//print_r($exhibit_locations);
	//echo "</pre>";
	echo '<div style="margin-bottom:20px">';
	echo '<h3>Exhibit Information</h3>';
	echo "Exhibit Name: <b>" . get_the_title($exhibit) . "</b><br>";
	echo "Exhibit Location: <b>";

	$error_location=0;

	if ((count($exhibit_locations) == 0) OR
		( count($exhibit_locations) == 1 AND $exhibit_locations[0]->name=="Undefined")) {
		$error_location = 1;
		echo 'Error: Exhibit location has not been set. ';
		echo 'You will not be able to select or change your load-in date / time. ';
		echo 'Please email us at ';
		echo do_shortcode('[mfo-support-email-link]');
		echo ' if you see this error message.';
	}
	else { 
		foreach ($exhibit_locations as $term) {
			echo $term->name . " ";
		} //end exhibit_locations
	} //end if undefined or no location
	echo '</b></div>';


	//get the loadin slot for the exhibit
	$exhibit_slot_title = get_post_meta( $exhibit, 'wpcf-exhibit-loadin-slot', true );
	//since we store the slot title, get the slot object from that title
	$exhibit_slot = get_page_by_title($exhibit_slot_title, OBJECT, 'loadin-slot');

	if ($exhibit_slot) {
		$exhibit_slot_time = get_post_meta( $exhibit_slot->ID, 'wpcf-loadin-start-time', true );
		$exhibit_slot_dateunix = get_post_meta( $exhibit_slot->ID, 'wpcf-loadin-date', true );
		$exhibit_slot_date = date("l, F j, Y", $exhibit_slot_dateunix);
		$exhibit_loc = get_post_meta( $exhibit_slot->ID, '_wpcf_belongs_loadin-location_id', true );
		$exhibit_slot_loc = get_the_title($exhibit_loc);

		//echo "<pre>";
		//print_r($exhibit_locations);
		//echo "</pre>";

		echo '<h3>Currently Assigned Load-in</h3>';
		echo "Exhibit Load-in Location: <b>" .$exhibit_slot_loc . "</b><br>"; 
		echo "Exhibit Load-in Date: <b>" . $exhibit_slot_date . "</b><br>"; 
		echo "Exhibit Load-in Time: <b>" . $exhibit_slot_time . "</b><br>"; 
		echo '</div>';

		echo '<div style="margin-bottom:20px">';
		echo '<button class="ui-widget" id="release" onclick="saveSlot(1)">Cancel this load-in date / time</button>';
		echo '</div>';

		echo '<div style="margin-bottom:20px">';
		echo get_post_field('post_content', $exhibit_loc);
		echo '</div>';
	}



	$args = array(
  	'post_type' => 'loadin-location',
  	'post_status' => 'publish',
  	'posts_per_page' => -1, // all
  	'orderby' => 'title',
  	'order' => 'ASC',
 	// 'meta_query' => array(array('key' => 'wpcf-approval-status', 'value' => '1'))
	);

	$locations_array = get_posts($args);
	//echo "<pre>";
	//print_r($locations_array);
	//echo "</pre>";

	if (!$error_location) {
	echo '<h3>Choose a load-in location and time below</h3> ';
	echo '<div style="margin-bottom:20px">';
	echo '<b>Click on a location below to see available times. ';
	echo 'Times not shown are no longer available. ';
	echo 'Clicking on a time will change your load-in to that location and time. ';
	echo 'If you are exhibiting on Sunday ONLY, please contact us at ';
	echo do_shortcode("[mfo-support-email-link]");
	echo ' to determine the best approach.</b>';
	echo '</div>';
	}


	echo '<div id="location-accordion">';
	foreach ($locations_array as $location) {

		//get the exhibit-locations taxonomy terms that are mapped to this loadin location
		$location_locations = wp_get_post_terms($location->ID, "exhibit-location");
		//echo "<pre>";
		//print_r($location_locations);
		//echo "</pre>";



		//check to see if this loadin location has terms that match any of the terms on the exhibit location
		$match = 0;
		foreach ($location_locations as $loc) {
			foreach ($exhibit_locations as $esl) {
				if ($esl->term_id == $loc->term_id) {
					$match = 1;
				} //end if
			} // end foreach $exhibit_slot_locations

		}//end foreach location_locations

		if ($match==0) continue; //don't show this location.

		echo '<h3>' . $location->post_title . '</h3>';
		echo '<div>';
		echo "$location->post_excerpt<br><br>";
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
			echo '<div id="accordion-' . $accordion_id .'" style="background-color:#f2f2f2; padding:10px">';

			echo '<h3>' . date("l, F j, Y",$value) . '</h3>';

			echo 'Click a time to select it for your load-in.';

			echo '<div class="loadin-times-group" >';

			//todo: print message if no slots left, or switch t disabled buttons

			foreach ($slots_array as $slot) {
				if (get_post_meta( $slot->ID, 'wpcf-loadin-date', true) == $value) {
					if (get_post_meta ( $slot->ID, 'wpcf-loadin-max-quantity', true) > 
					   $slot_count[$slot->post_title]) {
						echo '<div style="padding:10px">';
						//echo get_post_meta( $slot->ID, 'wpcf-loadin-start-time', true) . " ";
						echo '<button id="' . $slot->post_title .'" onClick="saveSlot(' . $slot->ID.')">';
						echo get_post_meta( $slot->ID, 'wpcf-loadin-start-time', true) . " ";
						echo '</button>';
						echo '</div>';
					}//end if less than slot count max

				} //end if is the right date

			} //end foreach slot

			echo '</div></div>'; //close the date accordion

		} //end foreach date

		echo '</div>';

	}//end foreach location

	echo '</div>';

}//end is valid incoming exhibit



?>
<script>

function saveSlot(btn_id){
	console.log(btn_id);
	jQuery("input").val(btn_id);
	jQuery("#slotform").submit();
};


jQuery(function() {
jQuery( "#location-accordion").accordion( {
	active: false,
	collapsible: true
});
<?php
 foreach ($accordions as $a) {
//	echo 'jQuery( "#accordion-' . $a . '").accordion();';
 }
?>
});
</script>




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
