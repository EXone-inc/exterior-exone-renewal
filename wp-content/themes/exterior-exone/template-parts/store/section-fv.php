<?php
/**
 * 支店: FV（ヒーロー）。
 *
 * カンプ: PC 612:761（背景 612:154・暗幕 612:155 / 612:156・下端グラデ 612:177・
 *         テキスト 612:166・写真 4 枚 612:760）
 *         SP 638:72〜638:94（背景・暗幕 638:73 / 638:74・下端グラデ 638:81・
 *         テキスト 638:82・写真の列 638:91）
 *
 * 背景は全支店共通の写真 1 枚で固定（決定事項 A。切り替え・サムネイルは無い）。
 * 暗幕は上→下と左→右の 2 枚（.p-sfv::before）。
 *
 * 写真 4 枚（__band）は FV の下端をまたぐため、セクションの外に出して
 * 負のマージンで引き上げている。SP は指で横にスクロールする列になる。
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

$exterior_exone_fv = exterior_exone_store_fv();
?>
<section class="p-sfv" data-section="store-fv">
	<div class="p-sfv__bg" aria-hidden="true">
		<img
			class="p-sfv__image"
			src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_fv['image'] ) ); ?>"
			width="1672"
			height="940"
			alt=""
			fetchpriority="high"
		>
	</div>

	<div class="p-sfv__body">
		<div class="p-sfv__copy">
			<?php // 612:172 + 612:168（支店名バッジ）。ページの主見出しを兼ねる。 ?>
			<h1 class="p-sfv__eyebrow">
				<span class="p-sfv__eyebrow-text"><?php echo esc_html( $exterior_exone_fv['eyebrow'] ); ?></span>
				<span class="p-sfv__badge"><?php echo esc_html( $exterior_exone_store['name'] ); ?></span>
			</h1>

			<?php // 612:170。Antonio SemiBold の 2 行組み。 ?>
			<p class="c-display p-sfv__title">
				<?php foreach ( $exterior_exone_fv['titles'] as $exterior_exone_title ) : ?>
					<span class="p-sfv__title-line"><?php echo esc_html( $exterior_exone_title ); ?></span>
				<?php endforeach; ?>
			</p>

			<p class="p-sfv__lead"><?php echo esc_html( $exterior_exone_fv['lead'] ); ?></p>

			<?php // 612:167。白の EX ONE ロゴ。 ?>
			<p class="p-sfv__logo">
				<img
					src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_fv['logo'] ) ); ?>"
					width="600"
					height="92"
					alt="EX ONE"
				>
			</p>
		</div>
	</div>
</section>

<?php // 612:760 / 638:91。FV と導入コピーの境界をまたぐ写真 4 枚。 ?>
<div class="p-sfv__band">
	<ul class="p-sfv__band-list">
		<?php foreach ( $exterior_exone_fv['photos'] as $exterior_exone_photo ) : ?>
			<li class="p-sfv__band-cell">
				<img
					class="p-sfv__band-item"
					src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_photo ) ); ?>"
					width="1200"
					height="628"
					alt=""
					loading="lazy"
				>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
