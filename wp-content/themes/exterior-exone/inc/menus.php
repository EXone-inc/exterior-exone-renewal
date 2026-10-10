<?php
/**
 * ナビゲーション定義。
 *
 * 管理画面でメニューが未設定の間は、ここのカンプ準拠の内容が
 * wp_nav_menu() の fallback_cb として出力される。
 * メニューが登録されればそちらが優先される。
 * 支店モード（inc/store-mode.php）のときは、割り当ての有無にかかわらず
 * ここの支店用項目を出す（exterior_exone_nav_menu()）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * グローバルナビ（PC ヘッダー）の項目。カンプ 670:45（支店用は 670:58）。
 *
 * @return array<int, array{label: string, url: string}>
 */
function exterior_exone_global_menu_items() {
	return array(
		array(
			'label' => 'DX EXPERIENCE',
			'url'   => home_url( '/dx/' ),
		),
		array(
			'label' => 'PLANS',
			'url'   => home_url( '/plans/' ),
		),
		array(
			'label' => 'WORKS',
			'url'   => home_url( '/works/' ),
		),
		array(
			'label'    => 'STORE',
			'url'      => exterior_exone_store_index_url(),
			// PC のグローバルナビでは遷移せず、ホバー／クリックで支店一覧を開く（children）。
			'children' => exterior_exone_store_menu_items(),
		),
		array(
			'label' => 'COMPANY',
			'url'   => home_url( '/company/' ),
		),
		array(
			'label' => 'RECRUIT',
			'url'   => '#',
		),
		exterior_exone_contact_item( 'CONTACT' ),
	);
}

/**
 * フッター MENU の項目。カンプ 670:88（PC。支店用は 670:193）/ 917:756-764（SP）。
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
 * ハンバーガーメニューの MENU 項目。カンプ 670:288（支店用は 670:289）。
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
			'url'   => home_url( '/dx/' ),
		),
		array(
			'label' => 'プラン一覧',
			'url'   => home_url( '/plans/' ),
		),
		array(
			'label' => '事例一覧',
			'url'   => home_url( '/works/' ),
		),
		array(
			'label' => 'お知らせ',
			'url'   => '#',
		),
		array(
			'label' => '企業情報',
			'url'   => home_url( '/company/' ),
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
 * 支店モードのグローバルナビ（PC ヘッダー）の項目。カンプ 670:58。
 *
 * COMPANY・RECRUIT は出さない。STORES は STORE と同じく遷移しないドロップダウン。
 *
 * @return array<int, array{label: string, url: string}>
 */
function exterior_exone_store_global_menu_items() {
	return array(
		array(
			'label' => 'DX EXPERIENCE',
			'url'   => exterior_exone_store_link( home_url( '/dx/' ) ),
		),
		array(
			'label' => 'WORKS',
			'url'   => exterior_exone_store_link( home_url( '/works/' ) ),
		),
		array(
			'label' => 'PLANS',
			'url'   => exterior_exone_store_link( home_url( '/plans/' ) ),
		),
		array(
			'label'    => 'STORES',
			'url'      => exterior_exone_store_index_url(),
			'children' => exterior_exone_store_menu_items(),
		),
		// オレンジのボタン（670:337）。
		array_merge( exterior_exone_contact_item( 'CONTACT' ), array( 'modifier' => 'button' ) ),
	);
}

/**
 * 支店モードのハンバーガーメニュー MENU 項目。カンプ 670:289。
 *
 * 先頭 5 件が 1 列目、コラム・コーポレートサイトが 2 列目に流れる。
 *
 * @return array<int, array{label: string, url: string}>
 */
function exterior_exone_store_drawer_menu_items() {
	return array(
		array(
			'label' => 'TOP',
			'url'   => exterior_exone_brand_url(),
		),
		array(
			'label' => '新しい外構体験',
			'url'   => exterior_exone_store_link( home_url( '/dx/' ) ),
		),
		array(
			'label' => 'プラン一覧',
			'url'   => exterior_exone_store_link( home_url( '/plans/' ) ),
		),
		array(
			'label' => '事例一覧',
			'url'   => exterior_exone_store_link( home_url( '/works/' ) ),
		),
		array(
			'label' => 'お知らせ',
			'url'   => '#',
		),
		array(
			'label' => 'コラム',
			'url'   => '#',
		),
		// コーポレートへ戻る唯一の入口なので ?store= を付けない。
		array(
			'label' => 'コーポレートサイト',
			'url'   => home_url( '/' ),
		),
	);
}

/**
 * 支店モードのフッター MENU の項目。カンプ 670:193。
 *
 * @return array<int, array{label: string, url: string}>
 */
function exterior_exone_store_footer_menu_items() {
	return array_merge(
		exterior_exone_store_drawer_menu_items(),
		array( exterior_exone_contact_item() )
	);
}

/**
 * お問い合わせ。カンプ 670:244・670:257（ハンバーガーメニューではボタン）。支店用 PC ヘッダーでは CONTACT ボタン（670:337）、
 * コーポレート PC ヘッダーではグローバルナビの CONTACT。行き先の出典はここ 1 か所。
 *
 * @param string $label 表示名（PC ヘッダーは CONTACT）。
 * @return array{label: string, url: string}
 */
function exterior_exone_contact_item( $label = 'お問い合わせ' ) {
	return array(
		'label' => $label,
		'url'   => exterior_exone_contact_url(), // /contact/（支店モードは ?store= 付き。inc/contact-data.php）
	);
}

/**
 * フッター STORE の項目。カンプ 669:836（支店ページ）/ 775:993（TOP）。
 *
 * ラベルと URL の二重管理をしないため、inc/store-data.php の 6 拠点から導出する。
 * 並び順もそちらが出典。
 *
 * @return array<int, array{label: string, url: string}>
 */
function exterior_exone_store_menu_items() {
	$items = array();

	foreach ( exterior_exone_stores() as $slug => $store ) {
		$items[] = array(
			'label' => $store['name'],
			'url'   => exterior_exone_store_url( $slug ),
		);
	}

	return $items;
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

	foreach ( $items as $index => $item ) {
		// children を持つ項目は遷移せず子メニューを開くトリガー（button）にする。
		// 開閉は CSS（:hover / :focus-within）と js/gnav-sub.js（クリック・Esc）。
		if ( ! empty( $item['children'] ) ) {
			$sub_id = sanitize_html_class( $class . '-sub-' . $index );

			printf(
				'<li class="%1$s__item %1$s__item--has-sub"><button type="button" class="%1$s__trigger" aria-expanded="false" aria-controls="%2$s" data-gnav-sub-trigger>%3$s</button>',
				esc_attr( $class ),
				esc_attr( $sub_id ),
				esc_html( $item['label'] )
			);
			echo '<ul class="' . esc_attr( $class ) . '__sub" id="' . esc_attr( $sub_id ) . '">';

			foreach ( $item['children'] as $child ) {
				printf(
					'<li class="%1$s__sub-item"><a href="%2$s">%3$s</a></li>',
					esc_attr( $class ),
					esc_url( $child['url'] ),
					esc_html( $child['label'] )
				);
			}

			echo '</ul></li>';

			continue;
		}

		// modifier を持つ項目は <li> に修飾クラスを足す（支店用ヘッダーの CONTACT ボタン = button）。
		$modifier = empty( $item['modifier'] ) ? '' : ' ' . $class . '__item--' . sanitize_html_class( $item['modifier'] );

		printf(
			'<li class="%1$s__item%4$s"><a href="%2$s">%3$s</a></li>',
			esc_attr( $class ),
			esc_url( $item['url'] ),
			esc_html( $item['label'] ),
			esc_attr( $modifier )
		);
	}

	echo '</ul>';
}

/**
 * wp_nav_menu() の代わりに呼ぶ。支店モードでは割り当てメニューを使わず fallback_cb を出す
 *（割り当てメニューには支店用の差し替えと ?store= の引き継ぎが効かないため）。
 *
 * @param array $args wp_nav_menu() の引数。
 * @return void
 */
function exterior_exone_nav_menu( array $args ) {
	if ( exterior_exone_is_store_mode() && ! empty( $args['fallback_cb'] ) && is_callable( $args['fallback_cb'] ) ) {
		call_user_func( $args['fallback_cb'] );

		return;
	}

	wp_nav_menu( $args );
}

/**
 * グローバルナビの fallback。
 *
 * @return void
 */
function exterior_exone_global_menu_fallback() {
	$items = exterior_exone_is_store_mode() ? exterior_exone_store_global_menu_items() : exterior_exone_global_menu_items();

	exterior_exone_render_menu_list( $items, 'p-gnav__list' );
}

/**
 * フッター MENU の fallback。
 *
 * @return void
 */
function exterior_exone_footer_menu_fallback() {
	$items = exterior_exone_is_store_mode() ? exterior_exone_store_footer_menu_items() : exterior_exone_footer_menu_items();

	exterior_exone_render_menu_list( $items, 'p-footer__list' );
}

/**
 * ハンバーガーメニュー MENU の fallback。
 *
 * @return void
 */
function exterior_exone_drawer_menu_fallback() {
	$items = exterior_exone_is_store_mode() ? exterior_exone_store_drawer_menu_items() : exterior_exone_drawer_menu_items();

	exterior_exone_render_menu_list( $items, 'p-drawer__list' );
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
