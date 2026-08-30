<?php
/**
 * 企業情報: EXone Quality。
 *
 * カンプ: PC 917:1425・917:1442-1444・917:1467-1471（左にテキスト、右に写真）
 *         SP  917:972-975（写真の下にテキスト）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_quality_items = array(
	'営業・プランナー・施工管理色の教育プロセス',
	'品質管理、ナレッジ共有',
);
?>
<section class="p-cquality" data-section="company-quality">
	<div class="p-cquality__media">
		<img
			src="<?php echo esc_url( exterior_exone_company_image( 'qualitiy-bg.jpg' ) ); ?>"
			width="1129"
			height="641"
			alt=""
			loading="lazy"
		>
	</div>

	<div class="p-cquality__body">
		<h2 class="c-company-eng p-cquality__eng">EXone Quality</h2>
		<p class="c-company-jp p-cquality__jp">属人化せず、<br class="u-br-sp">再現性がある仕組みの証明</p>

		<ul class="p-cquality__list">
			<?php foreach ( $exterior_exone_quality_items as $exterior_exone_item ) : ?>
				<li class="p-cquality__item"><?php echo esc_html( $exterior_exone_item ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
