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



function mfo_init(){
	/* hook for creating webhook endpoints */
	mfo_log(4, 'eventbrite','mfo_init();');
	add_action( 'init', 'mfo_endpoint' );
	add_action( 'parse_request', 'mfo_parse_request' );
}


function mfo_endpoint(){
	// access webhook at url such as http://[your site]/mailchimp/webhook
	mfo_log(4, 'eventbrite', 'mfo_endpoint();');
    	add_rewrite_rule( 'webhook' , 'index.php?webhook=1', 'top' );
    	add_rewrite_tag( '%webhook%' , '([^&]+)' );
}

function mfo_parse_request( $wp )
{
	mfo_log(4, 'eventbrite', 'mfo_parse_request();');
	mfo_log(4, 'eventbrite', 'query_vars: '.print_r($wp->query_vars, true));

	//note, the line below was commented to remove debug notice warnings
	//if($wp->query_vars['webhook']) {

	//and trying this line, but todo: need to retest with eventbrite to make sure it works
	if (get_query_var('webhook')){

	//if('webhook'== $wp->query_vars['pagename']) {
    	//if ( array_key_exists( 'webhook', $wp->query_vars ) ) {
       		mfo_action_webhook();
        	exit();
    	}
}

function mfo_action_webhook() {
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
		mfo_eb_order_placed($data);
		}

	echo "Webhook received.";
	mfo_log(3,'eventbrite', 'Finished processing request.');
}

function mfo_eb_order_placed ($data) {
	//todo: add setting for token

	$options = get_option('mfo_options');
	$token = $options['mfo_eventbrite_token_string'];

	mfo_log(3, 'eventbrite',"mfo_eb_order_placed();");

	mfo_log(3, 'eventbrite',"data->api_url: ".print_r($data->api_url, true));
	$url_order = $data->api_url.'?token='.$token.'&expand=attendees,event';
	mfo_log("order url: ".$url_order);
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
	update_post_meta($post_id, 'wpcf-eventbrite-order-debug', $debug_content);



}

mfo_init();

?>
