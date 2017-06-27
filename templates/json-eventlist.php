<?php
//header( 'Content-type: application/json' );
 
/**
 * Template Name: Events JSON page
 * Description: Outputs the list of EVENTS as JSON
 *
 */

$sort=0;
/*
function cmpstarttime($a, $b) {
	$sorts++;
	return ($b->start_time_ts - $a->start_time_ts);
}
*/

$version = get_query_var("version", 1);



$event_args = array(
  'post_type' => 'event',
  'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
  //'meta_query' => array(array('key' => 'wpcf-event-date', 'value' => '1442016000'))
);
$events = get_posts($event_args);


//build array of dates
foreach ($events as $event) {
	$dates[] = get_post_meta($event->ID, 'wpcf-event-date', true);
	$dates = array_unique($dates);
} //end foreach events

//sort events into dates
foreach ($dates as $date) {
	
	foreach ($events as $event) {
		if ($date == get_post_meta($event->ID, 'wpcf-event-date', true)) {

			$image_thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($event->ID), 'thumbnail');
			$image_medium = wp_get_attachment_image_src( get_post_thumbnail_id($event->ID), 'medium');
			$image_large = wp_get_attachment_image_src( get_post_thumbnail_id($event->ID), 'large');

			$e_dateunix = get_post_meta($event->ID, "wpcf-event-date", true);
			$e_date = date("F j", $e_dateunix);
			$e_start_time_text =  get_post_meta($event->ID, "wpcf-event-start-time", true);
			$e_start_time_ts = strtotime($e_date . ' '. $e_start_time_text);
			$e_end_time_text =  get_post_meta($event->ID, "wpcf-event-end-time", true);
			$e_end_time_ts = strtotime($e_date . ' '. $e_end_time_text);

 			//create the array for the event, arrayed by date
			//$e_output[$e_dateunix]['events'] = array (
			$events_output[] = array (
				'name' => html_entity_decode($event->post_title),
				'description' => html_entity_decode($event->post_excerpt),
				'maker' => get_post_meta($event->ID, "wpcf-event-maker", true),
				'promo_url' => get_permalink($event),
				'image_thumbnail' => $image_thumbnail[0],
				'image_medium' => $image_medium[0],
				'image_large' => $image_large[0],
				'date' => $e_date,
				'dateunix' =>$e_dateunix,
				'start_time' => $e_start_time_text,
				'start_time_ts' => $e_start_time_ts,
				'end_time' => $e_end_time_text,
				'end_time_ts' => $e_end_time_ts,
				'duration' => get_post_meta($event->ID, "wpcf-event-duration", true),
				'cost' => get_post_meta($event->ID, "wpcf-event-cost", true),
				'additional_info' => html_entity_decode($event->post_content),
				'location' => strip_tags(get_the_term_list($event->ID, "exhibit-location","",", ")),
				'exhibit_category' => strip_tags(get_the_term_list($event->ID, "exhibit-category","",", ")),
				'hidden_exhibit_category' => strip_tags(get_the_term_list($event->ID, "hidden-exhibit-category","",", ")),
				);
		} //end if sort dates
	} //end foreach event

	usort($events_output, function ($a, $b) {
					if ($a['start_time_ts'] == $b['start_time_ts']) {
						//they are equal start times, now check for end times
						if ($a['end_time_ts'] == $b['end_time_ts']) {
							//they are equal end times, now check for duration
							return intval($a['duration']) - intval($b['duration']);
						} //end if equal end times
						return ($a['end_time_ts'] < $b['end_time_ts']) ? -1 : 1;
					} //end if equal start times
					return ($a['start_time_ts'] < $b['start_time_ts']) ? -1 : 1;
					});

	$dates_output[] = array (
		'date_title' =>  date("F j", $date),
		'events' => $events_output,

		);
	unset($events_output);

} //end foreach dates


/*
	$taxes = array(
		'exhibit-category',
		'hidden-exhibit-category',
		'hidden-maker-category'
		);

	$cats = get_terms ($taxes);
	unset($cats_output);
	foreach ($cats as $cat) {
		$cats_output[] = array(
			'name' => $cat->name,
			'slug' => $cat->slug,
			'url'  => get_site_url() . "/makers/?category=".$cat->slug
		);
	}

*/

//create the overall JSON array
$output = array( 'days' => $dates_output );


//send headers & JSON
wp_send_json($output);
?>

