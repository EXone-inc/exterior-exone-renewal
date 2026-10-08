<?php
/**
 * ハイエンド: Gallery（パンフレットのめくり・自動送り・タブ・クリック・進捗下線）。
 *
 * カンプ: PC 713:164（見出し 713:166・本文 713:169・pamphlet 713:170・gallery name 713:195・btn list 713:179）
 *         SP 733:380〜404 / guide 718:245
 *
 * サーバー出力は 01 の状態（左ページ 01・右ページ 02・キャプション 01・タブ 01 が押下・下線は全幅）。
 * JS 無効ではこのまま 01 だけを見せる。本（めくり・自動送り・クリック・進捗）は共通部品
 * template-parts/common/flipbook.php + js/flipbook.js、タブと文字の差し替えは js/highend-gallery.js。
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

$exterior_exone_hep_gal_items    = array_values( $exterior_exone_hep_gal['items'] );
$exterior_exone_hep_gal_first    = $exterior_exone_hep_gal_items[0];
$exterior_exone_hep_gal_book     = 'hep-gallery-book';
$exterior_exone_hep_gal_progress = 'hep-gallery-progress';
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

	<?php
	$exterior_exone_hep_gal_photos = array();

	foreach ( $exterior_exone_hep_gal_items as $exterior_exone_item ) {
		$exterior_exone_hep_gal_photos[] = array(
			'src'    => exterior_exone_highend_image( $exterior_exone_item['file'] ),
			'width'  => $exterior_exone_item['img'][0],
			'height' => $exterior_exone_item['img'][1],
			'alt'    => $exterior_exone_item['tab'] . 'の施工例',
		);
	}

	// 本は共通部品（template-parts/common/flipbook.php）。左ページを押すと右へめくれて次へ、
	// 右の見切れページを押すと左へめくれて前へ（ユーザー指示 2026-10-07）。
	get_template_part(
		'template-parts/common/flipbook',
		null,
		array(
			'id'       => $exterior_exone_hep_gal_book,
			'class'    => 'p-hepgal__book',
			'photos'   => $exterior_exone_hep_gal_photos,
			'loading'  => 'lazy',
			'right'    => 'prev',
			'progress' => $exterior_exone_hep_gal_progress,
			'num'      => $exterior_exone_hep_gal_first['num'],
		)
	);
	?>

	<div class="p-hepgal__caption">
		<div class="p-hepgal__name">
			<p class="p-hepgal__eng" data-hep-text="eng"><?php echo esc_html( $exterior_exone_hep_gal_first['eng'] ); ?></p>
			<p class="p-hepgal__copy" data-hep-text="copy"><?php echo esc_html( $exterior_exone_hep_gal_first['copy'] ); ?></p>
		</div>
		<span class="p-hepgal__progress" id="<?php echo esc_attr( $exterior_exone_hep_gal_progress ); ?>" aria-hidden="true"></span>
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
