<?php
/**
 * WooCommerce integration: product page layout, size buttons, extra product
 * fields, header mini-cart.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Wrappers & general
 * ---------------------------------------------------------------------- */


/**
 * Remove WooCommerce's default layout hooks once they are all registered.
 */
add_action(
	'init',
	function () {
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );

		// Summary column in the reference-design order.
		add_action( 'woocommerce_before_single_product_summary', 'ha_product_gallery', 20 );
		add_action( 'woocommerce_single_product_summary', 'ha_product_eyebrow', 3 );
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		add_action( 'woocommerce_single_product_summary', 'ha_product_subtitle', 6 );
		add_action( 'woocommerce_single_product_summary', 'ha_product_rating', 8 );
		add_action( 'woocommerce_single_product_summary', 'ha_product_price', 10 );
		add_action( 'woocommerce_single_product_summary', 'ha_product_features', 20 );
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		add_action( 'woocommerce_single_product_summary', 'ha_product_trust', 40 );
		add_action( 'woocommerce_single_product_summary', 'ha_product_note', 45 );
		add_action( 'woocommerce_single_product_summary', 'ha_product_whatsapp', 50 );
	},
	20
);

add_action(
	'woocommerce_before_main_content',
	function () {
		echo '<main id="main" class="site-main woo-main container">';
	},
	10
);
add_action(
	'woocommerce_after_main_content',
	function () {
		echo '</main>';
	},
	10
);

// Breadcrumb styling.
add_filter(
	'woocommerce_breadcrumb_defaults',
	function ( $d ) {
		$d['delimiter']   = '<span class="sep">/</span>';
		$d['wrap_before'] = '<nav class="breadcrumb" aria-label="Breadcrumb">';
		$d['wrap_after']  = '</nav>';
		return $d;
	}
);

add_filter( 'loop_shop_per_page', fn() => 12 );
add_filter( 'loop_shop_columns', fn() => 4 );
add_filter(
	'woocommerce_output_related_products_args',
	function ( $args ) {
		$args['posts_per_page'] = 4;
		$args['columns']        = 4;
		return $args;
	}
);

/* -------------------------------------------------------------------------
 * Product cards (shop / home / related)
 * ---------------------------------------------------------------------- */

/**
 * Card markup used by content-product.php and the home page.
 *
 * @param WC_Product $product Product.
 */
function ha_product_card( $product ) {
	if ( ! $product ) {
		return;
	}
	$id       = $product->get_id();
	$url      = get_permalink( $id );
	$cats     = wc_get_product_category_list( $id, ', ' );
	$badge    = get_post_meta( $id, '_ha_badge', true );
	$gallery  = $product->get_gallery_image_ids();
	$terms    = get_the_terms( $id, 'product_cat' );
	$slugs    = $terms && ! is_wp_error( $terms ) ? wp_list_pluck( $terms, 'slug' ) : array();
	if ( ! $badge && $product->is_on_sale() ) {
		$badge = __( 'Sale', 'hide-atelier' );
	}
	?>
	<article class="card" data-cats="<?php echo esc_attr( implode( ' ', $slugs ) ); ?>">
		<a class="card__media" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
			<?php if ( $badge ) : ?>
				<span class="badge"><?php echo esc_html( $badge ); ?></span>
			<?php endif; ?>
			<?php echo $product->get_image( 'ha-card', array( 'class' => 'card__img' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php
			if ( $gallery ) {
				echo wp_get_attachment_image( $gallery[0], 'ha-card', false, array( 'class' => 'card__img card__img--alt', 'loading' => 'lazy' ) );
			}
			?>
			<span class="card__cta"><?php esc_html_e( 'Choose size', 'hide-atelier' ); ?> <?php echo ha_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		</a>
		<div class="card__body">
			<p class="card__cat"><?php echo wp_kses_post( wp_strip_all_tags( $cats ) ); ?></p>
			<h3 class="card__title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
			<?php if ( $product->get_review_count() ) : ?>
				<div class="stars" aria-label="<?php echo esc_attr( sprintf( /* translators: %s rating */ __( 'Rated %s out of 5', 'hide-atelier' ), $product->get_average_rating() ) ); ?>">
					<span style="--r:<?php echo esc_attr( (float) $product->get_average_rating() ); ?>"></span><em>(<?php echo (int) $product->get_review_count(); ?>)</em>
				</div>
			<?php endif; ?>
			<div class="card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		</div>
	</article>
	<?php
}

/* -------------------------------------------------------------------------
 * Single product layout (matches the reference design)
 * ---------------------------------------------------------------------- */

// Rebuild the summary column in our own order (structured data at priority 60 is kept).


/**
 * Gallery: vertical thumbnails + large image with stock badge.
 */
function ha_product_gallery() {
	global $product;
	$ids = array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) );
	if ( ! $ids ) {
		echo '<div class="pgallery"><div class="pgallery__main">' . wc_placeholder_img( 'woocommerce_single' ) . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		return;
	}
	$in_stock = $product->is_in_stock();
	?>
	<div class="pgallery" data-gallery>
		<div class="pgallery__thumbs" role="tablist" aria-label="<?php esc_attr_e( 'Product images', 'hide-atelier' ); ?>">
			<?php foreach ( $ids as $i => $id ) : ?>
				<button type="button" class="pgallery__thumb<?php echo 0 === $i ? ' active' : ''; ?>" data-index="<?php echo (int) $i; ?>" role="tab" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>">
					<?php echo wp_get_attachment_image( $id, 'thumbnail', false, array( 'loading' => 'lazy' ) ); ?>
				</button>
			<?php endforeach; ?>
		</div>
		<div class="pgallery__main">
			<span class="stock-badge<?php echo $in_stock ? '' : ' out'; ?>"><?php echo $in_stock ? esc_html__( 'In stock', 'hide-atelier' ) : esc_html__( 'Out of stock', 'hide-atelier' ); ?></span>
			<div class="pgallery__track">
				<?php
				foreach ( $ids as $i => $id ) :
					$full = wp_get_attachment_image_src( $id, 'full' );
					?>
					<figure class="pgallery__slide" data-zoom="<?php echo esc_url( $full ? $full[0] : '' ); ?>">
						<?php
						echo wp_get_attachment_image(
							$id,
							'woocommerce_single',
							false,
							array(
								'loading'       => 0 === $i ? 'eager' : 'lazy',
								'fetchpriority' => 0 === $i ? 'high' : 'auto',
							)
						);
						?>
					</figure>
				<?php endforeach; ?>
			</div>
			<div class="pgallery__dots" aria-hidden="true">
				<?php foreach ( $ids as $i => $id ) : ?>
					<span class="<?php echo 0 === $i ? 'active' : ''; ?>"></span>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * "LEATHER JACKET · TOP-GRAIN SHEEP LEATHER"
 */
function ha_product_eyebrow() {
	global $product;
	$parts = array();
	$terms = get_the_terms( $product->get_id(), 'product_cat' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$parts[] = $terms[0]->name;
	}
	$material = get_post_meta( $product->get_id(), '_ha_material', true );
	if ( $material ) {
		$parts[] = $material;
	}
	if ( $parts ) {
		echo '<p class="pinfo__eyebrow">' . esc_html( implode( ' · ', $parts ) ) . '</p>';
	}
}

/**
 * Short tagline under the title.
 */
function ha_product_subtitle() {
	global $product;
	$sub = get_post_meta( $product->get_id(), '_ha_subtitle', true );
	if ( ! $sub ) {
		$sub = wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 18 );
	}
	if ( $sub ) {
		echo '<p class="pinfo__subtitle">' . esc_html( $sub ) . '</p>';
	}
}

/**
 * Stars + average + review link.
 */
function ha_product_rating() {
	global $product;
	if ( ! wc_review_ratings_enabled() ) {
		return;
	}
	$count = $product->get_review_count();
	$avg   = (float) $product->get_average_rating();
	echo '<div class="pinfo__rating">';
	echo '<span class="stars"><span style="--r:' . esc_attr( $avg ) . '"></span></span>';
	if ( $count ) {
		echo '<b>' . esc_html( number_format_i18n( $avg, 1 ) ) . '</b><span class="dot">·</span>';
		/* translators: %d review count */
		echo '<a href="#reviews" class="js-open-reviews">' . esc_html( sprintf( _n( '%d review', '%d reviews', $count, 'hide-atelier' ), $count ) ) . '</a>';
	} else {
		echo '<a href="#reviews" class="js-open-reviews">' . esc_html__( 'Be the first to review', 'hide-atelier' ) . '</a>';
	}
	echo '</div>';
}

/**
 * Large price + tax note.
 */
function ha_product_price() {
	global $product;
	echo '<div class="pinfo__price"><span class="price">' . wp_kses_post( $product->get_price_html() ) . '</span>';
	if ( ha_opt( 'taxes_note' ) ) {
		echo '<small>' . esc_html( ha_opt( 'taxes_note' ) ) . '</small>';
	}
	echo '</div>';
}

/**
 * Checklist of features (one per line in the product's "Jacket details" box).
 */
function ha_product_features() {
	global $product;
	$raw = (string) get_post_meta( $product->get_id(), '_ha_features', true );
	$lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) );
	if ( ! $lines ) {
		return;
	}
	echo '<ul class="pinfo__features">';
	foreach ( $lines as $line ) {
		echo '<li>' . ha_icon( 'check', 18 ) . '<span>' . esc_html( $line ) . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</ul>';
}

/**
 * Trust badges.
 */
function ha_product_trust() {
	$items = array(
		array( 'shield', __( '100% genuine leather', 'hide-atelier' ) ),
		/* translators: %s: amount */
		array( 'truck', sprintf( __( 'Free delivery over %s', 'hide-atelier' ), ha_price( ha_opt( 'free_ship_min' ) ) ) ),
		array( 'award', __( 'Lifetime stitching warranty', 'hide-atelier' ) ),
	);
	echo '<div class="pinfo__trust">';
	foreach ( $items as $it ) {
		echo '<div>' . ha_icon( $it[0], 30 ) . '<span>' . esc_html( $it[1] ) . '</span></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</div>';
}

/**
 * Delivery / made-to-order note.
 */
function ha_product_note() {
	global $product;
	$note = get_post_meta( $product->get_id(), '_ha_note', true );
	if ( ! $note ) {
		$note = ha_opt( 'product_note' );
	}
	if ( $note ) {
		echo '<div class="pinfo__note">' . ha_icon( 'clock', 20 ) . '<p>' . wp_kses_post( $note ) . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * WhatsApp link.
 */
function ha_product_whatsapp() {
	global $product;
	/* translators: %s product name */
	$text = sprintf( __( 'Hi! I have a question about: %s', 'hide-atelier' ), $product->get_name() . ' ' . get_permalink( $product->get_id() ) );
	echo '<a class="pinfo__wa" href="' . esc_url( ha_whatsapp_url( $text ) ) . '" target="_blank" rel="noopener">' . ha_icon( 'whatsapp', 20 ) . esc_html__( 'Questions? Chat with us on WhatsApp', 'hide-atelier' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

/* -------------------------------------------------------------------------
 * Size buttons for variable products
 * ---------------------------------------------------------------------- */

/**
 * Add pill buttons next to each variation <select>. The select stays in the
 * page (hidden) so WooCommerce's own variation script keeps working.
 */
add_filter(
	'woocommerce_dropdown_variation_attribute_options_html',
	function ( $html, $args ) {
		if ( empty( $args['options'] ) || empty( $args['product'] ) ) {
			return $html;
		}
		$attribute = $args['attribute'];
		$name      = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
		$is_size   = false !== stripos( wc_attribute_label( $attribute ), 'size' );
		$labels    = $is_size ? ha_size_labels() : array();
		$options   = $args['options'];
		$terms     = taxonomy_exists( $attribute ) ? wc_get_product_terms( $args['product']->get_id(), $attribute, array( 'fields' => 'all' ) ) : array();

		$buttons = '';
		$list    = array();
		if ( $terms ) {
			foreach ( $terms as $t ) {
				if ( in_array( $t->slug, $options, true ) ) {
					$list[ $t->slug ] = $t->name;
				}
			}
		} else {
			foreach ( $options as $o ) {
				$list[ $o ] = $o;
			}
		}
		foreach ( $list as $value => $label ) {
			$key   = strtoupper( trim( $label ) );
			$extra = isset( $labels[ $key ] ) ? ' ( ' . $labels[ $key ] . ' )' : '';
			$buttons .= sprintf(
				'<button type="button" class="swatch-btn" data-value="%1$s" data-label="%2$s">%3$s</button>',
				esc_attr( $value ),
				esc_attr( $label . $extra ),
				esc_html( $label . $extra )
			);
		}
		return '<div class="swatch-group" data-for="' . esc_attr( $name ) . '">' . $buttons . '</div><div class="swatch-select">' . $html . '</div>';
	},
	10,
	2
);

// Keep "Clear" link out of the way; the buttons make it obvious.
add_filter( 'woocommerce_reset_variations_link', fn() => '' );

/* -------------------------------------------------------------------------
 * Tabs: add Size chart + Shipping & returns
 * ---------------------------------------------------------------------- */

add_filter(
	'woocommerce_product_tabs',
	function ( $tabs ) {
		$tabs['ha_size'] = array(
			'title'    => __( 'Size chart', 'hide-atelier' ),
			'priority' => 25,
			'callback' => function () {
				echo '<div class="tab-size"><div class="tab-size__head"><h2>' . esc_html__( 'Size chart', 'hide-atelier' ) . '</h2>' . ha_unit_switch() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
				echo ha_size_chart( 'men' ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo '<p class="muted">' . esc_html__( 'Body measurements. Between two sizes? Choose the larger one for layering.', 'hide-atelier' ) . ' <a href="' . esc_url( ha_page_url( 'size-guide' ) ) . '">' . esc_html__( 'Full size guide & how to measure', 'hide-atelier' ) . '</a></p></div>';
			},
		);
		$tabs['ha_shipping'] = array(
			'title'    => __( 'Shipping & returns', 'hide-atelier' ),
			'priority' => 28,
			'callback' => function () {
				echo '<h2>' . esc_html__( 'Shipping & returns', 'hide-atelier' ) . '</h2><ul class="ticks">';
				/* translators: %s days */
				echo '<li>' . esc_html( sprintf( __( 'Dispatched within %s working days.', 'hide-atelier' ), ha_opt( 'dispatch_days' ) ) ) . '</li>';
				/* translators: %s days */
				echo '<li>' . esc_html( sprintf( __( 'Pakistan: %s working days. International: %s working days.', 'hide-atelier' ), ha_opt( 'domestic_days' ), ha_opt( 'international_days' ) ) ) . '</li>';
				/* translators: %s amount */
				echo '<li>' . esc_html( sprintf( __( 'Free delivery on orders over %s.', 'hide-atelier' ), ha_price( ha_opt( 'free_ship_min' ) ) ) ) . '</li>';
				/* translators: %d days */
				echo '<li>' . esc_html( sprintf( __( 'Easy %d-day exchange or return on unworn jackets.', 'hide-atelier' ), (int) ha_opt( 'return_days' ) ) ) . '</li></ul>';
				echo '<p><a href="' . esc_url( ha_page_url( 'shipping-policy' ) ) . '">' . esc_html__( 'Shipping policy', 'hide-atelier' ) . '</a> · <a href="' . esc_url( ha_page_url( 'refund-policy' ) ) . '">' . esc_html__( 'Refund policy', 'hide-atelier' ) . '</a></p>';
			},
		);
		if ( isset( $tabs['additional_information'] ) ) {
			unset( $tabs['additional_information'] );
		}
		return $tabs;
	},
	20
);

/* -------------------------------------------------------------------------
 * Admin: "Jacket details" fields on the product edit screen
 * ---------------------------------------------------------------------- */

add_action(
	'woocommerce_product_options_general_product_data',
	function () {
		echo '<div class="options_group"><p class="form-field"><strong>' . esc_html__( 'Jacket details (Hide Atelier theme)', 'hide-atelier' ) . '</strong></p>';
		woocommerce_wp_text_input(
			array(
				'id'          => '_ha_material',
				'label'       => __( 'Material line', 'hide-atelier' ),
				'placeholder' => __( 'Top-grain sheep leather', 'hide-atelier' ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => '_ha_subtitle',
				'label'       => __( 'Tagline under title', 'hide-atelier' ),
				'placeholder' => __( 'Five outer pockets and a high neck that closes against wind.', 'hide-atelier' ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => '_ha_badge',
				'label'       => __( 'Card badge', 'hide-atelier' ),
				'placeholder' => __( 'Bestseller / New / Limited', 'hide-atelier' ),
			)
		);
		woocommerce_wp_textarea_input(
			array(
				'id'          => '_ha_features',
				'label'       => __( 'Feature checklist (one per line)', 'hide-atelier' ),
				'rows'        => 5,
				'placeholder' => "High neck collar\nFive outer pockets and two inside",
			)
		);
		woocommerce_wp_textarea_input(
			array(
				'id'          => '_ha_note',
				'label'       => __( 'Delivery note (leave empty for default)', 'hide-atelier' ),
				'rows'        => 3,
			)
		);
		echo '</div>';
	}
);

add_action(
	'woocommerce_process_product_meta',
	function ( $post_id ) {
		// Nonce is verified by WooCommerce before this hook runs.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		foreach ( array( '_ha_material', '_ha_subtitle', '_ha_badge' ) as $k ) {
			if ( isset( $_POST[ $k ] ) ) {
				update_post_meta( $post_id, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
			}
		}
		if ( isset( $_POST['_ha_features'] ) ) {
			update_post_meta( $post_id, '_ha_features', sanitize_textarea_field( wp_unslash( $_POST['_ha_features'] ) ) );
		}
		if ( isset( $_POST['_ha_note'] ) ) {
			update_post_meta( $post_id, '_ha_note', wp_kses_post( wp_unslash( $_POST['_ha_note'] ) ) );
		}
		// phpcs:enable
	}
);

/* -------------------------------------------------------------------------
 * Header mini cart
 * ---------------------------------------------------------------------- */

/**
 * Number of items in the cart.
 */
function ha_cart_count() {
	return ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
}

// Keep the header count fresh after AJAX add-to-cart (shop archive buttons).
add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $fragments ) {
		$fragments['.js-cart-count'] = '<em class="js-cart-count' . ( ha_cart_count() ? ' show' : '' ) . '">' . ha_cart_count() . '</em>';
		ob_start();
		woocommerce_mini_cart();
		$fragments['div.widget_shopping_cart_content'] = '<div class="widget_shopping_cart_content">' . ob_get_clean() . '</div>';
		return $fragments;
	}
);

/* -------------------------------------------------------------------------
 * Checkout tweaks
 * ---------------------------------------------------------------------- */

// Phone required (courier companies in Pakistan need it).
add_filter(
	'woocommerce_billing_fields',
	function ( $fields ) {
		if ( isset( $fields['billing_phone'] ) ) {
			$fields['billing_phone']['required'] = true;
		}
		return $fields;
	}
);

// Show "Rs." instead of "₨" for Pakistani Rupee.
add_filter(
	'woocommerce_currency_symbol',
	function ( $symbol, $currency ) {
		return 'PKR' === $currency ? 'Rs.' : $symbol;
	},
	10,
	2
);

/* -------------------------------------------------------------------------
 * Shop page: category chips; product page: sticky add-to-cart (mobile)
 * ---------------------------------------------------------------------- */

add_action(
	'woocommerce_before_shop_loop',
	function () {
		$cats = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => 0,
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			)
		);
		if ( ! $cats || is_wp_error( $cats ) ) {
			return;
		}
		$current = is_product_category() ? get_queried_object_id() : 0;
		echo '<nav class="chips shop-chips" aria-label="' . esc_attr__( 'Categories', 'hide-atelier' ) . '">';
		echo '<a class="' . ( $current ? '' : 'active' ) . '" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'All', 'hide-atelier' ) . '</a>';
		foreach ( $cats as $c ) {
			echo '<a class="' . ( $current === $c->term_id ? 'active' : '' ) . '" href="' . esc_url( get_term_link( $c ) ) . '">' . esc_html( $c->name ) . '</a>';
		}
		echo '</nav>';
	},
	5
);

add_action(
	'woocommerce_after_single_product',
	function () {
		global $product;
		if ( ! $product || ! $product->is_purchasable() ) {
			return;
		}
		echo '<div class="sticky-atc" id="stickyAtc"><div class="sticky-atc__info"><b>' . esc_html( $product->get_name() ) . '</b><span>' . wp_kses_post( $product->get_price_html() ) . '</span></div><button type="button" class="btn btn--primary">' . esc_html__( 'Select size', 'hide-atelier' ) . '</button></div>';
	}
);
