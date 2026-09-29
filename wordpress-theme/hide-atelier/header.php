<?php
/**
 * Site header.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#f4ece1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'hide-atelier' ); ?></a>

<div class="topbar">
	<p>
		<?php
		/* translators: %s: amount */
		echo esc_html( sprintf( __( 'Free delivery over %s', 'hide-atelier' ), ha_price( ha_opt( 'free_ship_min' ) ) ) );
		?>
		<span aria-hidden="true">✦</span> <?php esc_html_e( 'Cash on Delivery across Pakistan', 'hide-atelier' ); ?>
		<span aria-hidden="true" class="hide-sm">✦</span> <span class="hide-sm"><?php esc_html_e( 'Worldwide shipping', 'hide-atelier' ); ?></span>
	</p>
</div>

<header class="site-header" id="siteHeader">
	<div class="site-header__inner">
		<button class="icon-btn burger" id="burger" aria-label="<?php esc_attr_e( 'Menu', 'hide-atelier' ); ?>" aria-expanded="false" aria-controls="mainNav"><span></span><span></span></button>
		<?php ha_logo(); ?>
		<nav class="main-nav" id="mainNav" aria-label="<?php esc_attr_e( 'Main', 'hide-atelier' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 2,
					'fallback_cb'    => 'ha_menu_fallback',
				)
			);
			?>
			<div class="main-nav__extra">
				<a href="<?php echo esc_url( ha_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo ha_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> WhatsApp</a>
			</div>
		</nav>
		<div class="site-header__actions">
			<?php
			if ( function_exists( 'ha_currency_switcher' ) ) {
				ha_currency_switcher();
			}
			if ( ha_has_woo() ) :
				?>
				<a class="icon-btn hide-sm" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'My account', 'hide-atelier' ); ?>"><?php echo ha_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<a class="icon-btn js-cart-open" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'Cart', 'hide-atelier' ); ?>">
					<?php echo ha_icon( 'cart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<em class="js-cart-count<?php echo ha_cart_count() ? ' show' : ''; ?>"><?php echo (int) ha_cart_count(); ?></em>
				</a>
			<?php endif; ?>
		</div>
	</div>
</header>

<?php if ( ha_has_woo() && ! is_cart() && ! is_checkout() ) : ?>
<div class="overlay" id="overlay" hidden></div>
<aside class="drawer" id="cartDrawer" aria-label="<?php esc_attr_e( 'Shopping cart', 'hide-atelier' ); ?>" aria-hidden="true">
	<div class="drawer__head">
		<h3><?php esc_html_e( 'Your cart', 'hide-atelier' ); ?></h3>
		<button class="icon-btn js-drawer-close" aria-label="<?php esc_attr_e( 'Close', 'hide-atelier' ); ?>"><?php echo ha_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
	</div>
	<?php
	$ha_min   = function_exists( 'ha_currency_convert' ) ? ha_currency_convert( (float) ha_opt( 'free_ship_min' ) ) : (float) ha_opt( 'free_ship_min' );
	$ha_total = WC()->cart ? (float) WC()->cart->get_displayed_subtotal() : 0;
	$ha_left  = max( 0, $ha_min - $ha_total );
	?>
	<div class="ship-bar">
		<p>
			<?php
			if ( $ha_left > 0 ) {
				/* translators: %s amount */
				echo wp_kses_post( sprintf( __( 'Add <b>%s</b> more for free delivery', 'hide-atelier' ), wp_strip_all_tags( wc_price( $ha_left ) ) ) );
			} else {
				echo wp_kses_post( __( 'You unlocked <b>free delivery</b>', 'hide-atelier' ) );
			}
			?>
		</p>
		<div><span style="width:<?php echo esc_attr( $ha_min > 0 ? min( 100, round( $ha_total / $ha_min * 100 ) ) : 100 ); ?>%"></span></div>
	</div>
	<div class="drawer__body"><div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div></div>
</aside>
<?php endif; ?>
