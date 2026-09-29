<?php
/**
 * Customizer settings: Appearance → Customize → Hide Atelier.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'customize_register',
	function ( WP_Customize_Manager $wp ) {
		$wp->add_panel(
			'ha_panel',
			array(
				'title'    => __( 'Hide Atelier', 'hide-atelier' ),
				'priority' => 30,
			)
		);

		$sections = array(
			'ha_store'    => array(
				__( 'Store details', 'hide-atelier' ),
				array(
					'store_name'         => array( 'text', __( 'Store name', 'hide-atelier' ) ),
					'store_email'        => array( 'email', __( 'Support email', 'hide-atelier' ) ),
					'store_phone'        => array( 'text', __( 'Phone', 'hide-atelier' ) ),
					'whatsapp'           => array( 'text', __( 'WhatsApp number (country code, digits only, e.g. 923001234567)', 'hide-atelier' ) ),
					'store_address'      => array( 'textarea', __( 'Address', 'hide-atelier' ) ),
					'social_instagram'   => array( 'url', __( 'Instagram URL', 'hide-atelier' ) ),
					'social_facebook'    => array( 'url', __( 'Facebook URL', 'hide-atelier' ) ),
					'social_tiktok'      => array( 'url', __( 'TikTok URL', 'hide-atelier' ) ),
				),
			),
			'ha_policies' => array(
				__( 'Shipping & returns numbers', 'hide-atelier' ),
				array(
					'free_ship_min'      => array( 'number', __( 'Free delivery over (store base currency)', 'hide-atelier' ) ),
					'dispatch_days'      => array( 'text', __( 'Dispatch time (working days)', 'hide-atelier' ) ),
					'domestic_days'      => array( 'text', __( 'Delivery in Pakistan (working days)', 'hide-atelier' ) ),
					'international_days' => array( 'text', __( 'International delivery (working days)', 'hide-atelier' ) ),
					'return_days'        => array( 'number', __( 'Return window (days)', 'hide-atelier' ) ),
				),
			),
			'ha_hero'     => array(
				__( 'Home hero', 'hide-atelier' ),
				array(
					'hero_eyebrow' => array( 'text', __( 'Small heading', 'hide-atelier' ) ),
					'hero_title_1' => array( 'text', __( 'Title line 1', 'hide-atelier' ) ),
					'hero_title_2' => array( 'text', __( 'Title line 2 (italic)', 'hide-atelier' ) ),
					'hero_title_3' => array( 'text', __( 'Title line 3', 'hide-atelier' ) ),
					'hero_text'    => array( 'textarea', __( 'Intro text', 'hide-atelier' ) ),
					'stat_1_num'   => array( 'text', __( 'Highlight 1 — number', 'hide-atelier' ) ),
					'stat_1_suffix' => array( 'text', __( 'Highlight 1 — suffix', 'hide-atelier' ) ),
					'stat_1_label' => array( 'text', __( 'Highlight 1 — label', 'hide-atelier' ) ),
					'stat_2_num'   => array( 'text', __( 'Highlight 2 — number', 'hide-atelier' ) ),
					'stat_2_suffix' => array( 'text', __( 'Highlight 2 — suffix', 'hide-atelier' ) ),
					'stat_2_label' => array( 'text', __( 'Highlight 2 — label', 'hide-atelier' ) ),
					'stat_3_num'   => array( 'text', __( 'Highlight 3 — number', 'hide-atelier' ) ),
					'stat_3_suffix' => array( 'text', __( 'Highlight 3 — suffix', 'hide-atelier' ) ),
					'stat_3_label' => array( 'text', __( 'Highlight 3 — label', 'hide-atelier' ) ),
					'stat_4_num'   => array( 'text', __( 'Highlight 4 — number', 'hide-atelier' ) ),
					'stat_4_suffix' => array( 'text', __( 'Highlight 4 — suffix', 'hide-atelier' ) ),
					'stat_4_label' => array( 'text', __( 'Highlight 4 — label', 'hide-atelier' ) ),
				),
			),
			'ha_product'  => array(
				__( 'Product page', 'hide-atelier' ),
				array(
					'taxes_note'        => array( 'text', __( 'Text next to the price', 'hide-atelier' ) ),
					'product_note'      => array( 'textarea', __( 'Default delivery note (each product can override it)', 'hide-atelier' ) ),
					'size_chest_labels' => array( 'textarea', __( 'Size labels, one per line — e.g. S: Chest 36" - 38"', 'hide-atelier' ) ),
				),
			),
			'ha_currency' => array(
				__( 'Currencies', 'hide-atelier' ),
				array(
					'currencies'     => array( 'text', __( 'Currencies offered (comma separated ISO codes). The first one should be your WooCommerce store currency.', 'hide-atelier' ) ),
					'rates_auto'     => array( 'checkbox', __( 'Update exchange rates automatically every 12 hours', 'hide-atelier' ) ),
					'rates_manual'   => array( 'textarea', __( 'Manual rates (override), one per line: USD=0.0036 means 1 store-currency unit = 0.0036 USD', 'hide-atelier' ) ),
					'cod_currencies' => array( 'text', __( 'Cash on Delivery allowed only for these currencies', 'hide-atelier' ) ),
				),
			),
		);

		$priority = 10;
		foreach ( $sections as $section_id => $section ) {
			$wp->add_section(
				$section_id,
				array(
					'title'    => $section[0],
					'panel'    => 'ha_panel',
					'priority' => $priority++,
				)
			);
			foreach ( $section[1] as $key => $field ) {
				list( $type, $label ) = $field;
				$sanitize             = 'sanitize_text_field';
				if ( 'textarea' === $type ) {
					$sanitize = 'wp_kses_post';
				} elseif ( 'email' === $type ) {
					$sanitize = 'sanitize_email';
				} elseif ( 'url' === $type ) {
					$sanitize = 'esc_url_raw';
				} elseif ( 'number' === $type ) {
					$sanitize = 'ha_sanitize_number';
				} elseif ( 'checkbox' === $type ) {
					$sanitize = 'absint';
				}
				$defaults = ha_defaults();
				$wp->add_setting(
					'ha_' . $key,
					array(
						'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
						'sanitize_callback' => $sanitize,
					)
				);
				$wp->add_control(
					'ha_' . $key,
					array(
						'label'   => $label,
						'section' => $section_id,
						'type'    => $type,
					)
				);
			}
		}
	}
);

/**
 * Sanitize a positive number.
 *
 * @param mixed $v Value.
 */
function ha_sanitize_number( $v ) {
	return is_numeric( $v ) ? max( 0, (float) $v ) : 0;
}
