<?php
/**
 * Template Name: Duplicate Exhibit
 * will be automatically applied to page with slug duplicate-exhibit
 */

/*
todo:
*/
mfo_log(4, "duplicate-exhibit.php", "page load");

get_header(); ?>

		<div id="page-content">
			<div id="page-body" role="main">

			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<div class="container">
						<?php the_content(); ?>


<?php
//  <input type="submit" name="SubmitButton"/>


$exhibit = intval(get_query_var ("dup-exhibit",0));
$exhibit_author = get_post_field( 'post_author', $exhibit);

mfo_log(4, "duplicate-exhibit.php", "exhibit: " . $exhibit);
mfo_log(4, "duplicate-exhibit.php", "exhibit_author: " . $exhibit_author);
mfo_log(4, "duplicate-exhibit.php", "post_type: " . get_post_type($exhibit));


$curuser = wp_get_current_user();

//echo $curuser->ID . " " . $curuser->display_name . "<BR>";
//echo $exhibit_author . "<BR>";
//echo 'The post type is: ' . get_post_type($exhibit) . '<br>';
//echo "exhibit: ".$exhibit."<br>";

if (get_post_type($exhibit) != "exhibit") {
	echo '<h3>System Error: invalid post type.</h3>Please return to Maker Dashboard and report this issue.';
	}

elseif ($curuser->ID != $exhibit_author AND !current_user_can( 'manage_options' )) {
	echo '<h3>System Error</h3>Please return to Maker Dashboard and report this issue.';
	}
	//TODO: Also test if current user is has the rights to edit the exhibit...
else { 

	//general prep here?


	//is this a form post?

	if (isset($_POST['exhibit-id']))
	{
		$exhibit_id = $_POST['exhibit-id'];
		$old_post_name = $_POST['old-post-name'];
		$new_post_name = $_POST['new-post-name'];


		if (get_post_type($exhibit_id) == 'exhibit') {
		
			mfo_log(4, "duplicate-exhibit.php", "Form Submit - exhibit-id = " . $exhibit_id ." ; old-post-name=" . $old_post_name . " ;new-post-name=" . $new_post_name);
			$dup_ret = mfo_duplicate_post($exhibit_id, $old_post_name, $new_post_name );

			if ($dup_ret) {
				echo '<h3>Your Exhibit has been Duplicated - Please return to the Maker Dashboard to review and make any needed updates.</h3>';
			}
			else {
				echo '<h3>Exhibit Duplication Failed. Please email us at [mfo-support-email-link]"</h3>';
			}
		}
		else {
			echo '<h3>Exhibit Duplication Failed (post type is not exhibit).</h3> Please email us at ' . mfo_support_email_link();
		}


		echo '<div style="margin-bottom:20px">';
        	echo '<button class="ui-widget" id="release" onclick="cancel()">Return to Dashboard</button>';
        	echo '</div>';

	} //end is set post

	else  {

	$appr_year = get_post_meta($exhibit, 'wpcf-approval-year', TRUE);

	echo '<div style="margin-bottom:20px">';

	echo '<h3>Exhibit Information</h3>';
	echo "Exhibit Name: <b>" . get_the_title($exhibit) . "</b><br>";
	echo "Exhibit Year: <b>" . $appr_year . "</b><br>" ;

	echo '</div>';

	echo '<div style="margin-bottom:20px">';

	echo '<form id="dupform" action="" method="post">';
  	echo '<input type="hidden" name="exhibit-id"/>';

	$newname = get_post_field('post_name',$exhibit);
	$oldname = $newname . "-" .get_post_meta($exhibit, "wpcf-approval-year", true);

	echo '<div style="margin-bottom:20px">';
	echo '<H3>We strongly recommend using the pre-filled exhibit URLs below so that old links now point to the new exhibit.</h3>';
	echo 'Using the pre-filled exhibit URLs here will rename last years exhibit to now include the year at the end,';
	echo ' and will put the NEW exhibit at the previous link.';
	echo '</div>';


  	echo '<div>New Exhibit URL: <input type="text" name="new-post-name" value="' . $newname . '"size="60"/> </div>';


  	echo '<div>Old Exhibit URL changed to:  <input type="text" name="old-post-name" value="' . $oldname . '" size="60"/> </div>';
	echo '</form>';

	echo '</div>';

	echo '<div style="margin-bottom:20px">';
        echo '<button class="ui-widget" id="release" onclick="dupExhibit(' . $exhibit . ')">Duplicate this Exhibit</button>';
        echo '</div>';

	echo '<div style="margin-bottom:20px">';
        echo '<button class="ui-widget" id="release" onclick="cancel()">Cancel and Return to Dashboard</button>';
        echo '</div>';

	}

}//end is valid incoming exhibit


?>
<script>

function dupExhibit(exhibit_id){
	console.log(exhibit_id);
	jQuery("input[name='exhibit-id']").val(exhibit_id);
	jQuery("#dupform").submit();

};

function cancel() {
	window.location.href = "/maker-dashboard";
}


</script>




						<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'coraline' ), 'after' => '</div>' ) ); ?>
						<?php edit_post_link( __( 'Edit', 'coraline' ), '<span class="edit-link">', '</span>' ); ?>
					</div><!-- .entry-content -->
				</div><!-- #post-## -->

				<?php comments_template( '', true ); ?>

			<?php endwhile; ?>

			</div><!-- #content -->
		</div><!-- #content-container -->

<?php get_footer(); ?>
<?php



?>

