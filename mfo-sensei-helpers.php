<?php

/*
Now part of MFO base plugin
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//from http://ideas.woothemes.com/forums/191508-sensei/suggestions/5350719-add-next-lesson-button-once-lesson-is-complete
/* works, but keeps the anchor tag on the redirect
function sensei_user_lesson_end_goto_next () {
	global $post;
	$nav_id_array = sensei_get_prev_next_lessons( $post->ID );
	$next_lesson_id = absint( $nav_id_array['next_lesson'] );
	wp_redirect( get_permalink( $next_lesson_id ) );
}

add_action( 'sensei_user_lesson_end', 'sensei_user_lesson_end_goto_next' );
*/

/* DUH, this is a setting. Keeping the code here for reference
function mfo_sensei_configure_emails( $obj ) {
	remove_action( 'sensei_user_course_start', array( $obj, 'teacher_started_course' ), 10, 2 );
}

add_action('sensei_emails', 'mfo_sensei_configure_emails');
*/

/*
function enroll_course_shortcode( $atts, $content = null ) {
	WooThemes_Sensei_Utils::user_start_course('2121', '3825');
}
add_shortcode( 'enrollcourse', 'enroll_course_shortcode' );
*/

//todo: remove hard coded course ids

function enroll_maker_courses_shortcode( $atts, $content = null ) {
	$curuser = wp_get_current_user();
	WooThemes_Sensei_Utils::user_start_course($curuser->ID, '3825'); //registering
	WooThemes_Sensei_Utils::user_start_course($curuser->ID, '3874'); //maker-manual
	//return $output;
	return;
}
add_shortcode( 'enroll-maker-courses', 'enroll_maker_courses_shortcode' );

function enroll_producer_courses_shortcode( $atts, $content = null ) {
	$curuser = wp_get_current_user();
	WooThemes_Sensei_Utils::user_start_course($curuser->ID, '4631'); //website features for producers
	WooThemes_Sensei_Utils::user_start_course($curuser->ID, '3874'); //maker-manual
	return $output;
}
add_shortcode( 'enroll-producer-courses', 'enroll_producer_courses_shortcode' );


?>
