<?php
/**
 * DX: FV。
 *
 * カンプ: PC 436:381（1920x1080・436:103 / 436:104 / 436:113）
 *         SP  439:777〜439:796（375x723）
 *
 * 背景動画の上に、左から（SP は下から）効く暗幕と、半透明の「DX」に
 * 白の「EXPERIENCE」を重ねたコピー塊を置く。
 * 背景はカンプの「video」枠に TOP の FV 5 枚目の動画（FV05）を流用する
 * （2026-09-24 ユーザー指示）。PC / SP の出し分けと低電力モード時の扱いは
 * TOP のコピーセクションと同じ js/copy-video.js に任せる。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_dx_fv = exterior_exone_dx_fv();
?>
<section class="p-dxfv" data-section="dx-fv">
	<div class="p-dxfv__bg" aria-hidden="true">
		<?php
		// 436:103 / 439:777。下に動画のポスターを敷き、再生が始まったら動画を重ねる
		// （自動再生が拒否されたときもポスターが見えたまま）。
		?>
		<picture>
			<source media="(max-width: 1024px)" srcset="<?php echo esc_url( exterior_exone_top_video_poster( $exterior_exone_dx_fv['video_sp'] ) ); ?>">
			<img
				src="<?php echo esc_url( exterior_exone_top_video_poster( $exterior_exone_dx_fv['video'] ) ); ?>"
				width="1920"
				height="1080"
				alt=""
				fetchpriority="high"
			>
		</picture>
		<video
			class="p-dxfv__video"
			data-copy-video
			data-src-pc="<?php echo esc_url( exterior_exone_top_video( $exterior_exone_dx_fv['video'] ) ); ?>"
			data-src-sp="<?php echo esc_url( exterior_exone_top_video( $exterior_exone_dx_fv['video_sp'] ) ); ?>"
			muted
			loop
			playsinline
			preload="none"
		></video>
		<?php // 436:104（左端から）/ 439:778（下端から）の暗幕。向きは css/dx.css。 ?>
		<span class="p-dxfv__shade"></span>
	</div>

	<div class="p-dxfv__copy">
		<?php // 436:114 + 436:117 / 439:792 + 439:793。読みは「DX EXPERIENCE」。 ?>
		<h1 class="p-dxfv__title">
			<span class="p-dxfv__title-dx"><?php echo esc_html( $exterior_exone_dx_fv['title'] ); ?></span>
			<span class="p-dxfv__title-en"><?php echo esc_html( $exterior_exone_dx_fv['title_en'] ); ?></span>
		</h1>

		<p class="p-dxfv__heading"><?php echo esc_html( $exterior_exone_dx_fv['heading'] ); ?></p>

		<?php // 436:118 / 439:795。カンプの改行位置どおりに 4 行で組む。 ?>
		<p class="p-dxfv__lead">
			<?php foreach ( $exterior_exone_dx_fv['body'] as $exterior_exone_line ) : ?>
				<span class="p-dxfv__lead-line"><?php echo esc_html( $exterior_exone_line ); ?></span>
			<?php endforeach; ?>
		</p>
	</div>
</section>
