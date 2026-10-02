<?php
/**
 * 支店: Quality（施工保証）。
 *
 * カンプ: PC 612:839（写真 612:106 1920x960 + 612:107 黒 30%）/ 612:499 英字 /
 *         612:498 和文見出し / 612:500 本文 3 行 / 612:583「施工保証 5年」バッジ
 *         SP 641:1251〜641:1261（暗幕も 30%。英字はカンプの「FLOW」を誤記とみなし PC の文言）
 *
 * 旧カンプにあった右下の保証注記は新カンプに無いので出さない（決定 A）。
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

	</div>
</section>
