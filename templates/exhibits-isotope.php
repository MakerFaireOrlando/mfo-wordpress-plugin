<?php
/**
 * exhibits-isotope.php
 * Page Template for list of exhibits using isotope.js
 * todo: add category filtering from page params
 */

get_header(); ?>

		<?php
			$category = get_query_var("category");
			if ($category) {
				$cat_class = '<div id="category" class=".' . $category . '"></div>';
			}
			else $cat_class = "";
		?>
                <div id="page-content">
                <?php echo $cat_class ?>
                        <div id="page-body">

                        <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

                                <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                                        <div class="container">
                                                <?php the_content(); ?>
					</div>

					<?php //<div class="flag-banner"></div> ?>
					<div class="mtm">
	 				   <div class="mtm-search">
						<div class="container">
							<div class="row">
							<div class="col-md-8">
								<label class="search-filter-label">Search:</label>
								<input type="text" class="quicksearch form-control" id="maker-search-input" placeholder="Looking for a specific Exhibit or Maker?" />
							</div>

							<div class="col-md-4">
								<label class="search-filter-label">Filter by category:</label>
								<select class="filters-select form-control" id="makers-category-select">
						 		<option value="*" selected>show all</option>

								<?php
								//$categories = get_terms("exhibit-category");
								$categories = get_terms( array(
									'taxonomy' => 'exhibit-category', 
									'hide_empty' => true) );
								foreach ($categories as $cat) {
									echo '<option value=".' . $cat->slug . '">' . $cat->name . '</option>';
								}
						 		?>
								</select>
							</div><!-- #col -->
							</div><!-- #row -->
						</div><!-- #container -->
					</div><!-- #mtm-search -->
					</div><!-- #mtm -->
 				<?php
                                        $exhibit_args = array(
                                                'post_type' => 'exhibit',
                                                'numberposts' => -1,
                                                'meta_query' => array(
                                                        array(
                                                                'key' => 'wpcf-approval-year',
                                                                'value' => mfo_exhibits_year()
                                                        ),
                                                        array(
                                                                'key' => 'wpcf-approval-status',
                                                                'value' => '1'
                                                        )

                                                        )
                                                );
                                        $exhibits = get_posts($exhibit_args);
					shuffle($exhibits);
                                //echo json_encode($exhibits);
				echo '<div class="exhibits-container" id="exhibits">';
				foreach ($exhibits as $exhibit) {

					$termlist='';
					$terms = get_the_terms($exhibit->ID, "exhibit-category");
				
					foreach ($terms as $term) {
						$termlist = $termlist . " " . $term->slug;
					}

					//this works in conjunction with the .js since it forces a reload when the battlebot or combat-robot category is selected from dropdown
					$show = 1;
					$botcat = 0;
					if (($category == "battlebot") || ($category == "combat-robots")) $botcat = 1;
					if (strstr($termlist, "battlebot") 	&& ($botcat==0)) $show = 0;
					if (strstr($termlist, "combat-robots") 	&& ($botcat==0)) $show = 0;

					if ($show) {
						echo '<div class="item' . $termlist. '">';
						echo '<div class="title-container"><a href="' . get_permalink($exhibit->ID) . '">' . $exhibit->post_title  . '</a></div>';
						echo '<div class="excerpt-container">' . $exhibit->post_excerpt . '</div>';
						echo '<div class="img-container">';
						echo '<a href="' . get_permalink($exhibit->ID) .'">';
						echo '<img src="'. get_the_post_thumbnail_url ($exhibit->ID, 'medium') . '" style="max-width:300px"></a></div></div>'; 
					}
				}
?>

			</div>
			<div class="container">
			Please note that due to the number of individuals, groups, and organizations participating, content is subject to change at any time.
			</div>


                                                <?php edit_post_link( __( 'Edit', 'maker-faire-online' ),
                                                         '<span class="edit-link">', '</span>' ); ?>
                                        </div><!-- #container -->
                                </div><!-- #post-## -->


                        <?php endwhile; ?>


                        </div><!-- #page-body -->
                </div><!-- #page-content -->


<?php get_footer(); ?>

