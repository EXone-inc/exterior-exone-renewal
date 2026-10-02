<?php
/**
 * 支店: REVIEWS。
 *
 * カンプ: PC 612:819（612:261・612:294・612:290 見出し 3 点セット / カード 612:460
 *         498.1x515.5・間隔 36）/ SP 641:396〜641:416（280 幅のカードを指で横にスクロール。
 *         2 枚目が右端で見切れる）
 *
 * guide 617:47「カードのみ下からフェードイン」: カード 3 枚にだけ data-reveal を付ける
 *（見出しは動かさない。js/scroll-reveal.js）。SP の列で画面の外にあるカードは、
 * 横にスクロールして見えたときにフェードインする。
 *
 * 英字見出しはカンプの「REVIERS」を誤記とみなし REVIEWS で出す。
 * 画像・タイトル・本文はカンプどおりダミー（inc/store-data.php）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_reviews = exterior_exone_store_reviews();
?>
<section class="p-sreviews" data-section="store-reviews">
	<h2 class="c-store-title"><?php echo esc_html( $exterior_exone_reviews['title'] ); ?></h2>
	<p class="c-store-jp"><?php echo esc_html( $exterior_exone_reviews['jp'] ); ?></p>

	<p class="c-store-lead">
		<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_reviews['lead'] ), array( 'br' => array() ) ); ?>
	</p>

	<ul class="p-sreviews__list">
		<?php foreach ( $exterior_exone_reviews['cards'] as $exterior_exone_card ) : ?>
			<li class="p-sreviews__card" data-reveal>
				<?php // カンプの画像は灰色のプレースホルダ（写真は未支給）。 ?>
				<span class="p-sreviews__thumb" aria-hidden="true"></span>

				<div class="p-sreviews__body">
					<p class="p-sreviews__rating">
						<img
							class="p-sreviews__stars"
							src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_reviews['stars'] ) ); ?>"
							width="155"
							height="27"
							alt=""
							loading="lazy"
						>
						<span class="p-sreviews__score"><?php echo esc_html( $exterior_exone_card['score'] ); ?></span>
					</p>

					<p class="p-sreviews__title"><?php echo esc_html( $exterior_exone_card['title'] ); ?></p>

					<?php // 本文が長いときは 3 行で省略する（決定事項 Q10）。 ?>
					<p class="p-sreviews__text"><?php echo esc_html( $exterior_exone_card['body'] ); ?></p>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
