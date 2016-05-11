<?php

/*
Now part of mfo-base plugin


todo: TEST the switch away from form id below
*/



defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//don't let woocommerce take over the lost password page
function reset_pass_url() {
    $siteURL = get_option('siteurl');
    return "{$siteURL}/makerlogin/?action=lostpassword";
}
add_filter( 'lostpassword_url',  'reset_pass_url', 11, 0 );


//connect FeePayment to exhibit (not working in CRED form)
add_action('cred_save_data', 'my_save_data_action',10,2);
function my_save_data_action($post_id, $form_data)
{
    //if a specific form
    //if ($form_data['id']==4837)
    if ($form_data['post_type'] == "fee-payment" && $form_data['form_type'] == "add") { //Fee Payment - Add
	$debug = $debug.current_time('mysql')."\r\n";
	$debug = $debug."post_id: ".$post_id.": ".get_post_field( 'post_title', $post_id)."\r\n";
	$debug = $debug."post_title: ".$post_title."\r\n";
	$debug = $debug."maker: ".$maker."\r\n";
	$debug = $debug."exhibit: ".$exhibit."\r\n";
	/* so many tries...
	$debug = $debug."parent_id: ".$parent_id."\r\n";
	$debug = $debug."parent_id: ".$_POST['_wpcf_belongs_exhibit_id']."\r\n";
	$debug = $debug."parent_id: ".$_POST['parent_parent_id']."\r\n";
	*/
	$exhibit_id = $_GET['parent_exhibit_id'];
	$exhibit_title = get_the_title($exhibit_id);
	$debug = $debug."exhibit_id: ".$exhibit_id."\r\n";
	$debug = $debug."exhibit_title: ".$exhibit_title."\r\n";
	$post_title = $exhibit_title;

	//set the title of the FeePayment post
	wp_update_post(array('ID'=>$post_id, 'post_title'=>$post_title, 'post_name' => $slug));
	update_post_meta($post_id, 'wpcf-fee-payment-debug', $debug);
    }
}

add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );


//from: http://www.wpmayor.com/how-to-remove-the-billing-details-from-woocommerce-checkout/
function custom_override_checkout_fields( $fields ) {
    unset($fields['billing']['billing_first_name']);
    unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_phone']);
    unset($fields['order']['order_comments']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_email']);
    unset($fields['billing']['billing_city']);
    return $fields;
}

/**
 * Change text strings
 *
 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/gettext
 */
function my_text_strings( $translated_text, $text, $domain ) {
	switch ( $translated_text ) {
		case 'Billing Details' :
			$translated_text = __( '', 'woocommerce' );
			break;
		case 'Additional Information' :
			$translated_text = __( '', 'woocommerce' );
			break;
		case 'Your order' :
			$translated_text = __( '', 'woocommerce' );
			break;
		case 'Product' :
			$translated_text = __( '', 'woocommerce' );
			break;

	}
	return $translated_text;
}
add_filter( 'gettext', 'my_text_strings', 20, 3 );

?>
