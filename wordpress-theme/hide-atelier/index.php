<?php
/**
 * Fallback template (blog, archives, search).
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="site-main container page-pad">
	<header class="page-hero page-hero--sm">
		<h1 class="h1"><?php echo is_search() ? esc_html( sprintf( /* translators: %s query */ __( 'Search: %s', 'hide-atelier' ), get_search_query() ) ) : wp_kses_post( get_the_archive_title() ? get_the_archive_title() : __( 'Journal', 'hide-atelier' ) ); ?></h1>
	</header>
	<?php if ( have_posts() ) : ?>
		<div class="post-list">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'post-card' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>" class="post-card__img"><?php the_post_thumbnail( 'medium_large' ); ?></a>
					<?php endif; ?>
					<p class="eyebrow"><?php echo esc_html( get_the_date() ); ?></p>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php the_excerpt(); ?>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'hide-atelier' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
