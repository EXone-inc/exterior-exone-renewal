<?php
/**
 * 支店モード（支店サイトとしての表示）の判定と、サイト内リンクへの ?store= の引き継ぎ。
 *
 * 支店モード = 支店ページ /store/<slug>/ を表示中、または URL に ?store=<slug> が付いている。
 * 両方あって食い違うときはページ側を優先する。
 * store は公開クエリ変数として登録しない（静的フロントページ /?store= が投稿一覧扱いになるため）。
 * home_url フィルタも使わない（canonical・REST・フィードにまで付くため）。
 * リンクはテーマが出力する部品ごとに exterior_exone_store_link() を通す。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 表示中のリクエストの支店（支店モードでなければ null）。
 *
 * exterior_exone_current_store()（固定ページのスラッグで判定）とは別物。
 * こちらは ?store= も見る。入力値は照合にだけ使い、出力には支店データだけを使う。
 *
 * @return array<string, string>|null slug を含む支店データ。
 */
function exterior_exone_store_mode() {
	$page_store = exterior_exone_current_store();

	if ( $page_store ) {
		return $page_store;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 表示の切り替えのみ。
	if ( ! isset( $_GET['store'] ) || ! is_string( $_GET['store'] ) ) {
		return null;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$slug   = sanitize_key( wp_unslash( $_GET['store'] ) );
	$stores = exterior_exone_stores();

	if ( '' === $slug || ! isset( $stores[ $slug ] ) ) {
		return null;
	}

	return array_merge( array( 'slug' => $slug ), $stores[ $slug ] );
}

/**
 * 支店モードかどうか。
 *
 * @return bool
 */
function exterior_exone_is_store_mode() {
	return null !== exterior_exone_store_mode();
}

/**
 * サイト内リンクに、支店モードなら ?store=<slug> を付けて返す（コーポレートならそのまま）。
 *
 * 付けないもの: 空・# 始まり（未設定・ページ内リンク）・外部 / http(s) 以外（tel: 等）・
 * 支店ページ /store/<slug>/ そのもの。既にクエリがあれば &store=、フラグメントはその後ろに残る。
 * 「コーポレートサイト」（/）はこの関数を通さない。
 *
 * @param string $url URL。
 * @return string
 */
function exterior_exone_store_link( $url ) {
	$url   = (string) $url;
	$store = exterior_exone_store_mode();

	if ( ! $store || '' === $url || '#' === $url[0] ) {
		return $url;
	}

	$parts = wp_parse_url( $url );
	$home  = wp_parse_url( home_url( '/' ) );

	if ( false === $parts || empty( $parts['host'] ) || ! isset( $home['host'] ) ) {
		return $url;
	}

	if ( ! in_array( strtolower( (string) ( $parts['scheme'] ?? '' ) ), array( 'http', 'https' ), true ) || strtolower( $parts['host'] ) !== strtolower( $home['host'] ) ) {
		return $url;
	}

	$path = trailingslashit( (string) ( $parts['path'] ?? '/' ) );

	foreach ( array_keys( exterior_exone_stores() ) as $slug ) {
		if ( trailingslashit( (string) wp_parse_url( exterior_exone_store_url( $slug ), PHP_URL_PATH ) ) === $path ) {
			return $url;
		}
	}

	return add_query_arg( 'store', $store['slug'], $url );
}

/**
 * ロゴ・TOP の行き先。支店モードは支店の TOP、コーポレートはサイトのトップ。
 *
 * @return string
 */
function exterior_exone_brand_url() {
	$store = exterior_exone_store_mode();

	return $store ? exterior_exone_store_url( $store['slug'] ) : home_url( '/' );
}

/**
 * 支店モードのとき <html> に data-store-mode を付ける。
 *
 * ヘッダー高さ（--header-height）は :root のトークンで、そこから導出するトークンも :root で
 * 解決されるため、body のクラス（is-store-mode）ではなく <html> 側の印で切り替える。
 *
 * @param string $output language_attributes() の出力。
 * @return string
 */
function exterior_exone_store_mode_html_attribute( $output ) {
	if ( is_admin() || ! exterior_exone_is_store_mode() ) {
		return $output;
	}

	return $output . ' data-store-mode';
}
add_filter( 'language_attributes', 'exterior_exone_store_mode_html_attribute' );
