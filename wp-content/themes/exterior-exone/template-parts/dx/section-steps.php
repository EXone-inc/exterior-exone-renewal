<?php
/**
 * DX: タイトル帯 + ステップ 01〜04 + フローバー。
 *
 * カンプ: PC 436:394（タイトル帯 436:395 / 01 436:125 / 02 436:161 / 03 436:189 /
 *         04 436:216 / フローバー 436:399）
 *         SP  439:765〜439:775（タイトル帯）/ 439:758・439:756・439:759・439:760
 *         （01〜04）/ 439:839（フローバー）
 *
 * PC は 1 ステップ = 1 画面（1920x1080）で、テキスト列とビジュアルを左右交互に
 * 置く（01 文左 / 02 文右 / 03 文左 / 04 文右）。SP は縦積みで、カンプどおり
 * ビジュアルを先に出す。
 *
 * TOP の DX セクションと同じく、ステップ部分（.p-dxsteps__rail）を画面に固定し、
 * スクロール量で 01 → 04 を 1 画面ずつ切り替える（2026-09-24 ユーザー指示）。
 * 切り替わるときは今のステップが左へ流れ、次のステップが右から入る（戻るときは逆）。
 * フローバーは 1 本だけ画面下に置き、今のステップに当たる点をアクティブにして
 * 線がそこまで伸びる。挙動は js/dx-steps.js。JS が動かないときは 01〜04 を縦に
 * 並べ、フローバーは最後に 1 本出す。
 *
 * 写真・動画枠・3D モデルはすべて差し替え前提の仮素材。文言・画像は
 * inc/dx-data.php の exterior_exone_dx_page_steps() / exterior_exone_dx_flow()
 * が単一の出典。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_dx_steps = exterior_exone_dx_page_steps();
$exterior_exone_dx_flow  = exterior_exone_dx_flow();
$exterior_exone_first    = $exterior_exone_dx_steps['items'][0]['flow_active'];
?>
<section class="p-dxsteps" data-section="dx-steps">
	<?php // 436:395 / 439:765 ?>
	<div class="p-dxsteps__head">
		<p class="p-dxsteps__label"><?php echo esc_html( $exterior_exone_dx_steps['label'] ); ?></p>

		<h2 class="p-dxsteps__copy">
			<?php foreach ( $exterior_exone_dx_steps['copy'] as $exterior_exone_line ) : ?>
				<span class="p-dxsteps__copy-line"><?php echo esc_html( $exterior_exone_line ); ?></span>
			<?php endforeach; ?>
		</h2>
	</div>

	<?php // ステップ部分。JS が動くと画面に固定され、1 画面ずつ切り替わる。 ?>
	<div class="p-dxsteps__rail" data-dx-steps>
		<div class="p-dxsteps__stage">
			<div class="p-dxsteps__track">
				<?php foreach ( $exterior_exone_dx_steps['items'] as $exterior_exone_step_index => $exterior_exone_step ) : ?>
					<?php
					// 本文は PC / SP で文言・改行の切り方が違う。同じなら 1 つだけ出す。
					$exterior_exone_bodies = array(
						array(
							'modifier' => '',
							'lines'    => $exterior_exone_step['body']['pc'],
						),
					);

					if ( $exterior_exone_step['body']['pc'] !== $exterior_exone_step['body']['sp'] ) {
						$exterior_exone_bodies = array(
							array(
								'modifier' => ' p-dxstep__body--pc',
								'lines'    => $exterior_exone_step['body']['pc'],
							),
							array(
								'modifier' => ' p-dxstep__body--sp',
								'lines'    => $exterior_exone_step['body']['sp'],
							),
						);
					}
					?>
					<article
						class="p-dxstep p-dxstep--<?php echo esc_attr( $exterior_exone_step['num'] ); ?><?php echo 0 === $exterior_exone_step_index ? ' is-active' : ''; ?>"
						data-dx-step
						data-flow-active="<?php echo esc_attr( (string) $exterior_exone_step['flow_active'] ); ?>"
					>
						<div class="p-dxstep__inner">
							<div class="p-dxstep__text">
								<p class="p-dxstep__num"><?php echo esc_html( $exterior_exone_step['num'] ); ?></p>

								<?php // 436:133 / 436:166 ほか。番号の右に重ねる端末のモックアップ。 ?>
								<img
									class="p-dxstep__device"
									src="<?php echo esc_url( exterior_exone_dx_image( $exterior_exone_step['device']['image'] ) ); ?>"
									alt="<?php echo esc_attr( $exterior_exone_step['device']['alt'] ); ?>"
									loading="lazy"
								>

								<p class="p-dxstep__name"><?php echo esc_html( $exterior_exone_step['name'] ); ?></p>

								<?php // 単語ごとに包む。PC の CLIENT PORTAL だけカンプが 2 行なので CSS で折る。 ?>
								<p class="p-dxstep__en">
									<?php foreach ( explode( ' ', $exterior_exone_step['en'] ) as $exterior_exone_word ) : ?>
										<span class="p-dxstep__en-word"><?php echo esc_html( $exterior_exone_word ); ?></span>
									<?php endforeach; ?>
								</p>

								<?php // 区切り線は見出しの上罫線で引く（436:157 ほか）。 ?>
								<h3 class="p-dxstep__heading"><?php echo esc_html( $exterior_exone_step['heading'] ); ?></h3>

								<?php foreach ( $exterior_exone_bodies as $exterior_exone_body ) : ?>
									<p class="p-dxstep__body<?php echo esc_attr( $exterior_exone_body['modifier'] ); ?>">
										<?php foreach ( $exterior_exone_body['lines'] as $exterior_exone_line ) : ?>
											<span class="p-dxstep__body-line"><?php echo esc_html( $exterior_exone_line ); ?></span>
										<?php endforeach; ?>
									</p>
								<?php endforeach; ?>
							</div>

							<div class="p-dxstep__visual">
								<img
									src="<?php echo esc_url( exterior_exone_dx_image( $exterior_exone_step['visual']['image'] ) ); ?>"
									alt="<?php echo esc_attr( $exterior_exone_step['visual']['alt'] ); ?>"
									loading="lazy"
								>

								<?php // 436:396 / 439:772。01 だけ動画枠の中央下にボタンを重ねる。 ?>
								<?php if ( ! empty( $exterior_exone_step['button'] ) ) : ?>
									<a class="p-dxstep__button" href="<?php echo esc_url( $exterior_exone_step['button']['url'] ); ?>">
										<?php echo esc_html( $exterior_exone_step['button']['label'] ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>

			<?php
			// 436:399 / 439:839。1 本だけ置き、今のステップに当たる点をアクティブにして
			// 線をそこまで伸ばす（初期値は 01。切り替えは js/dx-steps.js）。
			?>
			<div class="p-dxflow" data-dx-flow>
				<span class="p-dxflow__line" aria-hidden="true">
					<span class="p-dxflow__fill" data-dx-flow-fill></span>
				</span>

				<ol class="p-dxflow__list" aria-label="外構づくりの流れ">
					<?php foreach ( $exterior_exone_dx_flow as $exterior_exone_index => $exterior_exone_point ) : ?>
						<?php $exterior_exone_is_active = ( $exterior_exone_index === $exterior_exone_first ); ?>
						<li
							class="p-dxflow__item<?php echo $exterior_exone_is_active ? ' is-active' : ''; ?>"
							data-dx-flow-item
							<?php echo $exterior_exone_is_active ? 'aria-current="step"' : ''; ?>
						>
							<?php if ( '' !== $exterior_exone_point['icon'] ) : ?>
								<img
									class="p-dxflow__icon"
									src="<?php echo esc_url( exterior_exone_dx_image( $exterior_exone_point['icon'] ) ); ?>"
									alt=""
									loading="lazy"
								>
							<?php else : ?>
								<span class="p-dxflow__dot" aria-hidden="true"></span>
							<?php endif; ?>

							<span class="p-dxflow__label"><?php echo esc_html( $exterior_exone_point['label'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</div>
	</div>
</section>
