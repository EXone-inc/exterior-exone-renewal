<?php
/**
 * 企業情報: EXone Ecosystem（事業構造）。
 *
 * カンプ: PC 917:1411-1412・917:1472-1473・917:1481-1523（左に Our Purpose、右に円環図）
 *         SP  917:976-1013（Our Purpose の下に 6 項目のリスト）
 *
 * 円環図（917:1481）はテキストごと 1 枚に焼き込まれた支給画像。PC ではこの図を、
 * SP では同じ内容のリストを見せるため、リストは PC で読み上げ用に隠す。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_eco_items = exterior_exone_company_ecosystem_items();
$exterior_exone_eco_lead  = 'EXoneは、デザイン・施工・テクノロジー・教育・ナレッジが循環する独自のエコシステムを構築。すべてのプロセスを可視化し、標準化することで、全国どこでも同じ品質と体験を提供します。';
?>
<section class="p-ceco" data-section="company-ecosystem">
	<div class="p-ceco__bg" aria-hidden="true">
		<img
			src="<?php echo esc_url( exterior_exone_company_image( 'ecosystem-bg.jpg' ) ); ?>"
			width="1920"
			height="758"
			alt=""
			loading="lazy"
		>
	</div>

	<div class="p-ceco__inner">
		<div class="p-ceco__head">
			<h2 class="c-company-eng p-ceco__eng">EXone Ecosystem</h2>
			<p class="c-company-jp">事業構造</p>
		</div>

		<div class="p-ceco__body">
			<div class="p-ceco__purpose">
				<div class="p-ceco__purpose-head">
					<img
						class="p-ceco__purpose-icon"
						src="<?php echo esc_url( exterior_exone_company_image( 'ecosystem-logo.png' ) ); ?>"
						alt=""
						loading="lazy"
					>
					<div class="p-ceco__purpose-title">
						<p class="p-ceco__purpose-eng">Our Purpose</p>
						<p class="p-ceco__purpose-jp">見える体験をすべての人へ。</p>
					</div>
				</div>

				<p class="p-ceco__purpose-text"><?php echo esc_html( $exterior_exone_eco_lead ); ?></p>
			</div>

			<figure class="p-ceco__figure" aria-hidden="true">
				<img
					src="<?php echo esc_url( exterior_exone_company_image( 'ecosystem-feature.png' ) ); ?>"
					width="906"
					height="751"
					alt=""
					loading="lazy"
				>
			</figure>

			<ul class="p-ceco__list">
				<?php foreach ( $exterior_exone_eco_items as $exterior_exone_item ) : ?>
					<li class="p-ceco__item">
						<div class="p-ceco__item-head">
							<p class="p-ceco__item-jp"><?php echo esc_html( $exterior_exone_item['jp'] ); ?></p>
							<p class="p-ceco__item-eng"><?php echo esc_html( $exterior_exone_item['eng'] ); ?></p>
						</div>
						<p class="p-ceco__item-desc"><?php echo nl2br( esc_html( $exterior_exone_item['desc'] ) ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
