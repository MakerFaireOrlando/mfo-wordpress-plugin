<?php
//header( 'Content-type: application/json' );

/**
 * Template Name: Jekyll Build
 * Description: Outputs EXHIBITS as a Jekyll Collection folder
 *
 */


/**
 * YAML notes: https://yaml-multiline.info/
 * We are targeting flow scalars to not need the spaces at the beginning
 * We want to use double quotes so that we get newlines to flow all the way through
 */
function mfo_yaml_prep($varname, $vartext)
{
	if (strlen($vartext))
	{
	  // remove any escaped characters
	  // we see this when people copy and paste from a website
	  //$vartext = urldecode($vartext); //urldecode eats + signs, so removing it.

	  $vartext = str_replace('\\', '', $vartext);

	  // escape newlines
	  str_replace('\n', "\\n", $vartext);

	  // escape double quotes, but not single quotes
	  $vartext = addcslashes($vartext, "\"");

	  //add variable name, wrap text in double quotes
	  $out = $varname . ': ' . '"' . $vartext . '"' . "\n";
	  return $out;
	}
	else
	{
	  $out = $varname . ':' . "\n";
	  return $out;
	}
}



$version = get_query_var("version", 1);

mfo_log(1,"jekyll-build", "starting...");

$args = array(
  'post_type' => 'exhibit',
  'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
  'meta_query' => array(
	array(
		'key' 		=> 'wpcf-approval-year',
		'value' 	=> mfo_event_year(),
		'compare'	=> '='
	)
  )
);

$exhibits_array = get_posts($args);

$exhibits_count = 0;

foreach ($exhibits_array as $exhibit) {

	//get the id of the maker
	$maker_id = wpcf_pr_post_get_belongs($exhibit->ID, 'maker');
	$maker = get_post($maker_id);
	unset($m_output);

	//note that the photo url is the first element, hence the index at the end of the lines below
	$photo_src_thumb  = wp_get_attachment_image_src( get_post_thumbnail_id($exhibit->ID), 'thumbnail')[0];
	$photo_src_medium = wp_get_attachment_image_src( get_post_thumbnail_id($exhibit->ID), 'medium')[0];
	$photo_src_large  = wp_get_attachment_image_src( get_post_thumbnail_id($exhibit->ID), 'large')[0];
	$photo_src_full   = wp_get_attachment_image_src( get_post_thumbnail_id($exhibit->ID), 'full')[0];

	$maker_photo_src = wp_get_attachment_image_src( get_post_thumbnail_id($maker_id), 'large')[0];


	//DO NOT CHANGE THIS ORDER AS THE JEKYLL CODE USES ARRAY OFFSET
	$e_image_primary_text = "image-primary:" . "\n".
                         "  thumbnail: " 	. $photo_src_thumb  . "\n" .
                         "  medium: " 	. $photo_src_medium . "\n" .
                         "  large: " 		. $photo_src_large  . "\n" .
                         "  full: " 		. $photo_src_full   . "\n" ;


/*
	$images = get_post_meta($exhibit->ID, "wpcf-additional-photos");

	//remove empty array items
	$images = array_filter ($images);

	$images_output_text = "images:" . "\n";

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

			$images_output_text . "  -" . $v  . ":\n".
						"  - thumbnail: " . $thumb  . "\n".
						"    medium: " 	. $medium . "\n".
						"    large: "	. $large  . "\n".
						"    full: " 	. $full   . "\n";


		}
	}
	else {
		$images_output[]="";
		$images_output_text="";
	}

*/

	$embed_media = get_post_meta($exhibit->ID, "wpcf-embeddable-media", false);



	$m_output = array(
			'name' => html_entity_decode(get_the_title($maker_id)),
			'description' => html_entity_decode($maker->post_excerpt),
			'photo_link' => $maker_photo_src
		);


	$m_output_text = "maker:" 		  . "\n".
			 "  " . mfo_yaml_prep("name", get_the_title($maker_id)) .
			 "  " . mfo_yaml_prep("description", html_entity_decode($maker->post_excerpt)) .
		 	 "  image-primary: " 	. $maker_photo_src   . "\n";



	$exhibitfilepath = plugin_dir_path(__DIR__) .'jekyll-build/' . $exhibit->post_name . '.md';
	$exhibitfile = fopen($exhibitfilepath, "w") or die();


	fwrite($exhibitfile, "---\n");
	fwrite($exhibitfile, mfo_yaml_prep("name", $exhibit->post_title));
	fwrite($exhibitfile, "slug: " 			. $exhibit->post_name . "\n");
	fwrite($exhibitfile, "id: "   			. $exhibit->ID . "\n");
	fwrite($exhibitfile, "status: " 		. get_post_meta($exhibit->ID, "wpcf-approval-status", true) . "\n");
	fwrite($exhibitfile, "url: "  			. get_post_meta($exhibit->ID, "wpcf-website", true) . "\n");

	fwrite($exhibitfile, mfo_yaml_prep("excerpt", $exhibit->post_excerpt));
	fwrite($exhibitfile, mfo_yaml_prep("description", get_post_meta($exhibit->ID, "wpcf-long-description", true)));

	fwrite($exhibitfile, "location: " 		. strip_tags(get_the_term_list($exhibit->ID, "exhibit-location","",", ")) . "\n");

	fwrite($exhibitfile, $e_image_primary_text);
	fwrite($exhibitfile, $m_output_text);	//output the previously built maker info

	date_default_timezone_set('America/New_York');
	fwrite($exhibitfile, mfo_yaml_prep("last-modified-db", $exhibit->post_modified));
	fwrite($exhibitfile, mfo_yaml_prep("last-exported", date('Y-d-m H:i:s', time())));


	fwrite($exhibitfile, "---\n");
	fclose($exhibitfile);
	$exhibits_count++;
} //end foreach


echo $exhibits_count . " files written";

mfo_log(1,"jekyll-build", "done...");

?>
