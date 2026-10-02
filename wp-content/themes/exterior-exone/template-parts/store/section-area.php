<?php
/**
 * 支店: AREA（対応エリア・店舗情報・店内写真のアーチ状ロール・Local Expertise）。
 *
 * カンプ: PC 612:855 = 店舗写真 612:115 / 「AREA」612:263 /
 *         店舗ボックス 612:637（白パネル 612:638・黒帯 612:659・リード 612:658・
 *         店舗情報 612:640・外観写真 612:639・ロゴ 612:665・支店名 612:664）/
 *         弧状ギャラリー 612:661（個別写真 641:1464）/
 *         Local Expertise 612:853 / カード 4 枚 612:673
 *         SP 641:1315〜641:1427（ギャラリーは直線の列、カードは Local Expertise の白パネル内に 2×2）
 *
 * 店内写真は 12 枚の枠に支店の写真を繰り返して入れ、js/store-arch.js が
 * PC は円筒（アーチ）に沿って、SP は直線で右から左へ流す（決定事項 A）。
 * 初期位置（--st-arch-a = 角度 / --st-arch-s = 列の何枚目か）はここで入れておき、
 * 動きを減らす設定・JS 無効ではこの並びのまま静止する。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_store = isset( $args['store'] ) ? $args['store'] : exterior_exone_current_store();

if ( ! $exterior_exone_store ) {
	return;
}

$exterior_exone_area = exterior_exone_store_area( $exterior_exone_store );

// 円筒 1 周の枠の数（30° おき。612:661 の並び）。支店の写真の枚数で割り切れると継ぎ目なく繰り返す。
$exterior_exone_arch_slots = 12;
$exterior_exone_arch_count = count( $exterior_exone_area['gallery'] );
?>
<section class="p-sarea" data-section="store-area">
	<?php // 612:115。上端の店舗内観写真。見出しだけがこの上に乗る。 ?>
	<div class="p-sarea__bg" aria-hidden="true">
		<img
			src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_area['image'] ) ); ?>"
			width="1920"
			height="934"
			alt=""
			loading="lazy"
		>
	</div>

	<h2 class="c-store-title p-sarea__title"><?php echo esc_html( $exterior_exone_area['title'] ); ?></h2>

	<div class="p-sarea__panel">
		<?php // 612:659 / 612:660。パネル上端にかぶる黒帯（SP は全幅・2 行）。 ?>
		<p class="p-sarea__banner"><?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_area['banner'] ), array( 'br' => array() ) ); ?></p>

		<p class="p-sarea__lead">
			<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_area['lead'] ), array( 'br' => array() ) ); ?>
		</p>

		<div class="p-sarea__info">
			<dl class="p-sarea__table">
				<?php foreach ( $exterior_exone_area['rows'] as $exterior_exone_row ) : ?>
					<div class="p-sarea__row">
						<dt class="p-sarea__row-label"><?php echo esc_html( $exterior_exone_row['label'] ); ?></dt>
						<dd class="p-sarea__row-value">
							<?php if ( is_array( $exterior_exone_row['value'] ) ) : ?>
								<?php // 所在地は SP で郵便番号のあとを改行する（641:1327）。 ?>
								<?php foreach ( $exterior_exone_row['value'] as $exterior_exone_part ) : ?>
									<span class="p-sarea__row-part"><?php echo esc_html( $exterior_exone_part ); ?></span>
								<?php endforeach; ?>
							<?php else : ?>
								<?php echo esc_html( $exterior_exone_row['value'] ); ?>
							<?php endif; ?>
						</dd>
					</div>
				<?php endforeach; ?>
			</dl>

			<img
				class="p-sarea__photo"
				src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_area['photo'] ) ); ?>"
				width="1030"
				height="686"
				alt="<?php echo esc_attr( $exterior_exone_store['name'] ); ?>の外観"
				loading="lazy"
			>
		</div>

		<p class="p-sarea__logo">
			<img
				src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_area['logo'] ) ); ?>"
				width="577"
				height="86"
				alt="EX ONE"
				loading="lazy"
			>
		</p>

		<p class="p-sarea__branch"><?php echo esc_html( $exterior_exone_area['branch'] ); ?></p>
	</div>

	<?php if ( $exterior_exone_arch_count ) : ?>
		<?php // 612:661 / 641:1464。店内写真のアーチ状エンドレスロール。2 周目以降の写真は複製なので支援技術から隠す。 ?>
		<div class="p-sarea__arch" data-store-arch>
			<ul class="p-sarea__arch-list">
				<?php for ( $exterior_exone_slot = 0; $exterior_exone_slot < $exterior_exone_arch_slots; $exterior_exone_slot++ ) : ?>
					<?php
					$exterior_exone_photo = $exterior_exone_area['gallery'][ $exterior_exone_slot % $exterior_exone_arch_count ];
					$exterior_exone_clone = $exterior_exone_slot >= $exterior_exone_arch_count;
					// 2 枚目（添字 1）が正面に来る角度から 1 枠ずつ左（角度が小さい側）へ並べる（612:661）。
					$exterior_exone_angle = fmod( ( 1 - $exterior_exone_slot ) * 360 / $exterior_exone_arch_slots + 540, 360 ) - 180;
					$exterior_exone_angle = -180.0 === (float) $exterior_exone_angle ? 180 : $exterior_exone_angle;
					?>
					<li
						class="p-sarea__arch-item"
						style="<?php echo esc_attr( '--st-arch-a: ' . round( $exterior_exone_angle, 3 ) . '; --st-arch-s: ' . ( ( $exterior_exone_slot + 1 ) % $exterior_exone_arch_slots - 1 ) . ';' ); ?>"
						<?php echo $exterior_exone_clone ? 'aria-hidden="true"' : ''; ?>
					>
						<img
							class="p-sarea__arch-image"
							src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_photo['file'] ) ); ?>"
							style="<?php echo esc_attr( 'object-position: ' . $exterior_exone_photo['position'] . ';' ); ?>"
							width="960"
							height="640"
							alt="<?php echo $exterior_exone_clone ? '' : esc_attr( $exterior_exone_store['name'] . 'の店内' ); ?>"
							loading="lazy"
						>
					</li>
				<?php endfor; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php // SP はこの箱が白パネル（641:1379）になり、カード 4 枚も中に入る。 ?>
	<div class="p-sarea__expertise">
		<div class="p-sarea__local">
			<div class="p-sarea__local-body">
				<p class="p-sarea__local-eng"><?php echo esc_html( $exterior_exone_area['local']['eng'] ); ?></p>

				<h3 class="p-sarea__local-heading">
					<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_area['local']['heading'] ), array( 'br' => array() ) ); ?>
				</h3>

				<p class="p-sarea__local-lead">
					<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_area['local']['lead'] ), array( 'br' => array() ) ); ?>
				</p>
			</div>

			<img
				class="p-sarea__local-photo"
				src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_area['local']['photo'] ) ); ?>"
				width="1915"
				height="1157"
				alt=""
				loading="lazy"
			>
		</div>

		<ul class="p-sarea__cards">
			<?php foreach ( $exterior_exone_area['cards'] as $exterior_exone_card ) : ?>
				<li class="p-sarea__card">
					<p class="p-sarea__card-number"><?php echo esc_html( $exterior_exone_card['number'] ); ?></p>

					<img
						class="p-sarea__card-photo"
						src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_card['image'] ) ); ?>"
						width="729"
						height="729"
						alt=""
						loading="lazy"
					>

					<p class="p-sarea__card-label">
						<span class="p-sarea__card-label-pc"><?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_card['label'] ), array( 'br' => array() ) ); ?></span>
						<span class="p-sarea__card-label-sp"><?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_card['label_sp'] ), array( 'br' => array() ) ); ?></span>
					</p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
