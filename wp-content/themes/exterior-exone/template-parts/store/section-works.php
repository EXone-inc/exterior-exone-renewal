<?php
/**
 * 支店: WORKS。
 *
 * カンプ: PC 698:1134（背景）/ 698:1136-1138（見出し 3 点セット）/
 *         698:1181・698:1178・698:1180・1153:104 + 698:1197（写真グリッド）/
 *         Group 380・Group 382（要望・提案）/ Group 379（見積テーブル）
 *         SP カンプなし（グリッド・2 ボックス・テーブルを縦積み）
 *
 * 写真グリッドは TOP と同じ既存 CPT works の最新（exterior_exone_works_cards()）を
 * 使う。1 件も無いときはセクションごと出さない（TOP の NEWS / WORKS と同じ作法）。
 * 同じ画像の投稿が続くとグリッドが同じ写真だらけになるので、重複した画像は
 * 飛ばしてカンプの写真（698:1181 ほか）で埋める。
 *
 * 3D パースと要望・提案・見積はカンプの 1 事例ぶんの固定値（inc/store-data.php）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_works_cards = exterior_exone_works_cards();

if ( ! $exterior_exone_works_cards ) {
	return;
}

$exterior_exone_works = exterior_exone_store_works();

// メイン 1 枚 + 右列 3 枚。CPT の画像（重複なし）を先に詰め、残りはカンプ写真。
$exterior_exone_slots = array();
$exterior_exone_seen  = array();

foreach ( $exterior_exone_works_cards as $exterior_exone_card ) {
	if ( ! $exterior_exone_card['image_id'] || in_array( $exterior_exone_card['image_id'], $exterior_exone_seen, true ) ) {
		continue;
	}

	$exterior_exone_seen[]  = $exterior_exone_card['image_id'];
	$exterior_exone_slots[] = array(
		'image_id' => $exterior_exone_card['image_id'],
		'file'     => '',
		'url'      => $exterior_exone_card['url'],
		'title'    => $exterior_exone_card['title'],
	);

	if ( 4 === count( $exterior_exone_slots ) ) {
		break;
	}
}

$exterior_exone_fallback = $exterior_exone_works['photos'];

while ( count( $exterior_exone_slots ) < 4 ) {
	$exterior_exone_slots[] = array(
		'image_id' => 0,
		'file'     => $exterior_exone_fallback[ count( $exterior_exone_slots ) % count( $exterior_exone_fallback ) ],
		'url'      => '',
		'title'    => '',
	);
}
?>
<section class="p-sworks" data-section="store-works">
	<h2 class="c-store-title"><?php echo esc_html( $exterior_exone_works['title'] ); ?></h2>
	<p class="c-store-jp"><?php echo esc_html( $exterior_exone_works['jp'] ); ?></p>

	<p class="c-store-lead p-sworks__lead">
		<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_works['lead'] ), array( 'br' => array() ) ); ?>
	</p>

	<div class="p-sworks__grid">
		<div class="p-sworks__main">
			<?php exterior_exone_store_works_photo( $exterior_exone_slots[0] ); ?>
		</div>

		<ul class="p-sworks__subs">
			<?php foreach ( array_slice( $exterior_exone_slots, 1 ) as $exterior_exone_slot ) : ?>
				<li class="p-sworks__sub">
					<?php exterior_exone_store_works_photo( $exterior_exone_slot ); ?>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php // 1153:104 灰色パネル + 698:1197 パース + Group 381 ラベル。 ?>
		<div class="p-sworks__render">
			<img
				class="p-sworks__render-image"
				src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_works['render']['image'] ) ); ?>"
				width="1200"
				height="800"
				alt=""
				loading="lazy"
			>
			<span class="p-sworks__render-label"><?php echo esc_html( $exterior_exone_works['render']['label'] ); ?></span>
		</div>

		<div class="p-sworks__boxes">
			<div class="p-sworks__box">
				<p class="p-sworks__box-head"><?php echo esc_html( $exterior_exone_works['request']['title'] ); ?></p>

				<div class="p-sworks__box-body">
					<p class="p-sworks__box-text">
						<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_works['request']['lines'] ), array( 'br' => array() ) ); ?>
					</p>
				</div>
			</div>

			<div class="p-sworks__box">
				<p class="p-sworks__box-head"><?php echo esc_html( $exterior_exone_works['proposal']['title'] ); ?></p>

				<div class="p-sworks__box-body">
					<p class="p-sworks__box-text"><?php echo esc_html( $exterior_exone_works['proposal']['body'] ); ?></p>
				</div>
			</div>
		</div>
	</div>

	<div class="p-sworks__estimate">
		<table class="p-sworks__table">
			<thead>
				<tr>
					<?php foreach ( $exterior_exone_works['estimate']['columns'] as $exterior_exone_column ) : ?>
						<th scope="col"><?php echo esc_html( $exterior_exone_column ); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>
				<?php foreach ( $exterior_exone_works['estimate']['rows'] as $exterior_exone_row ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $exterior_exone_row['work'] ); ?></th>
						<td class="p-sworks__material"><?php echo esc_html( $exterior_exone_row['material'] ); ?></td>
						<td class="p-sworks__price"><?php echo esc_html( $exterior_exone_row['price'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p class="p-sworks__total"><?php echo esc_html( $exterior_exone_works['total'] ); ?></p>
	</div>
</section>
