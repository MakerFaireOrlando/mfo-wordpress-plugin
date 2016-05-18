<?php
/**
 * @package WordPress
 * @subpackage Coraline
 * @since Coraline 1.0
 * Template Name: Event - Talks
 * Description: Outputs the list of talks
 
 */

get_header(); ?>

		<div id="content-container">
			<div id="content" role="main">

			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<div class="entry-content">
						<?php the_content(); ?>

<?php

$event_args = array(
  'post_type' => 'event',
  'post_status' => 'publish',
  'posts_per_page' => -1, // all
  'orderby' => 'title',
  'order' => 'ASC',
  //'meta_query' => array(array('key' => 'wpcf-event-date', 'value' => '1442016000'))
);
$events = get_posts($event_args);


//build array of dates
foreach ($events as $event) {
        $dates[] = get_post_meta($event->ID, 'wpcf-event-date', true);
        $dates = array_unique($dates);
} //end foreach events

//echo '<pre>';
//print_r ($dates);
//echo '</pre>';

//sort events into dates
foreach ($dates as $date) {
        
        foreach ($events as $event) {
                if ($date == get_post_meta($event->ID, 'wpcf-event-date', true) 
			AND 5 == get_post_meta($event->ID, 'wpcf-event-type', true) ) {

                        $e_dateunix = get_post_meta($event->ID, "wpcf-event-date", true);
                        $e_date = date("F j", $e_dateunix);
                        $e_start_time_text =  get_post_meta($event->ID, "wpcf-event-start-time", true);
                        $e_start_time_ts = strtotime($e_date . ' '. $e_start_time_text);
                        $e_end_time_text =  get_post_meta($event->ID, "wpcf-event-end-time", true);
                        $e_end_time_ts = strtotime($e_date . ' '. $e_end_time_text);

                        //create the array for the event, arrayed by date
                        //$e_output[$e_dateunix]['events'] = array (
                        $events_output[] = array (
                                'name' => html_entity_decode($event->post_title),
				'id' => $event->ID,
                                'start_time_ts' => $e_start_time_ts,
                                'end_time_ts' => $e_end_time_ts,
                                'duration' => get_post_meta($event->ID, "wpcf-event-duration", true),
                                );
                } //end if sort dates
        } //end foreach event

        usort($events_output, function ($a, $b) {
                                        if ($a['start_time_ts'] == $b['start_time_ts']) {
                                                //they are equal start times, now check for end times
                                                if ($a['end_time_ts'] == $b['end_time_ts']) {
                                                        //they are equal end times, now check for duration
                                                        return intval($a['duration']) - intval($b['duration']);
                                                } //end if equal end times
                                                return ($a['end_time_ts'] < $b['end_time_ts']) ? -1 : 1;
                                        } //end if equal start times
                                        return ($a['start_time_ts'] < $b['start_time_ts']) ? -1 : 1;
                                        });

        $dates_output[] = array (
                'date_title' =>  date("F j", $date),
                'events' => $events_output,

                );
        unset($events_output);

} //end foreach dates

echo '<pre>';
print_r ($dates_output);
echo '</pre>';


?>

						<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'coraline' ), 'after' => '</div>' ) ); ?>
						<?php edit_post_link( __( 'Edit', 'coraline' ), '<span class="edit-link">', '</span>' ); ?>
					</div><!-- .entry-content -->
				</div><!-- #post-## -->

				<?php comments_template( '', true ); ?>

			<?php endwhile; ?>

			</div><!-- #content -->
		</div><!-- #content-container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
