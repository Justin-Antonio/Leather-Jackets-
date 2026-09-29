<?php
/**
 * Site footer.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

/**
 * Footer menu column.
 *
 * @param string $location Menu location.
 * @param string $title    Column title.
 * @param array  $fallback slug => label.
 */
if ( ! function_exists( 'ha_footer_col' ) ) :
function ha_footer_col( $location, $title, $fallback ) {
	echo '<div class="footer__col"><h4>' . esc_html( $title ) . '</h4>';
	$locations = get_nav_menu_locations();
	if ( has_nav_menu( $location ) && wp_get_nav_menu_items( $locations[ $location ] ) ) {
		wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => 'footer__menu',
				'depth'          => 1,
			)
		);
	} else {
		echo '<ul class="footer__menu">';
		foreach ( $fallback as $slug => $label ) {
			echo '<li><a href="' . esc_url( ha_page_url( $slug ) ) . '">' . esc_html( $label ) . '</a></li>';
		}
		echo '</ul>';
	}
	echo '</div>';
}
endif;
?>
<footer class="site-footer">
	<div class="container footer__news">
		<div>
			<p class="eyebrow"><?php esc_html_e( 'Join the atelier', 'hide-atelier' ); ?></p>
			<h2 class="h2"><?php esc_html_e( 'New drops & members-only offers.', 'hide-atelier' ); ?></h2>
		</div>
		<form class="news-form" id="ha_news" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php echo ha_form_notice( 'ha_news' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<input type="hidden" name="action" value="ha_news">
			<?php wp_nonce_field( 'ha_news', 'ha_nonce' ); ?>
			<p class="hp" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></p>
			<label class="screen-reader-text" for="ha-news-email"><?php esc_html_e( 'Email', 'hide-atelier' ); ?></label>
			<input id="ha-news-email" type="email" name="email" placeholder="<?php esc_attr_e( 'Your email address', 'hide-atelier' ); ?>" required>
			<button class="btn btn--light" type="submit"><?php esc_html_e( 'Subscribe', 'hide-atelier' ); ?></button>
		</form>
	</div>

	<div class="container footer__grid">
		<div class="footer__brand">
			<?php ha_logo(); ?>
			<p><?php esc_html_e( 'Genuine leather jackets, cut and stitched by hand.', 'hide-atelier' ); ?></p>
			<ul class="footer__contact">
				<li><?php echo ha_icon( 'whatsapp', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><a href="<?php echo esc_url( ha_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo esc_html( ha_opt( 'store_phone' ) ); ?></a></li>
				<li><?php echo ha_icon( 'mail', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><a href="mailto:<?php echo esc_attr( ha_opt( 'store_email' ) ); ?>"><?php echo esc_html( ha_opt( 'store_email' ) ); ?></a></li>
			</ul>
			<div class="footer__social">
				<?php
				foreach ( array( 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok' ) as $k => $label ) {
					$url = ha_opt( 'social_' . $k );
					if ( $url ) {
						echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . '</a>';
					}
				}
				?>
			</div>
		</div>
		<?php
		ha_footer_col( 'footer_shop', __( 'Shop', 'hide-atelier' ), array( 'shop' => __( 'All jackets', 'hide-atelier' ) ) );
		ha_footer_col(
			'footer_help',
			__( 'Help', 'hide-atelier' ),
			array(
				'contact'    => __( 'Contact', 'hide-atelier' ),
				'size-guide' => __( 'Size guide', 'hide-atelier' ),
				'faq'        => __( 'FAQ', 'hide-atelier' ),
			)
		);
		ha_footer_col(
			'footer_legal',
			__( 'Policies', 'hide-atelier' ),
			array(
				'privacy-policy'  => __( 'Privacy policy', 'hide-atelier' ),
				'shipping-policy' => __( 'Shipping policy', 'hide-atelier' ),
				'refund-policy'   => __( 'Refund & return policy', 'hide-atelier' ),
				'terms'           => __( 'Terms & conditions', 'hide-atelier' ),
			)
		);
		?>
	</div>
	<div class="container footer__bottom">
		<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( ha_opt( 'store_name' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'hide-atelier' ); ?></span>
		<span class="pay-list"><?php esc_html_e( 'Secure checkout · Cash on Delivery across Pakistan', 'hide-atelier' ); ?></span>
	</div>
</footer>

<a class="wa-float" href="<?php echo esc_url( ha_whatsapp_url() ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Chat on WhatsApp', 'hide-atelier' ); ?>"><?php echo ha_icon( 'whatsapp', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
<?php wp_footer(); ?>
</body>
</html>
