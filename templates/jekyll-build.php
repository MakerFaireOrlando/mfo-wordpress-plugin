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



	$images = get_post_meta($exhibit->ID, "wpcf-additional-photos");

	//remove empty array items
	$images = array_filter ($images);

	$images_output_text = "images:" . "\n";

	//get all the sizes
	//https://toolset.com/documentation/customizing-sites-using-php/displaying-repeating-fields-one-kind/

	if (!empty ($images) ) {
		$field = wpcf_fields_get_field_by_slug ("additional-photos");
		unset($images_output); //clear the array
		foreach ( $images as $k=>$v ) {

			$params = array ("size" => "thumbnail", "proportional"=>"false", "url" => "true", 'field_value'=> $v);
			//$params ['field_value'] =$v;
			//print_r($params);
			$thumb =  types_render_field_single( $field, $params, null, '', $k);
			//echo $thumb;

/*
			$params ['size'] ='medium';
			$medium =  types_render_field_single( $field, $params, null, '', $k);
			$params ['size'] ='large';
			$large =  types_render_field_single( $field, $params, null, '', $k);
			$params ['size'] ='full';
			$full =  types_render_field_single( $field, $params, null, '', $k);
*/
			$images_output[] = array (
				'thumbnail' => $thumb,
//				'medium'    => $medium,
//				'large'     => $large,
//				'full'      => $full
				);

			$images_output_text . "  -" . $v  . ":\n".
						"  thumbnail: " . $thumb  . "\n"; //swith pack to period
//						"  medium: " 	. $medium . "\n".
//						"  large: "	. $large  . "\n".
//						"  full: " 	. $full   . "\n";

			//echo $images_output_text;
		}//end foreach image
	} //end if
	else {
		$images_output[]="";
		$images_output_text="";
	} //end else



	$embed_media = get_post_meta($exhibit->ID, "wpcf-embeddable-media", false);



	//get categories
	$terms = get_the_terms($exhibit->ID, "exhibit-category");
	$first = true;
	$combat_robot=0;

	if (!empty($terms)) {
		$terms_output_text = "categories:\n";

		foreach ($terms as $term) {
			//the decode is to handle ampersands in the category name
			$terms_output_text .=  "  - id: " . $term->term_id . "\n";
			$terms_output_text .=  "    slug: " . $term->slug . "\n";
			$terms_output_text .=  "    name: " . htmlspecialchars_decode($term->name) . "\n";

			if ($term->slug == "combat-robots") $combat_robot = 1;
			} //end foreach term

/* output just category name
		foreach ($terms as $term) {
			if ($first) {
				$terms_output_text.= "  - ";
				$first = false;
			}
			else $terms_output_text.= "    ";
			//the decode is to handle ampersands in the category name
			$terms_output_text .=  htmlspecialchars_decode($term->name) . "\n";
			} //end foreach term
*/
	} //end if
	else $images_output_text="";



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
	//title is used by jekyll-seo-tag
	fwrite($exhibitfile, mfo_yaml_prep("title", $exhibit->post_title));
	fwrite($exhibitfile, "slug: " 			. $exhibit->post_name . "\n");
	fwrite($exhibitfile, "id: "   			. $exhibit->ID . "\n");
	fwrite($exhibitfile, "status: " 		. get_post_meta($exhibit->ID, "wpcf-approval-status", true) . "\n");
	fwrite($exhibitfile, "url: "  			. get_post_meta($exhibit->ID, "wpcf-website", true) . "\n");

	//description is used by jekyll-seo-tag so we used the excerpt for it, and renamed description to description-long
	fwrite($exhibitfile, mfo_yaml_prep("description", $exhibit->post_excerpt));
	fwrite($exhibitfile, mfo_yaml_prep("description-long", get_post_meta($exhibit->ID, "wpcf-long-description", true)));

	fwrite($exhibitfile, "location: " 		. strip_tags(get_the_term_list($exhibit->ID, "exhibit-location","",", ")) . "\n");

	//image is used by jekyll-seo-tag
	fwrite($exhibitfile, "image: " . $photo_src_large . "\n");
	fwrite($exhibitfile, $e_image_primary_text);


	fwrite($exhibitfile, "website: " 		. get_post_meta($exhibit->ID, 'wpcf-website', true) . "\n");
	fwrite($exhibitfile, "email: " 			. get_post_meta($exhibit->ID, 'wpcf-public-email', true) . "\n");
	fwrite($exhibitfile, "twitter: " 		. get_post_meta($exhibit->ID, 'wpcf-twitter-url', true) . "\n");
	fwrite($exhibitfile, "instagram: " 		. get_post_meta($exhibit->ID, 'wpcf-instagram-url', true) . "\n");
	fwrite($exhibitfile, "facebook: " 		. get_post_meta($exhibit->ID, 'wpcf-facebook-url', true) . "\n");
	fwrite($exhibitfile, "youtube: " 		. get_post_meta($exhibit->ID, 'wpcf-youtube-url', true) . "\n");

	fwrite($exhibitfile, $terms_output_text);
	fwrite($exhibitfile, "combat-robot: " . $combat_robot . "\n");

	fwrite($exhibitfile, $m_output_text);	//output the previously built maker info


	date_default_timezone_set('America/New_York');
	fwrite($exhibitfile, mfo_yaml_prep("last-modified-db", $exhibit->post_modified));
	fwrite($exhibitfile, mfo_yaml_prep("last-exported", date('Y-d-m H:i:s', time())));


	fwrite($exhibitfile, "---\n");
	fclose($exhibitfile);
	$exhibits_count++;
} //end foreach


echo "\n$exhibits_count " . "files written";

mfo_log(1,"jekyll-build", "done...");

?>
