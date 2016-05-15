<?php

/*
Settings and shortcodes that output settings
*/


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*

General todo:
UPDATE MAIN TODO


Changelog:
UPDATE MAIN CHANGELOG
*/




function mfo_event_name() {
 $options = get_option('mfo_options_main');
 return $options['mfo_event_name_string'];
}
add_shortcode('mfo-event-name', 'mfo_event_name');

function mfo_event_year() {
 $options = get_option('mfo_options_main');
 return $options['mfo_event_year_string'];
}
add_shortcode('mfo-event-year', 'mfo_event_year');


function mfo_support_email() {

 $options = get_option('mfo_options_main');
 return $options['mfo_support_email_string'];
 //return "makers@makerfaireorlando.com";
}
add_shortcode('mfo-support-email', 'mfo_support_email');

function mfo_support_email_link() {

$options = get_option('mfo_options_main');
$link ="<a href=\"mailto:" . $options['mfo_support_email_string'] . "\">" . $options['mfo_support_email_string'] . "</a>";
return $link;
//return "<a href=\"mailto:makers@makerfaireorlando.com\">makers@makerfaireorlando.com</a>";
}
add_shortcode('mfo-support-email-link', 'mfo_support_email_link');

function mfo_notification_email() {

 $options = get_option('mfo_options_main');
 return $options[mfo_notification_email_string];
}
add_shortcode('mfo-notification-email', 'mfo_notification_email');

function mfo_notification_email_link() {

$options = get_option('mfo_options_main');
$link ="<a href=\"mailto:" . $options[mfo_notification_email_string] . "\">" . $options[mfo_notification_email_string] . "</a>";
return $link;
}
add_shortcode('mfo-notification-email-link', 'mfo_notification_email_link');

function mfo_agreements_enabled() {
	$options = get_option('mfo_options_features');
 	return $options[mfo_agreements_enabled_boolean];
}
add_shortcode('mfo-agreements-enabled', 'mfo_agreements_enabled');

function mfo_exhibithelpers_enabled() {
	$options = get_option('mfo_options_features');
 	return $options[mfo_exhibithelpers_enabled_boolean];
}
add_shortcode('mfo-exhibithelpers-enabled', 'mfo_exhibithelpers_enabled');

function mfo_exhibithelpers_default() {
	$options = get_option('mfo_options_features');
 	return $options[mfo_exhibithelpers_default_number];
}
add_shortcode('mfo-exhibithelpers-default', 'mfo_exhibithelpers_default');

function mfo_orientationrsvp_enabled() {
	$options = get_option('mfo_options_features');
 	return $options[mfo_orientationrsvp_enabled_boolean];
}
add_shortcode('mfo-orientationrsvp-enabled', 'mfo_orientationrsvp_enabled');

function mfo_loadin_enabled() {
	$options = get_option('mfo_options_features');
 	return $options[mfo_loadin_enabled_boolean];
}
add_shortcode('mfo-loadin-enabled', 'mfo_loadin_enabled');

function mfo_tablesigns_enabled() {
	$options = get_option('mfo_options_features');
 	return $options[mfo_tablesigns_enabled_boolean];
}
add_shortcode('mfo-tablesigns-enabled', 'mfo_tablesigns_enabled');


function mfo_exhibit_location_enabled() {
	$options = get_option('mfo_options_display');
 	return $options[mfo_exhibit_location_enabled_boolean];
}
add_shortcode('mfo-exhibit-location-enabled', 'mfo_exhibit_location_enabled');

function mfo_maker_badges_enabled() {
	$options = get_option('mfo_options_display');
 	return $options[mfo_maker_badges_enabled_boolean];
}
add_shortcode('mfo-maker-badges-enabled', 'mfo_maker_badges_enabled');

function mfo_maker_color() {
	$options = get_option('mfo_options_display');
 	return $options[mfo_maker_color_string];
}
add_shortcode('mfo-maker-color', 'mfo_maker_color');

function mfo_exhibit_color() {
	$options = get_option('mfo_options_display');
 	return $options[mfo_exhibit_color_string];
}
add_shortcode('mfo-exhibit-color', 'mfo_exhibit_color');

function mfo_exhibit_prioryear_color() {
	$options = get_option('mfo_options_display');
 	return $options[mfo_exhibit_prioryear_color_string];
}
add_shortcode('mfo-exhibit-prioryear-color', 'mfo_exhibit_prioryear_color');




/* SETTINGS
 *
 *
 */


//Create Settings menu option
//from https://codex.wordpress.org/Adding_Administration_Menus

/** Step 2 (from text above). */
add_action( 'admin_menu', 'mfo_plugin_menu' );

/** Step 1. */
function mfo_plugin_menu() {
	add_options_page( 'MFO Options', 'Maker Faire Online', 'manage_options', 'mfo-options-page', 'mfo_plugin_options' );
}

/** Step 3. */
/*
function mfo_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>

	<div class="wrap">
	<h1>Maker Faire Online System Options</h1>
	<form action="options.php" method="post">
	<?php settings_fields("mfo_options"); ?>
	<?php do_settings_sections("mfo"); ?>
	<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />

	</form></div>
	<?php
}
*/


function mfo_plugin_options() {
?>
    <!-- Create a header in the default WordPress 'wrap' container -->
    <div class="wrap">
        <h2>Maker Faire Online (MFO) Options</h2>
        <div id="icon-themes" class="icon32"></div>
        <?php
            if( isset( $_GET[ 'tab' ] ) ) {
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'display_options';
            } // end if
        ?>

        <h2 class="nav-tab-wrapper">
            <a href="?page=mfo-options-page&tab=main_options" class="nav-tab <?php echo $active_tab == 'main_options' ? 'nav-tab-active' : ''; ?>">Main Options</a>
            <a href="?page=mfo-options-page&tab=display_options" class="nav-tab <?php echo $active_tab == 'display_options' ? 'nav-tab-active' : ''; ?>">Display Options</a>
            <a href="?page=mfo-options-page&tab=feature_options" class="nav-tab <?php echo $active_tab == 'feature_options' ? 'nav-tab-active' : ''; ?>">Feature Options</a>
            <a href="?page=mfo-options-page&tab=module_options" class="nav-tab <?php echo $active_tab == 'module_options' ? 'nav-tab-active' : ''; ?>">Module Options</a>
            <a href="?page=mfo-options-page&tab=debug_options" class="nav-tab <?php echo $active_tab == 'debug_options' ? 'nav-tab-active' : ''; ?>">Debug Options</a>
        </h2>

	<form method="post" action="options.php">
    	<?php

        	if( $active_tab == 'display_options' ) {
            		settings_fields( 'mfo_options_display' );
            		do_settings_sections( 'mfo_display_tab' );
		} else if ( $active_tab == 'feature_options' ) {
            		settings_fields( 'mfo_options_features' );
            		do_settings_sections( 'mfo_features_tab' );
		} else if ( $active_tab == 'module_options' ) {
            		settings_fields( 'mfo_options_modules' );
            		do_settings_sections( 'mfo_module_tab' );
		} else if ( $active_tab == 'debug_options' ) {
            		settings_fields( 'mfo_options_debug' );
            		do_settings_sections( 'mfo_debug_tab' );
        	} else {
            		settings_fields( 'mfo_options_main' );
            		do_settings_sections( 'mfo_main_tab' );
        	} // end if/else

        submit_button();
    ?>
</form>
    </div><!-- /.wrap -->
<?php
} 


// add the admin settings and such
add_action('admin_init', 'mfo_admin_init');

function mfo_admin_init(){
register_setting( 'mfo_options_main', 'mfo_options_main', 'mfo_options_main_validate' );
register_setting( 'mfo_options_display', 'mfo_options_display', 'mfo_options_display_validate' );
register_setting( 'mfo_options_features', 'mfo_options_features', 'mfo_options_features_validate' );
register_setting( 'mfo_options_modules', 'mfo_options_modules', 'mfo_options_modules_validate' );
register_setting( 'mfo_options_debug', 'mfo_options_debug', 'mfo_options_debug_validate' );

//creates a settings section
add_settings_section('mfo_main', 'MFO Main Settings', 'mfo_main_section_text', 'mfo_main_tab');
add_settings_section('mfo_features', 'MFO Feature Settings', 'mfo_features_section_text', 'mfo_features_tab');
add_settings_section('mfo_display', 'MFO Display Settings', 'mfo_display_section_text', 'mfo_display_tab');
add_settings_section('mfo_modules', 'MFO Module Settings', 'mfo_modules_section_text', 'mfo_module_tab');
add_settings_section('mfo_eventbrite', 'MFO Eventbrite Settings', 'mfo_eventbrite_section_text', 'mfo_module_tab');
add_settings_section('mfo_debug', 'MFO Debug Settings', 'mfo_debug_section_text', 'mfo_debug_tab');


//adds a field to a settings section
//main
add_settings_field('mfo_event_name_string', 'Event Name', 'mfo_event_name_setting_string', 'mfo_main_tab', 'mfo_main');
add_settings_field('mfo_support_email_string', 'Support Email Address', 'mfo_support_email_setting_string', 'mfo_main_tab', 'mfo_main');
add_settings_field('mfo_notification_email_string', 'Notification Email Address', 'mfo_notification_email_setting_string', 'mfo_main_tab', 'mfo_main');
add_settings_field('mfo_event_year_string', 'Event Year', 'mfo_event_year_setting_string', 'mfo_main_tab', 'mfo_main');

//debug
add_settings_field('mfo_log_enabled_boolean', 'Logging Enabled?', 'mfo_log_enabled_setting_boolean', 'mfo_debug_tab', 'mfo_debug');
add_settings_field('mfo_log_level_number', 'Logging Level', 'mfo_log_level_setting_number', 'mfo_debug_tab', 'mfo_debug');
add_settings_field('mfo_warning_email_string', 'System Warning Email Address', 'mfo_warning_email_setting_string', 'mfo_debug_tab', 'mfo_debug');


//modules
add_settings_field('mfo_module_eventbrite_enabled_boolean', 'Eventbrite Integration Enabled?', 'mfo_module_eventbrite_enabled_setting_boolean', 'mfo_module_tab', 'mfo_modules');
add_settings_field('mfo_module_sensei_enabled_boolean', 'Sensei Integration Enabled?', 'mfo_module_sensei_enabled_setting_boolean', 'mfo_module_tab', 'mfo_modules');
add_settings_field('mfo_module_woocommerce_enabled_boolean', 'WooCommerce Integration Enabled?', 'mfo_module_woocommerce_enabled_setting_boolean', 'mfo_module_tab', 'mfo_modules');
add_settings_field('mfo_eventbrite_token_string', 'Eventbrite API Token', 'mfo_eventbrite_token_setting_string', 'mfo_module_tab', 'mfo_eventbrite');

//features
add_settings_field('mfo_agreements_enabled_boolean', 'Maker Agreements Enabled?', 'mfo_agreements_enabled_setting_boolean', 'mfo_features_tab', 'mfo_features');
add_settings_field('mfo_orientationrsvp_enabled_boolean', 'Maker Orientation RSVP Enabled?', 'mfo_orientationrsvp_enabled_setting_boolean', 'mfo_features_tab', 'mfo_features');
add_settings_field('mfo_feepayments_enabled_boolean', 'Exhibit Seller Fees Enabled?', 'mfo_feepayments_enabled_setting_boolean', 'mfo_features_tab', 'mfo_features');
add_settings_field('mfo_exhibithelpers_enabled_boolean', 'Exhibit Helper Entry Enabled?', 'mfo_exhibithelpers_enabled_setting_boolean', 'mfo_features_tab', 'mfo_features');
add_settings_field('mfo_exhibithelpers_default_number', 'Exhibit Helper Default Quantity?', 'mfo_exhibithelpers_default_setting_number', 'mfo_features_tab', 'mfo_features');
add_settings_field('mfo_loadin_enabled_boolean', 'Exhibit Load-In Selection Enabled?', 'mfo_loadin_enabled_setting_boolean', 'mfo_features_tab', 'mfo_features');
add_settings_field('mfo_tablesigns_enabled_boolean', 'Exhibit Table Sign Preview Enabled?', 'mfo_tablesigns_enabled_setting_boolean', 'mfo_features_tab', 'mfo_features');


//display
add_settings_field('mfo_exhibit_location_enabled_boolean', 'Show Exhibit Location?', 'mfo_exhibit_location_enabled_setting_boolean', 'mfo_display_tab', 'mfo_display');
add_settings_field('mfo_maker_badges_enabled_boolean', 'Show Maker Badges?', 'mfo_maker_badges_enabled_setting_boolean', 'mfo_display_tab', 'mfo_display');
add_settings_field('mfo_maker_color_string', 'Maker Color', 'mfo_maker_color_setting_string', 'mfo_display_tab', 'mfo_display');
add_settings_field('mfo_exhibit_color_string', 'Exhibit Color', 'mfo_exhibit_color_setting_string', 'mfo_display_tab', 'mfo_display');
add_settings_field('mfo_exhibit_prioryear_color_string', 'Exhibit(Prior Years) Color', 'mfo_exhibit_prioryear_color_setting_string', 'mfo_display_tab', 'mfo_display');


}



//outputs the header for a settings section
function mfo_main_section_text() {
echo '<p>Shortcode Settings for the MFO System</p>';
}

function mfo_debug_section_text() {
echo '<p>Debug Settings for the MFO System</p>';
}

function mfo_modules_section_text() {
echo '<p>Module Settings for the MFO System</p>';
}

function mfo_eventbrite_section_text() {
echo '<p>Eventbrite Settings for the MFO System</p>';
}

function mfo_features_section_text() {
echo '<p>Enable / Disable Features</p>';
}

function mfo_display_section_text() {
echo '<p>Enable / Disable the display of specific items </p>';
}


//outputs the field for a setting
function mfo_event_name_setting_string() {
	$options = get_option('mfo_options_main');
	echo "<input id='mfo_event_name_string' name='mfo_options_main[mfo_event_name_string]' size='60' type='text' value='{$options['mfo_event_name_string']}' />";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-event-name]</b> for use in posts / pages / views / forms, etc.";
}

function mfo_support_email_setting_string() {
	$options = get_option('mfo_options_main');
	echo "<input id='mfo_support_email_string' name='mfo_options_main[mfo_support_email_string]' size='60' type='text' value='{$options['mfo_support_email_string']}' />";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-support-email]</b> for use in posts / pages / views / forms, etc.";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-support-email-link]</b> for use in posts / pages / views / forms, etc.";
}

function mfo_notification_email_setting_string() {
	$options = get_option('mfo_options_main');
	echo "<input id='mfo_notification_email_string' name='mfo_options_main[mfo_notification_email_string]' size='60' type='text' value='{$options['mfo_notification_email_string']}' />";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-notification-email]</b> for use in posts / pages / views / forms, etc.";
	echo '<br>&nbsp&nbspCRED form notification email address: "<b>notification-email</b>" without quotes.';
}

function mfo_event_year_setting_string() {
	$options = get_option('mfo_options_main');
	echo "<input id='mfo_event_year_string' name='mfo_options_main[mfo_event_year_string]' size='4' type='text' value='{$options['mfo_event_year_string']}' />";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-event-year]</b> for use in posts / pages / views / forms, etc.";
}

function mfo_log_enabled_setting_boolean() {
	$options = get_option('mfo_options_debug');
	$html = '<input type="checkbox" id="mfo_log_enabled_boolean" name="mfo_options_debug[mfo_log_enabled_boolean]" value="1"' . checked( 1, $options['mfo_log_enabled_boolean'], false ) . '/>';
	echo $html;
}

function mfo_log_level_setting_number() {
	$options = get_option('mfo_options_debug');

	$html = '<select id="mfo_log_level_number" name="mfo_options_debug[mfo_log_level_number]">';
        $html .= '<option value="0">Select a Log Level...</option>';
        $html .= '<option value="1"' . selected( $options['mfo_log_level_number'], '1', false) . '>1: Critical Only</option>';
        $html .= '<option value="2"' . selected( $options['mfo_log_level_number'], '2', false) . '>2: CPT Changes / Stats</option>';
        $html .= '<option value="3"' . selected( $options['mfo_log_level_number'], '3', false) . '>3: Lots of stuff</option>';
        $html .= '<option value="4"' . selected( $options['mfo_log_level_number'], '4', false) . '>4: Bring the noise!</option>';
    	$html .= '</select>';
    	echo $html;
}

function mfo_warning_email_setting_string() {
$options = get_option('mfo_options_debug');
echo "<input id='mfo_warning_email_string' name='mfo_options_debug[mfo_warning_email_string]' size='60' type='text' value='{$options['mfo_warning_email_string']}' />";
}

function mfo_module_eventbrite_enabled_setting_boolean() {
	$options = get_option('mfo_options_modules');
	$html = '<input type="checkbox" id="mfo_module_eventbrite_enabled_boolean" name="mfo_options_modules[mfo_module_eventbrite_enabled_boolean]" value="1"' . checked( 1, $options['mfo_module_eventbrite_enabled_boolean'], false ) . '/>';
	echo $html;
}

function mfo_module_sensei_enabled_setting_boolean() {
	$options = get_option('mfo_options_modules');
	$html = '<input type="checkbox" id="mfo_module_sensei_enabled_boolean" name="mfo_options_modules[mfo_module_sensei_enabled_boolean]" value="1"' . checked( 1, $options['mfo_module_sensei_enabled_boolean'], false ) . '/>';
	echo $html;
}

function mfo_module_woocommerce_enabled_setting_boolean() {
	$options = get_option('mfo_options_modules');
	$html = '<input type="checkbox" id="mfo_module_woocommerce_enabled_boolean" name="mfo_options_modules[mfo_module_woocommerce_enabled_boolean]" value="1"' . checked( 1, $options['mfo_module_woocommerce_enabled_boolean'], false ) . '/>';
	echo $html;
}

function mfo_eventbrite_token_setting_string() {
$options = get_option('mfo_options_modules');
echo "<input id='mfo_eventbrite_token_string' name='mfo_options_modules[mfo_eventbrite_token_string]' size='30' type='text' value='{$options['mfo_eventbrite_token_string']}' />";
}


function mfo_feepayments_enabled_setting_boolean() {
	$options = get_option('mfo_options_features');
	$html = '<input type="checkbox" id="mfo_feepayments_enabled_boolean" name="mfo_options_features[mfo_feepayments_enabled_boolean]" value="1"' . checked( 1, $options['mfo_feepayments_enabled_boolean'], false ) . '/>';
	echo $html;
	echo "<br>&nbsp&nbspThis feature uses WooCommerce (you will need to purchase and configure) to allow a user to pay Seller Fees for an APPROVED EXHIBIT.";
	echo "<br>&nbsp&nbspThis checkbox controls the display of the option on the Maker Dashboard for an APPROVED EXHIBIT.";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-feepayments-enabled]</b> for use in Toolset Views conditional logic (make sure Toolset Settings include this shortcode).";
}

function mfo_agreements_enabled_setting_boolean() {
	$options = get_option('mfo_options_features');
	$html = '<input type="checkbox" id="mfo_agreements_enabled_boolean" name="mfo_options_features[mfo_agreements_enabled_boolean]" value="1"' . checked( 1, $options['mfo_agreements_enabled_boolean'], false ) . '/>';
	echo $html;
	echo "<br>&nbsp&nbspThis feature enables a user to acknowledge a Maker Agreement.";
	echo "<br>&nbsp&nbspThis checkbox controls the display of the option on the Maker Dashboard for an APPROVED EXHIBIT.";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-agreements-enabled]</b> for use in Toolset Views conditional logic (make sure Toolset Settings include this shortcode).";
}

function mfo_exhibithelpers_enabled_setting_boolean() {
	$options = get_option('mfo_options_features');
	$html = '<input type="checkbox" id="mfo_exhibithelpers_enabled_boolean" name="mfo_options_features[mfo_exhibithelpers_enabled_boolean]" value="1"' . checked( 1, $options['mfo_exhibithelpers_enabled_boolean'], false ) . '/>';
	echo $html;
	echo "<br>&nbsp&nbspThis feature enables a user to enter Exhibit Helpers for event credentials (1 maker + X helpers, with the default set below).";
	echo "<br>&nbsp&nbspThis checkbox controls the display of the option on the Maker Dashboard for an APPROVED EXHIBIT.";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-exhibithelpers-enabled]</b> for use in Toolset Views conditional logic (make sure Toolset Settings include this shortcode).";
}

function mfo_exhibithelpers_default_setting_number() {
	$options = get_option('mfo_options_features');
	echo "<input id='mfo_exhibithelpers_default_number' name='mfo_options_features[mfo_exhibithelpers_default_number]' size='4' type='text' value='{$options['mfo_exhibithelpers_default_number']}' />";
	echo "<br>&nbsp&nbspDefault number of Exhibit Helpers (for event credentials) in addition to one set of credentials for the Maker.";
	echo "<br>&nbsp&nbspThe number of approved Exhibit Helpers can be set per exhibit by editing the exhibit in the Wordpress backend.";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-exhibithelpers-default]</b> for use in Toolset Views conditional logic (make sure Toolset Settings include this shortcode).";
}


function mfo_orientationrsvp_enabled_setting_boolean() {
	$options = get_option('mfo_options_features');
	$html = '<input type="checkbox" id="mfo_orientationrsvp_enabled_boolean" name="mfo_options_features[mfo_orientationrsvp_enabled_boolean]" value="1"' . checked( 1, $options['mfo_orientationrsvp_enabled_boolean'], false ) . '/>';
	echo $html;
	echo "<br>&nbsp&nbspThis feature enables a user to RSVP for a Maker Orientation.";
	echo "<br>&nbsp&nbspThis checkbox controls the display of the option on the Maker Dashboard for an APPROVED EXHIBIT.";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-orientationrsvp-enabled]</b> for use in Toolset Views conditional logic (make sure Toolset Settings include this shortcode.";

}

function mfo_loadin_enabled_setting_boolean() {
	$options = get_option('mfo_options_features');
	$html = '<input type="checkbox" id="mfo_loadin_enabled_boolean" name="mfo_options_features[mfo_loadin_enabled_boolean]" value="1"' . checked( 1, $options['mfo_loadin_enabled_boolean'], false ) . '/>';
	echo $html;
	echo "<br>&nbsp&nbspThis feature enables a user to select a Load-In Location and Time Slot for an APPROVED EXHIBIT.";
	echo "<br>&nbsp&nbspThis checkbox controls the display of the option on the Maker Dashboard for an APPROVED EXHIBIT.";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-loadin-enabled]</b> for use in Toolset Views conditional logic (make sure Toolset Settings include this shortcode.";
}

function mfo_tablesigns_enabled_setting_boolean() {
	$options = get_option('mfo_options_features');
	$html = '<input type="checkbox" id="mfo_tablesigns_enabled_boolean" name="mfo_options_features[mfo_tablesigns_enabled_boolean]" value="1"' . checked( 1, $options['mfo_tablesigns_enabled_boolean'], false ) . '/>';
	echo $html;
	echo "<br>&nbsp&nbspThis feature enables a user to preview the tablesign for their exhibit..";
	echo "<br>&nbsp&nbspThis checkbox controls the display of the tablesign preview link on the Maker Dashboard for an APPROVED EXHIBIT.";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-tablesigns-enabled]</b> for use in Toolset Views conditional logic (make sure Toolset Settings include this shortcode).";
}


function mfo_exhibit_location_enabled_setting_boolean() {
	$options = get_option('mfo_options_display');
	$html = '<input type="checkbox" id="mfo_exhibit_location_enabled_boolean" name="mfo_options_display[mfo_exhibit_location_enabled_boolean]" value="1"' . checked( 1, $options['mfo_exhibit_location_enabled_boolean'], false ) . '/>';
	echo $html;
	echo "<br>&nbsp&nbspThis feature enables the display of the Exhibit location on the Maker Dashboard, the public exhibit page, etc..";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-exhibit-location-enabled]</b> for use in Toolset Views conditional logic (make sure Toolset Settings include this shortcode.";
}

function mfo_maker_badges_enabled_setting_boolean() {
	$options = get_option('mfo_options_display');
	$html = '<input type="checkbox" id="mfo_maker_badges_enabled_boolean" name="mfo_options_display[mfo_maker_badges_enabled_boolean]" value="1"' . checked( 1, $options['mfo_maker_badges_enabled_boolean'], false ) . '/>';
	echo $html;
	echo "<br>&nbsp&nbspThis feature enables the display of Maker Badges on the the public Maker and Exhibit pages.";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-maker-badges-enabled]</b> for use in Toolset Views conditional logic (make sure Toolset Settings include this shortcode.";
}

function mfo_maker_color_setting_string() {
	$options = get_option('mfo_options_display');
	echo "<input id='mfo_maker_color_string' name='mfo_options_display[mfo_maker_color_string]' size='60' type='text' value='{$options['mfo_maker_color_string']}' />";
	echo "<br>&nbsp&nbspExample: #00AEEF";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-maker-color]</b> for use in posts / pages / views / forms, etc.";
}

function mfo_exhibit_color_setting_string() {
	$options = get_option('mfo_options_display');
	echo "<input id='mfo_exhibit_color_string' name='mfo_options_display[mfo_exhibit_color_string]' size='60' type='text' value='{$options['mfo_exhibit_color_string']}' />";
	echo "<br>&nbsp&nbspExample: #B1CC51";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-exhibit-color]</b> for use in posts / pages / views / forms, etc.";
}

function mfo_exhibit_prioryear_color_setting_string() {
	$options = get_option('mfo_options_display');
	echo "<input id='mfo_exhibit_prioryear_color_string' name='mfo_options_display[mfo_exhibit_prioryear_color_string]' size='60' type='text' value='{$options['mfo_exhibit_prioryear_color_string']}' />";
	echo "<br>&nbsp&nbspShortcode: <b>[mfo-exhibit-prioryear-color]</b> for use in posts / pages / views / forms, etc.";
}




//todo: actual validation
function mfo_options_main_validate($input) {
	$newinput['mfo_event_name_string'] = trim($input['mfo_event_name_string']);
	$newinput['mfo_support_email_string'] = trim($input['mfo_support_email_string']);
	$newinput['mfo_notification_email_string'] = trim($input['mfo_notification_email_string']);
	$newinput['mfo_event_year_string'] = trim($input['mfo_event_year_string']);
	mfo_log(1, "MFO Settings" , "mfo_options_main changed");
	return $newinput;
}

function mfo_options_debug_validate($input) {
	$newinput['mfo_log_enabled_boolean'] = ( isset( $input['mfo_log_enabled_boolean'] ) && true == $input['mfo_log_enabled_boolean'] ? true : false );
	$newinput['mfo_log_level_number'] = $input['mfo_log_level_number'];
	$newinput['mfo_warning_email_string'] = trim($input['mfo_warning_email_string']);
	mfo_log(1, "MFO Settings" , "mfo_options_debug changed");
	return $newinput;
}

function mfo_options_modules_validate($input) {
	$newinput['mfo_module_eventbrite_enabled_boolean'] = ( isset( $input['mfo_module_eventbrite_enabled_boolean'] ) && true == $input['mfo_module_eventbrite_enabled_boolean'] ? true : false );
	$newinput['mfo_module_sensei_enabled_boolean'] = ( isset( $input['mfo_module_sensei_enabled_boolean'] ) && true == $input['mfo_module_sensei_enabled_boolean'] ? true : false );
	$newinput['mfo_module_woocommerce_enabled_boolean'] = ( isset( $input['mfo_module_woocommerce_enabled_boolean'] ) && true == $input['mfo_module_woocommerce_enabled_boolean'] ? true : false );
	$newinput['mfo_eventbrite_token_string'] = trim($input['mfo_eventbrite_token_string']);
	mfo_log(1, "MFO Settings" , "mfo_options_modules changed");
	return $newinput;
}

function mfo_options_features_validate($input) {
	$newinput['mfo_feepayments_enabled_boolean'] = ( isset( $input['mfo_feepayments_enabled_boolean'] ) && true == $input['mfo_feepayments_enabled_boolean'] ? true : false );
	$newinput['mfo_agreements_enabled_boolean'] = ( isset( $input['mfo_agreements_enabled_boolean'] ) && true == $input['mfo_agreements_enabled_boolean'] ? true : false );
	$newinput['mfo_exhibithelpers_enabled_boolean'] = ( isset( $input['mfo_exhibithelpers_enabled_boolean'] ) && true == $input['mfo_exhibithelpers_enabled_boolean'] ? true : false );
	$newinput['mfo_exhibithelpers_default_number'] = trim($input['mfo_exhibithelpers_default_number']);
	$newinput['mfo_orientationrsvp_enabled_boolean'] = ( isset( $input['mfo_orientationrsvp_enabled_boolean'] ) && true == $input['mfo_orientationrsvp_enabled_boolean'] ? true : false );
	$newinput['mfo_loadin_enabled_boolean'] = ( isset( $input['mfo_loadin_enabled_boolean'] ) && true == $input['mfo_loadin_enabled_boolean'] ? true : false );
	$newinput['mfo_tablesigns_enabled_boolean'] = ( isset( $input['mfo_tablesigns_enabled_boolean'] ) && true == $input['mfo_tablesigns_enabled_boolean'] ? true : false );
	mfo_log(1, "MFO Settings" , "mfo_options_features changed");
	return $newinput;
}

function mfo_options_display_validate($input) {

	$newinput['mfo_exhibit_location_enabled_boolean'] = ( isset( $input['mfo_exhibit_location_enabled_boolean'] ) && true == $input['mfo_exhibit_location_enabled_boolean'] ? true : false );
	$newinput['mfo_maker_badges_enabled_boolean'] = ( isset( $input['mfo_maker_badges_enabled_boolean'] ) && true == $input['mfo_maker_badges_enabled_boolean'] ? true : false );
	$newinput['mfo_maker_color_string'] = trim($input['mfo_maker_color_string']);
	$newinput['mfo_exhibit_color_string'] = trim($input['mfo_exhibit_color_string']);
	$newinput['mfo_exhibit_prioryear_color_string'] = trim($input['mfo_exhibit_prioryear_color_string']);
	mfo_log(1, "MFO Settings" , "mfo_options_display changed");
	return $newinput;
}


//todo: better comparison functions
function mfo_settings_admin_notice_error() {

	mfo_option_test('mfo_options_main', 'mfo_event_name_string', 'Event Name must be entered.');
	mfo_option_test('mfo_options_main', 'mfo_support_email_string', 'Support Email Address must be entered.');
	mfo_option_test('mfo_options_main', 'mfo_notification_email_string', 'Notification Email Address must be entered.');
	mfo_option_test('mfo_options_main', 'mfo_event_year_string', 'Event Year must be entered.');
	mfo_option_test('mfo_options_debug', 'mfo_warning_email_string', 'System Warning Email Address must be entered.');


}
add_action( 'admin_notices', 'mfo_settings_admin_notice_error' );

function mfo_option_test ($options, $opt, $txt) {
	$options = get_option($options);
        if (strlen($options[$opt])<1) {
		printf( '<div class="%1$s"><p><a href="/wp-admin/options-general.php?page=mfo-options-page">%2$s</a></p></div>','notice notice-error' , 'MFO Settings Error: ' . $txt ); 
        }
}

?>
