<?php
/**
 * 支店: AREA（対応エリア・店舗情報）。
 *
 * カンプ: PC 620:292（店舗写真 1920x933.8）/ 628:302「AREA」/
 *         Group 472（1148:89）= 白パネル 628:303 + 黒帯 628:337 + リード 628:329 +
 *         店舗情報テーブル 628:307 + 店舗外観写真 628:306 + 弧状ギャラリー 1126:34 +
 *         ロゴ 1131:38 / 1126:37 /
 *         Local Expertise 1148:48・1148:43・1148:45・1148:28・1148:29 /
 *         カード 4 枚 1151:93-101
 *         SP カンプなし（パネルの中身を縦積みにする）
 *
 * 弧状ギャラリーはブール演算で 1 枚に統合されていて個別写真を取り出せないため、
 * 合成画像のまま置く（決定事項 Q12）。
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

$exterior_exone_area = exterior_exone_store_area( $exterior_exone_store );
?>
<section class="p-sarea" data-section="store-area">
	<?php // 620:292。上端の店舗内観写真。見出しだけがこの上に乗る。 ?>
	<div class="p-sarea__bg" aria-hidden="true">
		<img
			src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_area['image'] ) ); ?>"
			width="1920"
			height="934"
			alt=""
			loading="lazy"
		>
	</div>

	<h2 class="c-store-title p-sarea__title"><?php echo esc_html( $exterior_exone_area['title'] ); ?></h2>

	<div class="p-sarea__panel">
		<?php // 628:337 / 628:338。パネル上端にかぶる黒帯。 ?>
		<p class="p-sarea__banner"><?php echo esc_html( $exterior_exone_area['banner'] ); ?></p>

		<p class="p-sarea__lead">
			<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_area['lead'] ), array( 'br' => array() ) ); ?>
		</p>

		<div class="p-sarea__info">
			<dl class="p-sarea__table">
				<?php foreach ( $exterior_exone_area['rows'] as $exterior_exone_row ) : ?>
					<div class="p-sarea__row">
						<dt class="p-sarea__row-label"><?php echo esc_html( $exterior_exone_row['label'] ); ?></dt>
						<dd class="p-sarea__row-value"><?php echo esc_html( $exterior_exone_row['value'] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>

			<img
				class="p-sarea__photo"
				src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_area['photo'] ) ); ?>"
				width="1030"
				height="686"
				alt="<?php echo esc_attr( $exterior_exone_store['name'] ); ?>の外観"
				loading="lazy"
			>
		</div>

		<p class="p-sarea__logo">
			<img
				src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_area['logo'] ) ); ?>"
				width="577"
				height="86"
				alt="EX ONE"
				loading="lazy"
			>
		</p>

		<p class="p-sarea__branch"><?php echo esc_html( $exterior_exone_area['branch'] ); ?></p>
	</div>

	<?php // 1126:34。店舗内観 5 枚を弧状に並べた合成 1 枚（内側は透明）。 ?>
	<img
		class="p-sarea__gallery"
		src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_area['gallery'] ) ); ?>"
		width="1920"
		height="1020"
		alt="<?php echo esc_attr( $exterior_exone_store['name'] ); ?>の店内"
		loading="lazy"
	>

	<div class="p-sarea__local">
		<div class="p-sarea__local-body">
			<p class="p-sarea__local-eng"><?php echo esc_html( $exterior_exone_area['local']['eng'] ); ?></p>

			<h3 class="p-sarea__local-heading">
				<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_area['local']['heading'] ), array( 'br' => array() ) ); ?>
			</h3>

			<p class="p-sarea__local-lead">
				<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_area['local']['lead'] ), array( 'br' => array() ) ); ?>
			</p>
		</div>

		<img
			class="p-sarea__local-photo"
			src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_area['local']['photo'] ) ); ?>"
			width="1915"
			height="1157"
			alt=""
			loading="lazy"
		>
	</div>

	<ul class="p-sarea__cards">
		<?php foreach ( $exterior_exone_area['cards'] as $exterior_exone_card ) : ?>
			<li class="p-sarea__card">
				<p class="p-sarea__card-number"><?php echo esc_html( $exterior_exone_card['number'] ); ?></p>

				<img
					class="p-sarea__card-photo"
					src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_card['image'] ) ); ?>"
					width="729"
					height="729"
					alt=""
					loading="lazy"
				>

				<p class="p-sarea__card-label">
					<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_card['label'] ), array( 'br' => array() ) ); ?>
				</p>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
