<?php
/** 新テーマ用に取り込んだ内容の読み取り専用一覧。 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/coexistence-slack.php';
require_once __DIR__ . '/coexistence-news-admin.php';
require_once __DIR__ . '/coexistence-proposal-admin.php';

function exterior_exone_coexistence_admin_allowed() {
	return defined( 'EXR_COEXISTENCE' ) && true === EXR_COEXISTENCE && current_user_can( 'manage_options' );
}

function exterior_exone_coexistence_admin_types() {
	return array( 'exr_page' => '固定ページ', 'exr_works' => '提案事例', 'exr_news' => 'お知らせ', 'exr_form' => 'フォーム', 'attachment' => '画像' );
}

/** 管理一覧でも旧データ・別データ群・取り込み途中を混ぜない。 */
function exterior_exone_coexistence_admin_is_record( $post ) {
	$types = exterior_exone_coexistence_admin_types();
	$key = $post instanceof WP_Post ? get_post_meta( $post->ID, '_exr_source_key', true ) : '';
	return $post instanceof WP_Post
		&& isset( $types[ $post->post_type ] )
		&& ( 'attachment' === $post->post_type ? 'private' : 'exr_stored' ) === $post->post_status
		&& 'exterior-exone-renewal:v1' === get_post_meta( $post->ID, '_exr_dataset', true )
		&& 'complete' === get_post_meta( $post->ID, '_exr_import_state', true )
		&& is_string( $key ) && '' !== $key;
}

function exterior_exone_coexistence_admin_records() {
	if ( ! exterior_exone_coexistence_admin_allowed() ) {
		return array();
	}
	$records = array();
	foreach ( exterior_exone_coexistence_admin_types() as $type => $label ) {
		$posts = get_posts( array(
			'post_type'        => $type,
			'post_status'      => 'attachment' === $type ? 'private' : 'exr_stored',
			'posts_per_page'   => -1,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'suppress_filters' => true,
			'meta_query'       => array(
				array( 'key' => '_exr_dataset', 'value' => 'exterior-exone-renewal:v1' ),
				array( 'key' => '_exr_import_state', 'value' => 'complete' ),
				array( 'key' => '_exr_source_key', 'compare' => 'EXISTS' ),
			),
		) );
		foreach ( $posts as $post ) {
			if ( exterior_exone_coexistence_admin_is_record( $post ) ) {
				$records[] = $post;
			}
		}
	}
	return $records;
}

function exterior_exone_coexistence_admin_status( $post ) {
	$labels = array( 'trash' => 'ゴミ箱', 'publish' => '公開', 'draft' => '下書き', 'pending' => '確認待ち', 'private' => '非公開', 'future' => '公開予約', 'inherit' => '添付画像' );
	$status = get_post_meta( $post->ID, '_exr_source_status', true );
	return is_string( $status ) && isset( $labels[ $status ] ) ? $labels[ $status ] . ( in_array( $post->post_type, array( 'exr_news', 'exr_works' ), true ) ? '（現在）' : '' ) : '確認が必要';
}

function exterior_exone_coexistence_admin_link( $post ) {
	if ( ! exterior_exone_coexistence_admin_allowed() || ! exterior_exone_coexistence_admin_is_record( $post ) ) {
		return array();
	}
	if ( 'exr_news' === $post->post_type ) {
		$args = 'trash' === get_post_meta( $post->ID, '_exr_source_status', true ) ? array( 'view' => 'trash' ) : array( 'edit' => (int) $post->ID );
		return array( 'url' => exterior_exone_news_admin_url( $args ), 'label' => 'お知らせ管理で確認・編集' );
	}
	if ( 'exr_works' === $post->post_type ) {
		$args = 'trash' === get_post_meta( $post->ID, '_exr_source_status', true ) ? array( 'view' => 'trash' ) : array( 'edit' => (int) $post->ID );
		return array( 'url' => exterior_exone_proposal_url( $args ), 'label' => '提案事例管理で確認・編集' );
	}
	if ( 'attachment' === $post->post_type ) {
		return current_user_can( 'read_post', $post->ID ) ? array( 'url' => admin_url( 'upload.php?item=' . (int) $post->ID ), 'label' => 'メディアライブラリで確認' ) : array();
	}
	if ( ! exterior_exone_coexistence_is_public_record( $post ) ) {
		return array();
	}
	if ( 'exr_form' === $post->post_type ) {
		$role = get_post_meta( $post->ID, '_exr_form_role', true );
		if ( ! in_array( $role, array( 'personal', 'corporate', 'entry' ), true ) ) {
			return array();
		}
		return array( 'url' => home_url( 'entry' === $role ? '/recruit/entry/' : '/contact/' ), 'label' => 'フォームのページを確認' );
	}
	if ( 'exr_page' === $post->post_type ) {
		$path = get_post_meta( $post->ID, '_exr_source_path', true );
		if ( ! is_string( $path ) || exterior_exone_coexistence_normalize_path( trim( $path, '/' ) ) !== $path ) {
			return array();
		}
	}
	$url = exterior_exone_coexistence_permalink( '', $post );
	return $url ? array( 'url' => $url, 'label' => '公開ページを確認' ) : array();
}

function exterior_exone_coexistence_admin_menu() {
	if ( exterior_exone_coexistence_admin_allowed() ) {
		add_theme_page( '新テーマ用コンテンツ', '新テーマ用コンテンツ', 'manage_options', 'exone-renewal-content', 'exterior_exone_coexistence_admin_render' );
	}
}

function exterior_exone_coexistence_admin_render() {
	if ( ! exterior_exone_coexistence_admin_allowed() ) {
		wp_die( esc_html( 'この画面を表示する権限がありません。' ), '', array( 'response' => 403 ) );
	}
	$types = exterior_exone_coexistence_admin_types();
	$posts = exterior_exone_coexistence_admin_records();
	echo '<div class="wrap"><h1>' . esc_html( '新テーマ用コンテンツ' ) . '</h1>';
	echo '<p>' . esc_html( '新テーマ用に取り込んだ内容を確認する画面です。この画面では内容の編集・保存はできません。' ) . '</p>';
	echo '<p>' . esc_html( '「元の公開状態」は取り込み元での状態です。お知らせ・提案事例は編集後の現在の状態を表示します。一般公開の準備が完了したことを示すものではありません。' ) . '</p>';
	echo '<p>' . esc_html( sprintf( '取り込み済み：%d件', count( $posts ) ) ) . '</p>';
	if ( ! $posts ) {
		echo '<p>' . esc_html( '確認できる取り込み済みの内容はありません。' ) . '</p></div>';
		return;
	}
	echo '<table class="widefat striped"><thead><tr>';
	foreach ( array( '分類', 'タイトル', '元の公開状態', '確認先' ) as $label ) {
		echo '<th scope="col">' . esc_html( $label ) . '</th>';
	}
	echo '</tr></thead><tbody>';
	foreach ( $posts as $post ) {
		$title = '' !== trim( $post->post_title ) ? $post->post_title : '（タイトルなし）';
		$link = exterior_exone_coexistence_admin_link( $post );
		echo '<tr><td>' . esc_html( $types[ $post->post_type ] ) . '</td><th scope="row">' . esc_html( $title ) . '</th><td>' . esc_html( exterior_exone_coexistence_admin_status( $post ) ) . '</td><td>';
		if ( $link ) {
			echo '<a href="' . esc_url( $link['url'] ) . '">' . esc_html( $link['label'] ) . '</a>';
		} else {
			echo esc_html( '公開リンクなし' );
		}
		echo '</td></tr>';
	}
	echo '</tbody></table></div>';
}

if ( defined( 'EXR_COEXISTENCE' ) && true === EXR_COEXISTENCE ) {
	add_action( 'admin_menu', 'exterior_exone_coexistence_admin_menu' );
}
