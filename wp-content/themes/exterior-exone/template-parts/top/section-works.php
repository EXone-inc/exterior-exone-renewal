<?php
/**
 * TOP: WORKS セクション。
 *
 * 管理画面の施工事例のうち「トップページに記載する」に入れたものを
 * 新しい順に最大 5 件出す。カードは画像の 1 枚目だけで、タイトルは
 * カルーセルの下（カンプ 917:117 の枠）に選択中のものを出す。
 *
 * 施工事例が 1 件も無いときはセクションごと出さない。
 *
 * カンプ: PC 917:113-123（中央のアクティブカードを軸に左右へ傾けた扇状の並び）
 *         SP  917:872-882（傾きなしの横スクロール）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_works = exterior_exone_works_cards();

if ( ! $exterior_exone_works ) {
	return;
}

$exterior_exone_works_archive = get_post_type_archive_link( 'works' );
?>
<section class="p-works" data-section="works">
	<div class="p-works__inner">
		<h2 class="c-section-title p-works__title">WORKS</h2>

		<div class="p-works__slider swiper" data-works-carousel>
			<ul class="p-works__list swiper-wrapper">
				<?php foreach ( $exterior_exone_works as $exterior_exone_work ) : ?>
					<li class="p-works__card swiper-slide" data-works-title="<?php echo esc_attr( $exterior_exone_work['title'] ); ?>">
						<a href="<?php echo esc_url( $exterior_exone_work['url'] ); ?>">
							<?php if ( $exterior_exone_work['image_id'] ) : ?>
								<?php
								echo wp_get_attachment_image(
									$exterior_exone_work['image_id'],
									'large',
									false,
									array(
										'alt'     => '',
										'loading' => 'lazy',
									)
								);
								?>
							<?php else : ?>
								<?php // 画像未設定。カードの箱だけ残して灰色で埋める。 ?>
								<span class="p-works__card-empty" aria-hidden="true"></span>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php // 中身は js/works-carousel.js が選択中のカードのタイトルに差し替える。 ?>
		<p class="p-works__desc" data-works-desc><?php echo esc_html( $exterior_exone_works[0]['title'] ); ?></p>

		<?php if ( $exterior_exone_works_archive ) : ?>
			<a class="c-btn c-btn--white p-works__btn" href="<?php echo esc_url( $exterior_exone_works_archive ); ?>">VIEW MORE</a>
		<?php endif; ?>
	</div>
</section>
