<?php
/**
 * TOP: コピーセクション（全面の背景動画 + キャッチコピー）。
 *
 * 背景は PC / SP で縦横比の違う動画を出し分ける（PC 1920x1080・SP 1080x1920）。
 * どちらを読むかは js/copy-video.js が幅を見て決めるので、通信は 1 本ぶんで済む。
 *
 * 下に敷いてある画像は、動画が読めるまでの間と JS 無効時の受け皿。
 * 動画が再生できたら CSS で上にかぶせる。
 *
 * カンプ: PC 917:35-38（黒 30% の暗幕）
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

	<?php // src は JS が幅に応じて入れる（両方読み込まないようにするため）。 ?>
	<video
		class="p-copy__video"
		data-copy-video
		data-src-pc="<?php echo esc_url( exterior_exone_top_video( 'p-copy-pc.mp4' ) ); ?>"
		data-src-sp="<?php echo esc_url( exterior_exone_top_video( 'p-copy-sp.mp4' ) ); ?>"
		muted
		loop
		playsinline
		preload="none"
		aria-hidden="true"
	></video>

	<p class="c-copy p-copy__text">エクステリア×DXで、<br>理想の暮らしをカタチに。</p>
</section>
