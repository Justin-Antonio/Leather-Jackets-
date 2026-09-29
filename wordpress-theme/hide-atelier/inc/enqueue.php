<?php
/**
 * Scripts & styles. Heavy 3D code is only loaded on the home page.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

/**
 * Version string based on file modification time (cache busting).
 *
 * @param string $rel Path relative to theme.
 */
function ha_ver( $rel ) {
	$file = HA_DIR . '/' . $rel;
	return file_exists( $file ) ? (string) filemtime( $file ) : HA_VERSION;
}

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'ha-main', HA_URI . '/assets/css/main.css', array(), ha_ver( 'assets/css/main.css' ) );

		wp_enqueue_script( 'ha-site', HA_URI . '/assets/js/site.js', array(), ha_ver( 'assets/js/site.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_add_inline_script(
			'ha-site',
			'window.HA=' . wp_json_encode(
				array(
					'home'     => home_url( '/' ),
					'currency' => function_exists( 'ha_currency_current' ) ? ha_currency_current() : '',
					'currencies' => function_exists( 'ha_currency_list' ) ? ha_currency_list() : array(),
					'cookie'   => 'ha_cur',
				)
			) . ';',
			'before'
		);

		if ( is_front_page() ) {
			wp_enqueue_script( 'ha-home', HA_URI . '/assets/js/home.js', array(), ha_ver( 'assets/js/home.js' ), array( 'in_footer' => true ) );
			wp_add_inline_script( 'ha-home', 'window.HA_HERO=' . wp_json_encode( ha_hero_products() ) . ';', 'before' );
		}

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) && ! ( function_exists( 'is_product' ) && is_product() ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
);

/**
 * Load home.js as an ES module (it lazy-loads the WebGL chunk).
 */
add_filter(
	'script_loader_tag',
	function ( $tag, $handle ) {
		if ( 'ha-home' === $handle ) {
			$tag = str_replace( '<script ', '<script type="module" ', $tag );
		}
		return $tag;
	},
	10,
	2
);

/**
 * Preload the fonts used above the fold.
 */
add_action(
	'wp_head',
	function () {
		foreach ( array( 'fraunces-latin-600-normal.woff2', 'inter-latin-400-normal.woff2' ) as $font ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( HA_URI . '/assets/fonts/' . $font ) );
		}
	},
	2
);

/**
 * Products shown in the 3D hero: featured products first, then newest.
 * Falls back to the bundled demo images when WooCommerce has no products yet.
 */
function ha_hero_products() {
	$items = array();
	if ( function_exists( 'wc_get_products' ) ) {
		$products = wc_get_products(
			array(
				'status'   => 'publish',
				'limit'    => 5,
				'featured' => true,
				'orderby'  => 'menu_order',
				'order'    => 'ASC',
			)
		);
		if ( count( $products ) < 2 ) {
			$products = wc_get_products(
				array(
					'status'  => 'publish',
					'limit'   => 5,
					'orderby' => 'date',
					'order'   => 'DESC',
				)
			);
		}
		foreach ( $products as $p ) {
			$img_id = $p->get_image_id();
			if ( ! $img_id ) {
				continue;
			}
			$src = wp_get_attachment_image_src( $img_id, 'large' );
			$items[] = array(
				'name'  => $p->get_name(),
				'url'   => get_permalink( $p->get_id() ),
				'price' => wp_strip_all_tags( $p->get_price_html() ),
				'image' => $src ? $src[0] : '',
				'thumb' => wp_get_attachment_image_url( $img_id, 'thumbnail' ),
			);
		}
	}
	if ( ! $items ) {
		foreach ( ha_demo_products() as $d ) {
			$items[] = array(
				'name'  => $d['name'],
				'url'   => ha_page_url( 'shop' ),
				'price' => ha_price( $d['price'] ),
				'image' => HA_URI . '/assets/images/products/' . $d['image'],
				'thumb' => HA_URI . '/assets/images/products/' . $d['image'],
			);
		}
	}
	return $items;
}
