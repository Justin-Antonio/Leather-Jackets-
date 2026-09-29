<?php
/**
 * Contact & newsletter forms (no plugin needed). Messages are emailed to the
 * store email and newsletter sign-ups are listed under Tools → Newsletter.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

/**
 * Redirect back to the form with a status flag.
 *
 * @param string $status Status key.
 * @param string $anchor Anchor id.
 */
function ha_form_redirect( $status, $anchor ) {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( array( 'ha_contact', 'ha_news' ), $back );
	wp_safe_redirect( add_query_arg( $anchor, $status, $back ) . '#' . $anchor );
	exit;
}

/**
 * Contact form.
 */
function ha_handle_contact() {
	if ( ! isset( $_POST['ha_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ha_nonce'] ), 'ha_contact' ) ) {
		ha_form_redirect( 'error', 'ha_contact' );
	}
	// Honeypot: bots fill every field.
	if ( ! empty( $_POST['website'] ) ) {
		ha_form_redirect( 'sent', 'ha_contact' );
	}
	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( strlen( $name ) < 2 || ! is_email( $email ) || strlen( $message ) < 5 ) {
		ha_form_redirect( 'invalid', 'ha_contact' );
	}

	$to      = ha_opt( 'store_email' ) ? ha_opt( 'store_email' ) : get_option( 'admin_email' );
	/* translators: %s: customer name */
	$subject = sprintf( __( 'New message from %s', 'hide-atelier' ), $name );
	$body    = "Name: $name\nEmail: $email\nPhone: $phone\n\n$message";
	$sent    = wp_mail( $to, $subject, $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );

	ha_form_redirect( $sent ? 'sent' : 'mailfail', 'ha_contact' );
}
add_action( 'admin_post_nopriv_ha_contact', 'ha_handle_contact' );
add_action( 'admin_post_ha_contact', 'ha_handle_contact' );

/**
 * Newsletter sign-up.
 */
function ha_handle_newsletter() {
	if ( ! isset( $_POST['ha_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ha_nonce'] ), 'ha_news' ) ) {
		ha_form_redirect( 'error', 'ha_news' );
	}
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) || ! empty( $_POST['website'] ) ) {
		ha_form_redirect( 'invalid', 'ha_news' );
	}
	$list = get_option( 'ha_newsletter', array() );
	if ( ! isset( $list[ $email ] ) ) {
		$list[ $email ] = current_time( 'mysql' );
		update_option( 'ha_newsletter', $list, false );
	}
	ha_form_redirect( 'joined', 'ha_news' );
}
add_action( 'admin_post_nopriv_ha_news', 'ha_handle_newsletter' );
add_action( 'admin_post_ha_news', 'ha_handle_newsletter' );

/**
 * Status message for a form.
 *
 * @param string $key ha_contact | ha_news.
 */
function ha_form_notice( $key ) {
	if ( empty( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return '';
	}
	$status   = sanitize_key( $_GET[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$messages = array(
		'sent'     => array( 'ok', __( 'Thank you! We will reply within 24 hours.', 'hide-atelier' ) ),
		'joined'   => array( 'ok', __( 'You are on the list. Welcome to the atelier!', 'hide-atelier' ) ),
		'invalid'  => array( 'err', __( 'Please check the form and try again.', 'hide-atelier' ) ),
		'mailfail' => array( 'err', __( 'Your message could not be sent. Please contact us on WhatsApp.', 'hide-atelier' ) ),
		'error'    => array( 'err', __( 'Session expired. Please try again.', 'hide-atelier' ) ),
	);
	if ( ! isset( $messages[ $status ] ) ) {
		return '';
	}
	return '<p class="form-msg ' . esc_attr( $messages[ $status ][0] ) . '" role="status">' . esc_html( $messages[ $status ][1] ) . '</p>';
}

/**
 * Tools → Newsletter: list and export sign-ups.
 */
add_action(
	'admin_menu',
	function () {
		add_management_page(
			__( 'Newsletter sign-ups', 'hide-atelier' ),
			__( 'Newsletter', 'hide-atelier' ),
			'manage_options',
			'ha-newsletter',
			function () {
				$list = get_option( 'ha_newsletter', array() );
				echo '<div class="wrap"><h1>' . esc_html__( 'Newsletter sign-ups', 'hide-atelier' ) . '</h1>';
				if ( ! $list ) {
					echo '<p>' . esc_html__( 'No sign-ups yet.', 'hide-atelier' ) . '</p></div>';
					return;
				}
				echo '<p>' . esc_html__( 'Copy these emails into your email marketing tool.', 'hide-atelier' ) . '</p>';
				echo '<textarea readonly rows="8" style="width:100%;max-width:640px">' . esc_textarea( implode( ', ', array_keys( $list ) ) ) . '</textarea>';
				echo '<table class="widefat striped" style="max-width:640px;margin-top:16px"><thead><tr><th>Email</th><th>Date</th></tr></thead><tbody>';
				foreach ( array_reverse( $list, true ) as $email => $date ) {
					echo '<tr><td>' . esc_html( $email ) . '</td><td>' . esc_html( $date ) . '</td></tr>';
				}
				echo '</tbody></table></div>';
			}
		);
	}
);
