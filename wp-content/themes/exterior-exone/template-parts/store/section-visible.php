<?php
/**
 * 支店: 見える安心。
 *
 * カンプ: PC 612:786 上部（#1a1a1a 背景）/ 612:255 線画 + 612:257・612:256 コピー /
 *         612:265 写真 4 枚 / 612:283 本文 5 行
 *         SP 641:175〜641:208（写真と文字の左右交互 4 段・本文 4 段落）
 *
 * 続く DX EXPERIENCE（section-dx.php = TOP の DX）と同じ黒背景の中に並ぶ。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_visible = exterior_exone_store_visible();
?>
<section class="p-svisible" data-section="store-visible">
	<div class="p-svisible__head">
		<?php
		// 612:255。コピーの背景に敷く線画。TOP 理念と同じ SVG を、画面に入ったら線描画する
		// （js/line-draw.js。guide 617:35「SVGアニメーション（薄め）」= opacity .3）。
		echo exterior_exone_inline_line_svg( $exterior_exone_visible['line'], 'p-svisible__sketch' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- テーマ同梱の SVG をヘルパー内で整形済み。
		?>

		<div class="p-svisible__copy">
			<p class="p-svisible__eyebrow"><?php echo esc_html( $exterior_exone_visible['eyebrow'] ); ?></p>
			<h2 class="p-svisible__heading"><?php echo esc_html( $exterior_exone_visible['heading'] ); ?></h2>
		</div>
	</div>

	<ul class="p-svisible__cards">
		<?php foreach ( $exterior_exone_visible['cards'] as $exterior_exone_card ) : ?>
			<li class="p-svisible__card">
				<?php // PC は写真の下端を黒に溶かし、その上に英字を置く（SP は写真の横に文字）。 ?>
				<div class="p-svisible__media">
					<img
						src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_card['image'] ) ); ?>"
						width="1200"
						height="800"
						alt=""
						loading="lazy"
					>
				</div>

				<p class="c-display p-svisible__card-eng"><?php echo esc_html( $exterior_exone_card['eng'] ); ?></p>
				<p class="p-svisible__card-label"><?php echo esc_html( $exterior_exone_card['label'] ); ?></p>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php // PC は 5 行（612:283）、SP は 2・3 行目をつないだ 4 段落（641:208）。 ?>
	<p class="p-svisible__lead p-svisible__lead--pc">
		<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_visible['lead'] ), array( 'br' => array() ) ); ?>
	</p>
	<p class="p-svisible__lead p-svisible__lead--sp">
		<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_visible['lead_sp'] ), array( 'br' => array() ) ); ?>
	</p>
</section>
