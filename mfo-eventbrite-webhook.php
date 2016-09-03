<?php

/*
Now part of overall mfo plugin
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//for the event-confirmation page don't return results unless there is an order parameter
add_filter('wpv_filter_query', 'my_filter_post_id', 14, 3);
function my_filter_post_id( $query_args, $query, $view_id ) {
    if ( !is_admin() && $view_id == 4995 && !isset($_GET['oid']) ) {
        $query_args['post__in'] = array('0');
    }
    return $query_args;
}



function mfo_eventbrite_init(){
	/* hook for creating webhook endpoints */
	mfo_log(4, 'eventbrite','mfo_eventbrite_init();');
	add_action( 'init', 'mfo_eventbrite_endpoint' );
	add_action( 'parse_request', 'mfo_eventbrite_parse_request' );
}


function mfo_eventbrite_endpoint(){
	// access webhook at url such as http://[your site]/mailchimp/webhook
	mfo_log(4, 'eventbrite', 'mfo_eventbrite_endpoint();');
    	add_rewrite_rule( 'webhook' , 'index.php?webhook=1', 'top' );
    	add_rewrite_tag( '%webhook%' , '([^&]+)' );
}

function mfo_eventbrite_parse_request( $wp )
{
	mfo_log(4, 'eventbrite', 'mfo_eventbrite_parse_request();');
	mfo_log(4, 'eventbrite', 'query_vars: '.print_r($wp->query_vars, true));

	//note, the line below was commented to remove debug notice warnings
	//if($wp->query_vars['webhook']) {

	//this appears to get rid of the warning
	if (isset($wp->query_vars['webhook'])) {

	//and trying this line, but todo: need to retest with eventbrite to make sure it works
	//if (get_query_var('webhook')){

	//if('webhook'== $wp->query_vars['pagename']) {
    	//if ( array_key_exists( 'webhook', $wp->query_vars ) ) {
       		mfo_eventbrite_action_webhook();
        	exit();
    	}
}

function mfo_eventbrite_action_webhook() {
	mfo_log(3, 'eventbrite', '==================[ Incoming Request ]==================');
	mfo_log(4, 'eventbrite', 'Full _REQUEST dump:\n'.print_r($_REQUEST,true)); 
	mfo_log(4, 'eventbrite', 'Full _POST dump:\n'.print_r($_POST,true)); 
	mfo_log(4, 'eventbrite', 'Full header dump:\n'.print_r(getallheaders(),true)); 
	/*
	if ( empty($_POST) ) {
		mfo_log(3,'eventbrite', 'No request details found.');
		die('No request details found.');
	}
	*/
	mfo_log(4,'eventbrite', 'payload: '.print_r($_POST['payload'],true)); 
	//$obj=json_decode($_POST['payload']); // put the second parameter as true if you want it to be a associative array

	$request_body = file_get_contents('php://input', true);
	mfo_log(4,'eventbrite', "request_body: ".print_r($request_body, true));
	$data = json_decode($request_body);
	$action = $data->config->action;
	mfo_log(3,'eventbrite', "data->config->action: ".print_r($action, true));
	mfo_log(3,'eventbrite', "data->config->user_id: ".print_r($data->config->user_id, true));
	if ($action == 'order.placed') {
		mfo_eventbrite_order_placed($data);
		}

	echo "Webhook received.";
	mfo_log(3,'eventbrite', 'Finished processing request.');
}

function mfo_eventbrite_order_placed ($data) {
	//todo: add setting for token

	$options = get_option('mfo_options_modules');
	$token = $options['mfo_eventbrite_token_string'];

	mfo_log(3, 'eventbrite',"mfo_eb_order_placed();");

	mfo_log(3, 'eventbrite',"data->api_url: ".print_r($data->api_url, true));
	$url_order = $data->api_url.'?token='.$token.'&expand=attendees,event';
	mfo_log(3, "order url: ".$url_order);
	$order_raw = file_get_contents($url_order);
	mfo_log(4, 'eventbrite',"file_get_contents: ".$order_raw);
	$order = json_decode($order_raw);
	mfo_log(3, 'eventbrite',"order->name: ".print_r($order->name, true));
	mfo_log(3, 'eventbrite',"order->email: ".print_r($order->email, true));

	$url_event = $order->event->resource_uri.'?token='.$token.'&expand=ticket_classes';
	mfo_log(3, 'eventbrite',"event url: ".$url_event);

	$event_raw = file_get_contents($url_event);
	mfo_log(4, 'eventbrite',"file_get_contents: ".$event_raw);
	$event = json_decode($event_raw);
	mfo_log(4, 'eventbrite',"event->name->text: ".print_r($event->name->text, true));
	mfo_log(4, 'eventbrite',"event->ticket_classes: ".print_r($event->ticket_classes, true));
	mfo_log(4, 'eventbrite',"order->attendees: ".print_r($order->attendees, true));

	$numTickets = 0;
	foreach($order->attendees as $attendee) {
		$numTickets++;
		mfo_log(3,'eventbrite', 'ticket '.$numTickets.' is ticket_class_id: '.$attendee->ticket_class_id);
		}

	$debug_content = $debug_content."**********order.placed**********<br>".json_encode($data, JSON_PRETTY_PRINT).'<br><br>';
	$debug_content = $debug_content."**********order**********<br>".json_encode($order, JSON_PRETTY_PRINT).'<br><br>';
	$debug_content = $debug_content."**********event**********<br>".json_encode($event, JSON_PRETTY_PRINT).'<br><br>';

	$post = array(
	'post_type'	=> 'eventbrite-order',
	//removed this from post_cotent for security
	//'post_content' 	=> $debug_content,
	'post_title' 	=> 'Order: '.$order->id.': '.$order->name,
	'post_name' 	=> 'eventbrite-order-'.$order->id,
	'post_status' 	=> 'publish',
	'post_author' 	=> '1'
	);
	$post_id = wp_insert_post( $post, false );


	update_post_meta($post_id, 'wpcf-order-name', $order->name);
	update_post_meta($post_id, 'wpcf-order-email', $order->email);
	update_post_meta($post_id, 'wpcf-order-id', $order->id);
	update_post_meta($post_id, 'wpcf-order-event-id', $order->event->id);
	update_post_meta($post_id, 'wpcf-order-number-of-tickets', $numTickets);
	//removed this from post_cotent for security
	//update_post_meta($post_id, 'wpcf-eventbrite-order-debug', $debug_content);

	$slack = $options['mfo_slack_enabled_boolean'];

	if ($slack) {
		mfo_slack_eventbrite_notification($post_id, $order->event->id);
	}


}

//for testing only 
/*
function mfo_slack_notify_test() {
         mfo_slack_eventbrite_notification("1234" , "22749813304");
}

add_shortcode("mfo-slack-notify-test", "mfo_slack_notify_test");
*/

function mfo_slack_eventbrite_notification( $order_id, $event_id ) {

        $imgflip_template_ids = array (15865071, 23862248, 36667827, 37113200, 52265123, 63777975, 69466679);
        $imgflip_template_id = $imgflip_template_ids[array_rand($imgflip_template_ids)];

        $qty = mfo_eventbrite_tickets_sold($event_id);

        $image_url = mfo_create_meme($imgflip_template_id, $qty. " Tickets", "HA! HA! HA!");

        //attachments must be an array of attachment objects, hence the nested arrays
        $attach = array(array( "text" => "", "image_url"=>$image_url ));

        mfo_log (4, "mfo_slack_eb_notify", "attach: " . print_r( $attach, true));
	$options = get_option('mfo_options_modules');
	$channel = $options[mfo_slack_producer_channel_string];

        mfo_post_to_slack("", $channel , "The Count", ":count:", $attach);

}

function mfo_eventbrite_tickets_sold($event_id){

	//need to have a setting for the event id
	$options = get_option('mfo_options_modules');
        $token = $options['mfo_eventbrite_token_string'];
	//$event_id = "22749813304";

	$url_tickets = 'https://www.eventbriteapi.com/v3/events/' . $event_id .'/ticket_classes/?token='.$token;

	//do not log the following, contains EB token...
	//mfo_log(3, 'mfo_eb_tix_sold', "event url: ".$url_tickets);

        $tickets_raw = file_get_contents($url_tickets);

	//mfo_log(4, 'eventbrite', "file_get_contents: ".$tickets_raw);
        $tickets = json_decode($tickets_raw);
	$ticket_classes = $tickets->ticket_classes;

	$total_tickets = 0;
        //mfo_log(4, 'eventbrite', "ticket_classes: ". print_r($ticket_classes, true));
	foreach ($ticket_classes as $ticket_class) {
		$total_tickets += $ticket_class->quantity_sold;
	}
	return $total_tickets;
}

//Eventbrite access code creation

function mfo_eb_code_ajax() {
 return  "<a id='button' href='" . admin_url('admin-ajax.php?action=eventbrite_create_code') .  "'>Click here now</a>";
 //admin_url('admin-ajax.php?action=eventbrite_create_code');
}

add_shortcode('mfo-eb-code-ajax', 'mfo_eb_code_ajax');

add_action('wp_ajax_eventbrite_create_code', 'mfo_eventbrite_create_code');

function mfo_eventbrite_create_code () {

	//todo: this code needs to be abstracted with settings, or a hook, so that the specific
	//	implementation is not in the plugin, but for now, this is here...

	$post_id = $_POST['postID'];
	mfo_log (4, 'eventbrite', "create_code - begin" . " : ". $post_id);

	$ed_email = get_post_meta($post_id, "wpcf-educator-email-address");

	//are there existing codes for this educator? If so, stop and error.
	$codes = get_post_meta($post_id, "wpcf-educator-eventbrite-code-url");
	if ( !empty($codes[0])) {
		echo 3;
		die();
	}

	//remove existing, to handle the case where there is an existing blank
	//this isnt elegant, but gets it done
	if (count($codes) > 0) {
		delete_post_meta($post_id, "wpcf-educator-eventbrite-code-url");
		}

	//todo, this should not be hardcoded :)
	$disc_tickets = "50863047,50863048,50401216,51376652,50863049,51376653,51376651,51376654";
	$access_tickets = "51114514";
	$eb_public_url = "http://makerfaireorlando.eventbrite.com?discount=";
	$event = "22749813304";
	$options = get_option('mfo_options_modules');
	$eb_token = $options['mfo_eventbrite_token_string'];


	//todo: test if codes already exist?

	$ed_email = get_post_meta($post_id, "wpcf-educator-email-address", true);

	$eb_discountcode = "ed_50off_" . $ed_email;
	$eb_discountcode_url = 'https://www.eventbriteapi.com/v3/events/'. $event . '/discounts/?token=' . $eb_token;
	$eb_discountcode_data = array ("discount.code" => $eb_discountcode,
			     	 "discount.percent_off" => '50.00',
				"discount.ticket_ids" => $disc_tickets,
			     	 "discount.quantity_available" => '1',
			     	);

	$dc_return = mfo_call_api("POST", $eb_discountcode_url, $eb_discountcode_data);
	mfo_log (4, 'eventbrite', "create_code - discount" . " : ". $dc_return);

	add_post_meta($post_id, "wpcf-educator-eventbrite-code-url", $eb_public_url . $eb_discountcode);

	$eb_accesscode = "ed_free_" . $ed_email;
	$eb_accesscode_url = 'https://www.eventbriteapi.com/v3/events/'. $event . '/access_codes/?token=' . $eb_token;
	$eb_accesscode_data = array ("access_code.code" => $eb_accesscode,
	  			     "access_code.ticket_ids" => $access_tickets,
			     	     "access_code.quantity_available" => '1',
			     	    );

	$ac_return = mfo_call_api("POST", $eb_accesscode_url, $eb_accesscode_data);
	mfo_log (4, 'eventbrite', "create_code - access" . " : ". $ac_return);
	add_post_meta($post_id, "wpcf-educator-eventbrite-code-url", $eb_public_url . $eb_accesscode);

	wp_publish_post($post_id);


	$headers[] = 'From: Maker Faire Orlando <educators@makerfaireorlando.com>';
	$headers[] = 'Bcc: producers@makerfaireorlando.com';
	$msg = "Your Maker Faire Orlando Educator ticket codes are below.\r\n\r\n" . 
		"These tickets are made possible by the generosity of Vistana Signature Experiences (www.vistana.com)\r\n\r\n" .
		'Please click the links to obtain each ticket from Eventbrite. (Once you are on the Eventbrite site, click the green "TICKETS" button' . 
		"Note that they will need to be transacted separately. You may give the 50% off code to a friend.\r\n\r\n" .
		"Free Ticket: http://makerfaireorlando.eventbrite.com?discount=" . $eb_accesscode . "\r\n\r\n" .
		"50% off discount code (good for 1 discounted ticket): http://makerfaireorlando.eventbrite.com?discount=" . $eb_discountcode . "\r\n\r\n" .
		"\r\n" .
		"Please tell other educators about this program (http://www.makerfaireorlando.com/educator-tickets/)," .
		" and we greatly appreciate your social media posts and mentions of Maker Faire Orlando.\r\n"

		;

	wp_mail($ed_email, "Your Maker Faire Orlando Educator tickets", $msg, $headers);

	echo 1;

	die(); //this prevents admin-ajax from also returning a zero :)
}
?>
