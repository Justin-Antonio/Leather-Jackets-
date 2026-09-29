<?php
/**
 * Product card in shop loops.
 *
 * @package HideAtelier
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
ha_product_card( $product );
