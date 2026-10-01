<?php
/**
 * PLANS: PACKAGE PLAN の詳細。
 *
 * カンプ: PC 586:561（1920x2669。plan name 586:556 / img list 586:409 / copy 586:557 /
 *         point 586:558 / style view 586:560 / style list 586:559 / btn 586:481）
 *         SP  586:1054 ほか（375x1406）
 *
 * カンプ上端の header 612:56 は既存の共通ヘッダー、タブ 611:16 は別スプリントで作るため
 * ここには置かない。プラン名はヘッダー 154 の下（上端 +270 / +294）。
 * img list は 1 本の列（js/plans-imglist.js が複製して自動スクロール）。style list のカードは
 * 5 件ぶんの英名・説明文・画像を data-plp-style-* に持ち、style view の
 * data-plp-view の各要素を差し替える（TOP の data-plan-* とは別名）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_plans_package = exterior_exone_plans_package();
$exterior_exone_plans_styles  = exterior_exone_plans_styles();
$exterior_exone_first_style   = $exterior_exone_plans_styles[0];
?>
<section id="package" class="p-plppkg" data-section="plans-package" data-plp-plan="package">
	<div class="p-plppkg__name" data-plp-plan-name>
		<p class="p-plppkg__jp"><?php echo esc_html( $exterior_exone_plans_package['name'] ); ?></p>
		<h2 class="p-plppkg__eng"><?php echo esc_html( $exterior_exone_plans_package['eng'] ); ?></h2>
	</div>

	<div class="p-plppkg__imglist" aria-hidden="true">
		<ul class="p-plppkg__imglist-row" data-plp-imglist>
			<?php foreach ( $exterior_exone_plans_package['imglist'] as $exterior_exone_item ) : ?>
				<li
					class="p-plppkg__imglist-item"
					style="--w:<?php echo esc_attr( (string) $exterior_exone_item['w'] ); ?>;--h:<?php echo esc_attr( (string) $exterior_exone_item['h'] ); ?>;--crop-w:<?php echo esc_attr( $exterior_exone_item['crop'][0] ); ?>;--crop-x:<?php echo esc_attr( $exterior_exone_item['crop'][1] ); ?>;--crop-y:<?php echo esc_attr( $exterior_exone_item['crop'][2] ); ?>"
				>
					<img
						src="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_item['file'] ) ); ?>"
						width="<?php echo esc_attr( (string) $exterior_exone_item['img'][0] ); ?>"
						height="<?php echo esc_attr( (string) $exterior_exone_item['img'][1] ); ?>"
						alt=""
						loading="lazy"
					>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="p-plppkg__copy">
		<h3 class="p-plppkg__heading"><?php echo esc_html( $exterior_exone_plans_package['heading'] ); ?></h3>
		<?php // PC と SP で本文が別文（586:435 / 586:1064）。幅ごとに出し分ける。 ?>
		<p class="p-plppkg__body p-plppkg__body--pc">
			<?php echo wp_kses( implode( '<br>', array_map( 'esc_html', $exterior_exone_plans_package['body_pc'] ) ), array( 'br' => array() ) ); ?>
		</p>
		<div class="p-plppkg__body p-plppkg__body--sp">
			<?php foreach ( $exterior_exone_plans_package['body_sp'] as $exterior_exone_para ) : ?>
				<p><?php echo esc_html( $exterior_exone_para ); ?></p>
			<?php endforeach; ?>
		</div>
	</div>

	<ul class="p-plppkg__points">
		<?php foreach ( $exterior_exone_plans_package['points'] as $exterior_exone_point ) : ?>
			<li class="p-plppkg__point">
				<span><?php echo wp_kses( implode( '<br>', array_map( 'esc_html', $exterior_exone_point ) ), array( 'br' => array() ) ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php // style view。初期表示は 01。カードを押すと data-plp-view の各要素を差し替える（js/plans-style.js）。 ?>
	<div class="p-plppkg__view" data-plp-view-root>
		<div class="p-plppkg__view-media">
			<img
				src="<?php echo esc_url( exterior_exone_top_image( $exterior_exone_first_style['image'] ) ); ?>"
				width="1800"
				height="909"
				alt="<?php echo esc_attr( $exterior_exone_first_style['name'] ); ?>の外観イメージ"
				loading="lazy"
				data-plp-view="image"
			>
		</div>

		<div class="p-plppkg__view-text">
			<p class="p-plppkg__view-jp" data-plp-view="name"><?php echo esc_html( $exterior_exone_first_style['name'] ); ?></p>
			<h3 class="p-plppkg__view-eng" data-plp-view="eng"><?php echo esc_html( $exterior_exone_first_style['eng'] ); ?></h3>
			<div class="p-plppkg__view-desc" data-plp-view="desc">
				<?php foreach ( $exterior_exone_first_style['desc'] as $exterior_exone_para ) : ?>
					<p><?php echo esc_html( $exterior_exone_para ); ?></p>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<?php // style list。横スクロールの列（右端は画面外へ続く）。マウスのドラッグは js/scroll-row.js、切り替えは js/plans-style.js。 ?>
	<ul class="p-plppkg__list" data-plp-style-list data-scroll-row>
		<?php foreach ( $exterior_exone_plans_styles as $exterior_exone_index => $exterior_exone_style ) : ?>
			<li class="p-plppkg__card<?php echo 0 === $exterior_exone_index ? ' is-active' : ''; ?>">
				<button
					type="button"
					class="p-plppkg__card-button"
					data-plp-style-image="<?php echo esc_url( exterior_exone_top_image( $exterior_exone_style['image'] ) ); ?>"
					data-plp-style-name="<?php echo esc_attr( $exterior_exone_style['name'] ); ?>"
					data-plp-style-eng="<?php echo esc_attr( $exterior_exone_style['eng'] ); ?>"
					data-plp-style-desc="<?php echo esc_attr( implode( "\n", $exterior_exone_style['desc'] ) ); ?>"
					aria-pressed="<?php echo 0 === $exterior_exone_index ? 'true' : 'false'; ?>"
				>
					<span class="p-plppkg__card-num"><?php echo esc_html( sprintf( '%02d', $exterior_exone_index + 1 ) ); ?></span>
					<img
						class="p-plppkg__card-image"
						src="<?php echo esc_url( exterior_exone_top_image( $exterior_exone_style['image'] ) ); ?>"
						width="1800"
						height="909"
						alt=""
						loading="lazy"
					>
					<span class="p-plppkg__card-name"><?php echo esc_html( $exterior_exone_style['name'] ); ?></span>
				</button>
			</li>
		<?php endforeach; ?>
	</ul>

	<div class="p-plppkg__more">
		<a class="c-btn c-btn--black" href="<?php echo esc_url( $exterior_exone_plans_package['more'] ); ?>"<?php echo ! empty( $exterior_exone_plans_package['more_new_tab'] ) ? ' target="_blank" rel="noopener"' : ''; ?>>VIEW MORE</a>
	</div>
</section>
