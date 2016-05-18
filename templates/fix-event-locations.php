<?php
/*
Template Name: Fix event locations
Description: hack to set ancestors on locations
Author: Ian Cole ian.cole@gmail.com
Date: Sep 6th, 2015
*/



echo "<html><body><pre>";
$args = array(
  'post_type' => 'event',
  'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
  //'meta_query' => array(array('key' => 'wpcf-approval-status', 'value' => '1'))
);

$events_array = get_posts($args);



//print_r($events_array);
echo count($events_array) . "\r\n";


foreach ($events_array as $event) {

	//location mess
	$locterms = get_the_terms($event->ID, "exhibit-location");

	foreach ($locterms as $locterm) {

	if (!array_search($locterm->parent, $locterms)) {
		echo "missing - adding: " . $locterm->parent ." : " . get_term_by('id', $locterm->parent, 'exhibit-location')->name;;
		echo "\r\n";
		 wp_set_object_terms ($event->ID, $locterm->parent, "exhibit-location", true);
		}
	


/*		//implementation found here: http://www.simonbattersby.com/blog/2014/06/getting-the-lowest-level-category-or-custom-taxonomy-for-a-wordpress-post/
		$ids[]  = $locterm->term_id; //this is an array of term_ids
		if ($locterm->parent) $parents[] = $locterm->parent; //this is an array of term_ids
		if ($locterm->parent == 0) $top[] = $locterm; //note this is an array of terms
	} //end foreach locterms
//	print_r ($ids);
//	print_r ($parents);

	$base_locs = array_diff($ids, $parents);
//	print_r ($base_locs);

	$num_base_locs = count($base_locs);
	$base_loc = array_shift($base_locs);

	if ($num_base_locs == 1) { //only one base exhibit location
		$locarea = get_term_by('id', $base_loc, 'exhibit-location')->name;
		if (count($top) == 1) {
			$locfloor = $top[0]->name;
		}
		else {
		 $locfloor = "ERROR - CHECK LOCATION"; 
		//print_r($locterms); //something up, lets see
		}
	}

	elseif ($num_base_locs == 0) { //only one base location with no parent
                $locfloor = $top[0]->name;
                $locarea = $top[0]->name;
                //print_r ($locterms);
        }
	else {
		$locfloor = "MULTIPLE";
		$locarea = "MULTIPLE";
 		//print_r ($locterms);
	}
*/
	//print_r ($locterm);


}
}//end for each exhibit

	echo "</pre></body></html>";


?>
