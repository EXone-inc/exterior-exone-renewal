<?php
/**
 * WORKS: FV。一覧・詳細で共通。
 *
 * カンプ: PC 821:114（1920x1080・背景 821:116 / 821:118・821:117）・詳細 821:176
 *         SP 836:38・836:48・836:46（375x723。上 45 はヘッダーが重なる）
 *
 * 写真を全面に敷き、中央に「WORKS」と和文コピー。暗幕は無い。組版は PLANS の FV と同じ。
 *
 * $args: data（exterior_exone_works_page_fv()）/ title_tag（h1 | p。詳細は投稿タイトルが h1）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_wkp_fv = isset( $args['data'] ) ? $args['data'] : null;

if ( ! is_array( $exterior_exone_wkp_fv ) ) {
	return;
}

$exterior_exone_wkp_fv_tag = ( isset( $args['title_tag'] ) && 'p' === $args['title_tag'] ) ? 'p' : 'h1';
?>
<section class="p-wkpfv" data-section="works-fv">
	<div class="p-wkpfv__bg" aria-hidden="true">
		<img
			src="<?php echo esc_url( exterior_exone_works_page_image( $exterior_exone_wkp_fv['image'] ) ); ?>"
			width="<?php echo esc_attr( (string) $exterior_exone_wkp_fv['img'][0] ); ?>"
			height="<?php echo esc_attr( (string) $exterior_exone_wkp_fv['img'][1] ); ?>"
			alt=""
			fetchpriority="high"
		>
	</div>

	<div class="p-wkpfv__copy">
		<<?php echo esc_html( $exterior_exone_wkp_fv_tag ); ?> class="p-wkpfv__title"><?php echo esc_html( $exterior_exone_wkp_fv['title'] ); ?></<?php echo esc_html( $exterior_exone_wkp_fv_tag ); ?>>
		<p class="p-wkpfv__lead"><?php echo exterior_exone_highend_lines( $exterior_exone_wkp_fv['lead'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>
	</div>
</section>
