<?php
/**
 * TOP: コピーセクション（全面写真 + キャッチコピー）。
 *
 * カンプ: PC 917:35-38（写真 + 黒 30% の暗幕）
 *         SP  917:747・917:808（暗幕なし）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="p-copy" data-section="copy">
	<picture class="p-copy__media">
		<source
			media="(max-width: 768px)"
			srcset="<?php echo esc_url( exterior_exone_top_image( 'dx-image-sp.jpg' ) ); ?>"
		>
		<img
			class="p-copy__image"
			src="<?php echo esc_url( exterior_exone_top_image( 'dx-image.jpg' ) ); ?>"
			alt=""
			loading="lazy"
		>
	</picture>

	<p class="c-copy p-copy__text">エクステリア×DXで、<br>理想の暮らしをカタチに。</p>
</section>
