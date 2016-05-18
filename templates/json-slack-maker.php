<?php
//header( 'Content-type: application/json' );
 
/**
 * Template Name: Makers Slack JSON page
 * Description: Searches the list of EXHIBITS as JSON for Slack
 *
 */

$version = get_query_var("version", 1);

$qv_token = get_query_var( 'token' );
$qv_text = get_query_var( 'text' );

if ($qv_token != "zxoXpOoOZKAzAU8N8AuTl6r0") {
wp_send_json("security error - are you calling this from the MFO slack?!");
}

$args = array(
  'post_type' => array('maker', 'exhibit'),
  'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
  's' => $qv_text,
);

$exhibits_array = get_posts($args);

//$exhibits_array = new WP_Query( array( 'post_type' => 'exhibit', 's' => $qv_text ) );

//echo count($exhibits_array);
//echo "\r\n";
//wp_send_json($makers_array);

//todo
//create readable locations
//create qrcodes
//create detail JSON
//validate photo size param
//is my rewrite page cached?

foreach ($exhibits_array as $exhibit) {

	//get the id of the maker
	$maker_id = wpcf_pr_post_get_belongs($exhibit->ID, 'maker');
	$maker = get_post($maker_id);
	unset($m_output);
	$photo_src = wp_get_attachment_image_src( get_post_thumbnail_id($exhibit->ID), 'medium');
	$maker_photo_src = wp_get_attachment_image_src( get_post_thumbnail_id($maker_id), 'medium');

	$images = get_post_meta($exhibit->ID, "wpcf-additional-photos");

	//remove empty array items
	$images = array_filter ($images);

	//get all the sizes
	if (!empty ($images) ) {
		$field = wpcf_fields_get_field_by_slug ("additional-photos");
		unset($images_output); //clear the array
		foreach ( $images as $k=>$v ) {
			$params = array ("size" => "thumbnail", "proportional"=>"false", "url" => "true");
			$params ['field_value'] =$v;
			$thumb =  types_render_field_single( $field, $params, null, '', $k);
			$params ['size'] ='medium';
			$medium =  types_render_field_single( $field, $params, null, '', $k);
			$params ['size'] ='large';
			$large =  types_render_field_single( $field, $params, null, '', $k);
			$params ['size'] ='full';
			$full =  types_render_field_single( $field, $params, null, '', $k);
			$images_output[] = array (
				'thumbnail' => $thumb,
				'medium'    => $medium,
				'large'     => $large,
				'full'      => $full);

		}

	
	}
	$embed_media = get_post_meta($exhibit->ID, "wpcf-embeddable-media", false);

	$m_output = array(
			'name' => html_entity_decode(get_the_title($maker_id)),
			'description' => html_entity_decode($maker->post_excerpt),
			'photo_link' => $maker_photo_src[0]
		);


	//create the array for the exhibit
	$e_output[]= array (
			//'exhibit_category' => strip_tags(get_the_term_list($exhibit->ID, "exhibit-category","",", ")),
			//'hidden_exhibit_category' => strip_tags(get_the_term_list($exhibit->ID, "hidden-exhibit-category","",", ")),
			//'hidden_maker_category' => strip_tags(get_the_term_list($maker_id, "hidden-maker-category","",", ")),
			'title' => html_entity_decode($exhibit->post_title." (". get_post_type($exhibit) .")"),
			//'description' => html_entity_decode(get_post_meta($exhibit->ID, "wpcf-long-description", true)),
			'title_link' => get_permalink($exhibit),
			'text' => html_entity_decode($exhibit->post_excerpt),
			//'location' => strip_tags(get_the_term_list($exhibit->ID, "exhibit-location","",", ")),
			'image_url' => $photo_src[0],
			'thumb_url' => $photo_src[0],
			//'additional_photos'=>$images_output,
			//'embeddable_media'=>$embed_media,
			//'maker' =>$m_output
			);
}

	$taxes = array(
		'exhibit-category',
		'hidden-exhibit-category',
		'hidden-maker-category'
		);
/*
	$cats = get_terms ($taxes);
	unset($cats_output);
	foreach ($cats as $cat) {
		$cats_output[] = array(
			'name' => $cat->name,
			'slug' => $cat->slug,
			'url'  => "http://www.makerfaireorlando.com/makers/?category=".$cat->slug
		);
	}
*/


//create the overall JSON array
$output = array(
		'response_type' => 'in_channel',
		'text' =>'Maker Faire Orlando search results for '. $qv_text,
//		'attend_link' => 'http://www.makerfaireorlando.com/attend',
		'attachments' => $e_output,
//		'categories' => $cats_output,
//		'title' => 'Maker Faire Orlando',
//		'sponsor_link' => 'http://www.makerfaireorlando.com/sponsor',
//		'volunteer_link' => 'http://www.makerfaireorlando.com/volunteers',
//		'about_url' => 'http://www.makerfaireorlando.com/about',
//		'info_url' => 'http://www.makerfaireorlando.com/mobile-app-information-page',
//		'json_version' => $version,
		);

/* Used htaccess rewrite so that I dont need to put the file out
//write to disk for the app (HACK FOR NOW)
$ss_dir = get_stylesheet_directory();
//echo $ss_dir;
//echo "\r\n";
$filename = $ss_dir."/makerlist/json_backup/makerlist_json";
//echo $filename;
//echo "\r\n";
//file_put_contents ( $filename,json_encode( $output));
*/

//send headers & JSON
wp_send_json($output);
?>

