<?php
/**
 * 企業情報: FV。
 *
 * カンプ: PC 917:1426-1440（1920x1080・写真 + 30% 暗幕に中央寄せの見出し）
 *         SP  917:927-940（375x677.81・同構成）
 *
 * 見出しは PC カンプでは "CAMPANY" と綴られているが、SP カンプ（917:1049）の
 * "COMPANY" が正しいためそちらを採用する。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="p-cfv" data-section="company-fv">
	<div class="p-cfv__bg" aria-hidden="true">
		<img
			src="<?php echo esc_url( exterior_exone_company_image( 'FV-bg.jpg' ) ); ?>"
			width="1920"
			height="1080"
			alt=""
			fetchpriority="high"
		>
	</div>

	<div class="p-cfv__copy">
		<h1 class="c-display c-display--hero p-cfv__title">COMPANY</h1>
		<p class="p-cfv__sub">企業情報</p>
	</div>
</section>
