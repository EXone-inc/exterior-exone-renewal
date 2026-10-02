<?php
/**
 * 支店: FAQ（アコーディオン）。
 *
 * カンプ: PC 612:843（612:262・612:293・612:289 見出し 3 点セット / 質問行 612:536
 *         1200x149.3・radius 10・ピッチ 169.3 / 開いた状態 641:1462 = 行 264 高）/
 *         SP 641:1264〜641:1300（白背景・行 337x43・ピッチ 52 / 開いた状態 666:32 = 337x194）
 *
 * guide 617:50「プルダウン」。山形は閉じているとき右向き、開くと下向き。
 *
 * 質問行を押すと回答が開き、開くのは 1 つだけ（js/store-faq.js）。
 * 回答文はカンプに無いため仮文（inc/store-data.php の TODO）。
 * JS が動かないときは回答を最初から出す（css/store.css の @media (scripting: none)）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_faq = exterior_exone_store_faq();
?>
<section class="p-sfaq" data-section="store-faq">
	<h2 class="c-store-title"><?php echo esc_html( $exterior_exone_faq['title'] ); ?></h2>
	<p class="c-store-jp"><?php echo esc_html( $exterior_exone_faq['jp'] ); ?></p>

	<p class="c-store-lead p-sfaq__lead">
		<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_faq['lead'] ), array( 'br' => array() ) ); ?>
	</p>

	<div class="p-sfaq__list" data-sfaq>
		<?php foreach ( $exterior_exone_faq['items'] as $exterior_exone_index => $exterior_exone_item ) : ?>
			<?php $exterior_exone_answer_id = 'store-faq-answer-' . ( $exterior_exone_index + 1 ); ?>
			<div class="p-sfaq__item">
				<h3 class="p-sfaq__heading">
					<button
						class="p-sfaq__q"
						type="button"
						aria-expanded="false"
						aria-controls="<?php echo esc_attr( $exterior_exone_answer_id ); ?>"
						data-sfaq-trigger
					>
						<span class="p-sfaq__mark" aria-hidden="true">Q</span>
						<span class="p-sfaq__q-text"><?php echo wp_kses( implode( '<wbr>', array_map( 'esc_html', $exterior_exone_item['q'] ) ), array( 'wbr' => array() ) ); ?></span>
						<img
							class="p-sfaq__arrow"
							src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_faq['arrow'] ) ); ?>"
							width="36"
							height="18"
							alt=""
							loading="lazy"
						>
					</button>
				</h3>

				<div class="p-sfaq__answer" id="<?php echo esc_attr( $exterior_exone_answer_id ); ?>" hidden>
					<p class="p-sfaq__answer-text"><?php echo esc_html( $exterior_exone_item['a'] ); ?></p>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
