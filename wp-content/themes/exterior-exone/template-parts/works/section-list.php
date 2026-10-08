<?php
/**
 * WORKS: 一覧ブロック（見出し + 絞り込みボタン + 写真グリッド + 0 件表示）。一覧・詳細で共通。
 *
 * カンプ: PC 821:120（見出し 821:127 / ボタン 821:121〜126 / 写真 821:136〜147）・詳細 821:231
 *         SP 836:88・836:89・836:63・836:50 / guide 821:262（色反転・画像クリックで詳細へ）
 *
 * ボタンはテイスト（works_package）の単一選択トグル。押したテイストの事例だけをその場で
 * 表示する（js/works-filter.js）。JS 無効時は全件表示のまま。写真に文字は載せない。
 *
 * $args: tastes（exterior_exone_works_page_tastes()）/ cards（exterior_exone_works_page_card() の配列）
 *        / filter（exterior_exone_works_page_filter()）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_wkp_tastes = isset( $args['tastes'] ) ? (array) $args['tastes'] : array();
$exterior_exone_wkp_cards  = isset( $args['cards'] ) ? (array) $args['cards'] : array();
$exterior_exone_wkp_filter = isset( $args['filter'] ) ? (array) $args['filter'] : exterior_exone_works_page_filter();
$exterior_exone_wkp_grid   = wp_unique_id( 'works-grid-' );
?>
<section class="p-wkplist" data-section="works-list" data-works-filter>
	<?php
	get_template_part(
		'template-parts/works/heading',
		null,
		array(
			'eng'     => $exterior_exone_wkp_filter['eng'],
			'heading' => $exterior_exone_wkp_filter['heading'],
		)
	);
	?>

	<?php if ( $exterior_exone_wkp_tastes ) : ?>
		<div class="p-wkplist__filter" role="group" aria-label="<?php echo esc_attr( $exterior_exone_wkp_filter['label'] ); ?>">
			<?php foreach ( $exterior_exone_wkp_tastes as $exterior_exone_wkp_taste ) : ?>
				<button
					type="button"
					class="p-wkplist__btn"
					aria-pressed="false"
					aria-controls="<?php echo esc_attr( $exterior_exone_wkp_grid ); ?>"
					data-works-filter-btn="<?php echo esc_attr( $exterior_exone_wkp_taste['slug'] ); ?>"
				><?php echo esc_html( $exterior_exone_wkp_taste['name'] ); ?></button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<ul class="p-wkplist__grid" id="<?php echo esc_attr( $exterior_exone_wkp_grid ); ?>" data-works-filter-grid>
		<?php foreach ( $exterior_exone_wkp_cards as $exterior_exone_wkp_card ) : ?>
			<?php
			$exterior_exone_wkp_img = $exterior_exone_wkp_card['image_id']
				? wp_get_attachment_image(
					$exterior_exone_wkp_card['image_id'],
					'large',
					false,
					array(
						'class'   => 'p-wkplist__img',
						'alt'     => $exterior_exone_wkp_card['title'],
						'loading' => 'lazy',
						'sizes'   => '(min-width: 1920px) 510px, (min-width: 1025px) 27vw, 44vw',
					)
				)
				: '';
			?>
			<li class="p-wkplist__item" data-works-item data-works-tastes="<?php echo esc_attr( implode( ' ', $exterior_exone_wkp_card['tastes'] ) ); ?>">
				<?php if ( $exterior_exone_wkp_img ) : ?>
					<a class="p-wkplist__card" href="<?php echo esc_url( $exterior_exone_wkp_card['url'] ); ?>"><?php echo $exterior_exone_wkp_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP 生成の img タグ。 ?></a>
				<?php else : ?>
					<a class="p-wkplist__card p-wkplist__card--empty" href="<?php echo esc_url( $exterior_exone_wkp_card['url'] ); ?>" aria-label="<?php echo esc_attr( $exterior_exone_wkp_card['title'] ); ?>"></a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<p class="p-wkplist__empty" aria-live="polite" data-works-filter-status data-empty-text="<?php echo esc_attr( $exterior_exone_wkp_filter['empty'] ); ?>"></p>
</section>
