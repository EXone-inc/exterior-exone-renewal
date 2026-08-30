<?php
/**
 * TOP: FV セクション。
 *
 * 背景は動画 5 本のクロスフェード。動画に合わせて英字コピーが 1 文字ずつ
 * せり上がり、サムネイルの外周リングが再生位置に合わせて一周する。
 * 動きの仕様は参照実装（test_cp）に合わせ、配置・寸法はカンプに合わせてある。
 *
 * カンプ: PC 1:280（1920x1080・ヘッダーが重なる）
 *         SP  16:64 / 16:71 / 16:93（375x677.81・ヘッダーは上に積む）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_fv_slides = exterior_exone_fv_slides();

if ( ! $exterior_exone_fv_slides ) {
	return;
}
?>
<section class="p-fv" data-section="fv" data-fv-slider>
	<?php foreach ( $exterior_exone_fv_slides as $exterior_exone_index => $exterior_exone_slide ) : ?>
		<?php // 1 本目だけ即再生。残りはメタデータのみ先読みし、切り替え時に再生する。 ?>
		<video
			class="p-fv__video<?php echo 0 === $exterior_exone_index ? ' is-active' : ''; ?>"
			src="<?php echo esc_url( exterior_exone_top_video( $exterior_exone_slide['video'] ) ); ?>"
			data-fv-video="<?php echo esc_attr( (string) $exterior_exone_index ); ?>"
			muted
			loop
			playsinline
			<?php if ( 0 === $exterior_exone_index ) : ?>
				autoplay
				preload="auto"
			<?php else : ?>
				preload="metadata"
			<?php endif; ?>
		></video>
	<?php endforeach; ?>

	<?php // コピーは 1 文字ずつ span に割るため、読み上げ用の見出しを別に置く。 ?>
	<h1 class="screen-reader-text">EXTERIOR by EXone</h1>

	<div class="p-fv__copy" aria-hidden="true">
		<?php foreach ( $exterior_exone_fv_slides as $exterior_exone_index => $exterior_exone_slide ) : ?>
			<?php foreach ( $exterior_exone_slide['texts'] as $exterior_exone_text ) : ?>
				<?php
				$exterior_exone_text_class = 'p-fv__text';

				if ( '' !== $exterior_exone_text['position'] ) {
					$exterior_exone_text_class .= ' p-fv__text--' . $exterior_exone_text['position'];
				}

				if ( $exterior_exone_text['small'] ) {
					$exterior_exone_text_class .= ' p-fv__text--small';
				}

				if ( 0 === $exterior_exone_index ) {
					$exterior_exone_text_class .= ' is-active';
				}
				?>
				<div class="<?php echo esc_attr( $exterior_exone_text_class ); ?>" data-fv-text="<?php echo esc_attr( (string) $exterior_exone_index ); ?>">
					<p class="c-display c-display--hero p-fv__title"><?php echo exterior_exone_split_chars( $exterior_exone_text['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ヘルパー内でエスケープ済み。 ?></p>

					<?php if ( '' !== $exterior_exone_text['byline'] ) : ?>
						<p class="c-display c-display--hero-sub p-fv__sub"><?php echo esc_html( $exterior_exone_text['byline'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</div>

	<div class="p-fv__thumbs" role="tablist" aria-label="メインビジュアルの切り替え">
		<?php foreach ( $exterior_exone_fv_slides as $exterior_exone_index => $exterior_exone_slide ) : ?>
			<button
				type="button"
				class="p-fv__thumb<?php echo 0 === $exterior_exone_index ? ' is-active' : ''; ?>"
				role="tab"
				aria-selected="<?php echo 0 === $exterior_exone_index ? 'true' : 'false'; ?>"
				aria-label="<?php echo esc_attr( $exterior_exone_slide['label'] ); ?>"
				data-fv-thumb="<?php echo esc_attr( (string) $exterior_exone_index ); ?>"
			>
				<?php // 外周リング。r=40 の円周 251.327 を dasharray に使い、再生位置ぶんだけ描く。 ?>
				<svg class="p-fv__thumb-progress" viewBox="0 0 82 82" aria-hidden="true" focusable="false">
					<circle class="p-fv__thumb-progress-track" cx="41" cy="41" r="40" />
					<circle class="p-fv__thumb-progress-bar" cx="41" cy="41" r="40" />
				</svg>

				<span class="p-fv__thumb-media" aria-hidden="true">
					<img
						src="<?php echo esc_url( exterior_exone_top_image( $exterior_exone_slide['thumb'] ) ); ?>"
						width="85"
						height="85"
						alt=""
						loading="lazy"
					>
				</span>
			</button>
		<?php endforeach; ?>
	</div>
</section>
