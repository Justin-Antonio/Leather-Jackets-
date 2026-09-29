<?php
/**
 * Template Name: About / Our craft
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<main id="main" class="site-main page-pad">
		<div class="container">
			<header class="page-hero about-hero">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Our craft', 'hide-atelier' ); ?></p>
					<h1 class="h1"><?php esc_html_e( 'One hide.', 'hide-atelier' ); ?> <em><?php esc_html_e( 'One maker.', 'hide-atelier' ); ?></em> <?php esc_html_e( 'One jacket.', 'hide-atelier' ); ?></h1>
					<div class="entry-content lead"><?php the_content(); ?></div>
				</div>
				<div class="about-hero__img"><img src="<?php echo esc_url( HA_URI . '/assets/images/products/camel-quilted-cafe-racer.jpg' ); ?>" alt="" loading="eager" decoding="async" width="416" height="480"></div>
			</header>
		</div>
		<?php get_template_part( 'template-parts/process' ); ?>
		<div class="container center page-pad-sm">
			<a class="btn btn--primary" href="<?php echo esc_url( ha_page_url( 'shop' ) ); ?>"><?php esc_html_e( 'Shop the collection', 'hide-atelier' ); ?></a>
		</div>
	</main>
	<?php
endwhile;
get_footer();
