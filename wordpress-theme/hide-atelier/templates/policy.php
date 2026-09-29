<?php
/**
 * Template Name: Policy page (with contents list)
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
			<header class="page-hero">
				<p class="eyebrow"><?php echo esc_html( ha_opt( 'store_name' ) ); ?></p>
				<h1 class="h1"><?php the_title(); ?></h1>
			</header>
			<div class="policy">
				<aside class="policy__toc" aria-label="<?php esc_attr_e( 'On this page', 'hide-atelier' ); ?>">
					<p class="policy__toc-title"><?php esc_html_e( 'On this page', 'hide-atelier' ); ?></p>
					<ol id="toc"></ol>
				</aside>
				<article class="entry-content policy__body" id="policyBody">
					<?php the_content(); ?>
				</article>
				<aside class="policy__help">
					<h3><?php esc_html_e( 'Need help?', 'hide-atelier' ); ?></h3>
					<p><?php esc_html_e( 'Our team replies within a few hours.', 'hide-atelier' ); ?></p>
					<a class="btn btn--primary btn--full" href="<?php echo esc_url( ha_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo ha_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>WhatsApp</a>
					<a class="btn btn--ghost btn--full" href="mailto:<?php echo esc_attr( ha_opt( 'store_email' ) ); ?>"><?php echo ha_icon( 'mail', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Email us', 'hide-atelier' ); ?></a>
				</aside>
			</div>
		</div>
	</main>
	<?php
endwhile;
get_footer();
