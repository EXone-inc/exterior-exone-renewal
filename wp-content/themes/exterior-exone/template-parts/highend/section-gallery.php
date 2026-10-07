<?php
/**
 * ハイエンド: Gallery（パンフレットのめくり・自動送り・タブ・クリック・進捗下線）。
 *
 * カンプ: PC 713:164（見出し 713:166・本文 713:169・pamphlet 713:170・gallery name 713:195・btn list 713:179）
 *         SP 733:380〜404 / guide 718:245
 *
 * サーバー出力は 01 の状態（左ページ 01・右ページ 02・キャプション 01・タブ 01 が押下・下線は全幅）。
 * JS 無効ではこのまま 01 だけを見せる。めくり・自動送り・タブ・クリックは js/highend-gallery.js。
 * 左右のページには 5 件すべての写真を重ねて置き、見せる 1 枚に is-current を付ける
 * （右ページは「次のカテゴリ」の写真を左ページと同じ幅で左揃えにして左端だけ見せる）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_hep_gal = isset( $args['data'] ) ? $args['data'] : null;

if ( ! is_array( $exterior_exone_hep_gal ) || empty( $exterior_exone_hep_gal['items'] ) ) {
	return;
}

$exterior_exone_hep_gal_items = array_values( $exterior_exone_hep_gal['items'] );
$exterior_exone_hep_gal_count = count( $exterior_exone_hep_gal_items );
$exterior_exone_hep_gal_first = $exterior_exone_hep_gal_items[0];
$exterior_exone_hep_gal_book  = 'hep-gallery-book';
?>
<section class="p-hepgal" data-section="highend-gallery">
	<div class="p-hepgal__inner">
		<?php
		get_template_part(
			'template-parts/highend/heading',
			null,
			array(
				'eng'     => $exterior_exone_hep_gal['eng'],
				'heading' => $exterior_exone_hep_gal['heading'],
			)
		);
		?>
		<p class="p-hepgal__body"><?php echo exterior_exone_highend_lines( $exterior_exone_hep_gal['body'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>
	</div>

	<div class="p-hepgal__book" id="<?php echo esc_attr( $exterior_exone_hep_gal_book ); ?>" data-hep-gallery>
		<div class="p-hepgal__page p-hepgal__page--left">
			<?php foreach ( $exterior_exone_hep_gal_items as $exterior_exone_index => $exterior_exone_item ) : ?>
				<img
					class="p-hepgal__photo<?php echo 0 === $exterior_exone_index ? ' is-current' : ''; ?>"
					src="<?php echo esc_url( exterior_exone_highend_image( $exterior_exone_item['file'] ) ); ?>"
					width="<?php echo esc_attr( (string) $exterior_exone_item['img'][0] ); ?>"
					height="<?php echo esc_attr( (string) $exterior_exone_item['img'][1] ); ?>"
					alt="<?php echo esc_attr( $exterior_exone_item['tab'] . 'の施工例' ); ?>"
					loading="lazy"
					decoding="async"
					<?php echo 0 === $exterior_exone_index ? '' : 'aria-hidden="true"'; ?>
				>
			<?php endforeach; ?>
			<span class="p-hepgal__fold p-hepgal__fold--left" aria-hidden="true"></span>
		</div>

		<div class="p-hepgal__page p-hepgal__page--right" aria-hidden="true">
			<?php foreach ( $exterior_exone_hep_gal_items as $exterior_exone_index => $exterior_exone_item ) : ?>
				<img
					class="p-hepgal__photo<?php echo 1 % $exterior_exone_hep_gal_count === $exterior_exone_index ? ' is-current' : ''; ?>"
					src="<?php echo esc_url( exterior_exone_highend_image( $exterior_exone_item['file'] ) ); ?>"
					width="<?php echo esc_attr( (string) $exterior_exone_item['img'][0] ); ?>"
					height="<?php echo esc_attr( (string) $exterior_exone_item['img'][1] ); ?>"
					alt=""
					loading="lazy"
					decoding="async"
				>
			<?php endforeach; ?>
			<span class="p-hepgal__fold p-hepgal__fold--right"></span>
		</div>

		<?php // めくりの落ち影と、めくれる 1 枚（短冊は JS が作る）。 ?>
		<span class="p-hepgal__cast" aria-hidden="true"></span>
		<div class="p-hepgal__leaf" aria-hidden="true"></div>

		<p class="p-hepgal__num" data-hep-text="num"><?php echo esc_html( $exterior_exone_hep_gal_first['num'] ); ?></p>

		<?php // 左ページを押すと右へめくれて次へ、右の見切れページを押すと左へめくれて前へ（ユーザー指示 2026-10-07）。 ?>
		<button class="p-hepgal__hit p-hepgal__hit--next" type="button" aria-label="次の写真へ" aria-controls="<?php echo esc_attr( $exterior_exone_hep_gal_book ); ?>"></button>
		<button class="p-hepgal__hit p-hepgal__hit--prev" type="button" aria-label="前の写真へ" aria-controls="<?php echo esc_attr( $exterior_exone_hep_gal_book ); ?>"></button>
	</div>

	<div class="p-hepgal__caption">
		<div class="p-hepgal__name">
			<p class="p-hepgal__eng" data-hep-text="eng"><?php echo esc_html( $exterior_exone_hep_gal_first['eng'] ); ?></p>
			<p class="p-hepgal__copy" data-hep-text="copy"><?php echo esc_html( $exterior_exone_hep_gal_first['copy'] ); ?></p>
		</div>
		<span class="p-hepgal__progress" aria-hidden="true"></span>
	</div>

	<div class="p-hepgal__tabs">
		<?php foreach ( $exterior_exone_hep_gal_items as $exterior_exone_index => $exterior_exone_item ) : ?>
			<button
				class="p-hepgal__tab"
				type="button"
				aria-pressed="<?php echo 0 === $exterior_exone_index ? 'true' : 'false'; ?>"
				aria-controls="<?php echo esc_attr( $exterior_exone_hep_gal_book ); ?>"
				data-num="<?php echo esc_attr( $exterior_exone_item['num'] ); ?>"
				data-eng="<?php echo esc_attr( $exterior_exone_item['eng'] ); ?>"
				data-copy="<?php echo esc_attr( $exterior_exone_item['copy'] ); ?>"
			><?php echo esc_html( $exterior_exone_item['tab'] ); ?></button>
		<?php endforeach; ?>
	</div>
</section>
