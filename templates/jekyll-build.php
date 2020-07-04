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
	  return $vartext;
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


foreach ($exhibits_array as $exhibit) {

	//get the id of the maker
	$maker_id = wpcf_pr_post_get_belongs($exhibit->ID, 'maker');
	$maker = get_post($maker_id);
	unset($m_output);

	//note that the photo url is the first element, hence the index at the end of the lines below
	$photo_src = wp_get_attachment_image_src( get_post_thumbnail_id($exhibit->ID), 'large')[0];
	$maker_photo_src = wp_get_attachment_image_src( get_post_thumbnail_id($maker_id), 'large')[0];

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

			$images_output_text = "images:" 		  . "\n".
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



	$embed_media = get_post_meta($exhibit->ID, "wpcf-embeddable-media", false);


	//if (empty($maker_photo_src[0])) $maker_photo_src[0]="";
	//if (empty($photo_src[0])) $photo_src[0]="";

	$m_output = array(
			'name' => html_entity_decode(get_the_title($maker_id)),
			'description' => html_entity_decode($maker->post_excerpt),
			'photo_link' => $maker_photo_src
		);

/*
	$m_output_text = "maker:" 		  . "\n".
			 "  - name: " 		. get_the_title($maker_id)  . "\n".
			 "    description: " 	. html_entity_decode($maker->post_excerpt) . "\n".
		 	 "    photo_link: " 	. $maker_photo_src   . "\n";
*/


	$m_output_text = "maker:" 		  . "\n".
			 "  - " . mfo_yaml_prep("name", get_the_title($maker_id)) .
			 "    " . mfo_yaml_prep("description", html_entity_decode($maker->post_excerpt)) .
		 	 "photo_link: " 	. $maker_photo_src   . "\n";


	//create the array for the exhibit
	$e_output[]= array (
			'approval_year' => get_post_meta($exhibit->ID, "wpcf-approval-year", true),
			'approval_status' => get_post_meta($exhibit->ID, "wpcf-approval-status", true),
			'id'=>$exhibit->ID,
			'exhibit_category' => strip_tags(get_the_term_list($exhibit->ID, "exhibit-category","",", ")),
			'project_name' => html_entity_decode($exhibit->post_title),
			'description' => html_entity_decode(get_post_meta($exhibit->ID, "wpcf-long-description", true)),
			'web_site' => get_post_meta($exhibit->ID, "wpcf-website", true),
			'promo_url' => get_permalink($exhibit),
			//'qrcode_url' => "",
			'project_short_summary' => html_entity_decode($exhibit->post_excerpt),
			'location' => strip_tags(get_the_term_list($exhibit->ID, "exhibit-location","",", ")),
			'photo_link' => $photo_src,
			'additional_photos'=>$images_output,
			'embeddable_media'=>$embed_media,
			'maker' =>$m_output
			);


	$exhibitfilepath = plugin_dir_path(__DIR__) .'jekyll-build/' . $exhibit->post_name . '.md';
	$exhibitfile = fopen($exhibitfilepath, "w") or die();

	//$exhibitfile = plugin_dir_path(__DIR__) .'jekyll-build/' . 'exhibit-' .$exhibit->ID;
        //file_put_contents($exhibitfile, $exhibittext);

	fwrite($exhibitfile, "---\n");
	//fwrite($exhibitfile, "name: " 			. $exhibit->post_title . "\n");
	fwrite($exhibitfile, mfo_yaml_prep("name", $exhibit->post_title));
	fwrite($exhibitfile, "slug: " 			. $exhibit->post_name . "\n");
	fwrite($exhibitfile, "id: "   			. $exhibit->ID . "\n");
	fwrite($exhibitfile, "status: " 		. get_post_meta($exhibit->ID, "wpcf-approval-status", true) . "\n");
	fwrite($exhibitfile, "url: "  			. get_post_meta($exhibit->ID, "wpcf-website", true) . "\n");
	//fwrite($exhibitfile, "excerpt: >\n  '"		. str_replace(array("\r"), "" , $exhibit->post_excerpt) . "'\n");

	fwrite($exhibitfile, mfo_yaml_prep("excerpt", $exhibit->post_excerpt));
	fwrite($exhibitfile, mfo_yaml_prep("description", get_post_meta($exhibit->ID, "wpcf-long-description", true)));

	fwrite($exhibitfile, "location: " 		. strip_tags(get_the_term_list($exhibit->ID, "exhibit-location","",", ")) . "\n");
	fwrite($exhibitfile, $images_output_text);
	fwrite($exhibitfile, $m_output_text);
	fwrite($exhibitfile, "---\n");
	fclose($exhibitfile);
	}







//fail gracefully on no exibits returned
if (!isset($e_output)) {
	 $e_output[]= array();
	 $e_output_count = 0;
}
else $e_output_count = count($e_output);


//create the overall JSON array
$output = array(
		'accepteds_count' => $e_output_count,
		//'accepteds' => $e_output,
		);

//send headers & JSON
wp_send_json($output);

?>
