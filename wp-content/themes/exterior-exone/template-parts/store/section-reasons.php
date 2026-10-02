<?php
/**
 * 支店: 選ばれる理由。
 *
 * カンプ: PC 612:770（夕景写真 612:150 / 上部の暗幕 612:158 / 下端の溶かし 612:253 /
 *         コピー 612:224 / 円形ダイアグラム 612:317 / 6 項目 612:772〜612:777）
 *         SP 641:22〜641:52（縦長の写真・コピー + 左揃えのリード・281φ のミニ円の周りに 1 語ラベル 6 個。
 *         見出し「選ばれる理由」と円内リードは無い）
 *
 * 6 項目の円周上の座標は css/store.css の PC / SP ブロックが持つ。
 * 6 項目は TOP 理念の円環図と同じ keyframes・周期で浮遊する（guide 617:23）。
 * 出現演出は無く、図が画面に入っている間だけ動かす（js/diagram-float.js が .is-floating を付ける）。
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
		<?php // SP は縦長の別写真（641:22。下端が背景の黒に落ちているので暗幕は足さない）。 ?>
		<picture>
			<source media="(max-width: 1024px)" srcset="<?php echo esc_url( exterior_exone_store_image( 'reasons-bg-sp-641-22.jpg' ) ); ?>" width="750" height="1117">
			<img
				class="p-sreasons__bg-photo"
				src="<?php echo esc_url( exterior_exone_store_image( 'reasons-bg-sky-572-326.jpg' ) ); ?>"
				width="1200"
				height="1006"
				alt=""
				loading="lazy"
			>
		</picture>
		<?php // 612:158。写真の上部を暗くする PNG（カンプの見た目をそのまま使う。PC のみ）。 ?>
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

		<p class="p-sreasons__copy-lead"><?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_reasons['copy_lead'] ), array( 'br' => array() ) ); ?></p>
	</div>

	<div class="p-sreasons__diagram" data-diagram-float>
		<?php // 612:332 / SP 641:40。アイコンの位置で途切れる細い円。 ?>
		<picture>
			<source media="(max-width: 1024px)" srcset="<?php echo esc_url( exterior_exone_store_image( 'reasons-ring-sp-641-40.svg' ) ); ?>" width="281" height="281">
			<img
				class="p-sreasons__ring"
				src="<?php echo esc_url( exterior_exone_store_image( 'reasons-ring-570-168.svg' ) ); ?>"
				width="1200"
				height="1200"
				alt=""
				aria-hidden="true"
				loading="lazy"
			>
		</picture>

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
			<p class="c-display p-sreasons__eng-sub p-sreasons__eng-sub--pc"><?php echo esc_html( $exterior_exone_reasons['eng_sub'] ); ?></p>
			<p class="c-display p-sreasons__eng-sub p-sreasons__eng-sub--sp"><?php echo esc_html( $exterior_exone_reasons['eng_sub_sp'] ); ?></p>
		</div>

		<ul class="p-sreasons__items">
			<?php foreach ( $exterior_exone_reasons['items'] as $exterior_exone_item ) : ?>
				<li
					class="p-sreasons__item"
					style="--icon-w:<?php echo esc_attr( (string) $exterior_exone_item['icon_w'] ); ?>;--icon-h:<?php echo esc_attr( (string) $exterior_exone_item['icon_h'] ); ?>"
				>
					<picture>
						<?php if ( ! empty( $exterior_exone_item['icon_sp'] ) ) : ?>
							<source media="(max-width: 1024px)" srcset="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_item['icon_sp'] ) ); ?>">
						<?php endif; ?>
						<img
							class="p-sreasons__icon"
							src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_item['icon'] ) ); ?>"
							width="<?php echo esc_attr( (string) (int) round( $exterior_exone_item['icon_w'] ) ); ?>"
							height="<?php echo esc_attr( (string) (int) round( $exterior_exone_item['icon_h'] ) ); ?>"
							alt=""
							loading="lazy"
						>
					</picture>
					<p class="p-sreasons__item-lead"><?php echo esc_html( $exterior_exone_item['lead'] ); ?></p>
					<p class="p-sreasons__item-label"><?php echo esc_html( $exterior_exone_item['label'] ); ?></p>
					<p class="p-sreasons__item-word"><?php echo esc_html( $exterior_exone_item['word'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
