<?php
/**
 * 支店: SERVICE。
 *
 * カンプ: PC 612:781（メインカード 612:780・サブサービス 612:585）/
 *         SP 641:59〜641:169（カード横 2 枚・帯見出し 2 行・カテゴリ 4 行）
 *
 * メインカード 2 枚とカテゴリの各行は下からフェードイン（guide 617:29 / 617:32。
 * js/scroll-reveal.js の [data-reveal] の既定）。
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
			<li class="p-sservice__card" data-reveal>
				<?php // 写真の下端に黒のグラデをかけ、その上に英字を置く（612:632 / 612:633）。 ?>
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
		<?php // 612:588 / SP 641:97。パネル上端にかぶる黒帯。改行は SP だけ効かせる。 ?>
		<p class="p-sservice__banner"><?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_service['banner'] ), array( 'br' => array() ) ); ?></p>

		<p class="p-sservice__lead"><?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_service['lead'] ), array( 'br' => array() ) ); ?></p>

		<ul class="p-sservice__rows">
			<?php foreach ( $exterior_exone_service['categories'] as $exterior_exone_category ) : ?>
				<li class="p-sservice__row" data-reveal>
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
								<?php if ( ! empty( $exterior_exone_photo['label_sp'] ) ) : ?>
									<?php // SP だけ短い名前にする（641:131 / 641:167）。 ?>
									<span class="p-sservice__photo-label">
										<span class="p-sservice__photo-label-pc"><?php echo esc_html( $exterior_exone_photo['label'] ); ?></span>
										<span class="p-sservice__photo-label-sp"><?php echo esc_html( $exterior_exone_photo['label_sp'] ); ?></span>
									</span>
								<?php else : ?>
									<span class="p-sservice__photo-label"><?php echo esc_html( $exterior_exone_photo['label'] ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
