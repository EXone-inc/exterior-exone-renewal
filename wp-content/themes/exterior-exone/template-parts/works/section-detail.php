<?php
/**
 * WORKS 詳細: 見出し・パンフレット・名前・サムネ 3 枚・キャプション・進捗の下線・本文。
 *
 * カンプ: PC 821:181（見出し）/ 821:209（pamphlet）/ 821:218・821:221（名前）/ 821:226〜228（サムネ）
 *         / 821:223〜225（キャプション）/ 821:219（下線）/ 821:222（本文）
 *         SP 836:86・836:87 / 836:77 / 836:84 / 836:85 / 836:90 / 836:76
 *         guide 821:271（右からフェードイン・ハイエンドと同じ・重ね順前面・サムネ＆右ページで切替）/ 並び順の図 821:334
 *
 * 写真はギャラリーの 1〜4 枚目（循環）。左ページ = 現在 N、右ページ = N+1、サムネ = N+1 / N+2 / N+3。
 * サムネを押すとその写真が左ページに来るよう 1 回だけ右へめくる。右ページ・左ページを押すと次へ。
 * 本は共通部品（template-parts/common/flipbook.php + js/flipbook.js）、サムネの差し替えは js/works-detail.js。
 * サーバー出力は 1 枚目の状態。名前〜本文はめくれる紙より前面に置き、右からフェードインする。
 *
 * $args: data（exterior_exone_works_page_detail()）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_wkp_dt = isset( $args['data'] ) ? $args['data'] : null;

if ( ! is_array( $exterior_exone_wkp_dt ) ) {
	return;
}

$exterior_exone_wkp_dt_photos   = array_values( (array) $exterior_exone_wkp_dt['photos'] );
$exterior_exone_wkp_dt_count    = count( $exterior_exone_wkp_dt_photos );
$exterior_exone_wkp_dt_book     = 'works-detail-book';
$exterior_exone_wkp_dt_progress = 'works-detail-progress';
$exterior_exone_wkp_dt_thumbs   = min( 3, max( 0, $exterior_exone_wkp_dt_count - 1 ) );

// 左ページの写真の alt =「<投稿タイトル>（<キャプション>）」。1 枚目（キャプション無し）はタイトルだけ。
$exterior_exone_wkp_dt_book_photos = array();

foreach ( $exterior_exone_wkp_dt_photos as $exterior_exone_wkp_dt_photo ) {
	$exterior_exone_wkp_dt_book_photos[] = array(
		'attachment_id' => $exterior_exone_wkp_dt_photo['attachment_id'],
		'alt'           => '' === $exterior_exone_wkp_dt_photo['caption']
			? $exterior_exone_wkp_dt['title']
			: $exterior_exone_wkp_dt['title'] . '（' . $exterior_exone_wkp_dt_photo['caption'] . '）',
	);
}

/**
 * サムネの読み上げ名。
 *
 * @param string $caption キャプション。
 * @return string
 */
$exterior_exone_wkp_dt_label = function ( $caption ) {
	return '' === $caption ? 'メインの写真を表示' : $caption . 'を表示';
};

$exterior_exone_wkp_dt_paragraphs = array();

foreach ( (array) $exterior_exone_wkp_dt['paragraphs'] as $exterior_exone_wkp_dt_para ) {
	$exterior_exone_wkp_dt_paragraphs[] = '<p>' . implode( '<br>', array_map( 'esc_html', explode( "\n", $exterior_exone_wkp_dt_para ) ) ) . '</p>';
}
?>
<section class="p-wkpdetail" data-section="works-detail" data-works-detail>
	<?php
	get_template_part(
		'template-parts/works/heading',
		null,
		array(
			'eng'     => $exterior_exone_wkp_dt['eng'],
			'heading' => $exterior_exone_wkp_dt['title'],
			'tag'     => 'h1',
			'id'      => 'works-detail',
		)
	);

	get_template_part(
		'template-parts/common/flipbook',
		null,
		array(
			'id'       => $exterior_exone_wkp_dt_book,
			'class'    => 'p-wkpdetail__book',
			'photos'   => $exterior_exone_wkp_dt_book_photos,
			'size'     => 'full',
			'sizes'    => '(min-width: 1920px) 1173px, (min-width: 1025px) 62vw, 84vw',
			'right'    => 'next',
			'progress' => $exterior_exone_wkp_dt_progress,
		)
	);
	?>

	<div class="p-wkpdetail__meta">
		<div class="p-wkpdetail__row">
			<?php if ( '' !== $exterior_exone_wkp_dt['name'] ) : ?>
				<p class="p-wkpdetail__name" data-reveal="right"><?php echo esc_html( $exterior_exone_wkp_dt['name'] ); ?></p>
			<?php endif; ?>

			<?php if ( $exterior_exone_wkp_dt_thumbs ) : ?>
				<ul class="p-wkpdetail__thumbs" data-reveal="right">
					<?php for ( $exterior_exone_wkp_dt_slot = 1; $exterior_exone_wkp_dt_slot <= $exterior_exone_wkp_dt_thumbs; $exterior_exone_wkp_dt_slot++ ) : ?>
						<?php $exterior_exone_wkp_dt_on = $exterior_exone_wkp_dt_slot % $exterior_exone_wkp_dt_count; ?>
						<li class="p-wkpdetail__thumb-item" data-works-thumb-item>
							<button
								class="p-wkpdetail__thumb"
								type="button"
								aria-label="<?php echo esc_attr( $exterior_exone_wkp_dt_label( $exterior_exone_wkp_dt_photos[ $exterior_exone_wkp_dt_on ]['caption'] ) ); ?>"
								aria-controls="<?php echo esc_attr( $exterior_exone_wkp_dt_book ); ?>"
								data-works-thumb="<?php echo esc_attr( (string) $exterior_exone_wkp_dt_on ); ?>"
							>
								<?php foreach ( $exterior_exone_wkp_dt_photos as $exterior_exone_wkp_dt_index => $exterior_exone_wkp_dt_photo ) : ?>
									<span
										class="p-wkpdetail__thumb-photo<?php echo 3 === $exterior_exone_wkp_dt_index ? ' p-wkpdetail__thumb-photo--plan' : ''; ?><?php echo $exterior_exone_wkp_dt_on === $exterior_exone_wkp_dt_index ? ' is-current' : ''; ?>"
										data-caption="<?php echo esc_attr( $exterior_exone_wkp_dt_photo['caption'] ); ?>"
									>
										<?php
										echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP 生成の img タグ。
											$exterior_exone_wkp_dt_photo['attachment_id'],
											'large',
											false,
											array(
												'class'   => 'p-wkpdetail__thumb-img',
												'alt'     => '',
												'loading' => 'lazy',
												'sizes'   => '(min-width: 1920px) 211px, (min-width: 1025px) 11vw, 26vw',
											)
										);
										?>
									</span>
								<?php endforeach; ?>
							</button>
							<p class="p-wkpdetail__cap" data-works-thumb-cap><?php echo esc_html( $exterior_exone_wkp_dt_photos[ $exterior_exone_wkp_dt_on ]['caption'] ); ?></p>
						</li>
					<?php endfor; ?>
				</ul>
			<?php endif; ?>
		</div>

		<?php // 自動送りの進捗（js/flipbook.js が左から伸ばす。JS 無効・動きを減らす設定は全幅）。 ?>
		<div class="p-wkpdetail__line" data-reveal="right">
			<span class="p-wkpdetail__progress" id="<?php echo esc_attr( $exterior_exone_wkp_dt_progress ); ?>" aria-hidden="true"></span>
		</div>

		<?php if ( $exterior_exone_wkp_dt_paragraphs ) : ?>
			<div class="p-wkpdetail__body" data-reveal="right">
				<?php
				echo wp_kses( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 段落ごとに esc_html 済み。
					implode( '', $exterior_exone_wkp_dt_paragraphs ),
					array(
						'p'  => array(),
						'br' => array(),
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
