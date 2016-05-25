<?php 

/*
Helper functions for Slack integrations

todo: 
*/



/**
 * Post a message to Slack from WordPress
 * From: http://aaronrutley.com/send-notifications-from-wordpress-to-slack/
 *
 * NOTE: THIS MUST BE RATE LIMITED!
 * @param string $message the message to be sent to Slack
 * @param string $channel the #channel to send the message to (or @user for a DM)
 * @param string $username the username for this bot eg : WordPress bot
 * @param string $icon_emoji the icon emoji name for this bot eg :monkey: 
 * 
 * @link slack incoming webhook docs https://api.slack.com/incoming-webhooks
 * @example ar_post_to_slack('message','#channel','bot-name',':monkey:');
 */
function mfo_post_to_slack($message, $channel, $username, $icon_emoji) {
	
	// Slack webhook endpoint from Slack settings
	$options = get_option('mfo_options_modules');
	//$options[mfo_edit_makers_enabled_boolean]
	$slack_endpoint = $options['mfo_slack_webhook_url_string'];
	
	// Prepare the data / payload to be posted to Slack
	$data = array(
		'payload'   => json_encode( array(
			"channel"       =>  $channel,
			"text"          =>  $message,
			"username"	=>  $username,
			"icon_emoji"    =>  $icon_emoji
			)
		)
	);
	// Post our data via the slack webhook endpoint using wp_remote_post
	$posting_to_slack = wp_remote_post( $slack_endpoint, array(
		'method' => 'POST',
		'timeout' => 30,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array(),
		'body' => $data,
		'cookies' => array()
		)
	);
	mfo_log(4, "mfo_post_to_slack", $message . "---" . !is_wp_error($posting_to_slack));
}

?>
