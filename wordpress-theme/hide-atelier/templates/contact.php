<?php
/**
 * Template Name: Contact
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
				<p class="eyebrow"><?php esc_html_e( 'We are here to help', 'hide-atelier' ); ?></p>
				<h1 class="h1"><?php the_title(); ?></h1>
				<div class="entry-content lead"><?php the_content(); ?></div>
			</header>
			<div class="contact">
				<div class="contact__cards">
					<a class="info-card" href="<?php echo esc_url( ha_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo ha_icon( 'whatsapp', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><b>WhatsApp</b><span><?php esc_html_e( 'Fastest reply', 'hide-atelier' ); ?></span></div></a>
					<a class="info-card" href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', ha_opt( 'store_phone' ) ) ); ?>"><?php echo ha_icon( 'phone', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><b><?php esc_html_e( 'Call us', 'hide-atelier' ); ?></b><span><?php echo esc_html( ha_opt( 'store_phone' ) ); ?></span></div></a>
					<a class="info-card" href="mailto:<?php echo esc_attr( ha_opt( 'store_email' ) ); ?>"><?php echo ha_icon( 'mail', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><b><?php esc_html_e( 'Email', 'hide-atelier' ); ?></b><span><?php echo esc_html( ha_opt( 'store_email' ) ); ?></span></div></a>
					<div class="info-card"><?php echo ha_icon( 'pin', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><b><?php esc_html_e( 'Workshop', 'hide-atelier' ); ?></b><span><?php echo nl2br( esc_html( ha_opt( 'store_address' ) ) ); ?></span></div></div>
				</div>
				<div class="contact__form">
					<h2 class="h3"><?php esc_html_e( 'Send us a message', 'hide-atelier' ); ?></h2>
					<?php echo ha_contact_form(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			</div>
		</div>
	</main>
	<?php
endwhile;
get_footer();
