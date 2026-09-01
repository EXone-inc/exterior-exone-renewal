<?php
/**
 * ナビゲーション定義。
 *
 * 管理画面でメニューが未設定の間は、ここのカンプ準拠の内容が
 * wp_nav_menu() の fallback_cb として出力される。
 * メニューが登録されればそちらが優先される。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * グローバルナビ（PC ヘッダー）の項目。カンプ 917:2733。
 *
 * @return array<int, array{label: string, url: string}>
 */
function exterior_exone_global_menu_items() {
	return array(
		array(
			'label' => 'DX EXPERIENCE',
			'url'   => '#',
		),
		array(
			'label' => 'PLANS',
			'url'   => '#',
		),
		array(
			'label' => 'WORKS',
			'url'   => '#',
		),
		array(
			'label' => 'STORE',
			'url'   => '#',
		),
		array(
			'label' => 'COMPANY',
			'url'   => home_url( '/company/' ),
		),
		array(
			'label' => 'RECRUIT',
			'url'   => '#',
		),
		array(
			'label' => 'CONTACT',
			'url'   => '#',
		),
	);
}

/**
 * フッター MENU の項目。カンプ 917:2595（PC）/ 917:756-764（SP）。
 *
 * 先頭 5 件が 1 列目、残りが 2 列目に流れる（CSS グリッドの行数と対応）。
 * ハンバーガーメニューでは「お問い合わせ」だけがボタンとして独立するため、
 * 本体を exterior_exone_drawer_menu_items()、末尾を exterior_exone_contact_item()
 * に分けて持ち、ここで連結する（項目の出典は 1 か所に保つ）。
 *
 * @return array<int, array{label: string, url: string}>
 */
function exterior_exone_footer_menu_items() {
	return array_merge(
		exterior_exone_drawer_menu_items(),
		array( exterior_exone_contact_item() )
	);
}

/**
 * ハンバーガーメニューの MENU 項目。カンプ 1024:188-196。
 *
 * 先頭 5 件が 1 列目、残る 3 件が 2 列目に流れる。
 *
 * @return array<int, array{label: string, url: string}>
 */
function exterior_exone_drawer_menu_items() {
	return array(
		array(
			'label' => 'TOP',
			'url'   => home_url( '/' ),
		),
		array(
			'label' => '新しい外構体験',
			'url'   => '#',
		),
		array(
			'label' => 'プラン一覧',
			'url'   => '#',
		),
		array(
			'label' => '事例一覧',
			'url'   => '#',
		),
		array(
			'label' => 'お知らせ',
			'url'   => '#',
		),
		array(
			'label' => '企業情報',
			'url'   => '#',
		),
		array(
			'label' => '採用情報',
			'url'   => '#',
		),
		array(
			'label' => 'コラム',
			'url'   => '#',
		),
	);
}

/**
 * お問い合わせ。カンプ 1024:238・1024:242（ハンバーガーメニューではボタン）。
 *
 * @return array{label: string, url: string}
 */
function exterior_exone_contact_item() {
	return array(
		'label' => 'お問い合わせ',
		'url'   => '#',
	);
}

/**
 * フッター STORE の項目。
 *
 * @return array<int, array{label: string, url: string}>
 */
function exterior_exone_store_menu_items() {
	return array(
		array(
			'label' => '仙台支店',
			'url'   => '#',
		),
		array(
			'label' => '戸塚支店',
			'url'   => '#',
		),
		array(
			'label' => '盛岡支店',
			'url'   => '#',
		),
		array(
			'label' => '青森支店',
			'url'   => '#',
		),
		array(
			'label' => '八戸支店',
			'url'   => '#',
		),
		array(
			'label' => '弘前支店',
			'url'   => '#',
		),
		array(
			'label' => '東京オフィス',
			'url'   => '#',
		),
	);
}

/**
 * フッターの SNS 一覧。
 *
 * icon は images/top/ 配下のファイル名。
 * （footer-icon1=Instagram / 2=Facebook / 3=NOTE / 4=LINE を実データで確認済み）
 *
 * @return array<int, array{label: string, url: string, icon: string}>
 */
function exterior_exone_sns_items() {
	return array(
		array(
			'label' => 'NOTE',
			'url'   => '#',
			'icon'  => 'footer-icon3.svg',
		),
		array(
			'label' => 'Instagram',
			'url'   => '#',
			'icon'  => 'footer-icon1.svg',
		),
		array(
			'label' => 'LINE',
			'url'   => '#',
			'icon'  => 'footer-icon4.svg',
		),
		array(
			'label' => 'Facebook',
			'url'   => '#',
			'icon'  => 'footer-icon2.svg',
		),
	);
}

/**
 * 項目配列を <ul> として出力する。
 *
 * @param array  $items 項目配列。
 * @param string $class ul に付与するクラス。
 * @return void
 */
function exterior_exone_render_menu_list( array $items, $class ) {
	echo '<ul class="' . esc_attr( $class ) . '">';

	foreach ( $items as $item ) {
		printf(
			'<li class="%1$s__item"><a href="%2$s">%3$s</a></li>',
			esc_attr( $class ),
			esc_url( $item['url'] ),
			esc_html( $item['label'] )
		);
	}

	echo '</ul>';
}

/**
 * グローバルナビの fallback。
 *
 * @return void
 */
function exterior_exone_global_menu_fallback() {
	exterior_exone_render_menu_list( exterior_exone_global_menu_items(), 'p-gnav__list' );
}

/**
 * フッター MENU の fallback。
 *
 * @return void
 */
function exterior_exone_footer_menu_fallback() {
	exterior_exone_render_menu_list( exterior_exone_footer_menu_items(), 'p-footer__list' );
}

/**
 * ハンバーガーメニュー MENU の fallback。
 *
 * @return void
 */
function exterior_exone_drawer_menu_fallback() {
	exterior_exone_render_menu_list( exterior_exone_drawer_menu_items(), 'p-drawer__list' );
}

/**
 * ハンバーガーメニュー STORE の fallback。
 *
 * @return void
 */
function exterior_exone_drawer_store_fallback() {
	exterior_exone_render_menu_list( exterior_exone_store_menu_items(), 'p-drawer__list' );
}

/**
 * フッター STORE の fallback。
 *
 * @return void
 */
function exterior_exone_store_menu_fallback() {
	exterior_exone_render_menu_list( exterior_exone_store_menu_items(), 'p-footer__list' );
}
