<?php

/*
Plugin Name: Maker Faire Online - CFM & More
Plugin URI: http://www.github.com/digitalman2112/mfo-wordpress-plugin
Description: Helper plugin for the Maker Faire Online system based using the Toolset plugins & more
Version: 3.24.4
Author: Ian Cole (Maker Faire Orlando)
Author URI: http://www.github.com/digitalman2112
GitHub Plugin URI: makerfaireorlando/mfo-wordpress-plugin
*/


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//include settings code
include( plugin_dir_path( __FILE__ ) . 'mfo-settings.php');
include( plugin_dir_path( __FILE__ ) . 'mfo-cleanup.php');

//disables the admin bar on the frontend
add_filter('show_admin_bar', '__return_false');
add_action( 'wp_enqueue_scripts', 'add_custom_scripts' );


// add a link to the WP Toolbar
function custom_toolbar_link($wp_admin_bar) {
    $args = array(
        'id' => 'mfooptions',
        'title' => 'MFO Options',
        'href' => '/wp-admin/options-general.php?page=mfo-options-page&tab=main_options',
        'meta' => array(
            'class' => 'mfooptions',
            'title' => 'MFO Options'
            )
    );
    $wp_admin_bar->add_node($args);

   // Add the child links

    $args = array(
        'id' => 'mfomainoptions',
        'title' => 'Main Options',
        'href' => '/wp-admin/options-general.php?page=mfo-options-page&tab=main_options',
        'parent' => 'mfooptions',
        'meta' => array(
            'class' => 'mfomainoptions',
            'title' => 'Main Options'
            )
    );
    $wp_admin_bar->add_node($args);

    $args = array(
        'id' => 'mfodisplayoptions',
        'title' => 'Display Options',
        'href' => '/wp-admin/options-general.php?page=mfo-options-page&tab=display_options',
        'parent' => 'mfooptions',
        'meta' => array(
            'class' => 'mfodisplayoptions',
            'title' => 'Display Options'
            )
    );
    $wp_admin_bar->add_node($args);


    $args = array(
        'id' => 'mfofeatureoptions',
        'title' => 'Feature Options',
        'href' => '/wp-admin/options-general.php?page=mfo-options-page&tab=feature_options',
        'parent' => 'mfooptions',
        'meta' => array(
            'class' => 'mfomainsettings',
            'title' => 'Feature Options'
            )
    );
    $wp_admin_bar->add_node($args);

    $args = array(
        'id' => 'mfomoduleoptions',
        'title' => 'Module Options',
        'href' => '/wp-admin/options-general.php?page=mfo-options-page&tab=module_options',
        'parent' => 'mfooptions',
        'meta' => array(
            'class' => 'mfomodulesettings',
            'title' => 'Module Options'
            )
    );
    $wp_admin_bar->add_node($args);

    $args = array(
        'id' => 'mfodebugoptions',
        'title' => 'Debug Options',
        'href' => '/wp-admin/options-general.php?page=mfo-options-page&tab=debug_options',
        'parent' => 'mfooptions',
        'meta' => array(
            'class' => 'mfodebugsettings',
            'title' => 'Debug Options'
            )
    );
    $wp_admin_bar->add_node($args);

    $args = array(
        'id' => 'mfoproducerdash',
        'title' => 'Producer Dashboard',
        'href' => '/producer-dashboard',
        'parent' => 'mfooptions',
        'meta' => array(
            'class' => 'mfoproducersdash',
            'title' => 'Producer Dashboard'
            )
    );
    $wp_admin_bar->add_node($args);

    $args = array(
        'id' => 'mfomakerdash',
        'title' => 'Maker Dashboard',
        'href' => '/maker-dashboard',
        'parent' => 'mfooptions',
        'meta' => array(
            'class' => 'mfomakerdash',
            'title' => 'Maker Dashboard'
            )
    );
    $wp_admin_bar->add_node($args);
}

add_action('admin_bar_menu', 'custom_toolbar_link', 999);




mfo_load_modules();


function mfo_load_modules () {
$options = get_option('mfo_options_modules');

if ( $options['mfo_module_eventbrite_enabled_boolean'] ) {
	mfo_log (4, "load modules", "loading mfo-eventbrite-webhook module");
	include( plugin_dir_path( __FILE__ ) . 'mfo-eventbrite-webhook.php');
	mfo_eventbrite_init();
}

if ( $options['mfo_module_sensei_enabled_boolean'] ) {
	mfo_log (4, "load modules", "loading mfo-sensei-helpers module");
	include( plugin_dir_path( __FILE__ ) . 'mfo-sensei-helpers.php');
	//mfo_sensei_compatibility();
}

if ( $options['mfo_module_woocommerce_enabled_boolean'] ) {
	mfo_log (4, "load modules", "loading mfo-woocommerce-helpers module");
	include( plugin_dir_path( __FILE__ ) . 'mfo-woocommerce-helpers.php');
}

if ( $options['mfo_slack_enabled_boolean'] ) {
	include( plugin_dir_path( __FILE__ ) . 'mfo-slack.php');
	mfo_log (4, "load modules", "loading mfo-slack module");
}

}

add_filter('single_template', 'mfo_single_template');

function mfo_single_template($single) {
    global $wp_query, $post;

    /* Checks for single template by post type */
    if ($post->post_type == "maker"){
        if(file_exists( dirname( __FILE__ ) . '/templates/single-maker.php'))
            return dirname( __FILE__ ) . '/templates/single-maker.php';
    }
    elseif ($post->post_type == "exhibit"){
        if(file_exists( dirname( __FILE__ ) . '/templates/single-exhibit.php'))
            return dirname( __FILE__ ) . '/templates/single-exhibit.php';
    }


    return $single;
}


add_filter( 'page_template', 'mfo_page_template' );
function mfo_page_template( $page_template )
{

	/* YOU still have to have a wordpress page, this will just auto-apply the template!" */

	$pagename = get_query_var('pagename');  

	//mfo_log(4, "mfo_page_template", "pre: page_template=" . $page_template);
	mfo_log(4, "mfo_page_template", "pre: pagename=" . $pagename);

	$templates = array (
	array('csv-exhibit-checkin', 	'csv-exhibit-checkin.php'),
	array('csv-helper-checkin', 	'csv-helper-checkin.php'),
	array('csv-maker-media-export',	'csv-maker-media-export.php'),
	array('producer-exhibit-search-csv-results', 	'csv-results.php'),
	array('producer-maker-search-csv-results', 	'csv-results.php'),
	array('csv-social', 		'csv-social.php'),
 	array('duplicate-exhibit',	'duplicate-exhibit.php'),
 	array('maker-dashboard', 	'maker-dashboard.php'),
	array('exhibit-table-signs',	'exhibit-table-signs.php'),
	array('exhibit-space-planning-sheets',	'exhibit-space-planning-sheets.php'),
	array('events-json',		'json-eventlist.php'),
	array('makers-json',		'json-makerlist.php'),
	array('makers-json2',		'json-makerlist2.php'),
	array('jekyll-build',		'jekyll-build.php'),
	array('slack-find-exhibit',	'json-slack-maker.php'),
	array('select-load-in-date-time','load-in.php'),
	array('producer-loadin-report',	'loadin-report.php'),
	array('makers',			'exhibits-isotope.php'),
	array('much-makers',		'stat.php')
	);

	foreach ($templates as $t) {
		if ($t[0] == $pagename)  {
        		$page_template = dirname( __FILE__ ) . '/templates/' . $t[1];
			break;
		}
	}


	//deprecated?
	//mfo_load_page_template('event-talks',		'json-eventlist.php'); 
	//mfo_load_page_template('fix-events',		'fix-event-locations.php'); 
	mfo_log(4, "mfo_page_template", "post: page_template=" . $page_template);
	return $page_template;
}





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





//add query string variables
function add_query_vars_filter($vars) {
	$vars[] = "li-exhibit";
	$vars[] = "dup-exhibit";
	$vars[] = "post_ids";
	$vars[] = "cred-edit-form";
	$vars[] = "id";

	//exhibits page
	$vars[] = "category";

	//vars for slash commands
	$vars[] = "token";
	$vars[] = "text";
	$vars[] = "channel_name";

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

	//todo: test for $atts existing to prevent warnings
	//$id = $atts["id"];
	//if (!$id) {
		$id=get_the_ID();
	//}
	$cap = false;
	$cap = current_user_can('edit_post',$id);
	//echo $id; 
	//echo " - "; 
	//echo  get_current_user_id();
	//echo " - "; 
	//echo $cap ? 'true' : 'false';
	//echo '<br>';
return $cap;
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
  add_rewrite_tag('%csv-year%', '([^&]+)');
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
	if (function_exists('mfo_post_to_slack')) {
		mfo_post_to_slack($subject, 'mfo-wp-debug', 'tacocat', ':taco:');
	}
}

function mfo_send_notification_email ($subject, $body) {
	$options = get_option('mfo_options_main');
	mfo_log (1, "mfo_notification_email", $subject . " | " . $body);
	wp_mail(  $options['mfo_notification_email_string'], $subject, $body);
	$attach = array(array( "text" => $body));
	if (function_exists('mfo_post_to_slack')) {
		mfo_post_to_slack($subject, 'system-notifications', 'makey', ':makey:', $body);
	}
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
        'post_status' => 'publish',
	'meta_query' => array(array('key' => 'wpcf-approval-year', 'value' => mfo_event_year()))
        );

        $children = get_posts($childargs);

        $count = count($children);

        return $count;
}
add_shortcode( 'count-exhibits', 'count_exhibits_shortcode' );

function count_exhibits_pending_shortcode( $atts, $content = null ) {
        $childargs = array(
        'post_type' => 'exhibit',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
                'relation' => 'AND',
                array('key' => 'wpcf-approval-year', 'value' => mfo_event_year()) ,
                array('key' => 'wpcf-approval-status', 'value' => '2'),
                )
        );

        $children = new WP_Query($childargs);
        return $children->post_count;
}
add_shortcode( 'count-exhibits-pending', 'count_exhibits_pending_shortcode' );

function count_exhibits_approved_shortcode( $atts, $content = null ) {
        $childargs = array(
        'post_type' => 'exhibit',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
                'relation' => 'AND',
                array('key' => 'wpcf-approval-year', 'value' => mfo_event_year()) ,
                array('key' => 'wpcf-approval-status', 'value' => '1'),
                )
        );

        $children = new WP_Query($childargs);
        return $children->post_count;
}
add_shortcode( 'count-exhibits-approved', 'count_exhibits_approved_shortcode' );

function count_exhibits_rejected_shortcode( $atts, $content = null ) {
	$childargs = array(
        'post_type' => 'exhibit',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
                'relation' => 'AND',
                array('key' => 'wpcf-approval-year', 'value' => mfo_event_year()) ,
                array('key' => 'wpcf-approval-status', 'value' => '3'),
                )
        );

        $children = new WP_Query($childargs);
        return $children->post_count; }
add_shortcode( 'count-exhibits-rejected', 'count_exhibits_rejected_shortcode' );

function count_exhibits_withdrawn_shortcode( $atts, $content = null ) {
$childargs = array(
        'post_type' => 'exhibit',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
                'relation' => 'AND',
                array('key' => 'wpcf-approval-year', 'value' => mfo_event_year()) ,
                array('key' => 'wpcf-approval-status', 'value' => '4'),
                )
        );

        $children = new WP_Query($childargs);
        return $children->post_count; }
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
		$author_info = get_userdata($author_id);
		$author_name = $author_info->first_name . " " . $author_info->last_name;
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
		$appr_year = ""; //avoid warnings with no child exhibits
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
				update_post_meta($child->ID, 'wpcf-helper-approved-quantity', mfo_exhibithelpers_default()); 
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

//this creates a post title from the name and email, and sets the event year
function mfo_cred_save_data_educator($post_id) {

	$post_type = get_post_type($post_id);

	if ($post_type == 'educator') {
		mfo_log(4, "mfo_cred_save_data_educator", "educator postid:" . $post_id);


		//better title
       		$first = get_post_meta($post_id,'wpcf-first-name', true);
        	$last = get_post_meta($post_id, 'wpcf-last-name', true);
        	$email = get_post_meta($post_id, 'wpcf-educator-email-address', true);
        	$title = $first." ".$last." - ".$email;

        	//collect data and define new title
        	$my_post = array(
            	'ID'           => $post_id,
           	'post_title'   => $title,
            	'post_name'    => $post_id,
        	);


		$year = get_post_meta($post_id, 'wpcf-educator-event-year', true);
		mfo_log(4, "mfo_cred_save_data_educator", "educator year:" . $year);

		if (!$year) {
	       		$upm = update_post_meta($post_id, 'wpcf-educator-event-year', mfo_event_year());
		}


        	// Update the post into the database
        	wp_update_post( $my_post );

        	mfo_log(3, "mfo_cred_save_data_educator", "post_title=".$title);

		}

}




//function cred_update_maker_stats($post_id, $form_data){
function mfo_cred_save_data($post_id, $form_data){
	//need to use this hook because the wpcf_pr_post_get_belongs function doesnt work
	//with the standard save_data hook.


	mfo_log(2, "mfo_cred_save_data",  $post_id . ", " . print_r ($form_data, true));

	//fix hard coding form ID
	//if ($form_data['id']==2725) { //Exhibit - Edit
	if ($form_data['post_type'] == "exhibit" && $form_data['form_type'] == "edit") { //Exhibit - Edit
		$parent = wpcf_pr_post_get_belongs($post_id, 'maker');
		//wp_mail( "ian.cole@gmail.com", "cred_update_maker_stats-Exhibit Edit:".$post_id, $post_id.":".$parent);
		if  (!$parent) {
			//wp_mail( "ian.cole@gmail.com", "WARNING:ORPHAN".$post_id, $post_id.":".$parent);
			mfo_warning_email( "WARNING:ORPHAN".$post_id, $post_id.":".$parent);
		}
		else update_maker_stats($parent);
	} //end exhibit - edit form
	else if (get_post_type($post_id)=="exhibit") {
		$parent = wpcf_pr_post_get_belongs($post_id, 'maker');
		//wp_mail( "ian.cole@gmail.com", "cred_update_maker_stats-Exhibit Add:".$post_id, $post_id.":".$parent);
		if  ($parent) {
			update_maker_stats($parent);
		}
	} //end exhibit - all other forms
	else if (get_post_type($post_id)=="educator") {
		mfo_log(4,"mfo_cred_save_data", "educator: ".$post_id);
		mfo_cred_save_data_educator($post_id);
	}

}

add_action ('cred_save_data', 'mfo_cred_save_data', 9999 ,2 );




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
		//$date = $_POST['wpcf-maker-agreement-date'];
		if (isset($_POST['wpcf-maker-agreement-date'])) {	//this is an error condition...
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
		mfo_log(3, "flush_json_from_cache()", "");
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
 	$replace = $options['mfo_notification_email_string'];

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
		 * get all current post terms and set them to the new post draft
		 */
		$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");

		//mfo_log(2, "mfo_duplicate_post", "taxonomies:" . print_r($taxonomies, true));

		//we want to NOT copy locations; featured posts, etc. - ONLY CATEGORIES

		foreach ($taxonomies as $taxonomy) {
			//mfo_log(2, "mfo_duplicate_post", "taxonomy:" . print_r($taxonomy, true));
			if ($taxonomy == "exhibit-category") {
				$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
				wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
			}
		}

		/*
		 * duplicate all post meta just in two SQL queries
		 */
		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
	

		mfo_log(4, "mfo_duplicate_post", "count(post_meta):" . count($post_meta_infos));
		if (count($post_meta_infos)!=0) {

			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;
				$meta_value = addslashes($meta_info->meta_value);
				mfo_log(4, "mfo_duplicate_post", $meta_key . "->" . $meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
			//mfo_log(4, "mfo_duplicate_post", "sql_query" . printr($sql_query, true));
		}



		delete_post_meta( $new_post_id, "wpcf-approval-status");
		delete_post_meta( $new_post_id, "wpcf-approval-status-date");
		delete_post_meta( $new_post_id, "wpcf-approval-status-notify");
		delete_post_meta( $new_post_id, "wpcf-agreement-status");
		delete_post_meta( $new_post_id, "wpcf-exhibit-space-number");
		delete_post_meta( $new_post_id, "wpcf-exhibit-space-number-sort-order");
		delete_post_meta( $new_post_id, "wpcf-orientation-status");
		delete_post_meta( $new_post_id, "wpcf-approval-year");
		delete_post_meta( $new_post_id, "wpcf-payment-status");
		delete_post_meta( $new_post_id, "wpcf-orientation-status");
		delete_post_meta( $new_post_id, "wpcf-exhibit-loadin-slot");


		update_post_meta( $new_post_id, "wpcf-approval-year", mfo_event_year());
		update_post_meta( $new_post_id, "wpcf-approval-status", "2");
		//update_post_meta( $new_post_id, "wpcf-agreement-status","0");  //not approved
		//update_post_meta( $new_post_id, "wpcf-exhibit-space-number","");  //no space assigned

		//update_maker_stats ran automatically, when the post was saved, need
		//to run it again after we make our changes
		update_maker_stats_child_id($new_post_id);


	$ret = $new_post_id;

	$meta = get_post_meta($new_post_id);

	mfo_send_notification_email("MFO: Exhibit Duplicated - " . $post->post_title, print_r($post, true) . print_r($meta, true));

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
		'mfo-feepayments-enabled',
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


//load custom scripts by page
function add_custom_scripts() {
        if (is_page('producer-exhibit-location-and-hidden-category-slides') OR
                is_page('select-load-in-date-time') ){
        	wp_enqueue_script('jquery-ui-accordion');
        	wp_enqueue_script('jquery-ui-button');
        	wp_enqueue_style('mfo-admin-ui-css',
                	'http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/blitzer/jquery-ui.css',
                	false, "1.11.4", false);
        }
	elseif (is_page('makers') OR is_page('schedule')) {


		wp_register_script (
			'isotope-js', 
			//get_stylesheet_directory_uri() . '/js/libs/isotope.pkgd.min.js',
			'https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js',
			array('jquery'), null
		); //jquery as a dependy for isotope
 		wp_enqueue_script( "isotope-js");
 		wp_enqueue_script( "exhibits-isotope-helper-js", plugin_dir_url( __FILE__ ) . '/templates/exhibits-isotope.js', null, false , true);
		//wp_enqueue_script( "imagefill-js", get_stylesheet_directory_uri() . '/js/libs/jquery-imagefill.js');
		wp_enqueue_script( "imagesloaded-js", 'https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js', array(), null);
		wp_enqueue_script( "imagefill-js", 'https://cdn.jsdelivr.net/jquery.imagefill/0.1/js/jquery-imagefill.js', array(), null);
		
 	}
}

// this function is used to retrieve custom fields for taxonomy "terms"
// example
// [mfo-term-field-output term="Featured" taxonomy="hidden-exhibit-category" field="badge-image"]
function mfo_term_field_output($atts) {
        $satts = shortcode_atts( array('term' => '', 'taxonomy' =>'' , 'field' => ''), $atts);
        $term_array = get_term_by('name', $satts['term'], $satts['taxonomy']);
        return types_render_termmeta($satts['field'], array( "term_id" => $term_array->term_id, "output"=>'raw') );
}

add_shortcode('mfo-term-field-output', 'mfo_term_field_output');


add_filter('wp_nav_menu_items','add_to_menu', 1, 2);
function add_to_menu( $items, $args ) {

    if( $args->theme_location == 'header-menu')  {

	  if (is_user_logged_in() ) {

		$user = isset($user) ? new WP_User( $user ) : wp_get_current_user();

        	$items .=  '<li itemscope="itemscope" itemtype="https://www.schema.org/SiteNavigationElement" id="menu-item-99900"'.
				'class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-99900 dropdown">'.
 
				 '<a title="My Account" href="#" data-toggle="dropdown" class="dropdown-toggle" aria-haspopup="true">'.
					'<i class=" fa My Account" aria-hidden="true"></i>' .
					'&nbsp;My Account ' .
					'<span class="caret"></span>' .
					'</a>'.
				'<ul role="menu" class=" dropdown-menu">'.
					'<li itemscope="itemscope" itemtype="https://www.schema.org/SiteNavigationElement" id="menu-item-99901"' .
							'class="menu-item menu-item-type-post_type menu-item-object-page menu-item-99901" >' .
							'<a title="Maker Dashboard" href="/maker-dashboard">Maker Dashboard</a></li>';

					if ( in_array( 'producer', $user->roles ) || in_array( 'administrator', $user->roles ) ) {

					$items .= '<li itemscope="itemscope" itemtype="https://www.schema.org/SiteNavigationElement" id="menu-item-99902"' .
							'class="menu-item menu-item-type-post_type menu-item-object-page menu-item-99902" >' .
							'<a title="Maker Dashboard" href="/producer-dashboard">Producer Dashboard</a></li>' .
					'<li itemscope="itemscope" itemtype="https://www.schema.org/SiteNavigationElement" id="menu-item-99903"' .
							'class="menu-item menu-item-type-post_type menu-item-object-page menu-item-99903" >' .
							'<a title="Maker Dashboard" href="/wp-admin">Wordpress Admin</a></li>';

					}


					$items .= '<li itemscope="itemscope" itemtype="https://www.schema.org/SiteNavigationElement" id="menu-item-99904"' .
							'class="menu-item menu-item-type-post_type menu-item-object-page menu-item-99904" >' .
							'<a title="Change Password" href="/wp-admin/profile.php">Change Password</a></li>'.
						'<li itemscope="itemscope" itemtype="https://www.schema.org/SiteNavigationElement" id="menu-item-99905"' .
							'class="menu-item menu-item-type-post_type menu-item-object-page menu-item-99905" >' .
							'<a title="Logout" href="' . wp_logout_url() . ' ">Logout</a></li>';

		}
	 else {

        	$items .=  '<li>' . "<a href='/wp-login/'>Maker Login</a>" .  '</li>';
	}

	}
    return $items;
}

/**
 * Redirect user after successful login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 *
 * url:https://codex.wordpress.org/Plugin_API/Filter_Reference/login_redirect
 * 
 */
function mfo_login_redirect( $redirect_to, $request, $user ) {
	//is there a user to check?
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check for administrator or producer
		if ( in_array( 'producer', $user->roles ) || in_array( 'administrator', $user->roles ) ) {
			// redirect them to the default place
			return  "/producer-dashboard";
		} else {
			return "/maker-dashboard";
		}
	} else {
		return $redirect_to;
	}
}

add_filter( 'login_redirect', 'mfo_login_redirect', 10, 3 );


/*
 * Shortcode to output makey border
 *
 */
function mfo_makey_border() {
	$ret  =  '<div class="wimf-border"><div class="wimf-triangle"></div></div>';
	$ret .= '<img src="/wp-content/themes/mfo-wordpress-theme/images/makey.png" alt="Maker Faire information Makey icon">';
	return $ret;
}

add_shortcode('mfo-makey-border', 'mfo_makey_border');



/*
 * Get media attachment ID from the URL (for image urls in fields)
 * From - https://frankiejarrett.com/2013/05/get-an-attachment-id-by-url-in-wordpress/
 */

function mfo_get_attachment_id_by_url( $url ) {

    //echo 'mfo_get_attachment_id_by_url( '.$url.' )';
    // Split the $url into two parts with the wp-content directory as the separator
    $parsed_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );
    // echo 'purl1 - ' . $parsed_url[1] .'<br>';
    // Get the host of the current site and the host of the $url, ignoring www
    $this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
    $file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );
    //echo 'this_host = ' . $this_host .'<br>';
    //echo 'file_host = ' . $file_host .'<br>';
    // Return nothing if there aren't any $url parts or if the current host and $url host do not match
    // ic - i removed the host matching criteria as it failed on the test server
    if ( ! isset( $parsed_url[1] ) || empty( $parsed_url[1] ) /* || ( $this_host != $file_host ) */ ) {
        //echo 'early return';
	 return;
    }

    // Now we're going to quickly search the DB for any attachment GUID with a partial path match

    // Example: /uploads/2013/05/test-image.jpg
    global $wpdb;
    //echo $parsed_url[1];
    $attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parsed_url[1] ) );

    // Returns null if no attachment is found
    return $attachment[0];
}


add_shortcode('mfo-today-cred', 'mfo_today_cred');
function mfo_today_cred() {
  return time();
}

?>
