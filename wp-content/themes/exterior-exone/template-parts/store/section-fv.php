<?php
/**
 * 支店: FV（ヒーロー）。
 *
 * カンプ: PC 535:838 / 534:647 / 561:3（テキスト塊）/ 561:46（サムネ行）/ 535:852
 *         写真 4 枚 535:844 / 535:845 / 268:348 / 535:850
 *         SP カンプなし（縦積みに補間）
 *
 * 背景は写真 5 枚のクロスフェード。サムネイルを押すと切り替わり、9 秒で
 * 自動送りされる（js/store-fv.js。TOP の FV と同じ操作感）。
 *
 * 写真 4 枚（__band）は FV の下端をまたぐため、セクションの外に出して
 * 負のマージンで引き上げている（PC カンプ y1125.1、FV 下端 y1237.5）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_store = isset( $args['store'] ) ? $args['store'] : exterior_exone_current_store();

if ( ! $exterior_exone_store ) {
	return;
}

$exterior_exone_fv = exterior_exone_store_fv();
?>
<section class="p-sfv" data-section="store-fv" data-sfv-slider>
	<?php // 5 枚を重ねて置き、is-active の 1 枚だけを見せる。 ?>
	<div class="p-sfv__bg" aria-hidden="true">
		<?php foreach ( $exterior_exone_fv['slides'] as $exterior_exone_index => $exterior_exone_slide ) : ?>
			<?php
			// 2〜5 枚目は TOP の FV の静止画（images/top/）を仮で借りている。
			$exterior_exone_slide_src = 'top' === $exterior_exone_slide['dir']
				? exterior_exone_top_image( $exterior_exone_slide['image'] )
				: exterior_exone_store_image( $exterior_exone_slide['image'] );
			?>
			<img
				class="p-sfv__slide<?php echo 0 === $exterior_exone_index ? ' is-active' : ''; ?>"
				data-sfv-slide="<?php echo esc_attr( (string) $exterior_exone_index ); ?>"
				src="<?php echo esc_url( $exterior_exone_slide_src ); ?>"
				width="1672"
				height="940"
				alt=""
				<?php if ( 0 === $exterior_exone_index ) : ?>
					fetchpriority="high"
				<?php else : ?>
					loading="lazy"
				<?php endif; ?>
			>
		<?php endforeach; ?>
	</div>

	<div class="p-sfv__body">
		<div class="p-sfv__copy">
			<?php // 535:833 + 535:834（支店名バッジ）。ページの主見出しを兼ねる。 ?>
			<h1 class="p-sfv__eyebrow">
				<span class="p-sfv__eyebrow-text"><?php echo esc_html( $exterior_exone_fv['eyebrow'] ); ?></span>
				<span class="p-sfv__badge"><?php echo esc_html( $exterior_exone_store['name'] ); ?></span>
			</h1>

			<?php // 534:650。Antonio SemiBold 150px の 2 行組み。 ?>
			<p class="c-display p-sfv__title">
				<?php foreach ( $exterior_exone_fv['titles'] as $exterior_exone_title ) : ?>
					<span class="p-sfv__title-line"><?php echo esc_html( $exterior_exone_title ); ?></span>
				<?php endforeach; ?>
			</p>

			<p class="p-sfv__lead"><?php echo esc_html( $exterior_exone_fv['lead'] ); ?></p>

			<?php // 535:951。白の EX ONE ロゴ。 ?>
			<p class="p-sfv__logo">
				<img
					src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_fv['logo'] ) ); ?>"
					width="600"
					height="92"
					alt="EX ONE"
				>
			</p>
		</div>

		<?php // 561:46 / 561:47。選択中のサムネイルに進捗リングが付く。 ?>
		<ul class="p-sfv__thumbs">
			<?php foreach ( $exterior_exone_fv['slides'] as $exterior_exone_index => $exterior_exone_slide ) : ?>
				<li class="p-sfv__thumb-item">
					<button
						class="p-sfv__thumb<?php echo 0 === $exterior_exone_index ? ' is-active' : ''; ?>"
						type="button"
						aria-pressed="<?php echo 0 === $exterior_exone_index ? 'true' : 'false'; ?>"
						aria-label="<?php echo esc_attr( sprintf( '背景写真 %d に切り替える', $exterior_exone_index + 1 ) ); ?>"
						data-sfv-thumb="<?php echo esc_attr( (string) $exterior_exone_index ); ?>"
					>
						<?php // 外周リング。r=40 の円周 251.327 を dasharray に使い、経過ぶんだけ描く。 ?>
						<svg class="p-sfv__ring" viewBox="0 0 82 82" aria-hidden="true" focusable="false">
							<circle class="p-sfv__ring-track" cx="41" cy="41" r="40" />
							<circle class="p-sfv__ring-bar" cx="41" cy="41" r="40" />
						</svg>

						<span class="p-sfv__thumb-media" aria-hidden="true">
							<img
								src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_slide['thumb'] ) ); ?>"
								width="400"
								height="400"
								alt=""
								loading="lazy"
							>
						</span>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php // 535:844 / 535:845 / 268:348 / 535:850。FV と導入コピーの境界をまたぐ写真 4 枚。 ?>
<div class="p-sfv__band">
	<?php foreach ( $exterior_exone_fv['photos'] as $exterior_exone_photo ) : ?>
		<img
			class="p-sfv__band-item"
			src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_photo ) ); ?>"
			width="1200"
			height="628"
			alt=""
			loading="lazy"
		>
	<?php endforeach; ?>
</div>
