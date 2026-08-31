<?php
/**
 * TOP: WORKS セクション。
 *
 * 管理画面の施工事例のうち「トップページに記載する」に入れたものを
 * 新しい順に最大 5 件出す。カードは画像の 1 枚目だけで、タイトルは
 * カルーセルの下（カンプ 917:117 の枠）に選択中のものを出す。
 *
 * 見た目はカンプ（PC 917:113-123 の扇状 / SP 917:872-882）のまま、
 * 動きは rara.ritsumei.ac.jp の FELLOWS セクションを踏襲した
 * 円弧（観覧車）カルーセル。ドラッグで輪が回り、左右ゾーンの
 * クリックで 1 枚ずつ送る（PC はカーソル追従の NEXT / PREV / DRAG 表示）。
 * カードの複製・円弧配置・回転は js/works-carousel.js が行い、
 * JS が無効の間はカードを横並びで全件見せる。
 *
 * 施工事例が 1 件も無いときはセクションごと出さない。
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

		<div class="p-works__slider" data-works-carousel>
			<ul class="p-works__list" data-works-list>
				<?php foreach ( $exterior_exone_works as $exterior_exone_work ) : ?>
					<li class="p-works__card" data-works-title="<?php echo esc_attr( $exterior_exone_work['title'] ); ?>">
						<a class="p-works__card-link" href="<?php echo esc_url( $exterior_exone_work['url'] ); ?>">
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

			<?php // 左右の送りゾーン（透明）。PC ではこの上でマウスストーカーが NEXT / PREV になる。 ?>
			<button type="button" class="p-works__nav p-works__nav--prev" data-works-prev>
				<span class="screen-reader-text">前の施工事例</span>
			</button>
			<button type="button" class="p-works__nav p-works__nav--next" data-works-next>
				<span class="screen-reader-text">次の施工事例</span>
			</button>

			<?php // カーソル追従の円（PC のみ表示）。ラベルは JS が NEXT / PREV / DRAG に切り替える。 ?>
			<div class="p-works__stalker" data-works-stalker aria-hidden="true">
				<span class="p-works__stalker-circle"></span>
				<span class="p-works__stalker-text" data-works-stalker-text>NEXT</span>
			</div>
		</div>

		<?php // 中身は js/works-carousel.js が選択中のカードのタイトルに差し替える。 ?>
		<p class="p-works__desc" data-works-desc aria-live="polite"><?php echo esc_html( $exterior_exone_works[0]['title'] ); ?></p>

		<?php // 現在何枚目かを示すドット。選択状態は js/works-carousel.js が切り替える。 ?>
		<?php // 読み上げは上のタイトル（aria-live）が担うため装飾扱いにする。 ?>
		<?php if ( count( $exterior_exone_works ) > 1 ) : ?>
			<div class="p-works__dots" data-works-dots aria-hidden="true">
				<?php foreach ( array_keys( $exterior_exone_works ) as $exterior_exone_works_index ) : ?>
					<span class="p-works__dot<?php echo 0 === $exterior_exone_works_index ? ' is-active' : ''; ?>"></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $exterior_exone_works_archive ) : ?>
			<a class="c-btn c-btn--white p-works__btn" href="<?php echo esc_url( $exterior_exone_works_archive ); ?>">VIEW MORE</a>
		<?php endif; ?>
	</div>
</section>
