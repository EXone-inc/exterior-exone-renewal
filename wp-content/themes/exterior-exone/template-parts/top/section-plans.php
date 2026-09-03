<?php
/**
 * TOP: PLANS セクション（テイストカード + パッケージプラン + ハイエンドプラン）。
 *
 * カンプ: PC 917:124-141（カード列は右端が画面外まで続く）
 *         SP  917:722-724・917:857-859・917:893-899・917:861-871
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_plan_cards = exterior_exone_plan_cards();
?>
<section class="p-plans" data-section="plans">
	<div class="p-plans__package">
		<h2 class="c-section-title p-plans__title">PLANS</h2>

		<?php // カード列は横スクロール（ドラッグでスライド）。カンプでも右端が画面外に続く。 ?>
		<ul class="p-plans__cards" data-scroll-row>
			<?php foreach ( $exterior_exone_plan_cards as $exterior_exone_index => $exterior_exone_card ) : ?>
				<li class="p-plans__card<?php echo 0 === $exterior_exone_index ? ' is-active' : ''; ?>">
					<?php // 押すと下の PACKAGE PLAN の外観画像が切り替わる。 ?>
					<button
						type="button"
						class="p-plans__card-button"
						data-plan-image="<?php echo esc_url( exterior_exone_top_image( $exterior_exone_card['image'] ) ); ?>"
						data-plan-name="<?php echo esc_attr( $exterior_exone_card['name'] ); ?>"
						aria-pressed="<?php echo 0 === $exterior_exone_index ? 'true' : 'false'; ?>"
					>
					<img
						class="p-plans__card-bg"
						src="<?php echo esc_url( exterior_exone_top_image( 'plans-card-bg.png' ) ); ?>"
						width="512"
						height="512"
						alt=""
						loading="lazy"
					>
					<span class="p-plans__card-name"><?php echo esc_html( $exterior_exone_card['name'] ); ?></span>
					<span class="c-display p-plans__card-eng"><?php echo esc_html( $exterior_exone_card['eng'] ); ?></span>

					<?php // 5 プランとも同じ 1800x909。カード下端に幅いっぱいで敷く。 ?>
					<img
						class="p-plans__card-image"
						src="<?php echo esc_url( exterior_exone_top_image( $exterior_exone_card['image'] ) ); ?>"
						width="1800"
						height="909"
						alt=""
						loading="lazy"
					>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="p-plans__body">
			<div class="p-plans__text">
				<p class="p-plans__jp">パッケージプラン</p>
				<h3 class="c-display p-plans__eng">PACKAGE PLAN</h3>
				<p class="p-plans__desc">人気の外構デザインをベースに、<br class="u-br-sp">必要な要素をわかりやすく整理。<br>予算感をつかみながら、<br class="u-br-sp">スムーズにお選びいただけます。</p>
				<a class="c-btn c-btn--black p-plans__btn" href="#">VIEW MORE</a>
			</div>

			<?php $exterior_exone_first_plan = $exterior_exone_plan_cards[0]; ?>
			<div class="p-plans__media" data-plan-media>
				<img
					src="<?php echo esc_url( exterior_exone_top_image( $exterior_exone_first_plan['image'] ) ); ?>"
					width="900"
					height="454"
					alt="<?php echo esc_attr( $exterior_exone_first_plan['name'] ); ?>の外観イメージ"
					loading="lazy"
				>
			</div>
		</div>
	</div>

	<div class="p-plans__highend">
		<div class="p-plans__highend-media" aria-hidden="true">
			<?php
			// SP の枠は 375x421.875（縦長）で、横長の原版だと cover で左右が大きく
			// 切れる。カンプ 16:111 は 960x1080 の縦位置素材なので、SP はそれを使う。
			?>
			<picture>
				<source
					media="(max-width: 1024px)"
					srcset="<?php echo esc_url( exterior_exone_top_image( 'highend-sp.jpg' ) ); ?>"
					width="960"
					height="1080"
				>
				<img
					src="<?php echo esc_url( exterior_exone_top_image( 'highend.jpg' ) ); ?>"
					width="1161"
					height="667"
					alt=""
					loading="lazy"
				>
			</picture>
		</div>

		<div class="p-plans__text p-plans__text--highend">
			<p class="p-plans__jp">ハイエンドプラン</p>
			<h3 class="c-display p-plans__eng">HIGH-END PLAN</h3>
			<p class="p-plans__desc">敷地条件や暮らしに合わせ、<br class="u-br-sp">細部までこだわりたい方向けの自由設計プラン。<br>理想の外構を一からかたちにします。</p>
			<a class="c-btn c-btn--white p-plans__btn" href="#">VIEW MORE</a>
		</div>
	</div>
</section>
