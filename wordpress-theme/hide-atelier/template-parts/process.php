<?php
/**
 * Craft process + highlights.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

$ha_steps = array(
	array( __( 'Select', 'hide-atelier' ), __( 'Every hide is graded by hand. Only the soft, even pieces make the cut.', 'hide-atelier' ), '<path d="M8 38c6-2 8-10 16-10s10 8 16 10M10 14c4 4 24 4 28 0M24 6v22"/>' ),
	array( __( 'Cut', 'hide-atelier' ), __( 'Panels are laid to follow the grain so the jacket moves with you.', 'hide-atelier' ), '<circle cx="14" cy="34" r="6"/><circle cx="34" cy="34" r="6"/><path d="M18 30 38 6M30 30 10 6"/>' ),
	array( __( 'Stitch', 'hide-atelier' ), __( 'Lock stitches, double-rowed on every seam that takes strain.', 'hide-atelier' ), '<path d="M6 42 40 8M34 8h6v6"/><path d="M10 30c4 0 4 4 8 4s4-4 8-4" stroke-dasharray="3 3"/>' ),
	array( __( 'Finish', 'hide-atelier' ), __( 'Edges burnished, leather conditioned, every jacket inspected twice.', 'hide-atelier' ), '<path d="M24 4l5 11 12 1-9 8 3 12-11-6-11 6 3-12-9-8 12-1z"/>' ),
);
?>
<section class="section process" id="process">
	<div class="container">
		<div class="section-head section-head--center">
			<p class="eyebrow reveal"><?php esc_html_e( 'From hide to hero', 'hide-atelier' ); ?></p>
			<h2 class="h2 reveal"><?php esc_html_e( 'Crafted by hand.', 'hide-atelier' ); ?> <em><?php esc_html_e( 'Made to last.', 'hide-atelier' ); ?></em></h2>
		</div>
		<div class="process__wrap">
			<svg class="process__thread" viewBox="0 0 1200 220" preserveAspectRatio="none" aria-hidden="true">
				<path class="thread-dash js-thread" d="M0 110 C150 20 250 200 400 110 S650 20 800 110 S1050 200 1200 110" pathLength="1"/>
			</svg>
			<div class="process__steps">
				<?php foreach ( $ha_steps as $i => $s ) : ?>
					<article class="step reveal">
						<div class="step__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></div>
						<div class="step__ico"><svg viewBox="0 0 48 48" aria-hidden="true"><?php echo $s[2]; // phpcs:ignore WordPress.Security.EscapeOutput ?></svg></div>
						<h3><?php echo esc_html( $s[0] ); ?></h3>
						<p><?php echo esc_html( $s[1] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="stats">
			<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
				<div class="stat reveal"><b data-count="<?php echo esc_attr( ha_opt( "stat_{$i}_num" ) ); ?>" data-suffix="<?php echo esc_attr( ha_opt( "stat_{$i}_suffix" ) ); ?>"><?php echo esc_html( ha_opt( "stat_{$i}_num" ) . ha_opt( "stat_{$i}_suffix" ) ); ?></b><span><?php echo esc_html( ha_opt( "stat_{$i}_label" ) ); ?></span></div>
			<?php endfor; ?>
		</div>
	</div>
</section>
