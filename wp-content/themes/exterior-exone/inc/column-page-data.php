<?php
/**
 * コラム一覧 /column/ と詳細 /column/<投稿名>/ のデータ。
 *
 * カンプ: ボード 875:50（指示書き 875:47 / 参考画像 875:30。精密なカンプは無い）
 * 仕様書: docs/spec-20261010-column-page.md（決定事項 docs/spec-20261010-column-page-decisions.md）
 * 構成要素の出典: 現サイトの page-column.php（docs/reference-20261010-column/）
 *
 * 記事そのものは投稿タイプ column・タクソノミー column_category（inc/post-types.php）。
 * ここは一覧のクエリ・絞り込み・ページ送り・カードの組み立てと固定の文言だけを持つ。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 一覧の 1 ページの件数。
 */
const EXTERIOR_EXONE_COLUMN_PER_PAGE = 10;

/**
 * 抜粋の文字数（現サイトと同じ。超えたら「…」）。
 */
const EXTERIOR_EXONE_COLUMN_EXCERPT_LENGTH = 90;

/**
 * コラム一覧（アーカイブ）のリクエストかどうか。request フィルタの段階で使う。
 *
 * @param array $query_vars クエリ変数。
 * @return bool
 */
function exterior_exone_column_is_archive_request( $query_vars ) {
	return isset( $query_vars['post_type'] ) && 'column' === $query_vars['post_type']
		&& empty( $query_vars['name'] ) && empty( $query_vars['column'] );
}

/**
 * 一覧の ?cat= は WordPress 予約の投稿カテゴリー（ID）として解釈させない。
 *
 * 数字を含む値だと投稿カテゴリーの絞り込みになって 404 になるため、コラム一覧の
 * リクエストからだけ外す。絞り込みは exterior_exone_column_current_cat() が $_GET から読む。
 *
 * @param array $query_vars クエリ変数。
 * @return array
 */
function exterior_exone_column_request( $query_vars ) {
	if ( exterior_exone_column_is_archive_request( $query_vars ) ) {
		unset( $query_vars['cat'] );
	}

	return $query_vars;
}
add_filter( 'request', 'exterior_exone_column_request' );

/**
 * 絞り込み中のカテゴリー（?cat=<スラッグ>）。無い・存在しないときは null（= すべて）。
 *
 * スラッグは日本語の URL エンコード（現サイトと同じ）。$_GET では復号済みなので
 * sanitize_title() で保存形式に戻して照合する。
 *
 * @return WP_Term|null
 */
function exterior_exone_column_current_cat() {
	static $term = false;

	if ( false !== $term ) {
		return $term;
	}

	$term = null;

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 表示の絞り込みのみ。
	if ( isset( $_GET['cat'] ) && is_string( $_GET['cat'] ) && '' !== $_GET['cat'] ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$found = get_term_by( 'slug', sanitize_title( wp_unslash( $_GET['cat'] ) ), 'column_category' );
		$term  = $found instanceof WP_Term ? $found : null;
	}

	return $term;
}

/**
 * 一覧のメインクエリだけ、10 件/ページ・公開日の新しい順・カテゴリーの絞り込みにする。
 *
 * 範囲外のページ（/column/page/3/ など）は該当なしになり、WordPress が 404 にする。
 *
 * @param WP_Query $query クエリ。
 */
function exterior_exone_column_page_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'column' ) ) {
		return;
	}

	$query->set( 'posts_per_page', EXTERIOR_EXONE_COLUMN_PER_PAGE );
	$query->set( 'orderby', 'date' );
	$query->set( 'order', 'DESC' );

	$term = exterior_exone_column_current_cat();

	if ( $term ) {
		$query->set(
			'tax_query',
			array(
				array(
					'taxonomy' => 'column_category',
					'field'    => 'term_id',
					'terms'    => (int) $term->term_id,
				),
			)
		);
	}
}
add_action( 'pre_get_posts', 'exterior_exone_column_page_query' );

/**
 * コラムの一覧・詳細ページかどうか。
 *
 * @return bool
 */
function exterior_exone_is_column_page() {
	return is_post_type_archive( 'column' ) || is_singular( 'column' )
		|| ( function_exists( 'exterior_exone_coexistence_column_is_list' ) && exterior_exone_coexistence_column_is_list() );
}

/**
 * 一覧の URL（ページ番号・カテゴリー付き）。1 ページ目は /column/（/column/page/1/ にしない）。
 * 支店モードなら ?store= が付く。
 *
 * @param int    $page ページ番号。
 * @param string $slug カテゴリーのスラッグ（保存形式。空ならすべて）。
 * @return string
 */
function exterior_exone_column_list_url( $page = 1, $slug = '' ) {
	$url = function_exists( 'exterior_exone_coexistence_column_enabled' ) && exterior_exone_coexistence_column_enabled()
		? exterior_exone_coexistence_column_list_base_url()
		: (string) get_post_type_archive_link( 'column' );

	if ( $page > 1 ) {
		$url = trailingslashit( $url ) . user_trailingslashit( 'page/' . (int) $page, 'paged' );
	}

	if ( '' !== $slug ) {
		$url = add_query_arg( 'cat', $slug, $url );
	}

	return exterior_exone_store_link( $url );
}

/**
 * 一覧の見出し・絞り込み・0 件の文言（現サイト page-column.php の構成要素）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_column_page_texts() {
	return array(
		'title'       => 'COLUMN',
		// SP は「外構・エクステリアの」/「お役立ち情報コラム」の 2 行。書式は exterior_exone_highend_lines()。
		'lead'        => array(
			array( '外構・エクステリアの', 'sp' ),
			'お役立ち情報コラム',
		),
		'filter'      => 'カテゴリーで絞り込む',
		'filter_all'  => 'すべて',
		'filter_aria' => 'コラムカテゴリー',
		// カンプに無い状態（仕様書 Q15）。
		'empty'       => '該当する記事はありません',
		'pager_aria'  => 'ページ送り',
		'prev'        => '前へ',
		'next'        => '次へ',
	);
}

/**
 * 絞り込みのタブ（すべて + カテゴリー。記事が 0 件のカテゴリーも出す）。
 *
 * 並びはカテゴリー名の順（現サイトの get_terms() の既定と同じ）。
 *
 * @return array<int, array{label:string, url:string, current:bool}>
 */
function exterior_exone_column_page_tabs() {
	$texts   = exterior_exone_column_page_texts();
	$current = exterior_exone_column_current_cat();
	$tabs    = array(
		array(
			'label'   => $texts['filter_all'],
			'url'     => exterior_exone_column_list_url(),
			'current' => null === $current,
		),
	);

	foreach ( exterior_exone_column_terms() as $term ) {
		$tabs[] = array(
			'label'   => $term->name,
			'url'     => exterior_exone_column_list_url( 1, $term->slug ),
			'current' => $current && (int) $current->term_id === (int) $term->term_id,
		);
	}

	return $tabs;
}

/**
 * コラムカテゴリーの全件（タブの並び順）。
 *
 * @return array<int, WP_Term>
 */
function exterior_exone_column_terms() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'column_category',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	return is_wp_error( $terms ) ? array() : array_values( $terms );
}

/**
 * 記事に付いたカテゴリー名（タブと同じ並び）。
 *
 * @param WP_Post $post コラム。
 * @return array<int, string>
 */
function exterior_exone_column_post_cats( $post ) {
	$terms = get_the_terms( $post, 'column_category' );

	if ( ! $terms || is_wp_error( $terms ) ) {
		return array();
	}

	usort( $terms, fn( $a, $b ) => strcmp( $a->name, $b->name ) );

	return wp_list_pluck( $terms, 'name' );
}

/**
 * 一覧の抜粋。手入力の抜粋があればそれ、無ければ本文から作る（90 文字 +「…」）。
 *
 * 本文は「カスタム HTML」ブロックで <style> / <script> を含むため、HTML コメント
 *（ブロックの区切り）と <style> / <script> の中身・タグを除き、空白を詰めてから切る。
 *
 * @param WP_Post $post コラム。
 * @return string エスケープ前のテキスト。
 */
function exterior_exone_column_excerpt( $post ) {
	if ( has_excerpt( $post ) ) {
		return trim( wp_strip_all_tags( (string) $post->post_excerpt, true ) );
	}

	$text = preg_replace( '/<!--.*?-->/s', '', (string) $post->post_content );
	$text = wp_strip_all_tags( strip_shortcodes( (string) $text ), true );
	$text = trim( html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );

	if ( mb_strlen( $text ) > EXTERIOR_EXONE_COLUMN_EXCERPT_LENGTH ) {
		$text = mb_substr( $text, 0, EXTERIOR_EXONE_COLUMN_EXCERPT_LENGTH ) . '…';
	}

	return $text;
}

/**
 * 一覧のカード 1 枚ぶん。
 *
 * @param WP_Post $post コラム。
 * @return array{id:int, title:string, url:string, image_id:int, date:string, datetime:string, cats:array<int, string>, excerpt:string}
 */
function exterior_exone_column_page_card( $post ) {
	return array(
		'id'       => (int) $post->ID,
		'title'    => get_the_title( $post ),
		'url'      => exterior_exone_store_link( (string) get_permalink( $post ) ),
		'image_id' => (int) get_post_thumbnail_id( $post ),
		'date'     => get_the_date( 'Y.m.d', $post ),
		'datetime' => get_the_date( 'Y-m-d', $post ),
		'cats'     => exterior_exone_column_post_cats( $post ),
		'excerpt'  => exterior_exone_column_excerpt( $post ),
	);
}

/**
 * ページ送りの項目（前へ・番号・省略・次へ）。全体が 1 ページなら空。
 *
 * 番号は先頭・末尾と現在の前後 1 つずつ（現サイトの mid_size 1 / end_size 1 と同じ）。
 * URL は exterior_exone_column_list_url() で作る（今の URL の余計なクエリは引き継がない）。
 *
 * @param int $current 現在のページ。
 * @param int $total   総ページ数。
 * @return array<int, array{type:string, label:string, url:string}>
 */
function exterior_exone_column_page_pager( $current, $total ) {
	$term = exterior_exone_column_current_cat();
	$slug = $term ? (string) $term->slug : '';

	return exterior_exone_list_pager(
		$current,
		$total,
		exterior_exone_column_page_texts(),
		fn( $page ) => exterior_exone_column_list_url( $page, $slug )
	);
}

/**
 * 一覧のページ送りの並び（コラム・お知らせで共有）。
 * 番号は先頭・末尾と現在の前後 1 つずつ（現サイトの mid_size 1 / end_size 1 と同じ）。
 *
 * @param int                   $current 現在のページ。
 * @param int                   $total   総ページ数。
 * @param array<string, string> $texts   文言（prev / next）。
 * @param callable              $url_for ページ番号 → URL。
 * @return array<int, array{type:string, label:string, url:string}>
 */
function exterior_exone_list_pager( $current, $total, $texts, $url_for ) {
	$current = max( 1, (int) $current );
	$total   = (int) $total;

	if ( $total <= 1 ) {
		return array();
	}

	$items = array();

	if ( $current > 1 ) {
		$items[] = array(
			'type'  => 'prev',
			'label' => $texts['prev'],
			'url'   => $url_for( $current - 1 ),
		);
	}

	$dots = false;

	for ( $page = 1; $page <= $total; $page++ ) {
		if ( 1 === $page || $total === $page || abs( $page - $current ) <= 1 ) {
			$items[] = array(
				'type'  => $page === $current ? 'current' : 'page',
				'label' => (string) $page,
				'url'   => $page === $current ? '' : $url_for( $page ),
			);
			$dots    = false;
		} elseif ( ! $dots ) {
			$items[] = array(
				'type'  => 'dots',
				'label' => '…',
				'url'   => '',
			);
			$dots    = true;
		}
	}

	if ( $current < $total ) {
		$items[] = array(
			'type'  => 'next',
			'label' => $texts['next'],
			'url'   => $url_for( $current + 1 ),
		);
	}

	return $items;
}

/* ============================================================
   詳細 /column/<投稿名>/（single-column.php）
   ============================================================ */

/**
 * 詳細の固定の文言（現サイト single-column.php の構成要素）。
 *
 * @return array<string, string>
 */
function exterior_exone_column_detail_texts() {
	return array(
		'updated'       => '最終更新日',
		'adjacent_aria' => '前後のコラム記事',
		'prev'          => '前の記事',
		'next'          => '次の記事',
		'back'          => 'コラム一覧へ戻る',
	);
}

/**
 * 詳細の表示データ（メタ・タイトル・アイキャッチ・前後の記事）。本文は the_content() で出す。
 *
 * カテゴリーは先頭の 1 つ（現サイトと同じ。並びは一覧のラベルと同じ名前順）。
 * 最終更新日は更新日が投稿日と違う日のときだけ。前後の記事は公開日順
 *（前 = 1 つ古い記事、次 = 1 つ新しい記事）。無い側は null。
 *
 * @param WP_Post $post コラム。
 * @return array<string, mixed>
 */
function exterior_exone_column_detail( $post ) {
	$cats      = exterior_exone_column_post_cats( $post );
	$published = get_the_date( 'Y-m-d', $post );
	$modified  = get_the_modified_date( 'Y-m-d', $post );
	$image_id  = (int) get_post_thumbnail_id( $post );
	$image_alt = $image_id ? trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ) : '';

	$adjacent = array();

	foreach ( array( 'prev' => true, 'next' => false ) as $key => $previous ) {
		$found = get_adjacent_post( false, '', $previous );

		$adjacent[ $key ] = $found instanceof WP_Post ? array(
			'title' => get_the_title( $found ),
			'url'   => exterior_exone_store_link( (string) get_permalink( $found ) ),
		) : null;
	}

	return array(
		'title'     => get_the_title( $post ),
		'cat'       => $cats ? $cats[0] : '',
		'date'      => get_the_date( 'Y.m.d', $post ),
		'datetime'  => $published,
		'modified'  => $modified !== $published ? get_the_modified_date( 'Y.m.d', $post ) : '',
		'mdatetime' => $modified,
		'image_id'  => $image_id,
		'image_alt' => '' !== $image_alt ? $image_alt : get_the_title( $post ),
		'prev'      => $adjacent['prev'],
		'next'      => $adjacent['next'],
		'back'      => exterior_exone_column_list_url(),
	);
}

/**
 * 詳細の本文を表示用に整える（表示時だけ。本文 post_content は書き換えない）。
 *
 * - 記事に埋め込まれた <style> を取り除く。現サイトの記事は「カスタム HTML」ブロックに
 *   黒地用の <style>（色・幅 880）を持つため、そのままだとテーマの配色・幅にならない
 *  （決定事項 §4 Q6）。見た目は css/column-page.css の本文・部品のスタイルが持つ
 * - 本文内の画像の loading="lazy" を外す。幅・高さの無い外部画像が遅れて読み込まれると
 *   目次（.toc → h2[id]）で移動した直後に上の画像が広がり、見出しが画面の外へずれるため
 *
 * <script>（ライトボックス・構造化データ）は残す。
 *
 * @param string $content 本文。
 * @return string
 */
function exterior_exone_column_display_content( $content ) {
	if ( ! is_singular( 'column' ) || 'column' !== get_post_type() ) {
		return $content;
	}

	$content = (string) preg_replace( '#<style\b[^>]*>.*?</style>#is', '', (string) $content );

	return (string) preg_replace( '#(<img\b[^>]*?)\s+loading=(["\'])lazy\2#i', '$1', $content );
}
add_filter( 'the_content', 'exterior_exone_column_display_content', 20 );

/* ============================================================
   共通 COLUMN セクションのカード（支店・WORKS・コラム詳細の末尾）
   ============================================================ */

/**
 * 共通 COLUMN（template-parts/common/section-column.php）のカード。コラムの最新 3 件。
 *
 * 見た目は exterior_exone_news_cards()（TOP の NEWS）と同じ形 {title, url, image_id} で返す。
 * コラム詳細では表示中の記事を除く（決定事項 §3「共通」）。0 件なら空配列 = セクションを出さない。
 *
 * @param int|int[] $exclude 除く記事の ID。
 * @return array<int, array{title:string, url:string, image_id:int}>
 */
function exterior_exone_column_cards( $exclude = array() ) {
	$posts = get_posts(
		array(
			'post_type'        => 'column',
			'post_status'      => 'publish',
			'posts_per_page'   => 3,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post__not_in'     => array_filter( array_map( 'absint', (array) $exclude ) ),
			'no_found_rows'    => true,
			'suppress_filters' => false,
		)
	);

	return array_map(
		function ( $post ) {
			return array(
				'title'    => get_the_title( $post ),
				'url'      => exterior_exone_store_link( (string) get_permalink( $post ) ),
				// 0 なら画像未設定（グレーの面）。
				'image_id' => (int) get_post_thumbnail_id( $post ),
			);
		},
		$posts
	);
}
