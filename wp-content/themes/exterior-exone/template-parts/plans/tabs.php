<?php
/**
 * PLANS: プランの切り替えタブ（F-10）。
 *
 * カンプ: PC plan btn 611:16（307x55 x2・間隔 13・右端 x1842・ヘッダー下端に揃える）
 *         SP はカンプ無し（決定事項 A: ヘッダー直下に画面幅いっぱいで半分ずつ）
 *
 * ヘッダーの下に固定する要素。プランを選ぶまでは出さず、JS が動かないときは
 * hidden のまま（縦に全部出るので切り替えは要らない）。
 * 出し入れと選択状態（aria-pressed）は js/plans-select.js。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_plans_tabs = exterior_exone_plans_select();
?>
<nav class="p-plptabs" aria-label="プランの切り替え" data-plp-tabs hidden>
	<?php foreach ( $exterior_exone_plans_tabs as $exterior_exone_plan ) : ?>
		<button
			type="button"
			class="p-plptabs__tab"
			aria-pressed="false"
			aria-controls="<?php echo esc_attr( $exterior_exone_plan['key'] ); ?>"
			data-plp-tab="<?php echo esc_attr( $exterior_exone_plan['key'] ); ?>"
		><?php echo esc_html( $exterior_exone_plan['name'] ); ?></button>
	<?php endforeach; ?>
</nav>
