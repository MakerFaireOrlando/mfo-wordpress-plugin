<?php
/*
Template Name: CSV-Exhibit Checkin
Description: CSV Output for Exhibits
Author: Ian Cole ian.cole@gmail.com
Date: Sep 6th, 2015
*/


	//set the filename from parameter if exists
	$fname = "exhibit-checkin";
	if(isset($wp_query->query_vars['csv-filename'])) {
		$fname = urldecode($wp_query->query_vars['csv-filename']);
	}
	//append the date & time to make file unique
	$fname=$fname."_".date('Y_m_d_His');
	$fname=$fname.".csv";
	//set the headers so the browser knows this is a download
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private", false);
	//header("Content-Type: application/octet-stream");
	header("Content-Type: text/csv");
	//header("Content-Disposition: attachment; filename=\"report.csv\";" );
	header("Content-Disposition: attachment; filename=\"".$fname."\";" );
	//header("Content-Transfer-Encoding: binary");



$args = array(
  'post_type' => 'exhibit',
  'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
  'meta_query' => array(array('key' => 'wpcf-approval-status', 'value' => '1'))
);

$exhibits_array = get_posts($args);


$helpers_array = get_posts($h_args);

//print_r($exhibits_array);

	echo 'exhibit-id,exhibit-year,exhibit-name,maker-id,maker-name,maker-last-name,maker-first-name,maker-email,maker-phone,';
	echo 'location-floor,location-area,load-in,helper-qty,agreement-status,seller-fee-status,chase-score' . "\r\n";


foreach ($exhibits_array as $exhibit) {

	unset($base_locs);
	unset($base_loc);
	unset($ids);
	unset($parents);
	unset($top);


	//setup
	$maker_id = wpcf_pr_post_get_belongs($exhibit->ID, 'maker');
        $maker = get_post($maker_id);


	$score = 0;

	//agreement_status
	$agreement_status = "NO ACK";
	if (get_post_meta($maker_id, "wpcf-maker-agreement-ack", true)) $agreement_status = "ACK";
	else $score++; //add 1 for missing ack

	//loadin
	$loadin = get_post_meta($exhibit->ID, "wpcf-exhibit-loadin-slot", true);
	if (!$loadin) $score++; //add 1 for missing loadin

	//seller fee
	$sellerfee = (do_shortcode('[types field="payment-status" id="' . $exhibit->ID . '"]'));
	if (get_post_meta($exhibit->ID, "wpcf-payment-status", true) == 2) $score++; //add 1 for unpaid fee
	//echo types_render_field("payment-status",array("output"=>"html")); //this won't work because you can't specify the ID
	//echo get_post_meta($exhibit->ID, "wpcf-payment-status", true); // this works but only gives you the raw value


	//exhibit-year
	$exhibityear = get_post_meta($exhibit->ID, "wpcf-approval-year", true);


	//location mess
	$locterms = get_the_terms($exhibit->ID, "exhibit-location");

	foreach ($locterms as $locterm) {
		//implementation found here: http://www.simonbattersby.com/blog/2014/06/getting-the-lowest-level-category-or-custom-taxonomy-for-a-wordpress-post/
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

//	print_r ($locterms);


	//helpers

	$childargs = array(
	'post_type' => 'exhibit-helper',
	'numberposts' => -1,
	//'meta_key' => 'wpcf-description',
	//'orderby' => 'meta_value',
	//'order' => 'ASC',
	'meta_query' => array(array('key' => '_wpcf_belongs_exhibit_id', 'value' => $exhibit->ID))
	);
	$helpers = get_posts($childargs);
	$helpercount = count($helpers);
	if (!$helpercount) $score++; //add 1 for no helpers
	//print_r($helpers);

	echo $exhibit->ID; //exhibit id
	echo ',';
	echo $exhibityear;
	echo ',';
	echo '"';
	echo html_entity_decode($exhibit->post_title); //exhibit name
	echo '"';
	echo ',';
	echo $maker->ID; //maker id
	echo ',';
	echo '"';
	echo html_entity_decode($maker->post_title); //maker name
	echo '"';
	echo ',';
	echo '"';
	echo get_post_meta($maker_id, "wpcf-last-name", true);
	echo '"';
	echo ',';
	echo '"';
	echo get_post_meta($maker_id, "wpcf-first-name", true);
	echo '"';
	echo ',';
	echo '"';
	echo get_post_meta($maker_id, "wpcf-contact-email", true);
	echo '"';
	echo ',';
	echo '"';
	echo get_post_meta($maker_id, "wpcf-contact-phone", true);
	echo '"';
	echo ',';
	echo '"';
	echo $locfloor;
	echo '"';
	echo ',';
	echo '"';
	echo $locarea;
	echo '"';
	echo ',';
	echo '"';
	echo $loadin;
	echo '"';
	echo ',';
	echo $helpercount;
	echo ',';
	echo '"';
	echo $agreement_status;
	echo '"';
	echo ',';
	echo '"';
	echo $sellerfee;
	echo '"';
	echo ',';
	echo $score;


	echo "\r\n";
}//end for each exhibit



?>
