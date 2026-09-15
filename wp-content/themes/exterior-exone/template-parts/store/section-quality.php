<?php
/**
 * 支店: Quality（施工保証）。
 *
 * カンプ: PC 669:258（写真 1920x960）+ 669:257（黒 30% オーバーレイ）/
 *         669:261 英字 / 669:255 和文見出し / 669:263 本文 3 行 /
 *         669:969 + 669:967「施工保証 5年」バッジ / 669:265 右下の注記
 *         SP カンプなし（中央寄せのまま縦に詰める）
 *
 * FLOW の 7 行目（弧）がこのセクションの写真に食い込むので、上端の余白は
 * その分を見込んである（css/store.css の --st-flow-bleed）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_quality = exterior_exone_store_quality();
?>
<section class="p-squality" data-section="store-quality">
	<div class="p-squality__bg" aria-hidden="true">
		<img
			class="p-squality__bg-photo"
			src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_quality['image'] ) ); ?>"
			width="1774"
			height="886"
			alt=""
			loading="lazy"
		>
	</div>

	<div class="p-squality__inner">
		<p class="p-squality__eng"><?php echo esc_html( $exterior_exone_quality['eng'] ); ?></p>

		<h2 class="p-squality__heading"><?php echo esc_html( $exterior_exone_quality['heading'] ); ?></h2>

		<p class="p-squality__lead">
			<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_quality['lead'] ), array( 'br' => array() ) ); ?>
		</p>

		<p class="p-squality__badge">
			<span class="p-squality__badge-text"><?php echo esc_html( $exterior_exone_quality['badge']['label'] ); ?> <span class="p-squality__badge-number"><?php echo esc_html( $exterior_exone_quality['badge']['number'] ); ?></span><?php echo esc_html( $exterior_exone_quality['badge']['unit'] ); ?></span>
		</p>

		<p class="p-squality__note"><?php echo esc_html( $exterior_exone_quality['note'] ); ?></p>
	</div>
</section>
