<?php
/**
 * 企業情報: Why We Exist（私たちの存在意義）。
 *
 * カンプ: PC 917:1532-1553（FV と同じ写真 + 70% 暗幕。右上に線画、下に 3 カード）
 *         SP  917:924-952・917:1047・917:926
 *
 * 3 行コピー（917:1047）は SP カンプにのみ登場する。PC では同じ文がブランド
 * ストーリー本文の冒頭に含まれるため、ここでは SP 専用として出力する。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_why_cards = exterior_exone_company_why_cards();
?>
<section class="p-cwhy" data-section="company-why">
	<div class="p-cwhy__bg" aria-hidden="true">
		<img
			src="<?php echo esc_url( exterior_exone_company_image( 'FV-bg.jpg' ) ); ?>"
			width="1920"
			height="1080"
			alt=""
			loading="lazy"
		>
	</div>

	<div class="p-cwhy__inner">
		<div class="p-cwhy__head">
			<h2 class="c-company-eng p-cwhy__eng">Why We Exist</h2>
			<p class="c-company-jp p-cwhy__jp">私たちの存在意義</p>
			<p class="p-cwhy__lead">エクステリア業界の古い常識を刷新し、<br>新たなスタンダードを<br class="u-br-sp">創造する。</p>
			<?php
			// 外構の線画（PC 483:14 / SP 34:1065）。images/company/line_animation02.svg（2026-09-24
			// 差し替え。TOP とは別の絵）をインラインで置き、画面に入ったら線描画する（js/line-draw.js。
			// 時間は style.css の --diagram-draw-*）。PC・SP ともカンプどおり inner 基準の絶対配置で、
			// PC は右上、SP はリード文の右に重ねる（css/company.css）。
			echo exterior_exone_inline_line_svg( 'images/company/line_animation02.svg', 'p-cwhy__feature' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- テーマ同梱の SVG をヘルパー内で整形済み。
			?>
			<p class="p-cwhy__text">私たちは、不透明な価格、属人的な提案、地域による品質格差といった業界課題に向き合い、テクノロジーとデザインの力で新しい顧客体験の仕組みを構築しています。</p>
		</div>

		<ul class="p-cwhy__cards">
			<?php foreach ( $exterior_exone_why_cards as $exterior_exone_card ) : ?>
				<li class="p-cwhy__card">
					<img
						class="p-cwhy__card-image"
						src="<?php echo esc_url( exterior_exone_company_image( $exterior_exone_card['image'] ) ); ?>"
						width="511"
						height="341"
						alt=""
						loading="lazy"
					>
					<div class="p-cwhy__card-body">
						<p class="c-display p-cwhy__card-eng"><?php echo esc_html( $exterior_exone_card['eng'] ); ?></p>
						<p class="p-cwhy__card-jp"><?php echo esc_html( $exterior_exone_card['jp'] ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>

		<p class="p-cwhy__catch">外構をもっと透明に。<br>もっと分かりやすく。<br>もっとワクワクするものへ。</p>

		<p class="p-cwhy__outro">暮らしに付加価値を届けるために。<br>そして、次世代のエクステリア業界を創るために。<br>業界全体の体験価値を再設計し、誰もが安心して外構を依頼できる未来を創っています。</p>
	</div>
</section>
