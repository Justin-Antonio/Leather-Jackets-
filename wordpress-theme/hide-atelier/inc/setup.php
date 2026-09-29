<?php
/**
 * Theme supports, menus, image sizes, widgets.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'hide-atelier', HA_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 80,
				'width'       => 320,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);

		add_theme_support(
			'woocommerce',
			array(
				'thumbnail_image_width' => 600,
				'single_image_width'    => 1000,
				'product_grid'          => array(
					'default_columns' => 4,
					'min_columns'     => 2,
					'max_columns'     => 4,
				),
			)
		);
		// The theme ships its own lightweight gallery, so WooCommerce's zoom/slider/lightbox scripts are not loaded.

		register_nav_menus(
			array(
				'primary'       => __( 'Primary menu', 'hide-atelier' ),
				'footer_shop'   => __( 'Footer — Shop', 'hide-atelier' ),
				'footer_help'   => __( 'Footer — Help', 'hide-atelier' ),
				'footer_legal'  => __( 'Footer — Policies', 'hide-atelier' ),
			)
		);

		add_image_size( 'ha-card', 600, 750, false );
	}
);

add_action(
	'widgets_init',
	function () {
		register_sidebar(
			array(
				'name'          => __( 'Shop sidebar', 'hide-atelier' ),
				'id'            => 'shop',
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h4 class="widget__title">',
				'after_title'   => '</h4>',
			)
		);
	}
);

/**
 * Body classes: mark the dark hero home page and WooCommerce pages.
 */
add_filter(
	'body_class',
	function ( $classes ) {
		if ( is_front_page() ) {
			$classes[] = 'is-home';
		}
		if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
			$classes[] = 'is-woo';
		}
		return $classes;
	}
);

/**
 * Page template registration (templates live in /templates).
 */
add_filter(
	'theme_page_templates',
	function ( $templates ) {
		$templates['templates/policy.php']     = __( 'Policy page (with contents list)', 'hide-atelier' );
		$templates['templates/size-guide.php'] = __( 'Size guide', 'hide-atelier' );
		$templates['templates/contact.php']    = __( 'Contact', 'hide-atelier' );
		$templates['templates/about.php']      = __( 'About / Our craft', 'hide-atelier' );
		return $templates;
	}
);
