<?php
/**
 * One-click setup: Appearance → Hide Atelier Setup.
 * Creates pages & policies, menus, demo products (with sizes), shipping zones,
 * payment methods and sets the home page. Safe to run more than once.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

require_once HA_DIR . '/inc/demo-content.php';

add_action(
	'admin_menu',
	function () {
		add_theme_page( __( 'Hide Atelier Setup', 'hide-atelier' ), __( 'Hide Atelier Setup', 'hide-atelier' ), 'manage_options', 'ha-setup', 'ha_setup_page' );
	}
);

/**
 * Notice after activation.
 */
add_action(
	'admin_notices',
	function () {
		if ( ! current_user_can( 'manage_options' ) || get_option( 'ha_setup_done' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( $screen && 'appearance_page_ha-setup' === $screen->id ) {
			return;
		}
		echo '<div class="notice notice-info"><p><strong>Hide Atelier:</strong> ' . esc_html__( 'Finish setting up your store in one click — pages, policies, demo jackets and menus.', 'hide-atelier' ) . ' <a class="button button-primary" href="' . esc_url( admin_url( 'themes.php?page=ha-setup' ) ) . '">' . esc_html__( 'Run setup', 'hide-atelier' ) . '</a></p></div>';
	}
);

/**
 * Setup screen.
 */
function ha_setup_page() {
	$log = array();
	if ( isset( $_POST['ha_run_setup'] ) && check_admin_referer( 'ha_setup' ) && current_user_can( 'manage_options' ) ) {
		$opts = array(
			'products' => ! empty( $_POST['ha_products'] ),
			'store'    => ! empty( $_POST['ha_store'] ),
		);
		$log  = ha_run_setup( $opts );
	}
	$woo = ha_has_woo();
	?>
	<div class="wrap" style="max-width:760px">
		<h1><?php esc_html_e( 'Hide Atelier Setup', 'hide-atelier' ); ?></h1>
		<?php if ( $log ) : ?>
			<div class="notice notice-success"><p><strong><?php esc_html_e( 'Done!', 'hide-atelier' ); ?></strong></p><ul style="list-style:disc;padding-left:20px">
			<?php foreach ( $log as $line ) : ?>
				<li><?php echo esc_html( $line ); ?></li>
			<?php endforeach; ?>
			</ul><p><a class="button button-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'View your store', 'hide-atelier' ); ?></a> <a class="button" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=ha_panel' ) ); ?>"><?php esc_html_e( 'Edit store details', 'hide-atelier' ); ?></a></p></div>
		<?php endif; ?>

		<?php if ( ! $woo ) : ?>
			<div class="notice notice-warning inline"><p><strong><?php esc_html_e( 'Step 1: install WooCommerce first.', 'hide-atelier' ); ?></strong> <?php esc_html_e( 'The shop, cart, checkout and product pages need the free WooCommerce plugin.', 'hide-atelier' ); ?></p>
			<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ); ?>"><?php esc_html_e( 'Install WooCommerce', 'hide-atelier' ); ?></a></p></div>
		<?php endif; ?>

		<p><?php esc_html_e( 'This creates everything your store needs. Existing pages and products with the same names are not duplicated.', 'hide-atelier' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'ha_setup' ); ?>
			<ul>
				<li>✔ <?php esc_html_e( 'Pages: Home, Our Craft, Contact, Size Guide, FAQ', 'hide-atelier' ); ?></li>
				<li>✔ <?php esc_html_e( 'Policies: Privacy, Shipping, Refund & Return, Terms & Conditions', 'hide-atelier' ); ?></li>
				<li>✔ <?php esc_html_e( 'Header and footer menus, home page, pretty links', 'hide-atelier' ); ?></li>
			</ul>
			<p><label><input type="checkbox" name="ha_products" value="1" <?php checked( $woo ); ?> <?php disabled( ! $woo ); ?>> <?php esc_html_e( 'Import 5 demo jackets with photos and sizes S–XXXL (you can edit or delete them later)', 'hide-atelier' ); ?></label></p>
			<p><label><input type="checkbox" name="ha_store" value="1" <?php checked( $woo ); ?> <?php disabled( ! $woo ); ?>> <?php esc_html_e( 'Set store currency to PKR (Rs.), create shipping zones (Pakistan + worldwide) and enable Cash on Delivery & Bank Transfer', 'hide-atelier' ); ?></label></p>
			<p><button class="button button-primary button-hero" name="ha_run_setup" value="1"><?php esc_html_e( 'Run setup', 'hide-atelier' ); ?></button></p>
		</form>
	</div>
	<?php
}

/**
 * Create or find a page by slug.
 *
 * @param string $slug     Slug.
 * @param string $title    Title.
 * @param string $content  Content.
 * @param string $template Page template.
 * @return int Page id.
 */
function ha_ensure_page( $slug, $title, $content, $template = '' ) {
	$page = get_page_by_path( $slug );
	if ( $page && 'publish' !== $page->post_status ) {
		// e.g. the draft "Privacy Policy" WordPress creates on install: replace it with ours.
		wp_update_post(
			array(
				'ID'           => $page->ID,
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_content' => $content,
			)
		);
	}
	if ( $page ) {
		if ( $template ) {
			update_post_meta( $page->ID, '_wp_page_template', $template );
		}
		return $page->ID;
	}
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => $content,
		)
	);
	if ( $id && $template ) {
		update_post_meta( $id, '_wp_page_template', $template );
	}
	return (int) $id;
}

/**
 * Run the setup.
 *
 * @param array $opts Options.
 * @return string[] Log lines.
 */
function ha_run_setup( $opts ) {
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$log = array();

	// Pretty permalinks.
	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
		$log[] = 'Pretty links enabled (/%postname%/).';
	}

	// Pages.
	$ids = array();
	foreach ( ha_demo_pages() as $slug => $p ) {
		$ids[ $slug ] = ha_ensure_page( $slug, $p[0], $p[2], $p[1] );
	}
	$log[] = 'Pages & policies created: ' . count( $ids ) . '.';

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['home'] );
	update_option( 'wp_page_for_privacy_policy', $ids['privacy-policy'] );
	$log[] = 'Home page set.';

	if ( ha_has_woo() ) {
		update_option( 'woocommerce_terms_page_id', $ids['terms'] );
		// Make sure WooCommerce pages exist.
		if ( class_exists( 'WC_Install' ) ) {
			WC_Install::create_pages();
		}
	}

	if ( ha_has_woo() && ! empty( $opts['store'] ) ) {
		$log = array_merge( $log, ha_setup_store() );
	}
	if ( ha_has_woo() && ! empty( $opts['products'] ) ) {
		$log = array_merge( $log, ha_import_products() );
	}

	$log = array_merge( $log, ha_setup_menus( $ids ) );

	flush_rewrite_rules();
	update_option( 'ha_setup_done', 1 );
	return $log;
}

/**
 * Store settings: currency, shipping, payments.
 */
function ha_setup_store() {
	$log = array();
	update_option( 'woocommerce_currency', 'PKR' );
	update_option( 'woocommerce_currency_pos', 'left' );
	update_option( 'woocommerce_price_thousand_sep', ',' );
	update_option( 'woocommerce_price_decimal_sep', '.' );
	update_option( 'woocommerce_price_num_decimals', 0 );
	update_option( 'woocommerce_default_country', 'PK:PB' );
	delete_option( 'ha_fx_rates' );
	$log[] = 'Store currency: PKR (Rs.). Visitors abroad see their own currency automatically.';

	if ( class_exists( 'WC_Shipping_Zone' ) ) {
		$existing = array();
		foreach ( WC_Shipping_Zones::get_zones() as $z ) {
			$existing[] = $z['zone_name'];
		}
		if ( ! in_array( 'Pakistan', $existing, true ) ) {
			$zone = new WC_Shipping_Zone();
			$zone->set_zone_name( 'Pakistan' );
			$zone->set_zone_order( 1 );
			$zone->add_location( 'PK', 'country' );
			$zone->save();
			$free_id = $zone->add_shipping_method( 'free_shipping' );
			$flat_id = $zone->add_shipping_method( 'flat_rate' );
			update_option(
				'woocommerce_free_shipping_' . $free_id . '_settings',
				array(
					'title'      => 'Free delivery',
					'requires'   => 'min_amount',
					'min_amount' => (string) ha_opt( 'free_ship_min' ),
				)
			);
			update_option(
				'woocommerce_flat_rate_' . $flat_id . '_settings',
				array(
					'title'      => 'Standard delivery',
					'tax_status' => 'none',
					'cost'       => '250',
				)
			);
			$log[] = 'Shipping zone "Pakistan": free over ' . ha_opt( 'free_ship_min' ) . ', otherwise Rs. 250.';
		}
		// Rest of the world (zone 0).
		$world = new WC_Shipping_Zone( 0 );
		if ( ! $world->get_shipping_methods() ) {
			$id = $world->add_shipping_method( 'flat_rate' );
			update_option(
				'woocommerce_flat_rate_' . $id . '_settings',
				array(
					'title'      => 'International express',
					'tax_status' => 'none',
					'cost'       => '7000',
				)
			);
			$log[] = 'International shipping: Rs. 7,000 flat (converted to local currency). Change it in WooCommerce → Settings → Shipping.';
		}
	}

	$cod = (array) get_option( 'woocommerce_cod_settings', array() );
	update_option(
		'woocommerce_cod_settings',
		array_merge(
			$cod,
			array(
				'enabled'     => 'yes',
				'title'       => 'Cash on Delivery',
				'description' => 'Pay in cash when your jacket arrives (Pakistan only).',
			)
		)
	);
	$bacs = (array) get_option( 'woocommerce_bacs_settings', array() );
	update_option(
		'woocommerce_bacs_settings',
		array_merge(
			$bacs,
			array(
				'enabled'     => 'yes',
				'title'       => 'Bank transfer / JazzCash / Easypaisa',
				'description' => 'Transfer the total to our account; we dispatch once payment is received. Account details are emailed with your order.',
			)
		)
	);
	$log[] = 'Payments: Cash on Delivery (Pakistan) and Bank transfer enabled. Add your bank details in WooCommerce → Settings → Payments.';
	return $log;
}

/**
 * Copy a bundled image into the media library.
 *
 * @param string $file File in assets/images/products.
 * @param int    $parent Parent post.
 */
function ha_import_image( $file, $parent = 0 ) {
	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'meta_key'    => '_ha_demo_file', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $file, // phpcs:ignore WordPress.DB.SlowDBQuery
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}
	$src = HA_DIR . '/assets/images/products/' . $file;
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$upload = wp_upload_bits( $file, null, file_get_contents( $src ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}
	$type = wp_check_filetype( $upload['file'] );
	$id   = wp_insert_attachment(
		array(
			'post_mime_type' => $type['type'],
			'post_title'     => sanitize_text_field( pathinfo( $file, PATHINFO_FILENAME ) ),
			'post_status'    => 'inherit',
		),
		$upload['file'],
		$parent
	);
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	update_post_meta( $id, '_ha_demo_file', $file );
	return (int) $id;
}

/**
 * Get or create a product category.
 *
 * @param string $name Name.
 */
function ha_ensure_cat( $name ) {
	$t = get_term_by( 'name', $name, 'product_cat' );
	if ( $t ) {
		return (int) $t->term_id;
	}
	$r = wp_insert_term( $name, 'product_cat' );
	return is_wp_error( $r ) ? 0 : (int) $r['term_id'];
}

/**
 * Import the demo products as variable products with a Size attribute.
 */
function ha_import_products() {
	$sizes = array( 'S', 'M', 'L', 'XL', 'XXL', 'XXXL' );
	$count = 0;
	foreach ( ha_demo_products() as $i => $d ) {
		if ( wc_get_product_id_by_sku( $d['sku'] ) ) {
			continue;
		}
		$product = new WC_Product_Variable();
		$product->set_name( $d['name'] );
		$product->set_sku( $d['sku'] );
		$product->set_status( 'publish' );
		$product->set_featured( true );
		$product->set_menu_order( $i );
		$product->set_description( $d['desc'] );
		$product->set_short_description( $d['subtitle'] );
		$product->set_category_ids( array_filter( array( ha_ensure_cat( $d['cat'] ) ) ) );
		$product->set_reviews_allowed( true );

		$attr = new WC_Product_Attribute();
		$attr->set_name( 'Size' );
		$attr->set_options( $sizes );
		$attr->set_visible( true );
		$attr->set_variation( true );
		$product->set_attributes( array( $attr ) );
		$product->set_default_attributes( array( 'size' => 'S' ) );
		$product_id = $product->save();

		$img = ha_import_image( $d['image'], $product_id );
		if ( $img ) {
			$product->set_image_id( $img );
		}
		$gallery = array();
		foreach ( $d['gallery'] as $g ) {
			$gid = ha_import_image( $g, $product_id );
			if ( $gid ) {
				$gallery[] = $gid;
			}
		}
		$product->set_gallery_image_ids( $gallery );
		$product->save();

		foreach ( $sizes as $s ) {
			$v = new WC_Product_Variation();
			$v->set_parent_id( $product_id );
			$v->set_attributes( array( 'size' => $s ) );
			if ( ! empty( $d['regular'] ) ) {
				$v->set_regular_price( (string) $d['regular'] );
				$v->set_sale_price( (string) $d['price'] );
			} else {
				$v->set_regular_price( (string) $d['price'] );
			}
			$v->set_stock_status( 'instock' );
			$v->save();
		}
		WC_Product_Variable::sync( $product_id );

		update_post_meta( $product_id, '_ha_material', $d['material'] );
		update_post_meta( $product_id, '_ha_subtitle', $d['subtitle'] );
		update_post_meta( $product_id, '_ha_features', $d['features'] );
		if ( ! empty( $d['badge'] ) ) {
			update_post_meta( $product_id, '_ha_badge', $d['badge'] );
		}
		++$count;
	}
	return array( $count ? sprintf( '%d demo jackets imported with sizes S–XXXL.', $count ) : 'Demo jackets already exist — skipped.' );
}

/**
 * Menus.
 *
 * @param array $ids Page ids by slug.
 */
function ha_setup_menus( $ids ) {
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$menus     = array(
		'primary'      => array( 'Main menu', array( 'shop', 'about', 'size-guide', 'contact' ) ),
		'footer_shop'  => array( 'Footer shop', array( 'shop', 'cats' ) ),
		'footer_help'  => array( 'Footer help', array( 'contact', 'size-guide', 'faq', 'myaccount' ) ),
		'footer_legal' => array( 'Footer policies', array( 'privacy-policy', 'shipping-policy', 'refund-policy', 'terms' ) ),
	);
	foreach ( $menus as $loc => $m ) {
		if ( ! empty( $locations[ $loc ] ) && wp_get_nav_menu_object( $locations[ $loc ] ) ) {
			continue;
		}
		$existing = wp_get_nav_menu_object( $m[0] );
		$menu_id  = $existing ? $existing->term_id : wp_create_nav_menu( $m[0] );
		if ( is_wp_error( $menu_id ) ) {
			continue;
		}
		if ( ! $existing || ! wp_get_nav_menu_items( $menu_id ) ) {
			foreach ( $m[1] as $key ) {
				if ( 'cats' === $key ) {
					$terms = taxonomy_exists( 'product_cat' ) ? get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'exclude' => array( (int) get_option( 'default_product_cat' ) ) ) ) : array();
					foreach ( is_wp_error( $terms ) ? array() : $terms as $t ) {
						wp_update_nav_menu_item(
							$menu_id,
							0,
							array(
								'menu-item-title'     => $t->name,
								'menu-item-object'    => 'product_cat',
								'menu-item-object-id' => $t->term_id,
								'menu-item-type'      => 'taxonomy',
								'menu-item-status'    => 'publish',
							)
						);
					}
					continue;
				}
				$page_id = 0;
				if ( 'shop' === $key || 'myaccount' === $key ) {
					$page_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( $key ) : 0;
				} elseif ( isset( $ids[ $key ] ) ) {
					$page_id = $ids[ $key ];
				}
				if ( $page_id > 0 ) {
					wp_update_nav_menu_item(
						$menu_id,
						0,
						array(
							'menu-item-object'    => 'page',
							'menu-item-object-id' => $page_id,
							'menu-item-type'      => 'post_type',
							'menu-item-status'    => 'publish',
						)
					);
				}
			}
		}
		$locations[ $loc ] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );
	return array( 'Header and footer menus created.' );
}
