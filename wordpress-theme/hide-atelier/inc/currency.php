<?php
/**
 * Multi-currency: visitors from Pakistan see PKR, USA USD, UK GBP, etc.
 *
 * Prices are stored in the WooCommerce store currency (e.g. PKR). For other
 * currencies every product, shipping and coupon amount is converted, so the
 * cart, checkout and the order are all in the visitor's currency.
 *
 * Detection order: saved choice (cookie) → CDN / hosting country header or
 * WooCommerce MaxMind geolocation → browser time zone (site.js) → store currency.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

const HA_CUR_COOKIE = 'ha_cur';

/**
 * Approximate USD rates used only when live rates cannot be downloaded.
 */
function ha_fx_fallback_usd() {
	return array(
		'USD' => 1,
		'PKR' => 281,
		'GBP' => 0.75,
		'EUR' => 0.86,
		'AED' => 3.6725,
		'SAR' => 3.75,
		'QAR' => 3.64,
		'KWD' => 0.306,
		'OMR' => 0.385,
		'BHD' => 0.376,
		'CAD' => 1.38,
		'AUD' => 1.53,
		'NZD' => 1.7,
		'INR' => 88,
		'TRY' => 41,
		'MYR' => 4.2,
		'SGD' => 1.29,
		'CHF' => 0.8,
		'NOK' => 10.1,
		'SEK' => 9.5,
		'DKK' => 6.4,
		'JPY' => 148,
		'CNY' => 7.1,
		'ZAR' => 17.5,
	);
}

/**
 * Country → currency.
 */
function ha_country_currency_map() {
	$map = array(
		'PK' => 'PKR', 'US' => 'USD', 'GB' => 'GBP', 'AE' => 'AED', 'SA' => 'SAR', 'QA' => 'QAR',
		'KW' => 'KWD', 'OM' => 'OMR', 'BH' => 'BHD', 'CA' => 'CAD', 'AU' => 'AUD', 'NZ' => 'NZD',
		'IN' => 'INR', 'TR' => 'TRY', 'MY' => 'MYR', 'SG' => 'SGD', 'CH' => 'CHF', 'NO' => 'NOK',
		'SE' => 'SEK', 'DK' => 'DKK', 'JP' => 'JPY', 'CN' => 'CNY', 'ZA' => 'ZAR',
	);
	foreach ( array( 'DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'IE', 'AT', 'PT', 'FI', 'GR', 'LU', 'SK', 'SI', 'EE', 'LV', 'LT', 'MT', 'CY', 'HR' ) as $eu ) {
		$map[ $eu ] = 'EUR';
	}
	return $map;
}

/**
 * Store (base) currency — the one prices are entered in.
 */
function ha_currency_base() {
	return strtoupper( (string) get_option( 'woocommerce_currency', 'PKR' ) );
}

/**
 * Enabled currencies, base first.
 */
function ha_currency_list() {
	static $list = null;
	if ( null !== $list ) {
		return $list;
	}
	$valid = array_keys( get_woocommerce_currencies() );
	$codes = array_map( 'strtoupper', array_map( 'trim', explode( ',', (string) ha_opt( 'currencies' ) ) ) );
	$codes = array_values( array_unique( array_merge( array( ha_currency_base() ), array_intersect( $codes, $valid ) ) ) );
	$list  = $codes;
	return $list;
}

/**
 * Exchange rates relative to the base currency (1 base = X currency).
 */
function ha_currency_rates() {
	static $rates = null;
	if ( null !== $rates ) {
		return $rates;
	}
	$base  = ha_currency_base();
	$saved = get_option( 'ha_fx_rates' );

	if ( ha_opt( 'rates_auto' ) && ( ! is_array( $saved ) || $saved['base'] !== $base ) && ! get_transient( 'ha_fx_lock' ) ) {
		$saved = ha_fx_refresh();
	}

	if ( is_array( $saved ) && $saved['base'] === $base && ha_opt( 'rates_auto' ) ) {
		$rates = $saved['rates'];
	} else {
		$usd = ha_fx_fallback_usd();
		$rates = array();
		if ( isset( $usd[ $base ] ) ) {
			foreach ( $usd as $code => $r ) {
				$rates[ $code ] = $r / $usd[ $base ];
			}
		}
	}

	// Manual overrides: "USD=0.0036" per line.
	foreach ( preg_split( '/\r\n|\r|\n|,/', (string) ha_opt( 'rates_manual' ) ) as $line ) {
		if ( preg_match( '/^\s*([A-Z]{3})\s*=\s*([0-9.]+)\s*$/i', $line, $m ) && (float) $m[2] > 0 ) {
			$rates[ strtoupper( $m[1] ) ] = (float) $m[2];
		}
	}
	$rates[ $base ] = 1;
	return $rates;
}

/**
 * Download live rates (free, no API key) and store them.
 */
function ha_fx_refresh() {
	set_transient( 'ha_fx_lock', 1, HOUR_IN_SECONDS );
	$base = ha_currency_base();
	$res  = wp_remote_get( 'https://open.er-api.com/v6/latest/' . rawurlencode( $base ), array( 'timeout' => 4 ) );
	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
		return get_option( 'ha_fx_rates' );
	}
	$data = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( empty( $data['rates'] ) || ! is_array( $data['rates'] ) ) {
		return get_option( 'ha_fx_rates' );
	}
	$saved = array(
		'base'  => $base,
		'rates' => array_map( 'floatval', $data['rates'] ),
		'time'  => time(),
	);
	update_option( 'ha_fx_rates', $saved, false );
	return $saved;
}

add_action( 'ha_fx_cron', 'ha_fx_refresh' );
add_action(
	'init',
	function () {
		if ( ! wp_next_scheduled( 'ha_fx_cron' ) ) {
			wp_schedule_event( time() + 300, 'twicedaily', 'ha_fx_cron' );
		}
	}
);

/**
 * Country of the visitor from hosting / CDN headers or WooCommerce geolocation.
 */
function ha_visitor_country() {
	foreach ( array( 'HTTP_CF_IPCOUNTRY', 'HTTP_X_COUNTRY_CODE', 'GEOIP_COUNTRY_CODE', 'HTTP_X_VERCEL_IP_COUNTRY', 'HTTP_CLOUDFRONT_VIEWER_COUNTRY' ) as $h ) {
		if ( ! empty( $_SERVER[ $h ] ) && preg_match( '/^[A-Z]{2}$/', strtoupper( sanitize_text_field( wp_unslash( $_SERVER[ $h ] ) ) ) ) ) {
			return strtoupper( sanitize_text_field( wp_unslash( $_SERVER[ $h ] ) ) );
		}
	}
	if ( class_exists( 'WC_Geolocation' ) ) {
		$geo = WC_Geolocation::geolocate_ip( '', false, false );
		if ( ! empty( $geo['country'] ) ) {
			return $geo['country'];
		}
	}
	return '';
}

/**
 * Currency for this visitor.
 */
function ha_currency_current() {
	static $cur = null;
	if ( null !== $cur ) {
		return $cur;
	}
	$list = ha_currency_list();
	$base = $list[0];

	if ( ! empty( $_COOKIE[ HA_CUR_COOKIE ] ) ) {
		$c = strtoupper( sanitize_key( wp_unslash( $_COOKIE[ HA_CUR_COOKIE ] ) ) );
		if ( in_array( $c, $list, true ) ) {
			$cur = $c;
			return $cur;
		}
	}
	$country = ha_visitor_country();
	if ( $country ) {
		$map = ha_country_currency_map();
		if ( isset( $map[ $country ] ) && in_array( $map[ $country ], $list, true ) ) {
			$cur = $map[ $country ];
		} elseif ( 'PK' !== $country && in_array( 'USD', $list, true ) && 'PKR' === $base ) {
			$cur = 'USD';
		}
	}
	if ( null === $cur ) {
		$cur = $base;
	}
	return $cur;
}

/**
 * Convert a base-currency amount to the visitor currency.
 *
 * @param float       $amount Amount in base currency.
 * @param string|null $to     Target currency.
 */
function ha_currency_convert( $amount, $to = null ) {
	$to   = $to ? $to : ha_currency_current();
	$base = ha_currency_base();
	if ( $to === $base || '' === $amount || null === $amount ) {
		return $amount;
	}
	$rates = ha_currency_rates();
	if ( empty( $rates[ $to ] ) ) {
		return $amount;
	}
	// Round up to a whole number: 104.2 → 105 (clean prices abroad).
	return (float) ceil( (float) $amount * $rates[ $to ] );
}

/**
 * Should prices be converted on this request?
 */
function ha_currency_active() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	return ha_currency_current() !== ha_currency_base();
}

/* ---------------------------- WooCommerce filters ---------------------------- */

add_filter(
	'woocommerce_currency',
	function ( $currency ) {
		return ( is_admin() && ! wp_doing_ajax() ) ? $currency : ha_currency_current();
	},
	99
);

/**
 * Convert a price value from a product getter.
 *
 * @param mixed $price Price.
 */
function ha_filter_price( $price ) {
	if ( '' === $price || null === $price || ! ha_currency_active() ) {
		return $price;
	}
	return ha_currency_convert( (float) $price );
}

foreach ( array(
	'woocommerce_product_get_price',
	'woocommerce_product_get_regular_price',
	'woocommerce_product_get_sale_price',
	'woocommerce_product_variation_get_price',
	'woocommerce_product_variation_get_regular_price',
	'woocommerce_product_variation_get_sale_price',
	'woocommerce_variation_prices_price',
	'woocommerce_variation_prices_regular_price',
	'woocommerce_variation_prices_sale_price',
) as $ha_hook ) {
	add_filter( $ha_hook, 'ha_filter_price', 99 );
}

// Variation price cache must be per currency.
add_filter(
	'woocommerce_get_variation_prices_hash',
	function ( $hash ) {
		$hash[] = ha_currency_current();
		return $hash;
	}
);

// No decimals for converted prices.
add_filter(
	'wc_get_price_decimals',
	function ( $decimals ) {
		return ha_currency_active() ? 0 : $decimals;
	}
);

// Shipping costs.
add_filter(
	'woocommerce_package_rates',
	function ( $rates ) {
		if ( ! ha_currency_active() ) {
			return $rates;
		}
		foreach ( $rates as $rate ) {
			$rate->set_cost( ha_currency_convert( (float) $rate->get_cost() ) );
			$taxes = $rate->get_taxes();
			if ( $taxes ) {
				$rate->set_taxes( array_map( fn( $t ) => ha_currency_convert( (float) $t ), $taxes ) );
			}
		}
		return $rates;
	},
	99
);

// Free shipping minimum.
add_filter(
	'woocommerce_shipping_free_shipping_instance_option',
	function ( $value, $key ) {
		return ( 'min_amount' === $key && is_numeric( $value ) && ha_currency_active() ) ? ha_currency_convert( (float) $value ) : $value;
	},
	10,
	2
);

// Fixed coupon amounts and coupon limits.
add_filter(
	'woocommerce_coupon_get_amount',
	function ( $amount, $coupon ) {
		return ( ha_currency_active() && ! $coupon->is_type( 'percent' ) ) ? ha_currency_convert( (float) $amount ) : $amount;
	},
	10,
	2
);
foreach ( array( 'woocommerce_coupon_get_minimum_amount', 'woocommerce_coupon_get_maximum_amount' ) as $ha_hook ) {
	add_filter( $ha_hook, 'ha_filter_price', 10 );
}

// Recalculate stored cart totals when the visitor switches currency.
add_action(
	'woocommerce_cart_loaded_from_session',
	function ( $cart ) {
		if ( ! WC()->session ) {
			return;
		}
		$cur = ha_currency_current();
		if ( WC()->session->get( 'ha_cur' ) !== $cur ) {
			WC()->session->set( 'ha_cur', $cur );
			$cart->calculate_totals();
		}
	}
);

// Cash on Delivery only where it makes sense (default: PKR / Pakistan).
add_filter(
	'woocommerce_available_payment_gateways',
	function ( $gateways ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $gateways;
		}
		$allowed = array_map( 'strtoupper', array_map( 'trim', explode( ',', (string) ha_opt( 'cod_currencies' ) ) ) );
		if ( isset( $gateways['cod'] ) && ! in_array( ha_currency_current(), $allowed, true ) ) {
			unset( $gateways['cod'] );
		}
		return $gateways;
	}
);

// Page caches (LiteSpeed on Hostinger) keep one copy per currency.
add_action(
	'send_headers',
	function () {
		if ( ! headers_sent() ) {
			header( 'X-LiteSpeed-Vary: cookie=' . HA_CUR_COOKIE, false );
		}
	}
);

/**
 * Header currency switcher.
 */
function ha_currency_switcher() {
	$list = ha_currency_list();
	if ( count( $list ) < 2 ) {
		return;
	}
	$cur = ha_currency_current();
	echo '<label class="cur-switch" title="' . esc_attr__( 'Currency', 'hide-atelier' ) . '">' . ha_icon( 'globe', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<span class="screen-reader-text">' . esc_html__( 'Currency', 'hide-atelier' ) . '</span><select class="js-currency">';
	foreach ( $list as $code ) {
		printf( '<option value="%1$s"%2$s>%1$s</option>', esc_attr( $code ), selected( $cur, $code, false ) );
	}
	echo '</select></label>';
}
