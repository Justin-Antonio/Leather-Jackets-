<?php
/**
 * Small helpers shared by templates.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values for every Customizer setting.
 */
function ha_defaults() {
	return array(
		'store_name'         => get_bloginfo( 'name' ) ? wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) : 'Hide & Atelier',
		'store_email'        => get_option( 'admin_email' ),
		'store_phone'        => '+92 300 0000000',
		'whatsapp'           => '923000000000',
		'store_address'      => 'Your store address, Lahore, Pakistan',
		'return_days'        => 14,
		'dispatch_days'      => '2–3',
		'domestic_days'      => '3–5',
		'international_days' => '7–14',
		'free_ship_min'      => 5500,
		'hero_eyebrow'       => 'Autumn / Winter Collection',
		'hero_title_1'       => 'Worn by',
		'hero_title_2'       => 'legends.',
		'hero_title_3'       => 'Stitched by hand.',
		'hero_text'          => 'Genuine leather jackets, cut from a single hide and hand-finished stitch by stitch. Watch yours come together — then make it your own.',
		'product_note'       => '<strong>Ready to ship.</strong> Dispatched within 2–3 working days. Cash on Delivery available across Pakistan; international orders pay online at checkout.',
		'taxes_note'         => 'Inclusive of all taxes',
		'size_chest_labels'  => "XS: Chest 34\" - 36\"\nS: Chest 36\" - 38\"\nM: Chest 38\" - 40\"\nL: Chest 40\" - 42\"\nXL: Chest 42\" - 44\"\nXXL: Chest 44\" - 46\"\nXXXL: Chest 46\" - 48\"",
		'currencies'         => 'PKR,USD,GBP,EUR,AED,SAR,CAD,AUD',
		'rates_auto'         => 1,
		'rates_manual'       => '',
		'cod_currencies'     => 'PKR',
		'stat_1_num'         => '100', 'stat_1_suffix' => '%', 'stat_1_label' => 'Genuine leather',
		'stat_2_num'         => '6', 'stat_2_suffix' => '', 'stat_2_label' => 'Sizes, S to XXXL',
		'stat_3_num'         => '48', 'stat_3_suffix' => 'h', 'stat_3_label' => 'Dispatch time',
		'stat_4_num'         => '14', 'stat_4_suffix' => '', 'stat_4_label' => 'Day easy returns',
		'social_instagram'   => '',
		'social_facebook'    => '',
		'social_tiktok'      => '',
	);
}

/**
 * Read a theme option with its default.
 *
 * @param string $key Option key.
 * @return mixed
 */
function ha_opt( $key ) {
	$d = ha_defaults();
	return get_theme_mod( 'ha_' . $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
}

/**
 * WhatsApp chat link.
 *
 * @param string $text Prefilled message.
 */
function ha_whatsapp_url( $text = '' ) {
	$num = preg_replace( '/\D+/', '', (string) ha_opt( 'whatsapp' ) );
	$url = 'https://wa.me/' . $num;
	return $text ? $url . '?text=' . rawurlencode( $text ) : $url;
}

/**
 * Chest labels per size: [ 'S' => 'Chest 36" - 38"', ... ].
 */
function ha_size_labels() {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) ha_opt( 'size_chest_labels' ) ) as $line ) {
		if ( strpos( $line, ':' ) === false ) {
			continue;
		}
		list( $size, $label ) = array_map( 'trim', explode( ':', $line, 2 ) );
		if ( $size ) {
			$out[ strtoupper( $size ) ] = $label;
		}
	}
	return $out;
}

/**
 * Format an amount stored in the shop's base currency for display in the visitor's currency.
 *
 * @param float $amount Base currency amount.
 */
function ha_price( $amount ) {
	if ( function_exists( 'wc_price' ) ) {
		$amount = function_exists( 'ha_currency_convert' ) ? ha_currency_convert( (float) $amount ) : (float) $amount;
		return wp_strip_all_tags( wc_price( $amount ) );
	}
	return number_format_i18n( (float) $amount );
}

/**
 * Inline SVG icons (stroke based, inherit currentColor).
 *
 * @param string $name Icon name.
 * @param int    $size Pixel size.
 */
function ha_icon( $name, $size = 22 ) {
	$paths = array(
		'cart'     => '<path d="M5 7h14l-1.2 12.1a2 2 0 0 1-2 1.9H8.2a2 2 0 0 1-2-1.9L5 7z"/><path d="M9 7V6a3 3 0 0 1 6 0v1"/>',
		'heart'    => '<path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 3 4.5 6.7 4.5c2.1 0 3.6 1.2 5.3 3 1.7-1.8 3.2-3 5.3-3 3.7 0 5.8 3.9 4.3 7.3C19.5 16.4 12 21 12 21z"/>',
		'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/>',
		'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'check'    => '<path d="m5 12 5 5L20 7"/>',
		'shield'   => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3z"/><path d="m9 12 2 2 4-4"/>',
		'truck'    => '<path d="M3 6h11v10H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
		'award'    => '<circle cx="12" cy="9" r="5.5"/><path d="m8.5 13.5-1.5 7.5 5-2.5 5 2.5-1.5-7.5"/>',
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'whatsapp' => '<path d="M20.5 3.5A11 11 0 0 0 3.3 17.1L2 22l5-1.3A11 11 0 1 0 20.5 3.5zM12 20.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 11.9 11.9 0 0 0 4.6 4c1.7.7 2.3.8 3.2.7a2.7 2.7 0 0 0 1.8-1.3 2.2 2.2 0 0 0 .2-1.3c-.1-.1-.2-.2-.4-.3z" fill="currentColor" stroke="none"/>',
		'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
		'menu'     => '<path d="M4 8h16M4 16h16"/>',
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'replay'   => '<path d="M4 12a8 8 0 1 0 2.3-5.7M4 4v4h4"/>',
		'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
		'ruler'    => '<path d="M3 16 16 3l5 5L8 21z"/><path d="m7 12 2 2M10 9l2 2M13 6l2 2"/>',
		'star'     => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'phone'    => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
		'pin'      => '<path d="M12 21s7-6.2 7-11.5A7 7 0 0 0 5 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="ico ico-%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%3$s</svg>',
		esc_attr( $name ),
		(int) $size,
		$paths[ $name ]
	);
}

/**
 * Brand logo markup (custom logo or text logo).
 */
function ha_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	$name  = (string) ha_opt( 'store_name' );
	$parts = preg_split( '/\s*&\s*/', $name, 2 );
	echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="logo" rel="home">';
	if ( count( $parts ) === 2 ) {
		echo esc_html( $parts[0] ) . ' <span>&amp;</span> ' . esc_html( $parts[1] );
	} else {
		echo esc_html( $name );
	}
	echo '</a>';
}

/**
 * Whether WooCommerce is active.
 */
function ha_has_woo() {
	return class_exists( 'WooCommerce' );
}

/**
 * Menu fallback so the site works before menus are created.
 *
 * @param array $args wp_nav_menu args.
 */
function ha_menu_fallback( $args ) {
	$links = array(
		home_url( '/' )           => __( 'Home', 'hide-atelier' ),
		ha_page_url( 'shop' )     => __( 'Shop', 'hide-atelier' ),
		ha_page_url( 'about' )    => __( 'Our Craft', 'hide-atelier' ),
		ha_page_url( 'size-guide' ) => __( 'Size Guide', 'hide-atelier' ),
		ha_page_url( 'contact' )  => __( 'Contact', 'hide-atelier' ),
	);
	echo '<ul class="' . esc_attr( $args['menu_class'] ) . '">';
	foreach ( $links as $url => $label ) {
		echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * URL of a theme page by slug (falls back to home anchors).
 *
 * @param string $slug Page slug.
 */
function ha_page_url( $slug ) {
	if ( 'shop' === $slug && function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'shop' );
	}
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}
