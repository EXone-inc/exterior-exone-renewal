<?php
/**
 * 企業情報: Partner Network。
 *
 * カンプ: PC 917:1413-1417・917:1474-1480・917:1524-1531（リード + 4 カード + 日本地図）
 *         SP  917:905-913・917:1014-1017
 *
 * 冒頭のリード（917:1476）は PC カンプのみ。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_partners = exterior_exone_company_partners();
?>
<section class="p-cpartner" data-section="company-partner">
	<div class="p-cpartner__inner">
		<div class="p-cpartner__intro">
			<p class="p-cpartner__intro-eng">BUILDING THE FUTURE OF EXTERIOR</p>
			<p class="p-cpartner__intro-jp">つなげる仕組みで、<br>業界の未来をつくる。</p>
			<p class="p-cpartner__intro-text">EXoneは、デザイン・施工・テクノロジー・教育・ナレッジが循環する独自のエコシステムを構築。すべてのプロセスを可視化し、標準化することで、全国どこでも同じ品質と体験を提供します。</p>
		</div>

		<h2 class="c-display p-cpartner__title">Partner Network</h2>
		<p class="p-cpartner__lead">信頼できるパートナーとともに、<br class="u-br-sp">全国へ高品質なサービスを届けます。</p>

		<ul class="p-cpartner__cards">
			<?php foreach ( $exterior_exone_partners as $exterior_exone_partner ) : ?>
				<li class="p-cpartner__card">
					<img
						src="<?php echo esc_url( exterior_exone_company_image( $exterior_exone_partner['image'] ) ); ?>"
						width="375"
						height="211"
						alt=""
						loading="lazy"
					>
					<p class="p-cpartner__card-label"><?php echo esc_html( $exterior_exone_partner['label'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="p-cpartner__outro">
			<div class="p-cpartner__outro-body">
				<p class="p-cpartner__outro-title"><span class="u-nobr">全国へ展開し、</span><br class="u-br-sp"><span class="u-nobr">業界の新しいスタンダードを創る。</span></p>
				<p class="p-cpartner__outro-desc">EXoneのエコシステムの力で、外構・エクステリア業界の未来をともに創造し、<br class="u-br-pc">すべての暮らしに、価値ある「見える体験」を届けていきます。</p>
			</div>

			<img
				class="p-cpartner__map"
				src="<?php echo esc_url( exterior_exone_company_image( 'ecosystem-map.jpg' ) ); ?>"
				width="836"
				height="499"
				alt="全国のパートナーネットワークを示した日本地図"
				loading="lazy"
			>
		</div>
	</div>
</section>
