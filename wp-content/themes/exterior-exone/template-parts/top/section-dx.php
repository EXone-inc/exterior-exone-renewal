<?php
/**
 * TOP: DX EXPERIENCE セクション。
 *
 * カンプ: PC 917:172-212（#1a1a1a 背景。左に 3 ステップの縦タイムライン、右に画像）
 *         SP  917:799-848（画像が上、ステップは 1 件ずつのスライダー）
 *
 * PC / SP とも、セクションをピン留めした区間のスクロール量に応じて現在のステップと
 * ビジュアルが 01 → 02 → 03 と進む（参照実装 test.html と同じ挙動）。
 * PC は 3 ステップを同時に見せて現在地を点で示し、SP はカンプどおり 1 件ずつ
 * 入れ替える。ステップを押すと、その位置までスクロールする。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_dx_steps = exterior_exone_dx_steps();
?>
<section class="p-dx" data-section="dx" data-dx-scroll>
	<div class="p-dx__inner">
		<div class="p-dx__head">
			<h2 class="c-display p-dx__title">DX EXPERIENCE</h2>
			<?php // PC と SP で文言が異なる（917:211 / 917:802）。 ?>
			<p class="p-dx__copy p-dx__copy--pc">デジタル技術で外構を完成前に可視化し、<br>理想の暮らしをカタチに。</p>
			<p class="p-dx__copy p-dx__copy--sp">デジタル技術で、外構を施工前に可視化する。</p>
		</div>

		<div class="p-dx__body">
			<div class="p-dx__media">
				<?php // ステップぶんのビジュアルを重ねて置き、is-active の 1 枚だけを見せる。 ?>
				<div class="p-dx__visuals">
					<?php foreach ( $exterior_exone_dx_steps as $exterior_exone_index => $exterior_exone_step ) : ?>
						<div
							class="p-dx__visual<?php echo 0 === $exterior_exone_index ? ' is-active' : ''; ?>"
							data-dx-visual="<?php echo esc_attr( (string) $exterior_exone_index ); ?>"
						>
							<?php if ( 'video' === $exterior_exone_step['media']['type'] ) : ?>
								<video
									class="p-dx__image"
									src="<?php echo esc_url( exterior_exone_top_video( $exterior_exone_step['media']['file'] ) ); ?>"
									muted
									loop
									playsinline
									preload="metadata"
								></video>
							<?php else : ?>
								<img
									class="p-dx__image"
									src="<?php echo esc_url( exterior_exone_top_image( $exterior_exone_step['media']['file'] ) ); ?>"
									width="900"
									height="520"
									alt=""
									loading="lazy"
								>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="p-dx__media-foot">
					<?php // ビジュアルの現在位置を示すバー（917:207 / 917:208）。ステップ数と揃える。 ?>
					<ul class="p-dx__bars" aria-hidden="true">
						<?php foreach ( $exterior_exone_dx_steps as $exterior_exone_index => $exterior_exone_step ) : ?>
							<li class="p-dx__bar<?php echo 0 === $exterior_exone_index ? ' is-active' : ''; ?>"></li>
						<?php endforeach; ?>
					</ul>

					<a class="c-btn c-btn--white p-dx__btn" href="#">VIEW MORE</a>
				</div>
			</div>

			<div class="p-dx__steps" data-dx-slider>
				<ol class="p-dx__list">
					<?php foreach ( $exterior_exone_dx_steps as $exterior_exone_index => $exterior_exone_step ) : ?>
						<li
							class="p-dx__step<?php echo 0 === $exterior_exone_index ? ' is-active' : ''; ?>"
							data-dx-step="<?php echo esc_attr( (string) $exterior_exone_index ); ?>"
						>
							<p class="p-dx__step-number"><?php echo esc_html( $exterior_exone_step['number'] ); ?></p>
							<p class="p-dx__step-title"><?php echo esc_html( $exterior_exone_step['title'] ); ?></p>
							<p class="p-dx__step-desc"><?php echo esc_html( $exterior_exone_step['desc'] ); ?></p>

							<ul class="p-dx__tags">
								<?php foreach ( $exterior_exone_step['tags'] as $exterior_exone_tag ) : ?>
									<li class="p-dx__tag"><?php echo esc_html( $exterior_exone_tag ); ?></li>
								<?php endforeach; ?>
							</ul>
						</li>
					<?php endforeach; ?>
				</ol>

				<?php // SP で現在のステップを示す縦インジケータ（PC は縦タイムラインの点を使う）。 ?>
				<ul class="p-dx__dots" aria-hidden="true">
					<?php foreach ( $exterior_exone_dx_steps as $exterior_exone_index => $exterior_exone_step ) : ?>
						<li class="p-dx__dot<?php echo 0 === $exterior_exone_index ? ' is-active' : ''; ?>"></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</section>
