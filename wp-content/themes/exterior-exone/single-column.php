<?php
/**
 * コラム詳細（投稿タイプ column の個別 /column/<投稿名>/）。
 *
 * カンプ: ボード 875:50（指示書き 875:47。精密なカンプは無い）/ 末尾の COLUMN は共通部品（支店 PC 612:869 / SP 641:1447）
 * 仕様書: docs/spec-20261010-column-page.md F-05・F-06（決定事項 docs/spec-20261010-column-page-decisions.md）
 * 構成要素は現サイトの詳細（docs/reference-20261010-column/single-column.php）と同じ。パンくずは付けない。
 *
 * カテゴリー・投稿日（・最終更新日）→ タイトル → アイキャッチ → 本文 → 前後の記事 → 一覧へ戻る → COLUMN。
 * 本文に埋め込まれた <style> は表示時に取り除き（inc/column-page-data.php）、見た目は css/column-page.css。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$exterior_exone_cold_texts = exterior_exone_column_detail_texts();
?>
<div class="p-cold">
	<?php
	while ( have_posts() ) :
		the_post();

		$exterior_exone_cold = exterior_exone_column_detail( get_post() );
		?>
		<article class="p-cold__article">
			<header class="p-cold__header">
				<div class="p-cold__meta">
					<?php if ( '' !== $exterior_exone_cold['cat'] ) : ?>
						<span class="p-cold__cat"><?php echo esc_html( $exterior_exone_cold['cat'] ); ?></span>
					<?php endif; ?>
					<time class="p-cold__date" datetime="<?php echo esc_attr( $exterior_exone_cold['datetime'] ); ?>"><?php echo esc_html( $exterior_exone_cold['date'] ); ?></time>
					<?php if ( '' !== $exterior_exone_cold['modified'] ) : ?>
						<span class="p-cold__date p-cold__date--updated">
							<?php echo esc_html( $exterior_exone_cold_texts['updated'] ); ?>
							<time datetime="<?php echo esc_attr( $exterior_exone_cold['mdatetime'] ); ?>"><?php echo esc_html( $exterior_exone_cold['modified'] ); ?></time>
						</span>
					<?php endif; ?>
				</div>
				<h1 class="p-cold__title"><?php echo esc_html( $exterior_exone_cold['title'] ); ?></h1>
			</header>

			<?php if ( $exterior_exone_cold['image_id'] ) : ?>
				<figure class="p-cold__eyecatch">
					<?php
					echo wp_get_attachment_image(
						$exterior_exone_cold['image_id'],
						'full',
						false,
						array(
							'class' => 'p-cold__eyecatch-img',
							'alt'   => $exterior_exone_cold['image_alt'],
							'sizes' => '(min-width: 1920px) 1400px, (min-width: 1025px) 73vw, 88vw',
						)
					);
					?>
				</figure>
			<?php endif; ?>

			<div class="p-cold__body">
				<?php the_content(); ?>
			</div>

			<?php if ( $exterior_exone_cold['prev'] || $exterior_exone_cold['next'] ) : ?>
				<nav class="p-cold__adjacent" aria-label="<?php echo esc_attr( $exterior_exone_cold_texts['adjacent_aria'] ); ?>">
					<?php
					foreach ( array( 'prev', 'next' ) as $exterior_exone_cold_dir ) :
						if ( ! $exterior_exone_cold[ $exterior_exone_cold_dir ] ) {
							continue;
						}
						?>
						<a
							class="p-cold__adjacent-link p-cold__adjacent-link--<?php echo esc_attr( $exterior_exone_cold_dir ); ?>"
							href="<?php echo esc_url( $exterior_exone_cold[ $exterior_exone_cold_dir ]['url'] ); ?>"
							rel="<?php echo esc_attr( $exterior_exone_cold_dir ); ?>"
						>
							<span class="p-cold__adjacent-label"><?php echo esc_html( $exterior_exone_cold_texts[ $exterior_exone_cold_dir ] ); ?></span>
							<span class="p-cold__adjacent-title"><?php echo esc_html( $exterior_exone_cold[ $exterior_exone_cold_dir ]['title'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>

			<div class="p-cold__back">
				<a class="p-cold__back-link" href="<?php echo esc_url( $exterior_exone_cold['back'] ); ?>"><?php echo esc_html( $exterior_exone_cold_texts['back'] ); ?></a>
			</div>
		</article>
	<?php endwhile; ?>
</div>
<?php
// COLUMN（共通部品。文言は WORKS と同じ。支店モードは支店の地域名入り）。
// カードはコラムの最新 3 件（表示中の記事を除く）。
$exterior_exone_cold_store = exterior_exone_store_mode();

get_template_part(
	'template-parts/common/section',
	'column',
	array(
		'column'  => $exterior_exone_cold_store ? exterior_exone_store_column( $exterior_exone_cold_store ) : exterior_exone_works_page_column(),
		'section' => 'column-latest',
		'exclude' => get_queried_object_id(),
	)
);

get_footer();
