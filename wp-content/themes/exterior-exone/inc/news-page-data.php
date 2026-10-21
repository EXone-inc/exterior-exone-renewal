<?php
/**
 * お知らせ一覧 /news/ のデータ。
 *
 * カンプ: 無し。デザイン・構成の正はコラム一覧（archive-column.php / css/column-page.css）。
 * 見出し「NEWS」は TOP の NEWS（PC 917:93 / SP 917:877）。
 * 仕様書: docs/spec-20261014-news-page.md（決定事項 docs/spec-20261014-news-page-decisions.md）
 *
 * お知らせそのものは投稿タイプ news（inc/post-types.php）。詳細ページは作らない（決定事項 §2）。
 * ここは一覧のクエリ・年月タブ・カードの組み立てと固定の文言だけを持つ。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 一覧の 1 ページの件数（コラムと同じ）。
 */
const EXTERIOR_EXONE_NEWS_PER_PAGE = 10;

/**
 * お知らせ一覧のページかどうか（コラムの exterior_exone_is_column_page() とは別の判定）。
 *
 * @return bool
 */
function exterior_exone_is_news_page() {
	$route = exterior_exone_coexistence_route();
	return exterior_exone_coexistence_enabled() && $route ? 'news-list' === $route['kind'] : is_post_type_archive( 'news' );
}

/**
 * 一覧のメインクエリだけ、10 件/ページ・公開日の新しい順にする。
 *
 * 範囲外のページ（/news/page/3/ など）は該当なしになり、WordPress が 404 にする。
 *
 * @param WP_Query $query クエリ。
 */
function exterior_exone_news_page_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'news' ) ) {
		return;
	}

	$query->set( 'posts_per_page', EXTERIOR_EXONE_NEWS_PER_PAGE );
	$query->set( 'orderby', 'date' );
	$query->set( 'order', 'DESC' );

	$ym = exterior_exone_news_current_ym();

	// 年月は投稿日（サイトのタイムゾーン）で絞る。該当なしの月は 0 件表示（1 ページ目は 200 のまま）。
	if ( '' !== $ym ) {
		list( $year, $month ) = array_map( 'intval', explode( '-', $ym ) );

		$query->set(
			'date_query',
			array(
				array(
					'year'  => $year,
					'month' => $month,
				),
			)
		);
	}
}
add_action( 'pre_get_posts', 'exterior_exone_news_page_query' );

/**
 * 絞り込み中の年月（?ym=YYYY-MM。月は 01〜12）。空文字 = すべて。
 *
 * ym は WordPress のクエリ変数ではないので $_GET から読む（コラムの ?cat= と同じ）。
 * 形式違い・配列は「すべて」扱い。形式が正しければ投稿が無い月でもそのまま返す（0 件表示にする）。
 *
 * @return string
 */
function exterior_exone_news_current_ym() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 表示の絞り込みのみ。
	if ( ! isset( $_GET['ym'] ) || ! is_string( $_GET['ym'] ) ) {
		return '';
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$ym = wp_unslash( $_GET['ym'] );

	return preg_match( '/^\d{4}-(0[1-9]|1[0-2])$/', $ym ) ? $ym : '';
}

/**
 * 一覧の URL（ページ番号・年月付き）。1 ページ目は /news/（/news/page/1/ にしない）。
 * 支店モードなら ?store= が付く。
 *
 * @param int    $page ページ番号。
 * @param string $ym   年月（YYYY-MM。空ならすべて）。
 * @return string
 */
function exterior_exone_news_list_url( $page = 1, $ym = '' ) {
	$url = (string) exterior_exone_content_archive_url( 'news' );

	if ( $page > 1 ) {
		$url = trailingslashit( $url ) . user_trailingslashit( 'page/' . (int) $page, 'paged' );
	}

	if ( '' !== $ym ) {
		$url = add_query_arg( 'ym', $ym, $url );
	}

	return exterior_exone_store_link( $url );
}

/**
 * 一覧の見出し・絞り込み・0 件の文言（コラム一覧と同じ構成要素）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_news_page_texts() {
	return array(
		'title'       => 'NEWS',
		'lead'        => 'お知らせ',
		'filter'      => '年月で絞り込む',
		'filter_all'  => 'すべて',
		'filter_aria' => 'お知らせの年月',
		'empty'       => '該当するお知らせはありません',
		'pager_aria'  => 'ページ送り',
		'prev'        => '前へ',
		'next'        => '次へ',
	);
}

/**
 * 公開済みのお知らせがある年月（YYYY-MM）の新しい順。リクエスト内で 1 回だけ集計する。
 *
 * 年月は投稿日（post_date = サイトのタイムゾーン。カードの日付と同じ基準）で取る。
 *
 * @return array<int, string>
 */
function exterior_exone_news_months() {
	static $months = null;

	if ( null !== $months ) {
		return $months;
	}

	if ( exterior_exone_coexistence_enabled() ) {
		$posts = exterior_exone_content_posts( array( 'post_type' => 'news', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC' ) );
		$months = array_values( array_unique( array_map( static function ( $post ) { return substr( $post->post_date, 0, 7 ); }, $posts ) ) );
		return $months;
	}

	global $wpdb;

	$rows = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT DISTINCT DATE_FORMAT( post_date, '%%Y-%%m' ) AS ym FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s ORDER BY ym DESC",
			'news',
			'publish'
		)
	);

	$months = array_values( array_filter( array_map( 'strval', (array) $rows ), fn( $ym ) => (bool) preg_match( '/^\d{4}-(0[1-9]|1[0-2])$/', $ym ) ) );

	return $months;
}

/**
 * 年月のタブの表示名（2026年5月。月はゼロ埋めしない）。
 *
 * @param string $ym 年月（YYYY-MM）。
 * @return string
 */
function exterior_exone_news_ym_label( $ym ) {
	list( $year, $month ) = array_map( 'intval', explode( '-', $ym ) );

	return sprintf( '%d年%d月', $year, $month );
}

/**
 * 絞り込みのタブ（すべて + 公開済みのお知らせがある年月）。
 *
 * @return array<int, array{label:string, url:string, current:bool}>
 */
function exterior_exone_news_page_tabs() {
	$texts   = exterior_exone_news_page_texts();
	$current = exterior_exone_news_current_ym();
	$tabs    = array(
		array(
			'label'   => $texts['filter_all'],
			'url'     => exterior_exone_news_list_url(),
			'current' => '' === $current,
		),
	);

	foreach ( exterior_exone_news_months() as $ym ) {
		$tabs[] = array(
			'label'   => exterior_exone_news_ym_label( $ym ),
			'url'     => exterior_exone_news_list_url( 1, $ym ),
			'current' => $current === $ym,
		);
	}

	return $tabs;
}

/**
 * 説明文（post_content のプレーンテキスト）を段落に分ける。表示時だけ。
 *
 * 1 行以上の空行で段落、段落内の改行はそのまま行として返す。前後の空白・空行は詰める。
 * HTML・ショートコードとして解釈しない（出力側で行ごとに esc_html する）。
 *
 * @param string $content 説明文。
 * @return array<int, array<int, string>> 段落 → 行。空なら空配列。
 */
function exterior_exone_news_paragraphs( $content ) {
	$text = trim( str_replace( array( "\r\n", "\r" ), "\n", (string) $content ) );

	if ( '' === $text ) {
		return array();
	}

	$paragraphs = array();

	foreach ( preg_split( '/\n[ \t\x{3000}]*\n\s*/u', $text ) as $block ) {
		$block = trim( $block );

		if ( '' !== $block ) {
			$paragraphs[] = array_map( 'rtrim', explode( "\n", $block ) );
		}
	}

	return $paragraphs;
}

/**
 * 説明文の段落の HTML（<p> + 段落内の改行は <br>）。中身は行ごとに esc_html 済み。
 *
 * @param array<int, array<int, string>> $paragraphs exterior_exone_news_paragraphs() の戻り値。
 * @return string
 */
function exterior_exone_news_paragraphs_html( $paragraphs ) {
	$html = '';

	foreach ( $paragraphs as $lines ) {
		$html .= '<p>' . implode( '<br>', array_map( 'esc_html', $lines ) ) . '</p>';
	}

	return $html;
}

/**
 * 一覧のカード 1 枚ぶん（押せないカード。リンクは持たない）。
 *
 * @param WP_Post $post お知らせ。
 * @return array{id:int, title:string, image_id:int, date:string, datetime:string, paragraphs:array<int, array<int, string>>}
 */
function exterior_exone_news_page_card( $post ) {
	return array(
		'id'         => (int) $post->ID,
		'title'      => get_the_title( $post ),
		'image_id'   => (int) get_post_thumbnail_id( $post ),
		'date'       => get_the_date( 'Y.m.d', $post ),
		'datetime'   => get_the_date( 'Y-m-d', $post ),
		'paragraphs' => exterior_exone_news_paragraphs( $post->post_content ),
	);
}

/**
 * ページ送り（前へ・番号・次へ）。並び・番号の省略規則はコラムと同じ（exterior_exone_list_pager()）。
 * URL は exterior_exone_news_list_url() で作る（?ym= と ?store= だけを引き継ぐ）。
 *
 * @param int $current 現在のページ。
 * @param int $total   総ページ数。
 * @return array<int, array{type:string, label:string, url:string}>
 */
function exterior_exone_news_page_pager( $current, $total ) {
	$texts = exterior_exone_news_page_texts();
	$ym    = exterior_exone_news_current_ym();

	return exterior_exone_list_pager(
		$current,
		$total,
		$texts,
		fn( $page ) => exterior_exone_news_list_url( $page, $ym )
	);
}

/* ============================================================
   個別 URL /news/<slug>/（詳細ページは作らない）
   ============================================================ */

/**
 * 公開済みのお知らせの個別 URL は一覧 /news/ へ 301（決定事項 §2）。
 *
 * 有効な ?store= だけ引き継ぎ、他のクエリは捨てる。存在しない・下書き・予約投稿は
 * is_singular にならないので 404 のまま。プレビューは転送しない。
 * canonical の転送より先に走らせて 1 回で着地させる。
 */
function exterior_exone_news_single_redirect() {
	if ( ! is_singular( 'news' ) || is_preview() ) {
		return;
	}

	if ( 'publish' !== get_post_status( get_queried_object_id() ) ) {
		return;
	}

	wp_safe_redirect( exterior_exone_news_list_url(), 301 );
	exit;
}
add_action( 'template_redirect', 'exterior_exone_news_single_redirect', 5 );

/**
 * 標準のサイトマップからお知らせの個別 URL を外す（転送される URL を載せない）。
 *
 * @param array<string, WP_Post_Type> $post_types サイトマップに載せる投稿タイプ。
 * @return array<string, WP_Post_Type>
 */
function exterior_exone_news_sitemap_post_types( $post_types ) {
	unset( $post_types['news'] );

	return $post_types;
}
add_filter( 'wp_sitemaps_post_types', 'exterior_exone_news_sitemap_post_types' );
