<?php
/**
 * Single blog post.
 *
 * @package HideAtelier
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<main id="main" class="site-main page-pad">
		<article class="container container--narrow">
			<header class="page-hero page-hero--sm">
				<p class="eyebrow"><?php echo esc_html( get_the_date() ); ?></p>
				<h1 class="h1"><?php the_title(); ?></h1>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="post-hero"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>
			<div class="entry-content"><?php the_content(); ?></div>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</article>
	</main>
	<?php
endwhile;
get_footer();
