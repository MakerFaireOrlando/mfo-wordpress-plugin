<?php

/*
mfo-cleanup.php
This file is for cleanup functions with special uses
Use with extreme care :)
*/

/*
//reset min default helpers - DOES not change if already at or above min
function mfo_utility_reset_exhibit_helpers() {

 $year = mfo_event_year();
  mfo_log (4, "mfo_utility_reset_exhibit_helpers", "year: " . $year);
 echo "Year: " . $year . "<br>";
        
$args = array(
  'post_type' => 'exhibit',
  'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
  'meta_query' => array(array('key' => 'wpcf-approval-year', 'value' => mfo_event_year()))
);

 $exhibits_array = get_posts($args);

 $haq_default = mfo_exhibithelpers_default();
 echo "Default HAQ = " . $haq_default. "<br>";

 echo "Exhibits: " . count($exhibits_array) . "<br>";

 foreach ($exhibits_array as $exhibit) {
        $haq = get_post_meta($exhibit->ID, "wpcf-helper-approved-quantity", true);
        echo "Exhibit: " . $exhibit->post_name . " - haq=". $haq ."<br>";
        if ($haq < $haq_default) {
                echo "Updating min <br>";
                update_post_meta($exhibit->ID, "wpcf-helper-approved-quantity", $haq_default);
        }
 }

}

//commented for safety!
//add_shortcode('mfo-utility-reset-exhibit-helpers', 'mfo_utility_reset_exhibit_helpers');
*/


/* */
//remove all exhibit space numbers
function mfo_utility_reset_exhibit_space_numbers() {

 $year = mfo_event_year();
  mfo_log (4, "mfo_utility_reset_exhibit_space_numbers", "year: " . $year);
 echo "Year: " . $year . "<br>";
        
$args = array(
  'post_type' => 'exhibit',
  'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
  'meta_query' => array(array('key' => 'wpcf-approval-year', 'value' => mfo_event_year()))
);

 $exhibits_array = get_posts($args);

 echo "Exhibits: " . count($exhibits_array) . "<br>";

 foreach ($exhibits_array as $exhibit) {
	$esn = get_post_meta($exhibit->ID, "wpcf-exhibit-space-number", true);
	echo "Exhibit: " . $exhibit->post_name . " - exhibit-space-number: ". $esn ."<br>";
     	update_post_meta($exhibit->ID, "wpcf-exhibit-space-number", '');
        }

}

//commented for safety!
//add_shortcode('mfo-utility-reset-exhibit-space-numbers', 'mfo_utility_reset_exhibit_space_numbers');




//strip all locations from current year exhibits
//this was needed because the duplicate exhibit function
//was copying over the exhibit-location taxonomy
function mfo_utility_strip_exhibit_locations() {

 $year = mfo_event_year();
 mfo_log (4, "mfo_utility_strip_exhibit_locations", "year: " . $year);
 echo "Year: " . $year . "<br>";

$args = array(
  'post_type' => 'exhibit',
  'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
  'meta_query' => array(array('key' => 'wpcf-approval-year', 'value' => mfo_event_year()))
);

 $exhibits_array = get_posts($args);
 echo "Exhibits: " . count($exhibits_array) . "<br>";

 $taxes = array('exhibit-location', 'hidden-exhibit-category');
 foreach ($exhibits_array as $exhibit) {
 	echo "Exhibit: " . $exhibit->post_name . "<br>";
	wp_delete_object_term_relationships ($exhibit->ID, $taxes);
 }

}

//commented for safety!
//add_shortcode('mfo-utility-strip-exhibit-locations', 'mfo_utility_strip_exhibit_locations');


//reset all agreement status
//need to do this anytime the agreement changes and needs to be acknowledged by all
//typically once a year
//tracking agreements per year is just too much of a pain to be worth it

function mfo_utility_strip_maker_agreement_acks() {

 mfo_log (1, "mfo_utility_strip_maker_agreement_acks", "start");


$args = array(
  'post_type' => 'maker',
  //'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
);

 $makers_array = get_posts($args);
 echo "Makers: " . count($makers_array) . "<br>";

 foreach ($makers_array as $maker) {
        echo "Maker: " . $maker->post_name . "<br>";
	mfo_log (4, "mfo_utility_strip_maker_agreement_acks", "Maker: " . $maker->post_name);
	update_post_meta( $maker->ID, "wpcf-maker-agreement-ack","");
	update_post_meta( $maker->ID, "wpcf-maker-agreement-date","");
	update_post_meta( $maker->ID, "wpcf-maker-agreement-user-name","");
	update_post_meta( $maker->ID, "wpcf-maker-agreement-user-id","");
 }

}

//commented for safety!
add_shortcode('mfo-utility-strip-maker-agreement-acks', 'mfo_utility_strip_maker_agreement_acks');


function mfo_utility_update_maker_stats_all() {

 mfo_log (4, "mfo_utility_update_maker_stats_all", "start");

$args = array(
  'post_type' => 'maker',
  //'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
);

 $makers_array = get_posts($args);

 foreach ($makers_array as $maker) {
        echo "Updating stats for Maker: " . $maker->post_name . "<br>";
        update_maker_stats($maker->ID);
 }

}

//commented for safety!
//add_shortcode('mfo-utility-update-maker-stats-all', 'mfo_utility_update_maker_stats_all');




function mfo_utility_set_all_exhibits_to_pending() {


$year = mfo_event_year();
 mfo_log (4, "mfo_utility_set_all_exhibits_to_pending", "start");
 echo "Year: " . $year . "<br>";

$args = array(
  'post_type' => 'exhibit',
  'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
  'meta_query' => array(array('key' => 'wpcf-approval-year', 'value' => mfo_event_year()))
);

 $exhibits_array = get_posts($args);
 echo "Exhibits: " . count($exhibits_array) . "<br>";
 foreach ($exhibits_array as $exhibit) {
 	echo "Exhibit: " . $exhibit->post_name . "<br>";
	update_post_meta( $exhibit->ID, "wpcf-approval-status","2"); //pending
 }


}

//commented for safety!
//add_shortcode('mfo-utility-set_all_exhibits_to_pending', 'mfo_utility_set_all_exhibits_to_pending');



function mfo_utility_fix_missing_approval_dates() {


$year = mfo_event_year();
 mfo_log (4, "mfo_utility_fix_missing_approval_dates", "start");
 echo "Year: " . $year . "<br>";

$args = array(
  'post_type' => 'exhibit',
  'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
  'meta_query' => array(array('key' => 'wpcf-approval-year', 'value' => mfo_event_year()))
);

 $exhibits_array = get_posts($args);
 echo "Exhibits: " . count($exhibits_array) . "<br>";
 foreach ($exhibits_array as $exhibit) {

	$approval_status = get_post_meta($exhibit->ID, "wpcf-approval-status", true);
	if ($approval_status == 1)  {
		$approval_status_date = get_post_meta($exhibit->ID, "wpcf-approval-status-date", true);
		if (!is_numeric($approval_status_date)) {
	       	 	echo "Exhibit: " . $exhibit->post_name ."; " . $approval_status."; ". $approval_status_date . "<br>";
        		update_post_meta( $exhibit->ID, "wpcf-approval-status-date","1532390400"); 

		} //end if approval-status-date is null
		else echo "Exhibit: " . $exhibit->post_name ."; " . $approval_status."; ". $approval_status_date . "; SKIPPED<br>";

	} //end if approval-status == 1
 }


}

//commented for safety!
//add_shortcode('mfo-utility-fix-missing-approval-dates', 'mfo_utility_fix_missing_approval_dates');

/*
function mfo_utility_delete_all_eventbrite_orders() {

 mfo_log (4, "mfo_utility_delete_all_eventbrite_orders", "start");

$args = array(
  'post_type' => 'eventbrite-order',
  //'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
);

 $orders_array = get_posts($args);
 echo count($orders_array)  .' objects<br>';

 $ord = 1;
 foreach ($orders_array as $order) {
        echo $ord. ":" ."Deleting Order: "  . $order->post_name . "<br>";

	mfo_log(1, "mfo_utility_delete_all_eventbrite_orders", $ord. ":" ."Deleting Order: "  . $order->post_name);
        wp_delete_post($order->ID, 1); //force_delete, not just move to trash
	$ord++;
	if ($ord>100) break;
 }

}

//commented for safety!
add_shortcode('mfo-utility-delete-all-eventbrite-orders', 'mfo_utility_delete_all_eventbrite_orders');
*/
/*
function mfo_utility_delete_all_exhibit_helpers() {

 mfo_log (4, "mfo_utility_delete_all_exhibit_helpers", "start");

$args = array(
  'post_type' => 'exhibit-helper',
  //'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
);

 $helper_array = get_posts($args);
 echo count($helper_array)  .' objects<br>';

 $hlp = 1;
 foreach ($helper_array as $helper) {
        echo $hlp. ":" ."Deleting Helper: "  . $helper->post_title . "<br>";

	mfo_log(1, "mfo_utility_delete_all_exhibit_helper", $ord. ":" ."Deleting Helper: "  . $helper->post_title);
        wp_delete_post($helper->ID, 1); //force_delete, not just move to trash
	$hlp++;
	if ($hlp>100) break;
 }

}

//commented for safety!
add_shortcode('mfo-utility-delete-all-exhibit_helpers', 'mfo_utility_delete_all_exhibit_helpers');
*/
?>
