<?php
/**
 * 支店: 選ばれる理由。
 *
 * カンプ: PC 535:997（黒背景）/ 572:326（夕景写真）/ 561:80（上部の暗幕）/
 *         Group 355（669:926 写真上のコピー）/ Group 354（669:925 円形ダイアグラム）/
 *         570:168（リング）/ 989:98（住宅 CG）
 *         SP カンプなし（円形ダイアグラムをやめて縦積み + 2 列）
 *
 * 6 項目の円周上の座標は css/store.css の PC ブロックが持つ（--item-x / --icon-y）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_reasons = exterior_exone_store_reasons();
?>
<section class="p-sreasons" data-section="store-reasons">
	<div class="p-sreasons__bg" aria-hidden="true">
		<img
			class="p-sreasons__bg-photo"
			src="<?php echo esc_url( exterior_exone_store_image( 'reasons-bg-sky-572-326.jpg' ) ); ?>"
			width="1200"
			height="1006"
			alt=""
			loading="lazy"
		>
		<?php // 561:80。写真の上部を暗くする PNG（カンプの見た目をそのまま使う）。 ?>
		<img
			class="p-sreasons__bg-shade"
			src="<?php echo esc_url( exterior_exone_store_image( 'reasons-bg-overlay-561-80.png' ) ); ?>"
			width="600"
			height="206"
			alt=""
			loading="lazy"
		>
	</div>

	<div class="p-sreasons__copy">
		<h2 class="p-sreasons__copy-heading">
			<?php foreach ( $exterior_exone_reasons['copy_heading'] as $exterior_exone_index => $exterior_exone_line ) : ?>
				<?php echo 0 === $exterior_exone_index ? '' : '<br>'; ?>
				<?php echo esc_html( $exterior_exone_line ); ?>
			<?php endforeach; ?>
		</h2>

		<p class="p-sreasons__copy-lead">
			<?php foreach ( $exterior_exone_reasons['copy_lead'] as $exterior_exone_index => $exterior_exone_line ) : ?>
				<?php echo 0 === $exterior_exone_index ? '' : '<br>'; ?>
				<?php echo esc_html( $exterior_exone_line ); ?>
			<?php endforeach; ?>
		</p>
	</div>

	<div class="p-sreasons__diagram">
		<?php // 570:168。アイコンの位置で途切れる細い円（PC のみ）。 ?>
		<img
			class="p-sreasons__ring"
			src="<?php echo esc_url( exterior_exone_store_image( 'reasons-ring-570-168.svg' ) ); ?>"
			width="1200"
			height="1200"
			alt=""
			aria-hidden="true"
			loading="lazy"
		>

		<div class="p-sreasons__center">
			<p class="p-sreasons__logo">
				<img
					src="<?php echo esc_url( exterior_exone_top_image( 'logo.svg' ) ); ?>"
					width="164"
					height="25"
					alt="<?php bloginfo( 'name' ); ?>"
					loading="lazy"
				>
			</p>

			<h3 class="p-sreasons__title"><?php echo esc_html( $exterior_exone_reasons['title'] ); ?></h3>

			<p class="p-sreasons__title-lead">
				<?php foreach ( $exterior_exone_reasons['title_lead'] as $exterior_exone_index => $exterior_exone_line ) : ?>
					<?php echo 0 === $exterior_exone_index ? '' : '<br>'; ?>
					<?php echo esc_html( $exterior_exone_line ); ?>
				<?php endforeach; ?>
			</p>

			<img
				class="p-sreasons__house"
				src="<?php echo esc_url( exterior_exone_store_image( 'reasons-house-989-98.png' ) ); ?>"
				width="1200"
				height="800"
				alt=""
				loading="lazy"
			>

			<p class="c-display p-sreasons__eng"><?php echo esc_html( $exterior_exone_reasons['eng'] ); ?></p>
			<p class="c-display p-sreasons__eng-sub"><?php echo esc_html( $exterior_exone_reasons['eng_sub'] ); ?></p>
		</div>

		<ul class="p-sreasons__items">
			<?php foreach ( $exterior_exone_reasons['items'] as $exterior_exone_item ) : ?>
				<li
					class="p-sreasons__item"
					style="--icon-w:<?php echo esc_attr( (string) $exterior_exone_item['icon_w'] ); ?>;--icon-h:<?php echo esc_attr( (string) $exterior_exone_item['icon_h'] ); ?>"
				>
					<img
						class="p-sreasons__icon"
						src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_item['icon'] ) ); ?>"
						width="<?php echo esc_attr( (string) (int) round( $exterior_exone_item['icon_w'] ) ); ?>"
						height="<?php echo esc_attr( (string) (int) round( $exterior_exone_item['icon_h'] ) ); ?>"
						alt=""
						loading="lazy"
					>
					<p class="p-sreasons__item-lead"><?php echo esc_html( $exterior_exone_item['lead'] ); ?></p>
					<p class="p-sreasons__item-label"><?php echo esc_html( $exterior_exone_item['label'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
