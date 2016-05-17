<?php

/*
Plugin Name: Maker Faire Online - CFM & More
Plugin URI: http://www.makerfaireorlando.com
Description: Helper plugin for the Maker Faire Online system based using the Toolset plugins & more
Version: 3.3.0
Author: Ian Cole (Maker Faire Orlando)
Author URI: http://www.themakereffect.org/about/
GitHub Plugin URI: digitalman2112/mfo-wordpress-plugin
*/


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//include settings code
include( plugin_dir_path( __FILE__ ) . 'mfo-settings.php');


mfo_load_modules();


function mfo_load_modules () {
$options = get_option('mfo_options_modules');

if ( $options['mfo_module_eventbrite_enabled_boolean'] ) {
	mfo_log (4, "load modules", "loading mfo-eventbrite-webhook module");
	include( plugin_dir_path( __FILE__ ) . 'mfo-eventbrite-webhook.php');
}

if ( $options['mfo_module_sensei_enabled_boolean'] ) {
	mfo_log (4, "load modules", "loading mfo-sensei-helpers module");
	include( plugin_dir_path( __FILE__ ) . 'mfo-sensei-helpers.php');
}

if ( $options['mfo_module_woocommerce_enabled_boolean'] ) {
	mfo_log (4, "load modules", "loading mfo-woocommerce-helpers module");
	include( plugin_dir_path( __FILE__ ) . 'mfo-woocommerce-helpers.php');
}

}

add_filter( 'page_template', 'mfo_page_template' );
function mfo_page_template( $page_template )
{
    if ( is_page( 'duplicate-exhibit' ) ) {
        $page_template = dirname( __FILE__ ) . '/templates/duplicate-exhibit.php';
    }
    return $page_template;
}


/*

General todo:
 - Remove form id dependent functions?
 - multi-year
 	- Append year to exhibit links? https://wp-types.com/forums/topic/custom-taxonomies-not-showing-on-permalink/
 - Remove 2015 badges from those that were not "approved" for 2015...See "Ability3D" as example exhibit
 - ^^^ Should 2015+ badge be calculated instead of category?

Changelog:

05-06-2016: Pulled mfo-stats, mfo-backend-tools, and mfo-shortcodes into one file
05-06-2016: Added settings for Logging Enabled, System Warning Email address
05-06-2016: Updated mfo_log to use setting
05-06-2016: Created mfo_warning_email to utilise the system warning email address, and to write to log
05-06-2016: Modified cred_update_maker_stats to not use form ids
05-06-2016: Modified update_maker_stats() to use approval-year in logic as start of multi-year mods
05-07-2016: Pulled mfo-eventbrite-webhook, mfo-woocommerce-helpers, mfo-sensei helpers into same directory
05-07-2016: Updated plugin about information
05-07-2016: Added setup_cred_recipients() to allow CRED forms to use option: mfo_notification_email_string
05-07-2016: Added setting and dynamic loading of modules for sensei, woocommerce, eventbrite
05-07-2016: Added log-level setting and modified all mfo_log calls to use level
05-09-2016: Updated settings page explanation of shortcodes, etc
05-10-2016: MAJOR settings additions; tabbed interface; admin notifications for missing settings
05-14-2016: New exhibits without a year are set to mfo_event_year() (thought this was already done...)
05-14-2016: Exhibits now at /exhibits/%year%/slug; requires modification to exhibit type
05-15-2016: Feature: duplicate exhibit: requires maker-dashboard mods and pages
05-16-2016: Added options to turn on / off exhibit editing
05-16-2016: Added mfo_toolset_add_shortcodes function to automatically register shortcodes with Toolset
05-17-2016: Updates to duplicate exhibit functionality
05-17-2016: Moved duplicate-exhibit.php into plugin from theme
*/




// custom exhibit permalinks with year
// NOTE THIS REQUIRES EXHIBIT CUSTOM POST TYPE MOD
// "Use a custom URL format" -> exhibit/%year%
// modify "AllTypes Row" content template to use [wpv-post-type show="single"]
// still have 404 on fireball :(
add_filter('post_link', 'year_permalink', 10, 3);

add_filter('post_type_link', 'year_permalink', 10, 3);

function year_permalink($permalink, $post, $leavename) {

        if (strpos($permalink, '%year%') === FALSE) return $permalink;
	mfo_log(4, "year_permalink", $permalink . "; " . $post->ID);

	$appr_year =  get_post_meta($post->ID, 'wpcf-approval-year', TRUE);
        return str_replace('%year%', $appr_year, $permalink);
}





//add variable for load-in page template and slack
function add_query_vars_filter($vars) {
	$vars[] = "li-exhibit";
	$vars[] = "dup-exhibit";
	$vars[] = "post_ids";
	$vars[] = "token";
	$vars[] = "text";

	return $vars;
}

add_filter( 'query_vars', 'add_query_vars_filter');

//add_filter( 'query_vars', 'add_query_vars_filter');

add_shortcode('mfo-loadin-short', 'mfo_loadin_short');
function mfo_loadin_short($atts) {
        $exhibit = $atts["id"];

        //get the loadin slot for the exhibit
        $exhibit_slot_title = get_post_meta( $exhibit, 'wpcf-exhibit-loadin-slot', true );
        //since we store the slot title, get the slot object from that title
        $exhibit_slot = get_page_by_title($exhibit_slot_title, OBJECT, 'loadin-slot');

        if ($exhibit_slot) {
                $exhibit_slot_time = get_post_meta( $exhibit_slot->ID, 'wpcf-loadin-start-time', true );
                $exhibit_slot_dateunix = get_post_meta( $exhibit_slot->ID, 'wpcf-loadin-date', true );
                $exhibit_slot_date = date("D, M j, Y", $exhibit_slot_dateunix);
                $exhibit_loc = get_post_meta( $exhibit_slot->ID, '_wpcf_belongs_loadin-location_id', true );
                $exhibit_slot_loc = get_the_title($exhibit_loc);
                $txt = $exhibit_slot_loc.' - '.$exhibit_slot_date.' at '.$exhibit_slot_time;
        }

        return $txt;
}

//workaround for wordpress 4.2.3 shortcode in html attribute issue
add_shortcode('mfo-exhibit-category-buttons', 'mfo_exhibit_category_buttons');
function mfo_exhibit_category_buttons($atts) {
        $class = $atts["class"];

	$args = array(
  		'orderby' => 'name',
  		'order' => 'ASC',
		'taxonomy' => 'exhibit-category'
 	 );

	$categories = get_categories($args);

  	foreach($categories as $category) { 
    		$output = $output.'<button id="'.$category->slug.'" class="'. $class. '" data-filter=".'.$category->slug.'" data-text="'.$category->name.'">'.$category->name.'</button>';
		}
	return $output;
        //return "<script>window.location.replace('/edit-helpers/?post_ids=".$id."');</script>";
	//return "<button class='myButton' data-filter='.".$id."' data-text='Arduino'>Arduino</button>";
}


/**
 * Unlimited Search Posts
 * From: http://jamescollings.co.uk/blog/wordpress-search-results-page-modifications/
 */
function jc_limit_search_posts() {
	if ( is_search())
		set_query_var('posts_per_page', -1);
}
add_filter('pre_get_posts', 'jc_limit_search_posts');

/*
//Don't paginate wordpress archives search results
//from https://wp-types.com/forums/topic/remove-pagination-from-archive-view/
function no_nopaging($query) {
	if (is_post_type_archive()) {
		$query->set('nopaging', 1);
	}
}

add_action('parse_query', 'no_nopaging');
*/

/**
 * Join posts and postmeta tables
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
 * from: http://adambalee.com/search-wordpress-by-custom-fields-without-a-plugin/
 */
function cf_search_join( $join ) {
    global $wpdb;

    if ( is_search() ) {
        $join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }
    return $join;
}

add_filter('posts_join', 'cf_search_join' );

/**
 * Modify the search query with posts_where
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_where
 */
function cf_search_where( $where ) {
    global $pagenow, $wpdb;

    if ( is_search() ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }

    return $where;
}
add_filter( 'posts_where', 'cf_search_where' );

/**
 * Prevent duplicates
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_distinct
 */
function cf_search_distinct( $where ) {
    global $wpdb;

    if ( is_search() ) {
        return "DISTINCT";
    }

    return $where;
}
add_filter( 'posts_distinct', 'cf_search_distinct' );


/*
function my_wpv_post_feature_image ($info) {
//this isnt going to do what I wanted...	

}

add_filter('wpv-post-featured-image', 'my_wpv_post_feature_image');
*/

add_shortcode('mfo-redirect-exhibit-helpers', 'mfo_redirect_exhibit_helpers_shortcode');
function mfo_redirect_exhibit_helpers_shortcode($atts) {
	$id = $atts["id"];
 	return "<script>window.location.replace('/edit-helpers/?post_ids=".$id."');</script>";
}


add_shortcode('mfo-redirect-home', 'mfo_redirect_home_shortcode');
function mfo_redirect_home_shortcode() {
	$html = "<script>window.location.replace('" . get_site_url() . "');</script>";
	//return "<script>window.location.replace('http://www.makerfaireorlando.com');</script>";
	return $html;
}



add_shortcode('wpv-post-today', 'today_shortcode');
function today_shortcode() {
  return date("m/d/Y");
}



add_shortcode( 'wpv-post-param', 'wpv_post_param_shortcode' );
//from - https://wp-types.com/forums/topic/how-can-my-view-access-a-url-parameter-in-a-query-string/

function wpv_post_param_shortcode( $atts ) {
  if ( !empty( $atts['var'] ) ) {
    $var = (array)$_GET[$atts['var']];
    return esc_html( implode( ', ', $var ) );
  }
}


add_filter( 'no_texturize_shortcodes', 'shortcodes_to_exempt_from_wptexturize' );
function shortcodes_to_exempt_from_wptexturize( $shortcodes ) {
    $shortcodes[] = 'trim';
    return $shortcodes;
}

function taxonomy_level($atts) {
 $a = shortcode_atts( array(
    'id' => 0,
    'type' => 'category',
), $atts );

 if ($a['id']==0) return -1;
 
 $ancestors = get_ancestors($a['id'], 'exhibit-location');
 return sizeof($ancestors);
}
add_shortcode('taxonomy-level', 'taxonomy_level');


function current_user_can_edit_post() {

	$id = $atts["id"];
	if (!$id) {
		$id=get_the_ID();
	}

return current_user_can('edit_post',$id);
}
add_shortcode('current-user-can-edit-post', 'current_user_can_edit_post');

function current_user_can_edit_others_posts() {
return current_user_can('edit_others_posts');
}
add_shortcode('current-user-can-edit-others-posts', 'current_user_can_edit_others_posts');

function cred_post_parent_title() {
 return do_shortcode("[cred-post-parent get='title']");
}
add_shortcode('cred-post-parent-title', 'cred_post_parent_title');



//from https://wp-types.com/forums/topic/testing-user-role-to-conditionally-display-content/
function get_wp_user_role() {
global $current_user;

$user_roles = $current_user->roles;
$user_role = array_shift($user_roles);

return $user_role;
}
add_shortcode('wpv-post-get-wp-user-role', 'get_wp_user_role');

/**
 *
 *These are mostly for the CSV hacking. TODO: Convert the .csv export to proper php pages
 * so that these aren't needed :)
 *
 **/

function mfo_hide( $atts, $content = null ) {
        return;
}
add_shortcode( 'mfo-hide', 'mfo_hide' );

function decode_shortcode( $atts, $content = null ) {
        $decoded = html_entity_decode(do_shortcode($content));
        return $decoded;
}
add_shortcode( 'decode', 'decode_shortcode' );

function striptags_shortcode( $atts, $content = null ) {
        $stripped = strip_tags(do_shortcode($content));
        return $stripped;
}
add_shortcode( 'striptags', 'striptags_shortcode' );



function stripcrlf_shortcode( $atts, $content = null ) {
	$stripped = str_replace (array("\r\n", "\n", "\r"), ' ', do_shortcode($content));
	return $stripped;
}
add_shortcode( 'stripcrlf', 'stripcrlf_shortcode' );

function trim_shortcode( $atts, $content = null ) {
	$trimmed = trim(html_entity_decode(do_shortcode($content)));
	return $trimmed;
}
add_shortcode( 'trim', 'trim_shortcode' );

function crlf_shortcode( $atts, $content = null ) {
	return "\r\n";
}
add_shortcode( 'crlf', 'crlf_shortcode' );


//allows the custom parameter for the csv export template
function custom_rewrite_tag() {
  //https://codex.wordpress.org/Rewrite_API/add_rewrite_rule
  add_rewrite_tag('%csv-filename%', '([^&]+)');
}

add_action('init', 'custom_rewrite_tag', 10, 0);


//  adding shortcode to get parent id for wp-types

function parent_id($atts) {
        global $wpdb;

        $current = $atts["id"];
        // Get current posts parent type
        $parentType = $wpdb->get_var("SELECT  `meta_key` FROM  `wp_postmeta` WHERE  `post_id` ={$current} AND `meta_key` LIKE '%belongs%'");

        // Get current posts parentID if project
        $parentID = $wpdb->get_var("SELECT  `meta_value` FROM  `wp_postmeta` WHERE  `post_id` ={$current} AND  `meta_key` =  '{$parentType}'" );
        return trim($parentID);
} //end function parent_id

add_shortcode("parentid", "parent_id");


//this is a hack and can likely be removed. 
//check the exhibit-helpers view
function helper_approved_quantity($atts) {
	global $exhibit;

//	$scid = do_shortcode("[wpv-post-id id='$exhibit']");

	$current = $atts["id"];
	$haq = get_post_meta($current, "wpcf-helper-approved-quantity", true);
        return trim($exhibit->ID);
}

add_shortcode("helper-approved-quantity", "helper_approved_quantity");





/*
 * Logging Functionality - enabled with setting
 * Writes to mfo-debug.log in mfo plugin directory
 * Watch the log file size, can get big!
 *
 */

function mfo_log($lvl, $header, $msg){
	$options = get_option('mfo_options_debug');
	$enabled = $options['mfo_log_enabled_boolean'];
	$log_level = $options['mfo_log_level_number'];


	$tz = 'America/New_York';
	$timestamp = time();
	$dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
	$dt->setTimestamp($timestamp); //adjust the object to correct timestamp
	$datetxt = $dt->format('Y-m-d H:i:s');

 	if ($enabled && ($lvl <= $log_level)) {
        	$logfile = plugin_dir_path(__FILE__) . 'mfo-debug.log';
        	file_put_contents($logfile, $datetxt. " | L" . $lvl . " | " . str_pad($header, 25) . " | " .$msg."\n",FILE_APPEND);
	}
    }


function mfo_warning_email ($subject, $body) {
	$options = get_option('mfo_options_debug');
	mfo_log (1, "WARNING EMAIL", $subject . " | " . $body);
	wp_mail(  $options['mfo_warning_email_string'], $subject, $body);
}




//Add all user types to author dropdown for switching maker to another user
//from: http://wordpress.stackexchange.com/questions/50827/select-subscriber-as-author-of-post-in-admin-panel

add_filter('wp_dropdown_users', 'MySwitchUser');
function MySwitchUser($output)
{
    //without this line, any admin edits will default the item to ADMIN
    $post = get_post();
    //global $post is available here, hence you can check for the post type here

    //$users = get_users('role=subscriber');
    $users = get_users(); //add all users

    $output = "<select id=\"post_author_override\" name=\"post_author_override\" class=\"\">";

    //Leave the admin in the list
    $output .= "<option value=\"1\">Admin</option>";
    foreach($users as $user)
    {
        $sel = ($post->post_author == $user->ID)?"selected='selected'":'';
        //$output .= '<option value="'.$user->ID.'"'.$sel.'>'.$user->user_login.'</option>';
        $output .= '<option value="'.$user->ID.'"'.$sel.'>'.$user->user_login." - ".$user->display_name.'</option>';
    }
    $output .= "</select>";

    return $output;
}




//registration functions from: http://pastebin.com/pw4rDhTP

// Register the column - Registered
function registerdate($columns) {
    $columns['registerdate'] = __('Registered', 'registerdate');
    return $columns;
}
add_filter('manage_users_columns', 'registerdate');
 
// Display the column content
function registerdate_columns( $value, $column_name, $user_id ) {
        if ( 'registerdate' != $column_name )
           return $value;
        $user = get_userdata( $user_id );
        $registerdate = $user->user_registered;
        $registerdate = date("Y-m-d", strtotime($registerdate));
        return $registerdate;
}
add_action('manage_users_custom_column',  'registerdate_columns', 10, 3);
 
function registerdate_column_sortable($columns) {
          $custom = array(
      // meta column id => sortby value used in query
          'registerdate'    => 'registered',
          );
      return wp_parse_args($custom, $columns);
}

add_filter( 'manage_users_sortable_columns', 'registerdate_column_sortable' );

function registerdate_column_orderby( $vars ) {
        if ( isset( $vars['orderby'] ) && 'registerdate' == $vars['orderby'] ) {
                $vars = array_merge( $vars, array(
                        'meta_key' => 'registerdate',
                        'orderby' => 'meta_value'
                ) );
        }

        return $vars;
}

add_filter( 'request', 'registerdate_column_orderby' );

//Add Maker counts to USER admin panel
function users_makers_column( $cols ) {
  $cols['user_makers'] = 'Makers';
  return $cols;
}
add_filter( 'manage_users_columns', 'users_makers_column' );

//Add Exhibit counts to USER admin panel
function users_exhibits_column( $cols ) {
  $cols['user_exhibits'] = 'Exhibits';
  return $cols;
}
add_filter( 'manage_users_columns', 'users_exhibits_column' );

/*
//make column sortable
//I don't think this works since the underlying content is computed
//versus a field with that name
//I'd need to add stats fields to the user object and update like
// maker stats...don't know that it is worth the effort yet.
function users_sortable_columns($columns) {
    $columns['user_makers'] = 'user_makers';
    $columns['user_exhibits'] = 'user_exhibits';

    return $columns;
}
add_filter('manage_users_sortable_columns', 'users_sortable_columns');
*/

//Custom USER admin panel columns
function user_custom_column_value( $value, $column_name, $id ) {
  if ( ('user_makers' != $column_name) AND ('user_exhibits' != $column_name)  )
     return $value;

  $count = 0;
  if( $column_name == 'user_makers' ) {
    global $wpdb;
    $count = (int) $wpdb->get_var( $wpdb->prepare(
      "SELECT COUNT(ID) FROM $wpdb->posts WHERE 
       post_type = 'maker' AND post_status = 'publish' AND post_author = %d",
       $id
    ) );
  } else if( $column_name == 'user_exhibits' ) {
    global $wpdb;
    $count = (int) $wpdb->get_var( $wpdb->prepare(
      "SELECT COUNT(ID) FROM $wpdb->posts WHERE 
       post_type = 'exhibit' AND post_status = 'publish' AND post_author = %d",
       $id
    ) );
  }
//	echo ($id.":".$column_name.":".$count."<br>");
    return intval($count);
}

add_filter( 'manage_users_custom_column', 'user_custom_column_value', 10, 3 );



//Todo: Generic count exhibits, etc shortcode / function with parameters




function save_post_fee_payment($post_id) {

	//$rsvp = get_post_meta($post_id, 'wpcf-orientation-session-rsvp', true);
	$parent = wpcf_pr_post_get_belongs($post_id, 'exhibit');
	$status = get_post_status($post_id);
	mfo_log(2, "save_post_fee_payment", $post_id."; ".$parent."; ".$status);
	if ($status == 'publish') {
 		update_post_meta($parent , 'wpcf-payment-status' , 3);  //3=paid
		mfo_log(2, "save_post_fee_payment", "updating parent parent-status ".$post_id."; ".$parent."; ".$status);
		}
	elseif ($status == 'auto-draft') {
		//do nothing for now
		//don't need to be alerted about these
		}
	else {
		mfo_warning_email( "ERROR: Fee-Payment status not = publish or auto-draft: ".$post_id.": ".$parent.": ".$status, "May need to edit the exhibit payment status on this exhibit" );
	}



}

add_action ('save_post_fee-payment', 'save_post_fee_payment');


function count_orientation_rsvp_shortcode( $atts ) {
        $count = -1;
	$id = $atts["id"];
	if ( $id < 1 or $id >6) {
		return "";
	}

        $childargs = array(
        'post_type' => 'maker',
        'numberposts' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
		//'relation' => 'and', 
		array('key' => 'wpcf-orientation-session-rsvp', 'value' => $id),
		//array('key' => 'wpcf-has-approved-children', 'value' => '1')
        ));

        $children = get_posts($childargs);

        $count = count($children);

        return $count;
}
add_shortcode( 'count-orientation-rsvp', 'count_orientation_rsvp_shortcode' );

function count_exhibits_shortcode( $atts, $content = null ) {
        $count = -1;

        $childargs = array(
        'post_type' => 'exhibit',
        'numberposts' => -1,
        'post_status' => 'publish'
        );

        $children = get_posts($childargs);

        $count = count($children);

        return $count;
}
add_shortcode( 'count-exhibits', 'count_exhibits_shortcode' );

function count_exhibits_pending_shortcode( $atts, $content = null ) {
	$count = -1;

	$childargs = array(
	'post_type' => 'exhibit',
	'numberposts' => -1,
	'post_status' => 'publish',
	'meta_query' => array(array('key' => 'wpcf-approval-status', 'value' => '2'))
	);

	$children = get_posts($childargs);

        $count = count($children);
        return $count;
}
add_shortcode( 'count-exhibits-pending', 'count_exhibits_pending_shortcode' );

function count_exhibits_approved_shortcode( $atts, $content = null ) {
	$count = -1;

	$childargs = array(
	'post_type' => 'exhibit',
	'numberposts' => -1,
	'post_status' => 'publish',
	'meta_query' => array(array('key' => 'wpcf-approval-status', 'value' => '1'))
	);

	$children = get_posts($childargs);

        $count = count($children);

        return $count;
}
add_shortcode( 'count-exhibits-approved', 'count_exhibits_approved_shortcode' );

function count_exhibits_rejected_shortcode( $atts, $content = null ) {
	$count = -1;

	$childargs = array(
	'post_type' => 'exhibit',
	'numberposts' => -1,
	'post_status' => 'publish',
	'meta_query' => array(array('key' => 'wpcf-approval-status', 'value' => '3'))
	);

	$children = get_posts($childargs);

        $count = count($children);

        return $count;
}
add_shortcode( 'count-exhibits-rejected', 'count_exhibits_rejected_shortcode' );

function count_exhibits_withdrawn_shortcode( $atts, $content = null ) {
	$count = -1;

	$childargs = array(
	'post_type' => 'exhibit',
	'numberposts' => -1,
	'post_status' => 'publish',
	'meta_query' => array(array('key' => 'wpcf-approval-status', 'value' => '4'))
	);

	$children = get_posts($childargs);

        $count = count($children);

        return $count;
}
add_shortcode( 'count-exhibits-withdrawn', 'count_exhibits_withdrawn_shortcode' );


function update_user_stats ($author_id) {

	$debug="";
	$debug = $debug.current_time('mysql')."\r\n";
	$author_name = get_the_author_meta('display_name', $author_id);
	$debug = $debug."User: ".$author_id.": ".$author_name."\r\n";

	$childargs = array(
	'author' => $author_id,
	'post_type' => 'maker',
	'numberposts' => -1,
	'post_status' => 'publish',
	);

	$children = get_posts($childargs);

 	//update number-of-children
        $number = count($children);
        update_user_meta($author_id, 'wpcf-number-of-makers', $number);

        //update has-children
        if ($number > 0) {
        	update_user_meta($author_id, 'wpcf-has-makers', 1);
       	} else {
        	update_user_meta($author_id, 'wpcf-has-makers', 0);
                }

	//update has-approved-children and number-approved-children
	$ham = 0; //start false
	$nam = 0;
	foreach ($children as $child) {
		$debug = $debug."     + ".$child->ID;
		$debug = $debug.": ".$child->post_title;
		$appr_status = get_post_meta($child->ID, 'wpcf-has-approved-children', TRUE);
		$debug = $debug.", appr_status=".print_r($appr_status, TRUE);
		$post_status = get_post_status($child->ID);
		$debug = $debug.", post_status=".print_r($post_status, TRUE).";\r\n";
		//$debug = $debug.types_render_field("approval-status", array());
		if ($appr_status==1) {
			$ham = 1;
			$nam = $nam+1;
			}
	}

	update_user_meta($author_id, 'wpcf-has-approved-makers', $ham);
	update_user_meta($author_id, 'wpcf-number-approved-makers', $nam);

	update_user_meta($author_id, 'wpcf-user-stats-debug', $debug);
}


function update_maker_stats_child_id ($post_id) {
	$post_type = get_post_type($post_id);

	if ($post_type == 'exhibit') {
		$title = get_post_field( 'post_title', $post_id);
		$parent = wpcf_pr_post_get_belongs($post_id, 'maker');
		//mfo_warning_email( "update_maker_stats_child_id ".$title, $post_id.":".$parent);
		if ($parent) {
			update_maker_stats($parent);
		}
	}
}

add_action ('untrashed_post', 'update_maker_stats_child_id', 10, 1);
add_action ('trashed_post', 'update_maker_stats_child_id', 10, 1);

function update_maker_stats ($post_id) {

	/*
	SPECIAL NOTE:
		- these will not fire on the admin screens if there is a child post type table showing
		- go to the CPT for the PARENT and turn off the "management fields" for the child type
		- so that they don't show.
	*/

	$debug = "";  //ensure it is declared

	$post_type = get_post_type($post_id);
	$title = get_post_field( 'post_title', $post_id);

	mfo_log(2, "update_maker_stats", $post_id.":".$post_type.":".$title);

	if ($post_type == 'maker') {

		flush_json_from_cache();

		//wp_mail( "ian.cole@gmail.com", "update_maker_stats for ".$title, $title);

		$debug = $debug.current_time('mysql')."\r\n";
		$debug = $debug."Maker: ".$post_id.": ".$title."\r\n";
		$author_id = get_post_field( 'post_author', $post_id );
		$debug = $debug."User: ".$author_id.": ".$author_name."\r\n";


		//check for the missing image
		if (has_post_thumbnail($post_id)) {
			update_post_meta($post_id, 'wpcf-missing-image', 0);
		} else {
			update_post_meta($post_id, 'wpcf-missing-image', 1);
		}

		//todo: check for missing agreement status

		//check for missing orientation value
		//this is likely not needed, the issue was with makers created
		//before the orientation fields were added
		$rsvp = get_post_meta($post_id, 'wpcf-orientation-session-rsvp', true);
		if (!$rsvp) {
			update_post_meta($post_id , 'wpcf-orientation-session-rsvp' , 1);
			mfo_log(2, "update_maker_stats", $post_id.": setting orientation rsvp to not selected");
		}


		//check for missing alumni taxonomy
		//todo: remove year hard coding...
		$years = array("2012", "2013", "2014", "2015");
		if (has_term($years,"hidden-maker-category",$post_id)) {
				//$debug = $debug."-Year tags present.\r\n";
			if (!has_term("Alumni","hidden-maker-category",$post_id)) {
				wp_set_object_terms ($post_id, "alumni", "hidden-maker-category", true);
				$debug = $debug."-Set missing Alumni tag.\r\n";
				}
			}


		$childargs = array(
		'post_type' => 'any',
		'numberposts' => -1,
		'post_status' => 'publish',
		'meta_query' => array(array('key' => '_wpcf_belongs_maker_id', 'value' => $post_id))
		);

		//$number = $post_id;
		$children=get_posts($childargs);

		//update number-of-children
		$number = count($children);
		update_post_meta($post_id, 'wpcf-number-of-children', $number);

		//update has-children
		if ($number > 0) {
			update_post_meta($post_id, 'wpcf-has-children', 1);
		} else {
			update_post_meta($post_id, 'wpcf-has-children', 0);
		}

		//update has-approved-children and number-approved-children
		$hac = 0; //start false
		$nac = 0;
		$author_name = get_the_author_meta('display_name', $author_id);
		foreach ($children as $child) {

			$sell_status = get_post_meta($child->ID, 'wpcf-items-by-maker-for-sale', TRUE);
			$resell_status = get_post_meta($child->ID, 'wpcf-items-for-resale', TRUE);
			$pay_status = get_post_meta($child->ID, 'wpcf-payment-status', TRUE);
			$appr_year =  get_post_meta($child->ID, 'wpcf-approval-year', TRUE);


			//check & auto-set payment status
			if ($sell_status ==1 || $resell_status ==1) {
				if ($pay_status <2) { //unset is zero; does not apply is 1
					update_post_meta($child->ID, 'wpcf-payment-status', 2); //set to not-paid
				}
			}

			$helper_qty = get_post_meta($child->ID, 'wpcf-helper-approved-quantity', TRUE);
			if ($helper_qty < 0 || $helper_qty=="") {
				update_post_meta($child->ID, 'wpcf-helper-approved-quantity', 2); 
				//default to two helpers
			}

			$debug = $debug."     + ".$child->ID;
			//$child_title = get_post_meta($child->ID, 'title', TRUE);
			$debug = $debug.": ".$child->post_title;
			//now using approval status earlier in the function
			$appr_status = get_post_meta($child->ID, 'wpcf-approval-status', TRUE);
			$debug = $debug.", appr_status=".print_r($appr_status, TRUE);
			//$appr_year = get_post_meta($child->ID, 'wpcf-approval-year', TRUE);
			$debug = $debug.", appr_year=".print_r($appr_year, TRUE);
			$sell_status = get_post_meta($child->ID, 'wpcf-items-by-maker-for-sale', TRUE);
			$debug = $debug.", sell_status=".print_r($sell_status, TRUE);
			$pay_status = get_post_meta($child->ID, 'wpcf-payment-status', TRUE);
			$debug = $debug.", pay_status=".print_r($pay_status, TRUE);
			$post_status = get_post_status($child->ID);
			$debug = $debug.", post_status=".print_r($post_status, TRUE).";\r\n";
			//$debug = $debug.types_render_field("approval-status", array());
			//05-06-2016: added get_event_year logic
			if ($appr_status==1 && $appr_year == mfo_event_year()) {
				$hac = 1;
				$nac = $nac+1;
				wp_set_object_terms ($post_id, "alumni", "hidden-maker-category", true);
				wp_set_object_terms ($post_id, $appr_year, "hidden-maker-category", true);
				}

		}
		update_post_meta($post_id, 'wpcf-has-approved-children', $hac);
		update_post_meta($post_id, 'wpcf-number-approved-children', $nac);
		update_post_meta($post_id, 'wpcf-maker-stats-debug', $debug);
		$debug = $debug."event-year:" . $appr_year . ", hac=" . $hac . ", nac=" . $nac;
		mfo_log(2, "update_maker_stats", "debug output:\r\n" . $debug);
		update_user_stats($author_id);

	}
	//this was else if - removing the else for testing
	if (($post_type == 'exhibit') OR ($post_type == 'demonstration') OR ($post_type == 'workshop')) {

		$title = get_post_field( 'post_title', $post_id);

		flush_json_from_cache();

		mfo_log(3, "update_maker_stats", "post_type is exhibit, demonstration or workshop");
		//TODO: MOVE THESE TO A CRED_SAVE_DATA ACTION OR A CRED_BEFORE_SAVE_DATA_ACTION?

		//$parent = wp_get_post_parent_id($post_id);

		//default approval status for new posts
		//a small helper function could be helpful here...
		$approval = get_post_meta($post_id, 'wpcf-approval-status', TRUE);
		if (empty($approval)) {
			update_post_meta($post_id, 'wpcf-approval-status', 2); //set pending
			}
		$payment = get_post_meta($post_id, 'wpcf-payment-status', TRUE);
		if (empty($payment)) {
			update_post_meta($post_id, 'wpcf-payment-status', 1);
			}
		//TODO not using this field anymore - remove when removing field
		$agreement = get_post_meta($post_id, 'wpcf-agreement-status', TRUE);
		if (empty($agreement)) {
                        update_post_meta($post_id, 'wpcf-agreement-status', 1);
			}
		$orientation = get_post_meta($post_id, 'wpcf-orientation-status', TRUE);
		if (empty($orientation)) {
                        update_post_meta($post_id, 'wpcf-orientation-status', 1);
			}
		$year = get_post_meta($post_id, 'wpcf-approval-year', TRUE);
		if (empty($year)) {
			mfo_log(2, "update_maker_stats", "no approval-year, setting to " . mfo_event_year());
                        update_post_meta($post_id, 'wpcf-approval-year', mfo_event_year());
			}

		$parent = wpcf_pr_post_get_belongs($post_id, 'maker');
                if  ($parent) {
                        update_maker_stats($parent);
                }

		//update the unassigned location
		$terms = get_the_terms($post_id, 'exhibit-location');
		if ($terms) { //locations exist
			mfo_log(3, "update_maker_stats" ,"Found ".count($terms)." locations for post: ".$post_id);
			foreach ($terms as $struct) {
				//this is an array of stdClass objects, probably need to iterate, or search for the ID of "unassigned"
				$termname = $struct->slug;
				mfo_log(3, "update_maker_stats", "found term: ".$termname);
				if ("unassigned" == $termname) {
					if ( count($terms) > 1) { 	// there is something / anything other than unassigned
						wp_remove_object_terms ($post_id, "unassigned", "exhibit-location");
						mfo_log(2, "update_maker_stats", "Locations exist for post: ".$post_id."->removed  unassigned");
						} //endif count terms
					break; //no need to keep iterating terms
				}//endif unassigned is a term object
			}// end foreach
		}//end if terms
		else  {
			wp_set_object_terms ($post_id, "unassigned", "exhibit-location", true);
			mfo_log(2, "update_maker_stats",  "No Location for post: ".$post_id."->Set to unassigned");
			}



	}
}

add_action ('save_post', 'update_maker_stats', 9999);


function cred_update_maker_stats($post_id, $form_data){
	//need to use this hook because the wpcf_pr_post_get_belongs function doesnt work
	//with the standard save_data hook.


	mfo_log(2, "cred_update_maker_stats(" . $post_id . ", " . print_r ($form_data, true) . ");");

	//fix hard coding form ID
	//if ($form_data['id']==2725) { //Exhibit - Edit
	if ($form_data['post_type'] == "exhibit" && $form_data['form_type'] == "edit") { //Exhibit - Edit
		$parent = wpcf_pr_post_get_belongs($post_id, 'maker');
		//wp_mail( "ian.cole@gmail.com", "cred_update_maker_stats-Exhibit Edit:".$post_id, $post_id.":".$parent);
		if  (!$parent) {
			//wp_mail( "ian.cole@gmail.com", "WARNING:ORPHAN".$post_id, $post_id.":".$parent);
			mfo_warning_email( "WARNING:ORPHAN".$post_id, $post_id.":".$parent);
		}
	} //end exhibit - edit form
	else if (get_post_type($post_id)=="exhibit") {
		$parent = wpcf_pr_post_get_belongs($post_id, 'maker');
		//wp_mail( "ian.cole@gmail.com", "cred_update_maker_stats-Exhibit Add:".$post_id, $post_id.":".$parent);
		if  ($parent) {
			update_maker_stats($parent);
		}
	} //end exhibit - all other forms

}

add_action ('cred_save_data', 'cred_update_maker_stats', 9999 ,2 );

//need to catch exhibit edits in the backend to trigger a maker stats update
//TODO: This appears to fire before updates are in database for the exhibit and isn't working
/* This was preventing the code above for saving an exhibit from firing
function save_post_exhibit_updates ($post_id) {
	if (get_post_type($post_id) == 'exhibit') {
		//do stuff
		$parent = wpcf_pr_post_get_belongs($post_id, 'maker');
		update_maker_stats($parent);
	}
}

add_action ('save_post', 'save_post_exhibit_updates', 10 ,2 );
*/


//this function was replaced, but leaving it here for now...
/*
function cred_update_agreement($post_id, $form_data){

	//5031 is the Maker Agreement CRED form

	$ack = get_post_meta($post_id, 'wpcf-maker-agreement-ack', TRUE);
	if ($ack) {
		$date = get_post_meta($post_id, 'wpcf-maker-agreement-date', TRUE);
		if ($date) {	//this is an error condition...
	              	//wp_mail( "ian.cole@gmail.com", "ERROR: cred_update_agreement: Already had agreement date!".$post_id, $post_id.":".$parent);
	              	mfo_warning_email( "ERROR: cred_update_agreement: Already had agreement date!".$post_id, $post_id.":".$parent);
		} else { // no date
			$date = current_time( "timestamp", $gmt = 0);
			update_post_meta($post_id, 'wpcf-maker-agreement-date', $date); //set to today
			}
	} else { //no ack
		update_post_meta($post_id, 'wpcf-maker-agreement-date', ""); //no ack, no date
	} //end if ack

}

//add_action ('cred_save_data_5031', 'cred_update_agreement', 10 ,2 );
*/

function cred_update_agreement_before_save($form_data){
	//5031 is the Maker Agreement CRED form
	$post_id = $form_data['container_id'];

	$ack = $_POST['wpcf-maker-agreement-ack'];

	if ($ack) {
	        //wp_mail( "ian.cole@gmail.com", "got ack".$post_id, "stuff");
		$date = $_POST['wpcf-maker-agreement-date'];
		if ($date) {	//this is an error condition...
	              	//wp_mail( "ian.cole@gmail.com", "ERROR: cred_update_agreement: Already had agreement date!", $post_id);
		} else { // no date
	        	//wp_mail( "ian.cole@gmail.com", "got ack, but no date",$post_id);
			$date = current_time( "timestamp", $gmt = 0);
			//$_POST['wpcf-maker-agreement-date'] = $date; //set to today
			update_post_meta($post_id, 'wpcf-maker-agreement-date', $date); //set to today

			$user = wp_get_current_user();
			$username = $user->display_name;
			$userid = $user->ID;
			//$_POST['wpcf-maker-agreement-user'] = $user->display_name; //set to current user
	        	update_post_meta($post_id, 'wpcf-maker-agreement-user-name', $username); //set to user
	        	update_post_meta($post_id, 'wpcf-maker-agreement-user-id', $userid); //set to user
			//wp_mail( "ian.cole@gmail.com", "got ack, but no date, set date and user",$post_id.";".$date.";".$user->display_name);
			//todo: test if current user is post author - if no, send warning email
			}
	} else { //no ack
		//$_POST['wpcf-maker-agreement-date'] = ""; //set to today
		update_post_meta($post_id, 'wpcf-maker-agreement-date', ""); //set to today
	} //end if ack

}

//todo: fix hard-coded form ids
add_action ('cred_before_save_data_5031', 'cred_update_agreement_before_save', 10 ,2 );


function cred_save_data_exhibit_helper($post_id, $form_data){
	mfo_log(2, "cred_save_data_exhibit_helper", "post_id=".$post_id);
	//5097 is the new exhibit helper form

	//better title
	$first = get_post_meta($post_id,'wpcf-helper-first-name', true);
	$last = get_post_meta($post_id, 'wpcf-helper-last-name', true);
	$title = $first." ".$last;

	//collect data and define new title
        $my_post = array(
            'ID'           => $post_id,
            'post_title'   => $title,
            'post_name'    => $post_id,
        );
        // Update the post into the database
        wp_update_post( $my_post );
	mfo_log(3, "cred_before_save_data_exhibit_helper", "post_title=".$title);

	//get the parent post from the post_id
	$parent_id = get_post_meta($post_id, "_wpcf_belongs_exhibit_id", true);
	mfo_log(3, "cred_before_save_data_exhibit_helper", "parent_id=".$parent_id);
	//get the haq
	$haq = get_post_meta($parent_id, 'wpcf-helper-approved-quantity', true);
	mfo_log(3, "cred_before_save_data_exhibit_helper", "haq=".$haq);

	//get the current number of exhibit helpers
	//trying this: https://wp-types.com/forums/topic/list-brotherssiblings-when-on-a-child-post-using-php/
	//types parent relationship doesnt use post_parent: https://wp-types.com/documentation/user-guides/querying-and-displaying-child-posts/

	$args = array(
		//'posts_per_page' 	=> -1,
		'meta_query' => array(array('key' => '_wpcf_belongs_exhibit_id', 'value' => $parent_id)),
		'post_type' 		=>'exhibit-helper',
		'post_status' 		=>'publish'
		);
	mfo_log(3, "cred_before_save_data_exhibit_helper", "args=".print_r($args, true));
	$myposts = get_posts ($args);
	mfo_log(3, "cred_before_save_data_exhibit_helper",  "myposts=".print_r($myposts, true));
	$helpers = count($myposts);
	mfo_log(3, "cred_before_save_data_exhibit_helper", "helpers=".$helpers);
	if ($helpers > $haq) {
		mfo_warning_email( "ERROR: Helpers exceed approved quantity","post: ".$post_id, "exhibit: ".parent_id);
	}
	//send a message (or prevent?) if over

}
//todo: fix hard-coded form ids

add_action ('cred_save_data_5097', 'cred_save_data_exhibit_helper', 10 ,2 );

/*
 * Flush JSON from cache when a maker or exhibit has changed
 * This may be possible with a setting in the caching plugin
 * Note this is dependent on the caching plugin used...this function is for w3 total cache
 *
 */
function flush_json_from_cache() {
	if (function_exists('w3tc_pgcache_flush_post')) {
		//todo: remove hard-coded post ID; use url_to_postid( $url )  function?
		w3tc_pgcache_flush_post(5604); //purge the JSON output from the cache
	}


}


/**
 *This allows use of the notification-email setting in CRED notification forms (yay!) 
 *It could be extended to look for a shortcode and do_shortcode() but this works for now...
 *CRED forms should have a notification email of "notification-email" - this is NOT a shortcode
 *minimal documentation here: https://wp-types.com/forums/topic/using-shortcode-output-as-recipient-email-address-in-cred-notification/
 */

add_filter( 'cred_notification_recipients', 'setup_cred_recipients', 10, 4 );

function setup_cred_recipients($recipients, $notification, $form_id, $post_id)
{

	//mfo_log(4, "setup_cred_recipients()");
	mfo_log(4, "setup_cred_recipients", "form_id:" . $form_id);
	mfo_log(4, "setup_cred_recipients", "post_id:" . $post_id);
	mfo_log(4, "setup_cred_recipients", "recipients:" . print_r($recipients, true));


	$search = "notification-email";
	$options = get_option('mfo_options_main');
 	$replace = $options[mfo_notification_email_string];

	$new_recipients = json_decode(str_ireplace($search, $replace, json_encode($recipients)),true);
	//found this trick here: http://codelinks.pachanka.org/post/94733910648/php-replace-strings-in-multidimensional-arrays
	mfo_log(4, "setup_cred_recipients", "cred_new_recipients:" . print_r($new_recipients, true));

    // do some with $recipients
    return $new_recipients;
}


function mfo_duplicate_post ($post_id, $old_name, $new_name) {
	//core code from here: http://rudrastyh.com/wordpress/duplicate-post.html
	$ret = 0;

	global $wpdb;
	mfo_log(2, "mfo_duplicate_post", "post_id=" . $post_id .  "; old_name=" . $old_name . "; new_name=" .$new_name);

	//todo: can current user edit the post (security check)

	/* get all the original post data then
	 */

	$post = get_post( $post_id );

	/*
	 * if you don't want current user to be the new post author,
	 * then change next couple of lines to this: $new_post_author = $post->post_author;
	 */
	//$current_user = wp_get_current_user();
	$new_post_author = $post->post_author;

	/*
	 * if post data exists, create the post duplicate
	 */
	if (isset( $post ) && $post != null) {

		/*
		 * new post data array
		 */
		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $new_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => $post->post_status,
			'post_title'     => $post->post_title,
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order,
		);


		$appr_year =  get_post_meta($post->ID, 'wpcf-approval-year', TRUE);

		$post->post_name = $old_name;
		wp_update_post ($post);

		/*
		 * insert the post by wp_insert_post() function
		 */
		$new_post_id = wp_insert_post( $args );


		/*
		 * get all current post terms ad set them to the new post draft
		 */
		$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}

		/*
		 * duplicate all post meta just in two SQL queries
		 */
		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
		if (count($post_meta_infos)!=0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;
				$meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
		}

	$ret = 1;
	}
	return $ret;
}


add_filter( 'wpv_custom_inner_shortcodes', 'mfo_toolset_add_shortcodes' );

function mfo_toolset_add_shortcodes( $shortcodes ) {

	array_push( $shortcodes,
		'current-user-can-edit-others-posts',
		'current-user-can-edit-post',
		'decode',
		'mfo-agreements-enabled',
		'mfo-edit-exhibits-enabled',
		'mfo-edit-makers-enabled',
		'mfo-exhibit-color',
		'mfo-exhibit-location-enabled',
		'mfo-exhibithelpers-enabled',
		'mfo-event-year',
		'mfo-loadin-enabled',
		'mfo-maker-color',
		'mfo-orientationrsvp-enabled',
		'mfo-prioryear-color',
		'mfo-tablesigns-enabled',
		'stripcrlf',
		'striptags',
		'taxonomy-level',
		'wpv-current-user',
		'wpv-found-count',
		'wpv-post-author',
		'wpv-post-param',
		'wpv-search-term' );

    return $shortcodes;
}

?>
