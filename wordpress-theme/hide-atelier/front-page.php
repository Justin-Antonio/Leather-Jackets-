<?php
/**
 * Home page.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

get_header();
$ha_hero = ha_hero_products();
$ha_first = $ha_hero ? $ha_hero[0] : null;
?>
<main id="main" class="site-main home">

	<!-- 3D stage (fixed behind hero + detail tour) -->
	<div class="stage" id="stage" aria-hidden="true">
		<?php if ( $ha_first ) : ?>
			<img class="stage__poster" id="stagePoster" src="<?php echo esc_url( $ha_first['image'] ); ?>" alt="" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<canvas id="webgl"></canvas>
	</div>
	<div class="hotspots" id="hotspots" aria-hidden="true">
		<div class="hotspot" data-key="hide"><i></i><div><b><?php esc_html_e( 'Genuine leather', 'hide-atelier' ); ?></b><small><?php esc_html_e( 'Soft, supple, ages beautifully', 'hide-atelier' ); ?></small></div></div>
		<div class="hotspot" data-key="stitch"><i></i><div><b><?php esc_html_e( 'Hand-finished seams', 'hide-atelier' ); ?></b><small><?php esc_html_e( 'Double-stitched where it matters', 'hide-atelier' ); ?></small></div></div>
		<div class="hotspot" data-key="hardware"><i></i><div><b><?php esc_html_e( 'Heavy-duty zips', 'hide-atelier' ); ?></b><small><?php esc_html_e( 'Smooth metal hardware', 'hide-atelier' ); ?></small></div></div>
		<div class="hotspot" data-key="collar"><i></i><div><b><?php esc_html_e( 'Tailored collar', 'hide-atelier' ); ?></b><small><?php esc_html_e( 'Shaped to sit close to the neck', 'hide-atelier' ); ?></small></div></div>
	</div>

	<section class="hero" id="hero">
		<div class="hero__content">
			<p class="eyebrow reveal-hero"><?php echo esc_html( ha_opt( 'hero_eyebrow' ) ); ?></p>
			<h1 class="hero__title">
				<span class="line"><span><?php echo esc_html( ha_opt( 'hero_title_1' ) ); ?></span></span>
				<span class="line"><span><em><?php echo esc_html( ha_opt( 'hero_title_2' ) ); ?></em></span></span>
				<span class="line line--sm"><span><?php echo esc_html( ha_opt( 'hero_title_3' ) ); ?></span></span>
			</h1>
			<p class="hero__sub reveal-hero"><?php echo esc_html( ha_opt( 'hero_text' ) ); ?></p>
			<div class="hero__cta reveal-hero">
				<a href="<?php echo esc_url( ha_page_url( 'shop' ) ); ?>" class="btn btn--primary"><?php esc_html_e( 'Shop the collection', 'hide-atelier' ); ?></a>
				<button class="btn btn--ghost" id="replayBtn" type="button"><?php echo ha_icon( 'replay', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Replay stitching', 'hide-atelier' ); ?></button>
			</div>
			<?php if ( count( $ha_hero ) > 1 ) : ?>
				<div class="picker reveal-hero">
					<div class="picker__row" id="picker" role="tablist" aria-label="<?php esc_attr_e( 'Choose a jacket', 'hide-atelier' ); ?>">
						<?php foreach ( $ha_hero as $i => $h ) : ?>
							<button type="button" class="picker__item<?php echo 0 === $i ? ' active' : ''; ?>" data-index="<?php echo (int) $i; ?>" role="tab" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( $h['name'] ); ?>">
								<img src="<?php echo esc_url( $h['thumb'] ); ?>" alt="" width="56" height="56" loading="lazy" decoding="async">
							</button>
						<?php endforeach; ?>
					</div>
					<a class="picker__info" id="pickerInfo" href="<?php echo esc_url( $ha_first['url'] ); ?>">
						<b id="pickerName"><?php echo esc_html( $ha_first['name'] ); ?></b>
						<span id="pickerPrice"><?php echo esc_html( $ha_first['price'] ); ?></span>
						<span class="picker__link"><?php esc_html_e( 'View jacket', 'hide-atelier' ); ?> <?php echo ha_icon( 'arrow', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					</a>
				</div>
			<?php endif; ?>
		</div>
		<div class="hero__hint reveal-hero"><span class="drag-ico"></span><?php esc_html_e( 'Drag to turn', 'hide-atelier' ); ?></div>
		<div class="scroll-cue"><span></span><?php esc_html_e( 'Scroll', 'hide-atelier' ); ?></div>
	</section>

	<section class="tour" id="tour">
		<div class="tour__panel">
			<p class="eyebrow"><?php esc_html_e( 'Up close', 'hide-atelier' ); ?></p>
			<h2 class="h2"><?php esc_html_e( 'Every detail,', 'hide-atelier' ); ?><br><em><?php esc_html_e( 'made to last.', 'hide-atelier' ); ?></em></h2>
			<ol class="tour__steps" id="tourSteps">
				<li class="active"><span>01</span><div><b><?php esc_html_e( 'The hide', 'hide-atelier' ); ?></b><p><?php esc_html_e( 'Hand-picked genuine leather, soft from day one and richer with every wear.', 'hide-atelier' ); ?></p></div></li>
				<li><span>02</span><div><b><?php esc_html_e( 'The stitch', 'hide-atelier' ); ?></b><p><?php esc_html_e( 'Bonded thread and double rows on every seam that works hard.', 'hide-atelier' ); ?></p></div></li>
				<li><span>03</span><div><b><?php esc_html_e( 'The hardware', 'hide-atelier' ); ?></b><p><?php esc_html_e( 'Heavy metal zips and snaps that glide for years.', 'hide-atelier' ); ?></p></div></li>
				<li><span>04</span><div><b><?php esc_html_e( 'The collar', 'hide-atelier' ); ?></b><p><?php esc_html_e( 'Shaped by hand to sit close and keep the wind out.', 'hide-atelier' ); ?></p></div></li>
			</ol>
			<div class="tour__progress"><span id="tourBar"></span></div>
		</div>
	</section>

	<div class="marquee" aria-hidden="true">
		<div class="marquee__track">
			<?php for ( $ha_i = 0; $ha_i < 2; $ha_i++ ) : ?>
				<span><?php esc_html_e( 'Genuine leather', 'hide-atelier' ); ?></span><i>✦</i><span><?php esc_html_e( 'Hand stitched', 'hide-atelier' ); ?></span><i>✦</i><span><?php esc_html_e( 'Cash on Delivery', 'hide-atelier' ); ?></span><i>✦</i><span><?php esc_html_e( 'Easy exchanges', 'hide-atelier' ); ?></span><i>✦</i><span><?php esc_html_e( 'Worldwide shipping', 'hide-atelier' ); ?></span><i>✦</i>
			<?php endfor; ?>
		</div>
	</div>

	<?php if ( ha_has_woo() ) : ?>
		<?php
		$ha_products = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => 8,
				'orderby' => 'menu_order',
				'order'   => 'ASC',
			)
		);
		$ha_cats     = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			)
		);
		?>
		<section class="section collection" id="collection">
			<div class="container">
				<div class="section-head">
					<div>
						<p class="eyebrow reveal"><?php esc_html_e( 'The collection', 'hide-atelier' ); ?></p>
						<h2 class="h2 reveal"><?php esc_html_e( 'Built to', 'hide-atelier' ); ?> <em><?php esc_html_e( 'age beautifully.', 'hide-atelier' ); ?></em></h2>
					</div>
					<?php if ( $ha_cats && ! is_wp_error( $ha_cats ) && count( $ha_cats ) > 1 ) : ?>
						<div class="chips reveal" id="filters">
							<button class="active" data-filter="all"><?php esc_html_e( 'All', 'hide-atelier' ); ?></button>
							<?php foreach ( $ha_cats as $c ) : ?>
								<button data-filter="<?php echo esc_attr( $c->slug ); ?>"><?php echo esc_html( $c->name ); ?></button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( $ha_products ) : ?>
					<div class="grid" id="homeGrid">
						<?php
						foreach ( $ha_products as $p ) {
							ha_product_card( $p );
						}
						?>
					</div>
				<?php else : ?>
					<p class="muted"><?php esc_html_e( 'Add products in WooCommerce → Products, or run Appearance → Hide Atelier Setup to import demo jackets.', 'hide-atelier' ); ?></p>
				<?php endif; ?>
				<p class="center"><a class="btn btn--ghost" href="<?php echo esc_url( ha_page_url( 'shop' ) ); ?>"><?php esc_html_e( 'View all jackets', 'hide-atelier' ); ?></a></p>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/process' ); ?>

	<section class="section banner">
		<div class="container banner__grid">
			<div class="banner__img reveal">
				<img src="<?php echo esc_url( HA_URI . '/assets/images/products/tobacco-cafe-racer.webp' ); ?>" alt="<?php esc_attr_e( 'Leather café racer jacket with plaid lining', 'hide-atelier' ); ?>" loading="lazy" decoding="async" width="500" height="640">
			</div>
			<div class="banner__txt">
				<p class="eyebrow reveal"><?php esc_html_e( 'Find your fit', 'hide-atelier' ); ?></p>
				<h2 class="h2 reveal"><?php esc_html_e( 'Your size.', 'hide-atelier' ); ?> <em><?php esc_html_e( 'Your story.', 'hide-atelier' ); ?></em></h2>
				<p class="reveal"><?php esc_html_e( 'Not sure about your size? Check our size guide, or send us your height and weight on WhatsApp and we will recommend the perfect fit.', 'hide-atelier' ); ?></p>
				<ul class="ticks reveal">
					<li><?php esc_html_e( 'Sizes S to XXXL', 'hide-atelier' ); ?></li>
					<?php /* translators: %d days */ ?>
					<li><?php echo esc_html( sprintf( __( 'Free size exchange within %d days', 'hide-atelier' ), (int) ha_opt( 'return_days' ) ) ); ?></li>
					<li><?php esc_html_e( 'Personal help on WhatsApp', 'hide-atelier' ); ?></li>
				</ul>
				<div class="btn-row reveal">
					<a class="btn btn--primary" href="<?php echo esc_url( ha_page_url( 'size-guide' ) ); ?>"><?php echo ha_icon( 'ruler', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Size guide', 'hide-atelier' ); ?></a>
					<a class="btn btn--ghost" href="<?php echo esc_url( ha_whatsapp_url( __( 'Hi! Please help me choose my jacket size.', 'hide-atelier' ) ) ); ?>" target="_blank" rel="noopener"><?php echo ha_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>WhatsApp</a>
				</div>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/reviews' ); ?>
</main>
<?php
get_footer();
