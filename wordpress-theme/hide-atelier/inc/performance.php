<?php
/**
 * Front-end speed: strip what the theme does not need.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

// Emoji scripts & styles.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

// Head clutter.
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'wp_oembed_add_host_js' );

/**
 * Is the current request a WooCommerce page that needs Woo assets?
 */
function ha_is_woo_request() {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return false;
	}
	return is_woocommerce() || is_cart() || is_checkout() || is_account_page();
}

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_admin() ) {
			return;
		}
		// jQuery Migrate is never needed by this theme.
		if ( ! is_customize_preview() ) {
			wp_deregister_script( 'wp-embed' );
		}
		if ( ! is_user_logged_in() ) {
			wp_dequeue_style( 'dashicons' );
		}

		$uses_blocks = is_singular() && has_blocks( get_post() );
		if ( ! $uses_blocks && ! ha_is_woo_request() ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
			wp_dequeue_style( 'global-styles' );
			wp_dequeue_style( 'classic-theme-styles' );
		}

		if ( ! ha_is_woo_request() ) {
			// WooCommerce loads CSS/JS on every page; keep it only where it is used.
			foreach ( array( 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen', 'wc-blocks-style', 'wc-blocks-vendors-style' ) as $h ) {
				wp_dequeue_style( $h );
			}
			foreach ( array( 'wc-cart-fragments', 'woocommerce', 'wc-add-to-cart', 'sourcebuster-js', 'wc-order-attribution' ) as $h ) {
				wp_dequeue_script( $h );
			}
		}
		// The theme styles WooCommerce itself; the default small-screen sheet fights the layout.
		wp_dequeue_style( 'woocommerce-smallscreen' );
	},
	99
);

add_action(
	'wp_default_scripts',
	function ( $scripts ) {
		if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
			$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
		}
	}
);

/**
 * Lazy-load and async-decode images that WordPress outputs (WP already adds loading=lazy; add decoding).
 */
add_filter(
	'wp_get_attachment_image_attributes',
	function ( $attr ) {
		if ( empty( $attr['decoding'] ) ) {
			$attr['decoding'] = 'async';
		}
		return $attr;
	}
);
