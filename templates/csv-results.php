<?php
/*
Template Name: CSV-Results
Description: CSV Output for wp-types.com toolset views
Author: Ian Cole ian.cole@gmail.com
Date: May 20th, 2015
*/


	//set the filename from parameter if exists
	$fname = "export";
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
	the_post();

foreach ( array (
		'bloginfo'
	,	'comment_text'
	,	'comment_author'
	,	'link_name'
	,	'link_description'
	,	'link_notes'
	,	'list_cats'
	,	'single_post_title'
	,	'single_cat_title'
	,	'single_tag_title'
	,	'single_month_title'
	,	'term_description'
	,	'term_name'
	,	'the_content'
	,	'the_excerpt'
	,	'the_title'
	,	'nav_menu_attr_title'
	,	'nav_menu_description'
	,	'widget_title'
	,	'wp_title'
	) as $target )
{
	remove_filter( $target, 'wptexturize' );
}
	the_content();
foreach ( array (
		'bloginfo'
	,	'comment_text'
	,	'comment_author'
	,	'link_name'
	,	'link_description'
	,	'link_notes'
	,	'list_cats'
	,	'single_post_title'
	,	'single_cat_title'
	,	'single_tag_title'
	,	'single_month_title'
	,	'term_description'
	,	'term_name'
	,	'the_content'
	,	'the_excerpt'
	,	'the_title'
	,	'nav_menu_attr_title'
	,	'nav_menu_description'
	,	'widget_title'
	,	'wp_title'
	) as $target )
{
	add_filter( $target, 'wptexturize' );
}?>
