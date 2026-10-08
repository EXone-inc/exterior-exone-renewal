<?php
/**
 * 共通部品: COLUMN（支店ページ・WORKS 一覧・詳細）。
 *
 * カンプ: 支店 PC 612:869（背景 #fefefe）/ 612:264「COLUMN」/ 612:629・612:628（見出し 3 点セット）/
 *         612:449 = カード 5 枚（373x226・ピッチ 410。5 枚目は右端で見切れる）/
 *         612:445 VIEW MORE
 *         SP 641:1447〜641:1459（背景 #f3f3f4、289x175 のカードを指で横にスクロール、VIEW MORE なし）
 *         WORKS PC 821:148・821:184 / SP 836:95（guide 821:266「支店ページと同じもの」）
 *
 * TOP の NEWS（template-parts/top/section-news.php）と同じ作りで、既存 CPT news の
 * 最新 5 件をそのまま出す（支店での絞り込みはしない）。1 件も無ければ出さない。
 * カンプで 5 枚目が見切れているのは横スクロールの表現（決定事項 Q5）。
 * CSS は css/column.css。リンクは exterior_exone_store_link() 経由（支店モードなら ?store= が付く）。
 *
 * TOP との差分（設計メモ §17）: 和文見出し + 本文 3 行が付く / カードの影が薄い /
 * ボタンの文字色が #4d4d4d。
 *
 * $args: column（必須。exterior_exone_store_column() と同じ形 {title, jp, lead[], lead_sp_join[]}）
 *        / section（data-section の値。既定 column）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_column = isset( $args['column'] ) && is_array( $args['column'] ) ? $args['column'] : null;

if ( ! $exterior_exone_column ) {
	return;
}

$exterior_exone_cards = exterior_exone_news_cards();

if ( ! $exterior_exone_cards ) {
	return;
}

$exterior_exone_column_section = empty( $args['section'] ) ? 'column' : (string) $args['section'];
$exterior_exone_archive        = exterior_exone_store_link( (string) get_post_type_archive_link( 'news' ) );
?>
<section class="p-scolumn" data-section="<?php echo esc_attr( $exterior_exone_column_section ); ?>">
	<h2 class="c-store-title"><?php echo esc_html( $exterior_exone_column['title'] ); ?></h2>
	<p class="c-store-jp"><?php echo esc_html( $exterior_exone_column['jp'] ); ?></p>

	<?php
	// SP（641:1448）でつなぐ行のあとの改行には印を付けて、SP の CSS で消す。
	$exterior_exone_lead_html = '';

	foreach ( $exterior_exone_column['lead'] as $exterior_exone_index => $exterior_exone_line ) {
		if ( $exterior_exone_index > 0 ) {
			$exterior_exone_lead_html .= in_array( $exterior_exone_index - 1, (array) $exterior_exone_column['lead_sp_join'], true ) ? '<br class="p-scolumn__br-pc">' : '<br>';
		}

		$exterior_exone_lead_html .= esc_html( $exterior_exone_line );
	}
	?>
	<p class="c-store-lead p-scolumn__lead">
		<?php echo wp_kses( $exterior_exone_lead_html, array( 'br' => array( 'class' => array() ) ) ); ?>
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
