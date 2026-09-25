<?php
/**
 * 共通: WORKS（見出し + 写真グリッド + 要望 / 提案 + 見積）。
 *
 * カンプ: 支店 PC 698:1134・698:1181・1153:104・Group 379-382（SP カンプ無し）/
 *         DX  PC 437:460（436:326-341 / 437:462 / 437:463）・
 *             SP 439:992-1019 + 見積モーダル 439:1020-1062
 *
 * 支店ページと DX ページで同じ部品を使う（docs/spec-20260919-dx-page-decisions.md）。
 * 呼び出しは template-parts/store/section-works.php と
 * template-parts/dx/section-works.php。ページごとの差分（見出し・リード文・写真・
 * 3D 枠の下敷き・配色）は $args と修飾子 .p-cworks--<variant> で吸収する。
 *
 * 写真グリッドは既存 CPT works の最新を使い、1 件も無いときはセクションごと
 * 出さない（TOP の NEWS / WORKS と同じ作法。決定事項 Q6）。3D パースと
 * 要望・提案・見積はカンプの 1 事例ぶんの固定値（inc/works-data.php）。
 *
 * gallery => true（DX ページ）のときは CPT を使わず、渡された写真（1 枚目がメイン、
 * 1〜3 枚目が右列）をカンプどおりに出し、右列の 3 枚と 3D パースを押すと
 * 左のメインがその画像に切り替わる（js/works-gallery.js。2026-09-25）。
 *
 * 見積は PC ではそのまま表で出し、SP（1024px 以下）では
 * 「施工内容・内訳を見る」ボタン → モーダルで出す（js/works-estimate-modal.js）。
 * 表の DOM は 1 つだけで、モーダルの器ごと位置と配色を変えている。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_works = wp_parse_args(
	( isset( $args ) && is_array( $args ) ) ? $args : array(),
	array(
		'variant'  => 'store',
		'gallery'  => false,
		'section'  => 'works',
		'title'    => '',
		'jp'       => '',
		'lead'     => array(),
		'lead_sp'  => array(),
		'photos'   => array(),
		'thumbs'   => array(), // gallery のとき、右列だけ別の切り抜きを出す写真（添字は photos と同じ）
		'render'   => array(),
		'request'  => array(
			'title' => '',
			'lines' => array(),
		),
		'proposal' => array(
			'title' => '',
			'body'  => '',
		),
		'estimate' => array(
			'columns' => array(),
			'rows'    => array(),
		),
		'total'    => '',
		'modal'    => array(),
	)
);

$exterior_exone_works_gallery = (bool) $exterior_exone_works['gallery'];

if ( $exterior_exone_works_gallery ) {
	// 1 枚目はメインと右列の先頭を兼ねる（カンプ 436:324）。
	$exterior_exone_works_slots = array();

	$exterior_exone_works_order = array_merge( array( 0 ), array_keys( array_slice( $exterior_exone_works['photos'], 0, 3 ) ) );

	foreach ( $exterior_exone_works_order as $exterior_exone_works_slot_index => $exterior_exone_works_key ) {
		$exterior_exone_works_full = $exterior_exone_works['photos'][ $exterior_exone_works_key ];
		$exterior_exone_works_thumb = ( $exterior_exone_works_slot_index > 0 && ! empty( $exterior_exone_works['thumbs'][ $exterior_exone_works_key ] ) )
			? $exterior_exone_works['thumbs'][ $exterior_exone_works_key ]
			: $exterior_exone_works_full;

		$exterior_exone_works_slots[] = array(
			'image_id' => 0,
			'url'      => '',
			'src'      => $exterior_exone_works_thumb,
			'full'     => $exterior_exone_works_full,
			'title'    => '',
		);
	}
} else {
	$exterior_exone_works_slots = exterior_exone_works_slots( $exterior_exone_works['photos'] );
}

if ( ! $exterior_exone_works_slots ) {
	return;
}

$exterior_exone_works_render = wp_parse_args(
	$exterior_exone_works['render'],
	array(
		'image'    => '',
		'label'    => '',
		'backdrop' => '',
		'model'    => array(),
	)
);

$exterior_exone_works_modal = wp_parse_args(
	$exterior_exone_works['modal'],
	array(
		'button' => '',
		'label'  => '',
		'close'  => '',
		'image'  => '',
	)
);
?>
<section class="p-cworks p-cworks--<?php echo esc_attr( $exterior_exone_works['variant'] ); ?>" data-section="<?php echo esc_attr( $exterior_exone_works['section'] ); ?>" data-works<?php echo $exterior_exone_works_gallery ? ' data-works-gallery' : ''; ?>>
	<h2 class="p-cworks__title"><?php echo esc_html( $exterior_exone_works['title'] ); ?></h2>
	<p class="p-cworks__jp"><?php echo esc_html( $exterior_exone_works['jp'] ); ?></p>

	<?php // PC と SP で改行位置が違うときだけ 2 本持ち、幅で出し分ける。 ?>
	<p class="p-cworks__lead">
		<?php if ( $exterior_exone_works['lead_sp'] ) : ?>
			<span class="p-cworks__lead-text p-cworks__lead-text--pc">
				<?php echo wp_kses( exterior_exone_works_lines( $exterior_exone_works['lead'] ), array( 'br' => array() ) ); ?>
			</span>
			<span class="p-cworks__lead-text p-cworks__lead-text--sp">
				<?php echo wp_kses( exterior_exone_works_lines( $exterior_exone_works['lead_sp'] ), array( 'br' => array() ) ); ?>
			</span>
		<?php else : ?>
			<?php echo wp_kses( exterior_exone_works_lines( $exterior_exone_works['lead'] ), array( 'br' => array() ) ); ?>
		<?php endif; ?>
	</p>

	<div class="p-cworks__grid">
		<div class="p-cworks__main" data-works-main>
			<?php exterior_exone_works_photo( $exterior_exone_works_slots[0] ); ?>
		</div>

		<ul class="p-cworks__subs">
			<?php foreach ( array_slice( $exterior_exone_works_slots, 1 ) as $exterior_exone_works_index => $exterior_exone_works_slot ) : ?>
				<li class="p-cworks__sub">
					<?php if ( $exterior_exone_works_gallery ) : ?>
						<?php // 押すと左のメインがこの写真に切り替わる。 ?>
						<button
							type="button"
							class="p-cworks__thumb<?php echo 0 === $exterior_exone_works_index ? ' is-current' : ''; ?>"
							data-works-thumb="<?php echo esc_url( $exterior_exone_works_slot['full'] ); ?>"
							aria-pressed="<?php echo 0 === $exterior_exone_works_index ? 'true' : 'false'; ?>"
							aria-label="<?php echo esc_attr( sprintf( '写真%dを大きく表示', $exterior_exone_works_index + 1 ) ); ?>"
						>
							<?php exterior_exone_works_photo( $exterior_exone_works_slot ); ?>
						</button>
					<?php else : ?>
						<?php exterior_exone_works_photo( $exterior_exone_works_slot ); ?>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php
		// 1153:104 灰色パネル / 437:463 空写真 + パース + ラベル。
		// gallery のときはボタンにして、押すとメインに 3D パース（空写真の上）を出す。
		$exterior_exone_works_render_tag = $exterior_exone_works_gallery ? 'button' : 'div';
		?>
		<<?php echo esc_html( $exterior_exone_works_render_tag ); ?>
			class="p-cworks__render<?php echo $exterior_exone_works_gallery ? ' p-cworks__thumb' : ''; ?>"
			<?php if ( $exterior_exone_works_gallery ) : ?>
				type="button"
				data-works-thumb="<?php echo esc_url( $exterior_exone_works_render['image'] ); ?>"
				data-works-thumb-backdrop="<?php echo esc_url( $exterior_exone_works_render['backdrop'] ); ?>"
				<?php if ( ! empty( $exterior_exone_works_render['model']['file'] ) ) : ?>
					data-works-model="<?php echo esc_url( $exterior_exone_works_render['model']['file'] ); ?>"
					data-works-model-viewer="<?php echo esc_url( $exterior_exone_works_render['model']['viewer'] ); ?>"
					data-works-model-draco="<?php echo esc_url( $exterior_exone_works_render['model']['draco'] ); ?>"
					data-works-model-orbit="<?php echo esc_attr( $exterior_exone_works_render['model']['orbit'] ); ?>"
					data-works-model-fov="<?php echo esc_attr( $exterior_exone_works_render['model']['fov'] ); ?>"
					data-works-model-target="<?php echo esc_attr( $exterior_exone_works_render['model']['target'] ); ?>"
				<?php endif; ?>
				aria-pressed="false"
				aria-label="<?php echo esc_attr( $exterior_exone_works_render['label'] . 'を大きく表示' ); ?>"
			<?php endif; ?>
		>
			<?php if ( $exterior_exone_works_render['backdrop'] ) : ?>
				<img
					class="p-cworks__render-backdrop"
					src="<?php echo esc_url( $exterior_exone_works_render['backdrop'] ); ?>"
					width="512"
					height="512"
					alt=""
					loading="lazy"
				>
			<?php endif; ?>

			<img
				class="p-cworks__render-image"
				src="<?php echo esc_url( $exterior_exone_works_render['image'] ); ?>"
				width="1200"
				height="800"
				alt=""
				loading="lazy"
			>
			<span class="p-cworks__render-label"><?php echo esc_html( $exterior_exone_works_render['label'] ); ?></span>
		</<?php echo esc_html( $exterior_exone_works_render_tag ); ?>>

		<div class="p-cworks__boxes">
			<div class="p-cworks__box">
				<p class="p-cworks__box-head"><?php echo esc_html( $exterior_exone_works['request']['title'] ); ?></p>

				<div class="p-cworks__box-body">
					<?php // 行ごとに箱を分ける（「・」始まりはぶら下げインデント。SP の折り返し対策）。 ?>
					<p class="p-cworks__box-text">
						<?php foreach ( $exterior_exone_works['request']['lines'] as $exterior_exone_works_line ) : ?>
							<span class="p-cworks__box-line<?php echo str_starts_with( $exterior_exone_works_line, '・' ) ? ' p-cworks__box-line--bullet' : ''; ?>"><?php echo esc_html( $exterior_exone_works_line ); ?></span>
						<?php endforeach; ?>
					</p>
				</div>
			</div>

			<div class="p-cworks__box">
				<p class="p-cworks__box-head"><?php echo esc_html( $exterior_exone_works['proposal']['title'] ); ?></p>

				<div class="p-cworks__box-body">
					<p class="p-cworks__box-text"><?php echo esc_html( $exterior_exone_works['proposal']['body'] ); ?></p>
				</div>
			</div>
		</div>
	</div>

	<?php // 439:993。SP でモーダルを開くボタン（JS が使えるときだけ CSS で出す）。 ?>
	<p class="p-cworks__actions">
		<button type="button" class="p-cworks__more" aria-haspopup="dialog" data-works-modal-open>
			<?php echo esc_html( $exterior_exone_works_modal['button'] ); ?>
		</button>
	</p>

	<?php
	// PC はこの器がそのまま見積の表、SP は暗幕付きのモーダルになる（439:1020-1062）。
	// 表を 2 つ持たないので、支援技術が同じ内容を二重に読むことがない。
	?>
	<div class="p-cworks__sheet" data-works-modal>
		<span class="p-cworks__sheet-scrim" aria-hidden="true"></span>

		<div class="p-cworks__sheet-window">
			<div
				class="p-cworks__sheet-panel"
				tabindex="-1"
				aria-label="<?php echo esc_attr( $exterior_exone_works_modal['label'] ); ?>"
				data-works-modal-panel
			>
				<?php if ( $exterior_exone_works_modal['image'] ) : ?>
					<img
						class="p-cworks__sheet-image"
						src="<?php echo esc_url( $exterior_exone_works_modal['image'] ); ?>"
						width="1200"
						height="800"
						alt=""
						loading="lazy"
					>
				<?php endif; ?>

				<div class="p-cworks__estimate">
					<table class="p-cworks__table">
						<thead>
							<tr>
								<?php foreach ( $exterior_exone_works['estimate']['columns'] as $exterior_exone_works_column ) : ?>
									<th scope="col"><?php echo esc_html( $exterior_exone_works_column ); ?></th>
								<?php endforeach; ?>
							</tr>
						</thead>

						<tbody>
							<?php foreach ( $exterior_exone_works['estimate']['rows'] as $exterior_exone_works_row ) : ?>
								<tr>
									<th scope="row"><?php echo esc_html( $exterior_exone_works_row['work'] ); ?></th>
									<td class="p-cworks__material"><?php echo esc_html( $exterior_exone_works_row['material'] ); ?></td>
									<td class="p-cworks__price"><?php echo esc_html( $exterior_exone_works_row['price'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<p class="p-cworks__total"><?php echo esc_html( $exterior_exone_works['total'] ); ?></p>
				</div>

				<?php // 439:1029。× は画像を使わず CSS の 2 本線で描く。 ?>
				<button type="button" class="p-cworks__sheet-close" data-works-modal-close>
					<span class="p-cworks__sheet-close-icon" aria-hidden="true"></span>
					<?php echo esc_html( $exterior_exone_works_modal['close'] ); ?>
				</button>
			</div>
		</div>
	</div>
</section>
