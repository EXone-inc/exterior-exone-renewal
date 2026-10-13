<?php
/**
 * 採用ページ: 流れる大文字（Challenge Creative Let's Entry）。
 *
 * 現サイト: .recruit_text--slide（Splide AutoScroll）。
 *
 * 高さ 0 の枠から、Work の下端に食い込む位置へ絶対配置で重ねる（css/recruit.css）。
 * 流すのは js/plans-imglist.js、速さ（px/秒）は js/recruit-flow.js（--rc-flow-speed）。
 * 装飾なので支援技術から隠し、クリックは下へ通す。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="p-rcmarquee" data-section="recruit-marquee" aria-hidden="true">
	<div class="p-rcmarquee__frame">
		<ul class="p-rcmarquee__row" data-plp-imglist data-recruit-marquee>
			<li class="p-rcmarquee__item"><?php echo esc_html( exterior_exone_recruit_marquee() ); ?></li>
		</ul>
	</div>
</section>
