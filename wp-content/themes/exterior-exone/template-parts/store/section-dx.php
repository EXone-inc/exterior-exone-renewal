<?php
/**
 * 支店: DX EXPERIENCE。
 *
 * カンプ: PC Group 331（572:619）= 572:600 見出し / 572:599 コピー / 572:593 画像 /
 *         572:585-595 ステップのタイムライン / 572:596-597 ナビ / 572:602 VIEW MORE
 *         SP カンプなし（画像が上、ステップを縦に並べる）
 *
 * TOP の DX（template-parts/top/section-dx.php）とほぼ同じ見た目だが、
 * 支店はピン留めのスクロールジャックを使わず、ステップの見出しを押すと
 * アクティブとビジュアルが切り替わるだけにする（js/store-dx.js）。
 * タグの見た目と VIEW MORE の位置（枠外・中央）も TOP と異なる。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_dx = exterior_exone_store_dx();
?>
<section class="p-sdx" data-section="store-dx">
	<div class="p-sdx__inner">
		<div class="p-sdx__head">
			<h2 class="c-display p-sdx__title"><?php echo esc_html( $exterior_exone_dx['title'] ); ?></h2>

			<p class="p-sdx__copy">
				<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_dx['copy'] ), array( 'br' => array() ) ); ?>
			</p>
		</div>

		<div class="p-sdx__body" data-sdx>
			<div class="p-sdx__steps">
				<?php // 572:585。ステップをつなぐ縦線。 ?>
				<span class="p-sdx__timeline" aria-hidden="true"></span>

				<ol class="p-sdx__list">
					<?php foreach ( $exterior_exone_dx['steps'] as $exterior_exone_index => $exterior_exone_step ) : ?>
						<li
							class="p-sdx__step<?php echo 0 === $exterior_exone_index ? ' is-active' : ''; ?>"
							data-sdx-step="<?php echo esc_attr( (string) $exterior_exone_index ); ?>"
						>
							<button
								class="p-sdx__step-head"
								type="button"
								data-sdx-trigger="<?php echo esc_attr( (string) $exterior_exone_index ); ?>"
								aria-pressed="<?php echo 0 === $exterior_exone_index ? 'true' : 'false'; ?>"
							>
								<span class="p-sdx__step-number"><?php echo esc_html( $exterior_exone_step['number'] ); ?></span>
								<span class="p-sdx__step-title"><?php echo esc_html( $exterior_exone_step['title'] ); ?></span>
							</button>

							<p class="p-sdx__step-desc"><?php echo esc_html( $exterior_exone_step['desc'] ); ?></p>

							<ul class="p-sdx__tags">
								<?php foreach ( $exterior_exone_step['tags'] as $exterior_exone_tag ) : ?>
									<li class="p-sdx__tag"><?php echo esc_html( $exterior_exone_tag ); ?></li>
								<?php endforeach; ?>
							</ul>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>

			<div class="p-sdx__media">
				<?php // ステップぶんのビジュアルを重ねて置き、is-active の 1 枚だけを見せる。 ?>
				<?php foreach ( $exterior_exone_dx['steps'] as $exterior_exone_index => $exterior_exone_step ) : ?>
					<?php
					// 02 / 03 は TOP の DX と同じ画像（images/top/）を参照する。
					$exterior_exone_visual = 'top' === $exterior_exone_step['dir']
						? exterior_exone_top_image( $exterior_exone_step['image'] )
						: exterior_exone_store_image( $exterior_exone_step['image'] );
					?>
					<img
						class="p-sdx__visual<?php echo 0 === $exterior_exone_index ? ' is-active' : ''; ?>"
						data-sdx-visual="<?php echo esc_attr( (string) $exterior_exone_index ); ?>"
						src="<?php echo esc_url( $exterior_exone_visual ); ?>"
						width="1200"
						height="705"
						alt=""
						loading="lazy"
					>
				<?php endforeach; ?>
			</div>
		</div>

		<?php // 572:596 / 572:597。現在のステップを示すバー。 ?>
		<ul class="p-sdx__bars" aria-hidden="true">
			<?php foreach ( $exterior_exone_dx['steps'] as $exterior_exone_index => $exterior_exone_step ) : ?>
				<li class="p-sdx__bar<?php echo 0 === $exterior_exone_index ? ' is-active' : ''; ?>" data-sdx-bar="<?php echo esc_attr( (string) $exterior_exone_index ); ?>"></li>
			<?php endforeach; ?>
		</ul>

		<div class="p-sdx__foot">
			<a class="c-btn c-btn--white p-sdx__btn" href="<?php echo esc_url( $exterior_exone_dx['more'] ); ?>">VIEW MORE</a>
		</div>
	</div>
</section>
