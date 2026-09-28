<?php
/**
 * VR 展示場（固定ページ vr。DX EXPERIENCE の子ページ /dx/vr/）。
 *
 * DX ページのステップ 01「VR展示場を体験する」から別タブで開く。
 * 外部の VR（exone-package.com）を iframe で、ヘッダーの下の 1 画面いっぱいに表示し、
 * スクロールするとフッターが出る（カンプ無し。2026-09-28 ユーザー決定）。
 * URL と見出しは inc/dx-data.php の exterior_exone_dx_vr() が出典。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$exterior_exone_vr = exterior_exone_dx_vr();
?>
<div class="p-dxvr">
	<h1 class="screen-reader-text"><?php echo esc_html( $exterior_exone_vr['title'] ); ?></h1>

	<?php
	// VR 内の全画面表示・端末の向きを使う操作のために allow を付ける。
	?>
	<iframe
		class="p-dxvr__frame"
		src="<?php echo esc_url( $exterior_exone_vr['src'] ); ?>"
		title="<?php echo esc_attr( $exterior_exone_vr['title'] ); ?>"
		allow="fullscreen; xr-spatial-tracking; gyroscope; accelerometer"
		allowfullscreen
	></iframe>
</div>
<?php
get_footer();
