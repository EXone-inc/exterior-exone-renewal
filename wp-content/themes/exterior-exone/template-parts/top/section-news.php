<?php
/**
 * TOP: NEWS セクション。
 *
 * 管理画面のお知らせ（news）から新しい 5 件を出す。出すのはタイトルと画像だけで、
 * 詳細（本文）はカードのリンク先＝お知らせの個別ページで読ませる。
 *
 * お知らせが 1 件も無いときはセクションごと出さない。
 *
 * カンプ: PC 917:93-112（カード 5 枚。右端は画面外へ続く）
 *         SP  917:877-884（横スクロール・ボタンなし）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_news = exterior_exone_news_cards();

if ( ! $exterior_exone_news ) {
	return;
}

$exterior_exone_news_archive = get_post_type_archive_link( 'news' );
?>
<section class="p-news" data-section="news">
	<h2 class="c-section-title p-news__title">NEWS</h2>

	<ul class="p-news__list">
		<?php foreach ( $exterior_exone_news as $exterior_exone_item ) : ?>
			<li class="p-news__card">
				<a class="p-news__card-link" href="<?php echo esc_url( $exterior_exone_item['url'] ); ?>">
					<?php if ( $exterior_exone_item['image_id'] ) : ?>
						<?php
						echo wp_get_attachment_image(
							$exterior_exone_item['image_id'],
							'medium_large',
							false,
							array(
								'class'   => 'p-news__card-image',
								'alt'     => '',
								'loading' => 'lazy',
							)
						);
						?>
					<?php else : ?>
						<?php // 画像未設定（カンプもグレーの塗り 917:111）。 ?>
						<span class="p-news__card-image p-news__card-image--empty" aria-hidden="true"></span>
					<?php endif; ?>

					<p class="p-news__card-title"><?php echo esc_html( $exterior_exone_item['title'] ); ?></p>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php if ( $exterior_exone_news_archive ) : ?>
		<a class="c-btn c-btn--black p-news__btn" href="<?php echo esc_url( $exterior_exone_news_archive ); ?>">VIEW MORE</a>
	<?php endif; ?>
</section>
