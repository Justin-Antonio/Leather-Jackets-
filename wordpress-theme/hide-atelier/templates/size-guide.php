<?php
/**
 * Template Name: Size guide
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<main id="main" class="site-main page-pad">
		<div class="container">
			<header class="page-hero">
				<p class="eyebrow"><?php esc_html_e( 'Find your perfect fit', 'hide-atelier' ); ?></p>
				<h1 class="h1"><?php the_title(); ?></h1>
				<div class="entry-content lead"><?php the_content(); ?></div>
			</header>

			<div class="sg">
				<div class="sg__main" data-units>
					<div class="sg__bar">
						<div class="tabs" role="tablist">
							<button class="active" role="tab" data-tab="men"><?php esc_html_e( 'Men', 'hide-atelier' ); ?></button>
							<button role="tab" data-tab="women"><?php esc_html_e( 'Women', 'hide-atelier' ); ?></button>
							<button role="tab" data-tab="intl"><?php esc_html_e( 'International', 'hide-atelier' ); ?></button>
						</div>
						<?php echo ha_unit_switch(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
					<div class="sg__panel active" data-panel="men"><?php echo ha_size_chart( 'men' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					<div class="sg__panel" data-panel="women"><?php echo ha_size_chart( 'women' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					<div class="sg__panel" data-panel="intl"><?php echo ha_size_chart( 'intl' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					<p class="muted small"><?php esc_html_e( 'These are body measurements. Our jackets include room for a T-shirt or light knit underneath.', 'hide-atelier' ); ?></p>
				</div>

				<div class="sg__measure">
					<h2 class="h3"><?php esc_html_e( 'How to measure', 'hide-atelier' ); ?></h2>
					<div class="measure">
						<svg class="measure__fig" viewBox="0 0 220 260" aria-hidden="true">
							<path class="fig" d="M110 20c14 0 22 10 22 22s-8 24-22 24-22-12-22-24 8-22 22-22zM70 78c12-6 26-8 40-8s28 2 40 8l26 14c8 4 12 12 12 20v70c0 6-8 8-12 4l-14-60v126H58V126l-14 60c-4 4-12 2-12-4v-70c0-8 4-16 12-20z"/>
							<path class="m m1" d="M62 112h96"/><path class="m m2" d="M66 150h88"/><path class="m m3" d="M72 84h76"/><path class="m m4" d="M152 86l30 16v70"/><path class="m m5" d="M110 76v170"/>
							<text x="110" y="107">1</text><text x="110" y="146">2</text><text x="110" y="80">3</text><text x="178" y="130">4</text><text x="116" y="226">5</text>
						</svg>
						<ol class="measure__list">
							<li><b><?php esc_html_e( 'Chest', 'hide-atelier' ); ?></b> <?php esc_html_e( 'Around the fullest part of your chest, under the arms, keeping the tape level.', 'hide-atelier' ); ?></li>
							<li><b><?php esc_html_e( 'Waist', 'hide-atelier' ); ?></b> <?php esc_html_e( 'Around your natural waistline, just above the belly button.', 'hide-atelier' ); ?></li>
							<li><b><?php esc_html_e( 'Shoulder', 'hide-atelier' ); ?></b> <?php esc_html_e( 'From the edge of one shoulder to the other, across the back.', 'hide-atelier' ); ?></li>
							<li><b><?php esc_html_e( 'Sleeve', 'hide-atelier' ); ?></b> <?php esc_html_e( 'From the shoulder seam down to the wrist bone with the arm relaxed.', 'hide-atelier' ); ?></li>
							<li><b><?php esc_html_e( 'Length', 'hide-atelier' ); ?></b> <?php esc_html_e( 'From the base of the collar at the back down to where you want the jacket to end.', 'hide-atelier' ); ?></li>
						</ol>
					</div>
				</div>
			</div>

			<div class="sg-tips">
				<div class="tip"><?php echo ha_icon( 'ruler', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><h3><?php esc_html_e( 'Between two sizes?', 'hide-atelier' ); ?></h3><p><?php esc_html_e( 'Pick the larger size if you like to layer, the smaller one for a close biker fit.', 'hide-atelier' ); ?></p></div>
				<div class="tip"><?php echo ha_icon( 'clock', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><h3><?php esc_html_e( 'Leather relaxes', 'hide-atelier' ); ?></h3><p><?php esc_html_e( 'Genuine leather softens and moulds to your body within the first few weeks of wear.', 'hide-atelier' ); ?></p></div>
				<div class="tip"><?php echo ha_icon( 'whatsapp', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><h3><?php esc_html_e( 'Still unsure?', 'hide-atelier' ); ?></h3><p><?php esc_html_e( 'Send your height, weight and usual size on WhatsApp — we will recommend one.', 'hide-atelier' ); ?></p><a href="<?php echo esc_url( ha_whatsapp_url( __( 'Hi! Please help me choose my jacket size.', 'hide-atelier' ) ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Ask on WhatsApp', 'hide-atelier' ); ?> →</a></div>
			</div>
		</div>
	</main>
	<?php
endwhile;
get_footer();
