<?php
/**
 * 企業情報: 私たちを支える価値観（Our Principles）。
 *
 * カンプ: PC 917:1418-1466（510.26x656.94 のカード 3 枚。番号・アイコン・英字・説明・写真）
 *         SP  917:916-970（写真 + アイコン + テキストが横に並ぶ帯 3 本。左右交互）
 *
 * PC と SP で並び順が変わるため、DOM は SP の順（写真 → アイコン → テキスト）で置き、
 * PC 側は css/company.css の order で組み替える。番号は SP カンプには無い。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_principles = exterior_exone_company_principles();
?>
<section class="p-cvalue" data-section="company-principles">
	<div class="p-cvalue__inner">
		<h2 class="c-company-eng">Our Principles</h2>
		<p class="c-company-jp">私たちを支える価値観</p>

		<ul class="p-cvalue__cards">
			<?php foreach ( $exterior_exone_principles as $exterior_exone_item ) : ?>
				<li class="p-cvalue__card">
					<div class="p-cvalue__card-media">
						<img
							src="<?php echo esc_url( exterior_exone_company_image( $exterior_exone_item['image'] ) ); ?>"
							width="510"
							height="277"
							alt=""
							loading="lazy"
						>
					</div>

					<div class="p-cvalue__card-head">
						<p class="p-cvalue__number"><?php echo esc_html( $exterior_exone_item['number'] ); ?></p>
						<img
							class="p-cvalue__icon"
							src="<?php echo esc_url( exterior_exone_company_image( $exterior_exone_item['icon'] ) ); ?>"
							alt=""
							loading="lazy"
						>
					</div>

					<div class="p-cvalue__card-body">
						<p class="c-display p-cvalue__card-eng"><?php echo esc_html( $exterior_exone_item['eng'] ); ?></p>
						<p class="p-cvalue__card-desc"><?php echo nl2br( esc_html( $exterior_exone_item['desc'] ) ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
