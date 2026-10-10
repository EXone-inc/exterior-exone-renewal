<?php
/**
 * CONTACT: FV（写真帯・英字・和文）。
 *
 * カンプ: PC 860:142（img bg 869:409 / img 864:390 / mask 864:405 / CONTACT 853:390 / お問い合わせ 853:389）
 *         SP 869:618（FV 869:619 / CONTACT 869:671 / お問い合わせ 869:669）
 *
 * 写真帯（暗幕 20% のベタ）の下端を「CONTACT」（#d9d9d9 のゴースト）がまたぐ。和文は地の上。
 * PC はヘッダーが写真に重なる（has-fv-header）。
 *
 * $args: data（exterior_exone_contact_fv()）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_ct_fv = isset( $args['data'] ) ? $args['data'] : null;

if ( ! is_array( $exterior_exone_ct_fv ) ) {
	return;
}
?>
<section class="p-contactfv" data-section="contact-fv">
	<div class="p-contactfv__bg" aria-hidden="true">
		<img
			src="<?php echo esc_url( exterior_exone_contact_image( $exterior_exone_ct_fv['image'] ) ); ?>"
			width="<?php echo esc_attr( (string) $exterior_exone_ct_fv['img'][0] ); ?>"
			height="<?php echo esc_attr( (string) $exterior_exone_ct_fv['img'][1] ); ?>"
			alt=""
			fetchpriority="high"
		>
	</div>

	<div class="p-contactfv__copy">
		<h1 class="p-contactfv__title"><?php echo esc_html( $exterior_exone_ct_fv['title'] ); ?></h1>
		<p class="p-contactfv__sub"><?php echo esc_html( $exterior_exone_ct_fv['sub'] ); ?></p>
	</div>
</section>
