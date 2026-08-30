<?php
/**
 * TOP: 実績数値バンド。
 *
 * 理念セクション（section-philosophy.php）の中で読み込まれる。
 * 4 項目・アイコン・並び順は PC / SP 共通で、見せ方だけメディアクエリで変える。
 * PC は横 1 列（項目名が上、アイコンと数値が横並び）、
 * SP は 2x2 の円バッジ（円の中にアイコン → 項目名 → 数値）。
 *
 * カンプ: PC 16:563-592（1:11）/ SP 16:156-222（16:16）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_stats = exterior_exone_stats_items();
?>
<div class="p-stats" data-section="stats">
	<p class="p-stats__heading">
		<span class="p-stats__heading-text">数字で見る</span>
		<img
			class="p-stats__heading-logo"
			src="<?php echo esc_url( exterior_exone_top_image( 'logo.svg' ) ); ?>"
			width="164"
			height="25"
			alt="<?php bloginfo( 'name' ); ?>"
			loading="lazy"
		>
	</p>

	<ul class="p-stats__list">
		<?php foreach ( $exterior_exone_stats as $exterior_exone_index => $exterior_exone_stat ) : ?>
			<li class="p-stats__item" data-reveal data-reveal-delay="<?php echo esc_attr( (string) ( $exterior_exone_index * 120 ) ); ?>">
				<?php // 補足（(昨対比率)）は PC カンプのみ。SP では CSS で隠すので間の空白も出さない。 ?>
				<p class="p-stats__label"><?php echo esc_html( $exterior_exone_stat['label'] ); ?><?php if ( '' !== $exterior_exone_stat['note'] ) : ?><span class="p-stats__note"><?php echo esc_html( $exterior_exone_stat['note'] ); ?></span><?php endif; ?></p>

				<div class="p-stats__body">
					<img
						class="p-stats__icon"
						src="<?php echo esc_url( exterior_exone_top_image( $exterior_exone_stat['icon'] ) ); ?>"
						width="<?php echo esc_attr( (string) $exterior_exone_stat['icon_w'] ); ?>"
						height="<?php echo esc_attr( (string) $exterior_exone_stat['icon_h'] ); ?>"
						alt=""
						loading="lazy"
					>
					<p class="p-stats__value">
						<?php // JS が中身を読んで 0 からカウントアップする（無効時は実数値のまま）。 ?>
						<span class="p-stats__number" data-countup><?php echo esc_html( $exterior_exone_stat['number'] ); ?></span>
						<span class="p-stats__unit"><?php echo esc_html( $exterior_exone_stat['unit'] ); ?></span>
					</p>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
