<?php 

/*
Helper functions for Slack integrations

todo: 
*/




function mfo_create_meme ($template, $top_text, $bottom_text) {

        $options = get_option('mfo_options_modules');

        $imgflip_username = $options['mfo_imgflip_username_string'];
        $imgflip_password = $options['mfo_imgflip_password_string'];


	$imgflip_url = "https://api.imgflip.com/caption_image";


	$imgflip_data = array (	"template_id" 	=> $template,
				"username" 	=> $imgflip_username,
				"password"	=> $imgflip_password,
				"text0" 	=> $top_text,
				"text1"		=> $bottom_text, 
				);

	$imgflip_json = mfo_call_api("POST", $imgflip_url, $imgflip_data);
	//do not log, will contain username and password
	//mfo_log (4, "mfo_create_meme", print_r($imgflip_data, true));
	mfo_log (4, "mfo_create_meme", print_r($imgflip_json, true));

	$json_array = json_decode($imgflip_json, true);
	$image_url = $json_array['data']['url'];
	mfo_log (4, "mfo_create_meme", $image_url);

	return $image_url;
}

//from: http://stackoverflow.com/questions/9802788/call-a-rest-api-in-php
function mfo_call_api($method, $url, $data = false)
{
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}

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
function mfo_post_to_slack($message, $channel, $username, $icon_emoji, $attachments) {
	
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
			"icon_emoji"    =>  $icon_emoji,
			"attachments"   =>  $attachments,
			)
		)
	);
	mfo_log(4, "mfo_post_to_slack", print_r($data, true));

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
