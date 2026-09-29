<?php
/**
 * Default page.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$ha_is_woo_page = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
	?>
	<main id="main" class="site-main page-pad<?php echo $ha_is_woo_page ? ' woo-main' : ''; ?>">
		<div class="container<?php echo $ha_is_woo_page ? '' : ' container--narrow'; ?>">
			<header class="page-hero page-hero--sm">
				<h1 class="h1"><?php the_title(); ?></h1>
			</header>
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</div>
	</main>
	<?php
endwhile;
get_footer();
