<?php
/**
 * 支店: SERVICE。
 *
 * カンプ: PC 572:448（背景）/ 572:456（見出し）/ 691:1036・689:1001（2 枚カード）/
 *         Group 377（691:1009 黒帯 + 白パネル + カテゴリ 4 行）
 *         SP カンプなし（カード・カテゴリ行を縦積み）
 *
 * カテゴリ 1 行目の英字はカンプでは「GAR SPACE」だが、誤記のため「CAR SPACE」で
 * 実装する（docs/spec-20260911-store-page-decisions.md Q1）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_service = exterior_exone_store_service();
?>
<section class="p-sservice" data-section="store-service">
	<h2 class="c-store-title"><?php echo esc_html( $exterior_exone_service['title'] ); ?></h2>

	<ul class="p-sservice__cards">
		<?php foreach ( $exterior_exone_service['cards'] as $exterior_exone_card ) : ?>
			<li class="p-sservice__card">
				<?php // 写真の下端に黒 40% のグラデをかけ、その上に英字を置く（691:1016 / 691:1013）。 ?>
				<div class="p-sservice__card-media">
					<img
						src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_card['image'] ) ); ?>"
						width="1200"
						height="808"
						alt=""
						loading="lazy"
					>
					<p class="c-display p-sservice__card-eng"><?php echo esc_html( $exterior_exone_card['eng'] ); ?></p>
				</div>

				<p class="p-sservice__card-label"><?php echo esc_html( $exterior_exone_card['label'] ); ?></p>
			</li>
		<?php endforeach; ?>
	</ul>

	<div class="p-sservice__panel">
		<?php // 572:495 / 572:451。パネル上端にかぶる黒帯。 ?>
		<p class="p-sservice__banner"><?php echo esc_html( $exterior_exone_service['banner'] ); ?></p>

		<p class="p-sservice__lead">
			<?php foreach ( $exterior_exone_service['lead'] as $exterior_exone_index => $exterior_exone_line ) : ?>
				<?php echo 0 === $exterior_exone_index ? '' : '<br>'; ?>
				<?php echo esc_html( $exterior_exone_line ); ?>
			<?php endforeach; ?>
		</p>

		<ul class="p-sservice__rows">
			<?php foreach ( $exterior_exone_service['categories'] as $exterior_exone_category ) : ?>
				<li class="p-sservice__row">
					<div class="p-sservice__row-head">
						<p class="c-display p-sservice__row-eng"><?php echo esc_html( $exterior_exone_category['eng'] ); ?></p>
						<p class="p-sservice__row-label"><?php echo esc_html( $exterior_exone_category['label'] ); ?></p>
					</div>

					<ul class="p-sservice__photos">
						<?php foreach ( $exterior_exone_category['photos'] as $exterior_exone_photo ) : ?>
							<li class="p-sservice__photo">
								<img
									src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_photo['image'] ) ); ?>"
									width="1200"
									height="725"
									alt="<?php echo esc_attr( $exterior_exone_photo['label'] ); ?>"
									loading="lazy"
								>
								<span class="p-sservice__photo-label"><?php echo esc_html( $exterior_exone_photo['label'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
