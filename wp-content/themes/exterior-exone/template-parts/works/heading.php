<?php
/**
 * WORKS: 見出し（英字小 + 和文大）。一覧ブロックと詳細の見出しで共通。
 *
 * カンプ: PC 821:129・821:128（SF Pro Medium 32 → +55.86 に Noto Bold 40）
 *         SP 836:88・836:89（14px → +24.23 に 16px）
 * ハイエンドの heading.php と同じ組み（css/works-page.css の .p-wkphead）。
 *
 * $args: eng（英字。空なら出さない）/ heading（和文）/ tag（h1 | h2。既定 h2）/ id（任意）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $args['heading'] ) ) {
	return;
}

$exterior_exone_wkp_head_tag = ( isset( $args['tag'] ) && 'h1' === $args['tag'] ) ? 'h1' : 'h2';
?>
<div class="p-wkphead"<?php echo empty( $args['id'] ) ? '' : ' id="' . esc_attr( $args['id'] ) . '"'; ?>>
	<?php if ( ! empty( $args['eng'] ) ) : ?>
		<p class="p-wkphead__eng"><?php echo esc_html( $args['eng'] ); ?></p>
	<?php endif; ?>
	<<?php echo esc_html( $exterior_exone_wkp_head_tag ); ?> class="p-wkphead__heading"><?php echo esc_html( $args['heading'] ); ?></<?php echo esc_html( $exterior_exone_wkp_head_tag ); ?>>
</div>
