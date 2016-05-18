<?php
/*
Template Name: CSV-Social
Description: CSV Output for Maker Social Info
Author: Ian Cole ian.cole@gmail.com
Date: Sep 6th, 2015
*/


	//set the filename from parameter if exists
	$fname = "maker-social";
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



//print_r($exhibits_array);

	echo 'exhibit-id,exhibit-name,maker-id,maker-name,maker-last-name,maker-first-name,maker-email,';
	echo 'maker-twitter,maker-instagram, maker-facebook,'; 
	echo 'exhibit-twitter,exhibit-instagram, exhibit-facebook'; 
	echo "\r\n";


foreach ($exhibits_array as $exhibit) {

	unset($base_locs);
	unset($base_loc);
	unset($ids);
	unset($parents);
	unset($top);


	//setup
	$maker_id = wpcf_pr_post_get_belongs($exhibit->ID, 'maker');
        $maker = get_post($maker_id);




	echo $exhibit->ID; //exhibit id
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
	echo get_post_meta($maker_id, "wpcf-twitter-url", true);
	echo '"';
	echo ',';
	echo '"';
	echo get_post_meta($maker_id, "wpcf-instagram-url", true);
	echo '"';
	echo ',';
	echo '"';
	echo get_post_meta($maker_id, "wpcf-facebook-url", true);
	echo '"';
	echo ',';
	echo '"';
	echo get_post_meta($exhibit->ID, "wpcf-twitter-url", true);
	echo '"';
	echo ',';
	echo '"';
	echo get_post_meta($exhibit->ID, "wpcf-instagram-url", true);
	echo '"';
	echo ',';
	echo '"';
	echo get_post_meta($exhibit->ID, "wpcf-facebook-url", true);
	echo '"';

	echo "\r\n";
}//end for each exhibit



?>
