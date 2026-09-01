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
 * 03 SHARE のビジュアルだけは動画ではなく、背景写真の上にスマホの図を CSS で
 * 重ねたもの（media.type = 'mockup'）。こちらも test.html の 3 枚目に合わせてある。
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
						<?php $exterior_exone_mockup = isset( $exterior_exone_step['media']['mockup'] ) ? $exterior_exone_step['media']['mockup'] : null; ?>
						<div
							class="p-dx__visual<?php echo $exterior_exone_mockup ? ' p-dx__visual--mockup' : ''; ?><?php echo 0 === $exterior_exone_index ? ' is-active' : ''; ?>"
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

							<?php if ( $exterior_exone_mockup ) : ?>
								<?php
								// 03 SHARE。背景写真の上に、確認事項の円がスマホの中に集まる図を CSS で描く
								// （参照実装 test.html の 3 枚目）。中の文字は図の一部なので、
								// 読み上げには role="img" の説明 1 本だけを渡す。
								?>
								<span class="p-dx__mockup-scrim" aria-hidden="true"></span>

								<div class="p-dx__mockup" role="img" aria-label="<?php echo esc_attr( $exterior_exone_mockup['alt'] ); ?>">
									<?php $exterior_exone_corners = array( 'tl', 'tr', 'bl', 'br' ); ?>

									<?php // 円とスマホをつなぐ線（PC のみ。SP は円が上下に来るため出さない）。 ?>
									<?php foreach ( $exterior_exone_corners as $exterior_exone_corner ) : ?>
										<span class="p-dx__mockup-line p-dx__mockup-line--<?php echo esc_attr( $exterior_exone_corner ); ?>"></span>
									<?php endforeach; ?>

									<?php // スマホの外に散らばっている確認事項。中央へ吸い込まれて消える。 ?>
									<?php foreach ( array_values( $exterior_exone_mockup['labels'] ) as $exterior_exone_label_index => $exterior_exone_label ) : ?>
										<span class="p-dx__mockup-item p-dx__mockup-item--<?php echo esc_attr( $exterior_exone_corners[ $exterior_exone_label_index ] ); ?>"><?php echo esc_html( $exterior_exone_label ); ?></span>
									<?php endforeach; ?>

									<div class="p-dx__mockup-phone">
										<div class="p-dx__mockup-screen">
											<p class="p-dx__mockup-app"><?php echo esc_html( $exterior_exone_mockup['app'] ); ?></p>
											<p class="p-dx__mockup-lead"><?php echo esc_html( implode( "\n", $exterior_exone_mockup['title'] ) ); ?></p>

											<ul class="p-dx__mockup-list">
												<?php foreach ( $exterior_exone_mockup['labels'] as $exterior_exone_label ) : ?>
													<li class="p-dx__mockup-row"><i class="p-dx__mockup-check">&#10003;</i><?php echo esc_html( $exterior_exone_label ); ?></li>
												<?php endforeach; ?>
											</ul>
										</div>
									</div>
								</div>
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
