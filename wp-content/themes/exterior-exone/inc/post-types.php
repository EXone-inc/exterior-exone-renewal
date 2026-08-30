<?php
/**
 * カスタム投稿タイプの登録。
 *
 * プラグインではなくテーマ側のコードで登録する（管理画面の操作で
 * 定義が変わらないようにするため）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * お知らせ（news）。
 *
 * 管理画面で扱う項目は「タイトル・詳細（本文）・画像（アイキャッチ）」の 3 つ。
 * 画像はアイキャッチ画像をそのまま使い、ラベルだけ「画像」に言い換える。
 */
function exterior_exone_register_news_post_type() {
	register_post_type(
		'news',
		array(
			'labels'        => array(
				'name'                  => 'お知らせ',
				'singular_name'         => 'お知らせ',
				'add_new'               => '新規追加',
				'add_new_item'          => 'お知らせを追加',
				'edit_item'             => 'お知らせを編集',
				'new_item'              => '新しいお知らせ',
				'view_item'             => 'お知らせを表示',
				'view_items'            => 'お知らせ一覧を表示',
				'search_items'          => 'お知らせを検索',
				'not_found'             => 'お知らせが見つかりませんでした',
				'not_found_in_trash'    => 'ゴミ箱にお知らせはありません',
				'all_items'             => 'お知らせ一覧',
				'archives'              => 'お知らせ一覧',
				'menu_name'             => 'お知らせ',
				// アイキャッチ画像まわりは「画像」と言い換える。
				'featured_image'        => '画像',
				'set_featured_image'    => '画像を設定',
				'remove_featured_image' => '画像を削除',
				'use_featured_image'    => '画像として使用',
			),
			'public'        => true,
			'has_archive'   => true,
			'menu_position' => 5,
			'menu_icon'     => 'dashicons-megaphone',
			// 既定のエディタ枠は出さず、説明・画像を 1 つのフォームにまとめる
			// （inc/meta-boxes.php）。thumbnail は画像の保存先として残す。
			'supports'      => array( 'title', 'thumbnail' ),
			'show_in_rest'  => true,
			'rewrite'       => array(
				'slug'       => 'news',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'exterior_exone_register_news_post_type' );

/**
 * 施工事例（works）。
 *
 * 管理画面で扱う項目:
 * - タイトル / 説明文（本文）… 投稿タイプの標準機能
 * - カテゴリータイプ / パッケージタイプ / ハッシュタグ … タクソノミー（下で登録）
 * - 画像（複数枚）/ 所在地 / トップページに記載する … カスタムフィールド（inc/meta-boxes.php）
 *
 * 画像はアイキャッチではなくギャラリー（並び順つきの複数枚）で持つため
 * thumbnail はサポートしない。TOP に出すのは 1 枚目。
 */
function exterior_exone_register_works_post_type() {
	register_post_type(
		'works',
		array(
			'labels'        => array(
				'name'               => '施工事例',
				'singular_name'      => '施工事例',
				'add_new'            => '新規追加',
				'add_new_item'       => '施工事例を追加',
				'edit_item'          => '施工事例を編集',
				'new_item'           => '新しい施工事例',
				'view_item'          => '施工事例を表示',
				'view_items'         => '施工事例一覧を表示',
				'search_items'       => '施工事例を検索',
				'not_found'          => '施工事例が見つかりませんでした',
				'not_found_in_trash' => 'ゴミ箱に施工事例はありません',
				'all_items'          => '施工事例一覧',
				'archives'           => '施工事例一覧',
				'menu_name'          => '施工事例',
			),
			'public'        => true,
			'has_archive'   => true,
			'menu_position' => 6,
			'menu_icon'     => 'dashicons-format-gallery',
			// 既定のエディタ枠は出さない。説明文は独自フォームから post_content に
			// 保存する（inc/meta-boxes.php）。
			'supports'      => array( 'title' ),
			'show_in_rest'  => true,
			'rewrite'       => array(
				'slug'       => 'works',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'exterior_exone_register_works_post_type' );

/**
 * 施工事例のタクソノミー。
 *
 * カテゴリータイプ（カーポート・ウッドデッキ 等）とパッケージタイプ
 * （シンプルモダン・アーバンモダン 等）は絞り込みの系統が別なので分けて持つ。
 * どちらも複数選択できるよう hierarchical にしてチェックボックスで出す。
 *
 * ハッシュタグは件数が増える前提なので、タグ形式（カンマ区切り入力）にする。
 */
function exterior_exone_register_works_taxonomies() {
	register_taxonomy(
		'works_category',
		'works',
		array(
			'labels'            => array(
				'name'          => 'カテゴリータイプ',
				'singular_name' => 'カテゴリータイプ',
				'all_items'     => 'すべてのカテゴリータイプ',
				'edit_item'     => 'カテゴリータイプを編集',
				'add_new_item'  => 'カテゴリータイプを追加',
				'search_items'  => 'カテゴリータイプを検索',
				'menu_name'     => 'カテゴリータイプ',
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'works-category',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'works_package',
		'works',
		array(
			'labels'            => array(
				'name'          => 'パッケージタイプ',
				'singular_name' => 'パッケージタイプ',
				'all_items'     => 'すべてのパッケージタイプ',
				'edit_item'     => 'パッケージタイプを編集',
				'add_new_item'  => 'パッケージタイプを追加',
				'search_items'  => 'パッケージタイプを検索',
				'menu_name'     => 'パッケージタイプ',
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'works-package',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'works_tag',
		'works',
		array(
			'labels'            => array(
				'name'                       => 'ハッシュタグ',
				'singular_name'              => 'ハッシュタグ',
				'all_items'                  => 'すべてのハッシュタグ',
				'edit_item'                  => 'ハッシュタグを編集',
				'add_new_item'               => 'ハッシュタグを追加',
				'search_items'               => 'ハッシュタグを検索',
				'separate_items_with_commas' => 'ハッシュタグをカンマで区切ってください',
				'menu_name'                  => 'ハッシュタグ',
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'works-tag',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'exterior_exone_register_works_taxonomies' );

/**
 * お知らせ・施工事例はブロックエディタを使わない。
 *
 * 項目を縦 1 列で順に埋められる形にしたいので、旧来の編集画面に寄せる。
 *
 * @param bool   $use       ブロックエディタを使うか。
 * @param string $post_type 投稿タイプ。
 * @return bool
 */
function exterior_exone_disable_block_editor( $use, $post_type ) {
	if ( in_array( $post_type, array( 'news', 'works' ), true ) ) {
		return false;
	}

	return $use;
}
add_filter( 'use_block_editor_for_post_type', 'exterior_exone_disable_block_editor', 10, 2 );

/**
 * 投稿タイプ登録後にリライトルールを作り直す。
 *
 * テーマ切り替え時に 1 度だけ走ればよいので after_switch_theme に載せる。
 * 既に有効なテーマへ CPT を後から足した場合は「設定 > パーマリンク」を
 * 開き直すか wp rewrite flush で反映する。
 */
function exterior_exone_flush_rewrite_rules() {
	exterior_exone_register_news_post_type();
	exterior_exone_register_works_post_type();
	exterior_exone_register_works_taxonomies();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'exterior_exone_flush_rewrite_rules' );
