<?php
/**
 * Hide Atelier theme bootstrap.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

define( 'HA_VERSION', '1.0.0' );
define( 'HA_DIR', get_template_directory() );
define( 'HA_URI', get_template_directory_uri() );

require HA_DIR . '/inc/helpers.php';
require HA_DIR . '/inc/setup.php';
require HA_DIR . '/inc/customizer.php';
require HA_DIR . '/inc/enqueue.php';
require HA_DIR . '/inc/performance.php';
require HA_DIR . '/inc/forms.php';
require HA_DIR . '/inc/shortcodes.php';
require HA_DIR . '/inc/demo-setup.php';

if ( class_exists( 'WooCommerce' ) ) {
	require HA_DIR . '/inc/woocommerce.php';
	require HA_DIR . '/inc/currency.php';
}
