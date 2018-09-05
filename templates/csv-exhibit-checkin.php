<?php
/*
Template Name: CSV-Exhibit Checkin
Description: CSV Output for Exhibits
Author: Ian Cole ian.cole@gmail.com
Date: Sep 6th, 2015
*/


 	//set the year from parameter if exists
        $year = "0";
        if(isset($wp_query->query_vars['csv-year'])) {
                $year = urldecode($wp_query->query_vars['csv-year']);
        }


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


	echo $year ."\r\n";
	echo 'exhibit-id,exhibit-year,exhibit-name,maker-id,maker-name,maker-last-name,maker-first-name,maker-email,maker-phone,';
	echo 'location-floor,location-area,load-in,helper-qty,agreement-status,seller-fee-status,chase-score,approval-date,power-amps,';
	echo 'electrical-items, power-dedicated,water-required,inside-outside,light-dark,quiet-loud,location-notes,with-group,related-group,';
	echo 'share-table,internet-requirements,internet-requirements-notes,haz-chemicals,haz-chemicals-notes,heat-flame,heat-flame-notes,';
	echo 'safety-concerns,safety-concerns-notes,hours-to-setup,bringing-a-tent,bringing-a-tent-notes,makes-loud-noise,makes-loud-noise-notes,anything-else' . "\r\n";


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

	//if year was specified, check the year, and skip if not a match
	if ($year) {
		if ($exhibityear != $year) continue;
	}

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

	$approvaldateunix = get_post_meta($exhibit->ID, "wpcf-approval-status-date", true);
	$approvaldate= date('Y-m-d', $approvaldateunix);

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
	echo ',';
        echo '"';
        echo $approvaldate;
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('power-amps', $exhibit->ID);
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('electrical-items-for-my-exhibit', $exhibit->ID);
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('power-dedicated', $exhibit->ID);
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('location-requirement-water', $exhibit->ID);
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('location-requirement-in-out', $exhibit->ID);
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('location-requirement-light-dark', $exhibit->ID);
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('location-requirement-quiet-loud', $exhibit->ID);
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('location-requirement-notes', $exhibit->ID);
        echo '"';
	 echo ',';
        echo '"';
        echo mfo_trf('exhibiting-with-a-group', $exhibit->ID);
        echo '"';
	 echo ',';
        echo '"';
        echo mfo_trf('related-group', $exhibit->ID);
        echo '"';
	 echo ',';
        echo '"';
        echo mfo_trf('share-table', $exhibit->ID);
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('internet-requirements', $exhibit->ID);
        echo '"';
        echo ',';
        echo '"';
        echo mfo_trf('internet-requirements-notes', $exhibit->ID);
        echo '"';
        echo ',';
        echo '"';
        echo mfo_trf('hazardous-chemicals', $exhibit->ID);
        echo '"';
	 echo ',';
        echo '"';
        echo mfo_trf('hazardous-chemicals-notes', $exhibit->ID);
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('heat-flame', $exhibit->ID);
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('heat-flame-notes', $exhibit->ID);
        echo '"';
 	echo ',';
        echo '"';
        echo mfo_trf('safety-concerns', $exhibit->ID);
        echo '"';
 	echo ',';
        echo '"';
        echo mfo_trf('safety-concerns-notes', $exhibit->ID);
        echo '"';
	 echo ',';
        echo '"';
        echo mfo_trf('hours-to-setup', $exhibit->ID);
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('bringing-a-tent', $exhibit->ID);
        echo '"';
	 echo ',';
        echo '"';
        echo mfo_trf('bringing-a-tent-notes', $exhibit->ID);
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('makes-loud-noise', $exhibit->ID);
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('makes-loud-noise--notes', $exhibit->ID);
        echo '"';
	echo ',';
        echo '"';
        echo mfo_trf('anything-else', $exhibit->ID);
        echo '"';
	echo "\r\n";
}//end for each exhibit


function mfo_trf($slug, $id, $strip = TRUE) {
	$fieldtext = types_render_field($slug, array('id' => $id));
	$fieldtext = str_replace ('"', '`', $fieldtext);
	if ($strip) $fieldtext = wp_strip_all_tags($fieldtext);
	return $fieldtext;
}

?>
