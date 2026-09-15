<?php
/**
 * 支店: COLUMN。
 *
 * カンプ: PC 639:417（白背景）/ 639:418「COLUMN」/ 687:997・687:996（見出し 3 点セット）/
 *         Group 479（1153:108）= カード 5 枚（373x226・ピッチ 410。5 枚目は右端で見切れる）/
 *         669:204 VIEW MORE
 *         SP カンプなし（TOP の NEWS と同じ横スクロール）
 *
 * TOP の NEWS（template-parts/top/section-news.php）と同じ作りで、既存 CPT news の
 * 最新 5 件をそのまま出す（支店での絞り込みはしない）。1 件も無ければ出さない。
 * カンプで 5 枚目が見切れているのは横スクロールの表現（決定事項 Q5）。
 *
 * TOP との差分（設計メモ §17）: 和文見出し + 本文 3 行が付く / カードの影が薄い /
 * ボタンの文字色が #4d4d4d。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_store = isset( $args['store'] ) ? $args['store'] : exterior_exone_current_store();

if ( ! $exterior_exone_store ) {
	return;
}

$exterior_exone_cards = exterior_exone_news_cards();

if ( ! $exterior_exone_cards ) {
	return;
}

$exterior_exone_column  = exterior_exone_store_column( $exterior_exone_store );
$exterior_exone_archive = get_post_type_archive_link( 'news' );
?>
<section class="p-scolumn" data-section="store-column">
	<h2 class="c-store-title"><?php echo esc_html( $exterior_exone_column['title'] ); ?></h2>
	<p class="c-store-jp"><?php echo esc_html( $exterior_exone_column['jp'] ); ?></p>

	<p class="c-store-lead p-scolumn__lead">
		<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_column['lead'] ), array( 'br' => array() ) ); ?>
	</p>

	<ul class="p-scolumn__list">
		<?php foreach ( $exterior_exone_cards as $exterior_exone_card ) : ?>
			<li class="p-scolumn__card">
				<a class="p-scolumn__card-link" href="<?php echo esc_url( $exterior_exone_card['url'] ); ?>">
					<?php if ( $exterior_exone_card['image_id'] ) : ?>
						<?php
						echo wp_get_attachment_image(
							$exterior_exone_card['image_id'],
							'medium_large',
							false,
							array(
								'class'   => 'p-scolumn__card-image',
								'alt'     => '',
								'loading' => 'lazy',
							)
						);
						?>
					<?php else : ?>
						<?php // 画像未設定（カンプ 1153:108 もグレーの塗り）。 ?>
						<span class="p-scolumn__card-image p-scolumn__card-image--empty" aria-hidden="true"></span>
					<?php endif; ?>

					<p class="p-scolumn__card-title"><?php echo esc_html( $exterior_exone_card['title'] ); ?></p>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php if ( $exterior_exone_archive ) : ?>
		<a class="c-btn c-btn--black p-scolumn__btn" href="<?php echo esc_url( $exterior_exone_archive ); ?>">VIEW MORE</a>
	<?php endif; ?>
</section>
