<?php
/**
 * 採用ページ: FV（粒子・写真スライダー・コピー・応募ボタン・下端のカーブ）。
 *
 * 現サイト: .recruit-first--view（#mycanvas / .recruit_slide / .text-container / .rec-rink_btn / .clipped-rounded）
 *
 * 粒子は js/recruit.js（Canvas 2D。色は css/recruit.css の --rc-particle-1〜6）。
 * 写真の列は js/plans-imglist.js で流し、速さ（px/秒）は js/recruit-flow.js が合わせる。
 * 下端のカーブは現サイトの clipPath（roundedClipMobile / roundedClipPC）と同じ形の SVG。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_rc_fv = exterior_exone_recruit_fv();
?>
<section class="p-rcfv" data-section="recruit-fv">
	<div class="p-rcfv__bg" aria-hidden="true">
		<canvas class="p-rcfv__canvas" data-recruit-particles="140"></canvas>
	</div>

	<div class="p-rcfv__slider">
		<ul class="p-rcfv__slides" data-plp-imglist data-recruit-slider>
			<?php foreach ( $exterior_exone_rc_fv['slides'] as $exterior_exone_rc_index => $exterior_exone_rc_slide ) : ?>
				<li class="p-rcfv__slide">
					<img
						src="<?php echo esc_url( exterior_exone_recruit_image( $exterior_exone_rc_slide['file'] ) ); ?>"
						width="<?php echo esc_attr( (string) $exterior_exone_rc_slide['size'][0] ); ?>"
						height="<?php echo esc_attr( (string) $exterior_exone_rc_slide['size'][1] ); ?>"
						alt="<?php echo esc_attr( $exterior_exone_rc_slide['alt'] ); ?>"
						<?php echo $exterior_exone_rc_index < 5 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
						decoding="async"
					>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="p-rcfv__copy">
		<h1 class="p-rcfv__title">
			<span class="p-rcfv__en"><?php echo esc_html( $exterior_exone_rc_fv['en'] ); ?></span>
			<span class="p-rcfv__ja"><?php echo wp_kses( implode( '<br>', array_map( 'esc_html', $exterior_exone_rc_fv['ja'] ) ), array( 'br' => array() ) ); ?></span>
		</h1>
		<p class="p-rcfv__sub"><?php echo wp_kses( implode( '<br>', array_map( 'esc_html', $exterior_exone_rc_fv['sub'] ) ), array( 'br' => array() ) ); ?></p>
	</div>

	<div class="p-rcfv__apply">
		<a class="p-rcbtn" href="<?php echo esc_url( exterior_exone_store_link( exterior_exone_recruit_url( 'entry/' ) ) ); ?>" data-recruit-apply="fv">
			<span class="p-rcbtn__label"><?php echo esc_html( $exterior_exone_rc_fv['apply'] ); ?></span>
			<span class="p-rcbtn__icon" aria-hidden="true"></span>
		</a>
	</div>

	<div class="p-rcfv__curve" aria-hidden="true">
		<svg class="p-rcfv__curve-svg p-rcfv__curve-svg--sp" viewBox="0 0 1 1" preserveAspectRatio="none" focusable="false">
			<path d="M0,0 V0.9 H1 V0 C0.75,0.45 0.25,0.45 0,0 Z"/>
		</svg>
		<svg class="p-rcfv__curve-svg p-rcfv__curve-svg--pc" viewBox="0 0 1 1" preserveAspectRatio="none" focusable="false">
			<path d="M0,0 V1 H1 V0 C0.75,1.1 0.25,1.1 0,0 Z"/>
		</svg>
	</div>
</section>
