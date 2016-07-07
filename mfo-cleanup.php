<?php

/*
mfo-cleanup.php
This file is for cleanup functions with special uses
Use with extreme care :)
*/


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

 mfo_log (4, "mfo_utility_strip_maker_agreement_acks", "start");

$args = array(
  'post_type' => 'maker',
  //'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
);

 $makers_array = get_posts($args);
 echo "Exhibits: " . count($makers_array) . "<br>";

 foreach ($makers_array as $maker) {
        echo "Maker: " . $maker->post_name . "<br>";
	update_post_meta( $maker->ID, "wpcf-maker-agreement-ack","");
	update_post_meta( $maker->ID, "wpcf-maker-agreement-date","");
	update_post_meta( $maker->ID, "wpcf-maker-agreement-user-name","");
	update_post_meta( $maker->ID, "wpcf-maker-agreement-user-id","");
 }

}

//commented for safety!
//add_shortcode('mfo-utility-strip-maker-agreement-acks', 'mfo_utility_strip_maker_agreement_acks');

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


?>
