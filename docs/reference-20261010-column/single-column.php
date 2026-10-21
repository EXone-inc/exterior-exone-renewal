<?php
/*
Template Name: single-column
*/
?>

<?php get_header(); ?>

<div id="page_column_detail">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<?php
		$column_list_url = get_permalink( get_page_by_path( 'column' ) );
		$home_url        = home_url( '/' );
		$current_title   = get_the_title();
		$current_url     = get_permalink();
		$published_ymd   = get_the_date( 'Y-m-d' );
		$modified_ymd    = get_the_modified_date( 'Y-m-d' );
		$column_terms    = get_the_terms( get_the_ID(), 'column_category' );

		$breadcrumb_json = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => 'HOME',
					'item'     => $home_url,
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => 'コラム',
					'item'     => $column_list_url,
				),
				array(
					'@type'    => 'ListItem',
					'position' => 3,
					'name'     => $current_title,
					'item'     => $current_url,
				),
			),
		);
		?>

		<article class="cd_article" id="column_detail">
			<nav class="cd_breadcrumb" aria-label="パンくずリスト">
				<ol class="cd_breadcrumb_list">
					<li class="cd_breadcrumb_item">
						<a href="<?php echo esc_url( $home_url ); ?>" class="cd_breadcrumb_link">HOME</a>
					</li>
					<li class="cd_breadcrumb_item">
						<a href="<?php echo esc_url( $column_list_url ); ?>" class="cd_breadcrumb_link">コラム</a>
					</li>
					<li class="cd_breadcrumb_item cd_breadcrumb_item--current" aria-current="page">
						<span class="cd_breadcrumb_current"><?php echo esc_html( $current_title ); ?></span>
					</li>
				</ol>
			</nav>

			<script type="application/ld+json"><?php echo wp_json_encode( $breadcrumb_json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>

			<header class="cd_header">
				<div class="cd_meta">
					<?php if ( ! empty( $column_terms ) && ! is_wp_error( $column_terms ) ) : ?>
						<span class="cd_cat"><?php echo esc_html( $column_terms[0]->name ); ?></span>
					<?php endif; ?>
					<time class="cd_date" datetime="<?php echo esc_attr( $published_ymd ); ?>"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></time>
					<?php if ( $modified_ymd !== $published_ymd ) : ?>
						<time class="cd_date cd_date--updated" datetime="<?php echo esc_attr( $modified_ymd ); ?>">更新 <?php echo esc_html( get_the_modified_date( 'Y.m.d' ) ); ?></time>
					<?php endif; ?>
				</div>
				<h1 class="cd_title"><?php the_title(); ?></h1>
			</header>

			<?php if ( has_post_thumbnail() ) :
				$thumb_id  = get_post_thumbnail_id();
				$thumb_alt = trim( (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) );
				if ( $thumb_alt === '' ) {
					$thumb_alt = get_the_title();
				}
			?>
				<figure class="cd_eyecatch">
					<?php the_post_thumbnail( 'large', array( 'class' => 'cd_eyecatch_img', 'alt' => $thumb_alt ) ); ?>
				</figure>
			<?php endif; ?>

			<div class="cd_body">
				<?php the_content(); ?>
			</div>

			<nav class="cd_adjacent" aria-label="前後のコラム記事">
				<?php
				$prev_post = get_previous_post();
				$next_post = get_next_post();
				?>
				<?php if ( $prev_post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="cd_adjacent_link cd_adjacent_link--prev" rel="prev">
						<span class="cd_adjacent_label">前の記事</span>
						<span class="cd_adjacent_text"><?php echo esc_html( get_the_title( $prev_post->ID ) ); ?></span>
					</a>
				<?php else : ?>
					<span class="cd_adjacent_link cd_adjacent_link--prev is-disabled" aria-hidden="true"></span>
				<?php endif; ?>

				<?php if ( $next_post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="cd_adjacent_link cd_adjacent_link--next" rel="next">
						<span class="cd_adjacent_label">次の記事</span>
						<span class="cd_adjacent_text"><?php echo esc_html( get_the_title( $next_post->ID ) ); ?></span>
					</a>
				<?php else : ?>
					<span class="cd_adjacent_link cd_adjacent_link--next is-disabled" aria-hidden="true"></span>
				<?php endif; ?>
			</nav>

			<div class="cd_back">
				<a href="<?php echo esc_url( $column_list_url ); ?>" class="cd_back_link">コラム一覧へ戻る</a>
			</div>
		</article>

	<?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>
