<?php
/**
 * 企業情報: Partner Network。
 *
 * カンプ: PC 917:1413-1417・917:1474-1480・917:1524-1531（リード + 4 カード + 日本地図）
 *         SP  917:905-913・917:1014-1017
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

		<?php
		// SP カンプ（34:1173-1176）はカードの下にラベルだけを縦に並べる。
		// PC ではカード内のラベルを使うため、この一覧は SP でだけ表示する
		// （display: none で切り替えるので読み上げが二重になることはない）。
		?>
		<ul class="p-cpartner__label-list">
			<?php foreach ( $exterior_exone_partners as $exterior_exone_partner ) : ?>
				<li class="p-cpartner__label-item"><?php echo esc_html( $exterior_exone_partner['label'] ); ?></li>
			<?php endforeach; ?>
		</ul>

		<div class="p-cpartner__outro">
			<div class="p-cpartner__outro-body">
				<p class="p-cpartner__outro-title"><span class="u-nobr">全国へ展開し、</span><br class="u-br-sp"><span class="u-nobr">業界の新しいスタンダードを創る。</span></p>
				<p class="p-cpartner__outro-desc">EXoneのエコシステムの力で、外構・エクステリア業界の未来をともに創造し、<br class="u-br-pc">すべての暮らしに、価値ある「見える体験」を届けていきます。</p>
			</div>

			<?php
			// 地図の上に、支給アニメーション（日本地図アニメーション.html）の
			// 経路・拠点・粒子を SVG で重ねる。座標はすべて地図の実寸 1672x941 基準（枠はカンプどおり少し縦長で、画像と SVG は左寄せで右端を切り落として揃える）。
			// id は文書内で衝突しないよう exone-map- を頭に付ける。
			// 画面に入ると .is-inview が付いて動き出す（js/scroll-reveal.js）。

			// 拠点をつなぐ弧。--i の順に引かれる。
			$exterior_exone_map_arcs = array(
				'M279 684 Q520 365 831 500',
				'M475 552 Q690 332 831 500',
				'M574 620 Q720 475 831 500',
				'M755 592 Q805 520 831 500',
				'M831 500 Q1075 200 1327 181',
				'M831 500 Q1110 322 1187 434',
				'M831 500 Q1075 465 1117 536',
				'M1117 536 Q1220 404 1260 350',
			);

			// 拠点。halo は淡いにじみ、core は中心の点（単位は地図座標）。
			$exterior_exone_map_nodes = array(
				array( 279, 684, 18, 4, 3.2 ),
				array( 475, 552, 16, 4, 3 ),
				array( 574, 620, 17, 4, 3 ),
				array( 755, 592, 16, 4, 3 ),
				array( 831, 500, 28, 5, 4.5 ),
				array( 1117, 536, 24, 5, 4 ),
				array( 1187, 434, 19, 4, 3.4 ),
				array( 1327, 181, 23, 5, 4 ),
			);

			// 弧の上を流れる粒子。array( 弧の番号, 半径, 一周の秒数, 開始の遅れ )。
			$exterior_exone_map_sparks = array(
				array( 1, 2.5, '3.6s', '0.3s' ),
				array( 5, 2.5, '4.5s', '1.1s' ),
				array( 6, 2, '3.9s', '2s' ),
				array( 2, 2, '3.2s', '1.7s' ),
			);
			?>
			<figure class="p-cpartner__map" data-reveal>
				<img
					class="p-cpartner__map-image"
					src="<?php echo esc_url( exterior_exone_company_image( 'ecosystem-map.jpg' ) ); ?>"
					width="1672"
					height="941"
					alt="全国のパートナーネットワークを示した日本地図"
					loading="lazy"
				>

				<?php // 地図の中心あたりでゆっくり明滅する光。 ?>
				<span class="p-cpartner__map-glow" aria-hidden="true"></span>

				<svg
					class="p-cpartner__map-net"
					viewBox="0 0 1672 941"
					preserveAspectRatio="xMinYMid slice"
					aria-hidden="true"
					focusable="false"
				>
					<defs>
						<?php // 色は css/company.css のトークンで持つ（stop-color を CSS 側から当てる）。 ?>
						<linearGradient id="exone-map-line" x1="0" y1="0" x2="1" y2="0">
							<stop class="p-cpartner__map-stop--edge" offset="0" stop-opacity="0.22"></stop>
							<stop class="p-cpartner__map-stop--mid" offset="0.5" stop-opacity="0.95"></stop>
							<stop class="p-cpartner__map-stop--edge" offset="1" stop-opacity="0.22"></stop>
						</linearGradient>
						<filter id="exone-map-glow" x="-500%" y="-500%" width="1000%" height="1000%">
							<feGaussianBlur stdDeviation="4" result="b"></feGaussianBlur>
							<feMerge>
								<feMergeNode in="b"></feMergeNode>
								<feMergeNode in="b"></feMergeNode>
								<feMergeNode in="SourceGraphic"></feMergeNode>
							</feMerge>
						</filter>
						<filter id="exone-map-blur" x="-500%" y="-500%" width="1000%" height="1000%">
							<feGaussianBlur stdDeviation="8"></feGaussianBlur>
						</filter>
					</defs>

					<?php foreach ( $exterior_exone_map_arcs as $exterior_exone_index => $exterior_exone_arc ) : ?>
						<path
							id="exone-map-p<?php echo esc_attr( (string) ( $exterior_exone_index + 1 ) ); ?>"
							class="p-cpartner__map-arc"
							style="--i:<?php echo esc_attr( (string) $exterior_exone_index ); ?>"
							d="<?php echo esc_attr( $exterior_exone_arc ); ?>"
						></path>
					<?php endforeach; ?>

					<?php // 弧の下敷きになる破線。ゆっくり流れ続ける。 ?>
					<path class="p-cpartner__map-trace" d="M280 684 C460 625 640 560 831 500 C1020 442 1170 325 1327 181"></path>

					<?php foreach ( $exterior_exone_map_nodes as $exterior_exone_index => $exterior_exone_node ) : ?>
						<g
							class="p-cpartner__map-node"
							style="--i:<?php echo esc_attr( (string) $exterior_exone_index ); ?>"
							transform="translate(<?php echo esc_attr( (string) $exterior_exone_node[0] ); ?> <?php echo esc_attr( (string) $exterior_exone_node[1] ); ?>)"
						>
							<circle class="p-cpartner__map-halo" r="<?php echo esc_attr( (string) $exterior_exone_node[2] ); ?>"></circle>
							<circle class="p-cpartner__map-ring" r="<?php echo esc_attr( (string) $exterior_exone_node[3] ); ?>"></circle>
							<circle class="p-cpartner__map-core" r="<?php echo esc_attr( (string) $exterior_exone_node[4] ); ?>"></circle>
						</g>
					<?php endforeach; ?>

					<?php foreach ( $exterior_exone_map_sparks as $exterior_exone_spark ) : ?>
						<circle class="p-cpartner__map-spark" r="<?php echo esc_attr( (string) $exterior_exone_spark[1] ); ?>">
							<animateMotion
								dur="<?php echo esc_attr( $exterior_exone_spark[2] ); ?>"
								begin="<?php echo esc_attr( $exterior_exone_spark[3] ); ?>"
								repeatCount="indefinite"
							>
								<mpath href="#exone-map-p<?php echo esc_attr( (string) $exterior_exone_spark[0] ); ?>"></mpath>
							</animateMotion>
						</circle>
					<?php endforeach; ?>
				</svg>

				<?php // 上から下へ流れる走査線。 ?>
				<span class="p-cpartner__map-scan" aria-hidden="true"></span>
			</figure>
		</div>
	</div>
</section>
