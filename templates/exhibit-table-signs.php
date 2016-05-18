<?php
/**
 * @package WordPress
 * @subpackage Coraline
 * @since Coraline 1.0
 * Template Name: Exhibit Table Signs
 */

//this exists so that we can drop all the non-essential content wrappers 
//and get working page breaks in chrome...

//look for the post_ids param
//if its not there, are you a producer
//if not, then error

$exhibit = get_query_var ("post_ids",0);

//if ($exhibit ||  current_user_can('edit_others_posts'))
//{

	$args = array(
    	'title' => 'Exhibit - Table Signs',
	);
	echo render_view( $args );
//}
//else
//echo "Table Sign: System Error, please report this to " . do_shortcode("[mfo-support-email-link]");

?>

