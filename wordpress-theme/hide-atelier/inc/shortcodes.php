<?php
/**
 * Shortcodes used inside the policy pages so store details stay in one place
 * (Appearance → Customize → Hide Atelier).
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

/**
 * [ha_store field="email"] — prints a store detail.
 */
add_shortcode(
	'ha_store',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'field' => 'name' ), $atts );
		$field = sanitize_key( $atts['field'] );
		switch ( $field ) {
			case 'name':
				return esc_html( ha_opt( 'store_name' ) );
			case 'email':
				$e = sanitize_email( ha_opt( 'store_email' ) );
				return '<a href="mailto:' . esc_attr( $e ) . '">' . esc_html( $e ) . '</a>';
			case 'phone':
				$p = (string) ha_opt( 'store_phone' );
				return '<a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $p ) ) . '">' . esc_html( $p ) . '</a>';
			case 'whatsapp':
				return '<a href="' . esc_url( ha_whatsapp_url() ) . '" target="_blank" rel="noopener">WhatsApp</a>';
			case 'address':
				return nl2br( esc_html( ha_opt( 'store_address' ) ) );
			case 'free_ship':
				return esc_html( ha_price( ha_opt( 'free_ship_min' ) ) );
			case 'return_days':
			case 'dispatch_days':
			case 'domestic_days':
			case 'international_days':
				return esc_html( ha_opt( $field ) );
			case 'site':
				return '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ) . '</a>';
			case 'date':
				return esc_html( date_i18n( get_option( 'date_format' ) ) );
		}
		return '';
	}
);

/**
 * Size chart data (inches). Centimetres are calculated in the browser.
 */
function ha_size_chart_data() {
	return array(
		'men'   => array(
			'title'   => __( "Men's jackets", 'hide-atelier' ),
			'columns' => array( __( 'Size', 'hide-atelier' ), __( 'Chest', 'hide-atelier' ), __( 'Waist', 'hide-atelier' ), __( 'Shoulder', 'hide-atelier' ), __( 'Sleeve', 'hide-atelier' ), __( 'Length', 'hide-atelier' ) ),
			'rows'    => array(
				array( 'XS', '34–36', '28–30', '16.5', '24', '24.5' ),
				array( 'S', '36–38', '30–32', '17', '24.5', '25' ),
				array( 'M', '38–40', '32–34', '17.75', '25', '26' ),
				array( 'L', '40–42', '34–36', '18.5', '25.5', '27' ),
				array( 'XL', '42–44', '36–38', '19.25', '26', '28' ),
				array( 'XXL', '44–46', '38–40', '20', '26.5', '29' ),
				array( 'XXXL', '46–48', '40–42', '20.75', '27', '30' ),
			),
		),
		'women' => array(
			'title'   => __( "Women's jackets", 'hide-atelier' ),
			'columns' => array( __( 'Size', 'hide-atelier' ), __( 'Bust', 'hide-atelier' ), __( 'Waist', 'hide-atelier' ), __( 'Hip', 'hide-atelier' ), __( 'Sleeve', 'hide-atelier' ), __( 'Length', 'hide-atelier' ) ),
			'rows'    => array(
				array( 'XS', '31–32', '24–25', '34–35', '23', '20' ),
				array( 'S', '33–34', '26–27', '36–37', '23.5', '20.5' ),
				array( 'M', '35–36', '28–29', '38–39', '24', '21' ),
				array( 'L', '37–39', '30–32', '40–42', '24.5', '21.5' ),
				array( 'XL', '40–42', '33–35', '43–45', '25', '22' ),
				array( 'XXL', '43–45', '36–38', '46–48', '25.5', '22.5' ),
			),
		),
		'intl'  => array(
			'title'   => __( 'International size conversion', 'hide-atelier' ),
			'columns' => array( __( 'Size', 'hide-atelier' ), 'US', 'UK', 'EU', __( 'Pakistan / India', 'hide-atelier' ) ),
			'rows'    => array(
				array( 'XS', '34', '34', '44', '34' ),
				array( 'S', '36', '36', '46', '36' ),
				array( 'M', '38', '38', '48', '38' ),
				array( 'L', '40', '40', '50', '40' ),
				array( 'XL', '42', '42', '52', '42' ),
				array( 'XXL', '44', '44', '54', '44' ),
				array( 'XXXL', '46', '46', '56', '46' ),
			),
			'nounits' => true,
		),
	);
}

/**
 * Render a size chart table.
 *
 * @param string $type men | women | intl.
 */
function ha_size_chart( $type = 'men' ) {
	$data = ha_size_chart_data();
	if ( ! isset( $data[ $type ] ) ) {
		return '';
	}
	$t   = $data[ $type ];
	$out = '<div class="size-table-wrap"><table class="size-table' . ( empty( $t['nounits'] ) ? ' has-units' : '' ) . '"><thead><tr>';
	foreach ( $t['columns'] as $c ) {
		$out .= '<th scope="col">' . esc_html( $c ) . '</th>';
	}
	$out .= '</tr></thead><tbody>';
	foreach ( $t['rows'] as $row ) {
		$out .= '<tr>';
		foreach ( $row as $i => $cell ) {
			$out .= 0 === $i ? '<th scope="row">' . esc_html( $cell ) . '</th>' : '<td data-in="' . esc_attr( $cell ) . '">' . esc_html( $cell ) . '</td>';
		}
		$out .= '</tr>';
	}
	return $out . '</tbody></table></div>';
}

/**
 * [ha_size_chart type="men"]
 */
add_shortcode(
	'ha_size_chart',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'type' => 'men' ), $atts );
		return ha_size_chart( sanitize_key( $atts['type'] ) );
	}
);

/**
 * Inch / cm switch.
 */
function ha_unit_switch() {
	return '<div class="unit-switch" role="group" aria-label="' . esc_attr__( 'Units', 'hide-atelier' ) . '"><button type="button" class="active" data-unit="in">' . esc_html__( 'Inches', 'hide-atelier' ) . '</button><button type="button" data-unit="cm">' . esc_html__( 'Centimetres', 'hide-atelier' ) . '</button></div>';
}
add_shortcode( 'ha_unit_switch', 'ha_unit_switch' );

/**
 * [ha_contact_form]
 */
function ha_contact_form() {
	ob_start();
	?>
	<form class="contact-form" id="ha_contact" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php echo ha_form_notice( 'ha_contact' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<input type="hidden" name="action" value="ha_contact">
		<?php wp_nonce_field( 'ha_contact', 'ha_nonce' ); ?>
		<p class="hp" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></p>
		<div class="row">
			<label><?php esc_html_e( 'Name', 'hide-atelier' ); ?><input name="name" required minlength="2" autocomplete="name"></label>
			<label><?php esc_html_e( 'Phone (optional)', 'hide-atelier' ); ?><input name="phone" autocomplete="tel"></label>
		</div>
		<label><?php esc_html_e( 'Email', 'hide-atelier' ); ?><input name="email" type="email" required autocomplete="email"></label>
		<label><?php esc_html_e( 'Message', 'hide-atelier' ); ?><textarea name="message" rows="5" required minlength="5"></textarea></label>
		<button class="btn btn--primary" type="submit"><?php esc_html_e( 'Send message', 'hide-atelier' ); ?></button>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'ha_contact_form', 'ha_contact_form' );
