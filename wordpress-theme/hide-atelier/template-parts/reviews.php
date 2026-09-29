<?php
/**
 * Real customer reviews from WooCommerce (4–5 stars). Hidden when there are none.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;

if ( ! ha_has_woo() ) {
	return;
}
$ha_reviews = get_comments(
	array(
		'post_type'  => 'product',
		'status'     => 'approve',
		'type'       => 'review',
		'number'     => 6,
		'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'key'     => 'rating',
				'value'   => 4,
				'compare' => '>=',
				'type'    => 'NUMERIC',
			),
		),
	)
);
if ( ! $ha_reviews ) {
	return;
}
?>
<section class="section reviews" id="reviews">
	<div class="container">
		<div class="section-head section-head--center">
			<p class="eyebrow reveal"><?php esc_html_e( 'Worn & loved', 'hide-atelier' ); ?></p>
			<h2 class="h2 reveal"><?php esc_html_e( 'What our customers', 'hide-atelier' ); ?> <em><?php esc_html_e( 'say.', 'hide-atelier' ); ?></em></h2>
		</div>
		<div class="slider reveal" data-slider>
			<div class="slider__track">
				<?php foreach ( $ha_reviews as $r ) : ?>
					<figure class="review">
						<div class="stars stars--lg"><span style="--r:<?php echo esc_attr( (int) get_comment_meta( $r->comment_ID, 'rating', true ) ); ?>"></span></div>
						<blockquote>“<?php echo esc_html( wp_trim_words( $r->comment_content, 40 ) ); ?>”</blockquote>
						<figcaption><span class="avatar"><?php echo esc_html( mb_substr( $r->comment_author, 0, 1 ) ); ?></span><div><b><?php echo esc_html( $r->comment_author ); ?></b><small><?php echo esc_html( get_the_title( $r->comment_post_ID ) ); ?></small></div></figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
			<div class="slider__dots"></div>
		</div>
	</div>
</section>
