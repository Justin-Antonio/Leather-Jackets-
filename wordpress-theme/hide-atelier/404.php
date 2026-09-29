<?php
/**
 * 404.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="site-main page-pad center">
	<div class="container container--narrow notfound">
		<p class="eyebrow"><?php esc_html_e( 'Error 404', 'hide-atelier' ); ?></p>
		<h1 class="h1"><?php esc_html_e( 'This seam came undone.', 'hide-atelier' ); ?></h1>
		<p class="muted"><?php esc_html_e( 'The page you are looking for does not exist or has moved.', 'hide-atelier' ); ?></p>
		<p class="btn-row btn-row--center">
			<a class="btn btn--primary" href="<?php echo esc_url( ha_page_url( 'shop' ) ); ?>"><?php esc_html_e( 'Shop jackets', 'hide-atelier' ); ?></a>
			<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'hide-atelier' ); ?></a>
		</p>
	</div>
</main>
<?php
get_footer();
