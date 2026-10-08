<?php
/**
 * WORKS（施工事例）一覧 /works/ と詳細 /works/<投稿名>/ のデータ。
 *
 * カンプ: PC 一覧 821:113 / PC 詳細 821:174 / SP 836:37（詳細）/ guide 821:337・821:336
 * 設計メモ: docs/figma-works-page.md
 * 仕様書: docs/spec-20261008-works-page.md（決定事項 docs/spec-20261008-works-page-decisions.md）
 *
 * 事例そのものは投稿タイプ works（管理画面で登録）。ここはテイストの並び順・英名、
 * 固定の文言、投稿 → 表示用データの組み立てだけを持つ。
 * 1 支店 / DX の 1 事例部品（.p-cworks）は inc/works-data.php で、別物。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 一覧（works のアーカイブ）のメインクエリだけ、全件・日付の新しい順にする。
 *
 * ページ送りは付けない（決定 §3）。/works/page/2/ 以降は該当なしにして 404 にする。
 *
 * @param WP_Query $query クエリ。
 */
function exterior_exone_works_page_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'works' ) ) {
		return;
	}

	$query->set( 'posts_per_page', -1 );
	$query->set( 'orderby', 'date' );
	$query->set( 'order', 'DESC' );

	if ( $query->is_paged() ) {
		$query->set( 'post__in', array( 0 ) );
	}
}
add_action( 'pre_get_posts', 'exterior_exone_works_page_query' );

/**
 * テイスト（works_package のターム名）の並び順と英名。カンプのボタンの順。
 *
 * @return array<string, string> ターム名 => 英名。
 */
function exterior_exone_works_page_taste_table() {
	return array(
		'クールモダン'       => 'Cool Modern',
		'アーバンモダン'     => 'Urban Modern',
		'シンプルモダン'     => 'Simple Modern',
		'ラスティックモダン' => 'Rustic Modern',
		'スタイリッシュモダン' => 'Stylish Modern',
		'ナチュラルモダン'   => 'Natural Modern',
	);
}

/**
 * タームを並び順表の順に並べ、{slug, name, eng} にする。表に無いタームは末尾に名前順。
 *
 * @param array<int, WP_Term> $terms タームの配列。
 * @return array<int, array{slug:string, name:string, eng:string}>
 */
function exterior_exone_works_page_sort_tastes( $terms ) {
	$table = exterior_exone_works_page_taste_table();
	$order = array_flip( array_keys( $table ) );
	$terms = array_values( array_filter( (array) $terms, fn( $term ) => $term instanceof WP_Term ) );

	usort(
		$terms,
		function ( $a, $b ) use ( $order ) {
			$ia = $order[ $a->name ] ?? PHP_INT_MAX;
			$ib = $order[ $b->name ] ?? PHP_INT_MAX;

			return $ia === $ib ? strcmp( $a->name, $b->name ) : $ia <=> $ib;
		}
	);

	return array_map(
		fn( $term ) => array(
			'slug' => (string) $term->slug,
			'name' => (string) $term->name,
			'eng'  => $table[ $term->name ] ?? (string) $term->name,
		),
		$terms
	);
}

/**
 * 絞り込みボタン用のテイスト一覧（公開事例が 0 件のタームも含める）。
 *
 * @return array<int, array{slug:string, name:string, eng:string}>
 */
function exterior_exone_works_page_tastes() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'works_package',
			'hide_empty' => false,
		)
	);

	return is_wp_error( $terms ) ? array() : exterior_exone_works_page_sort_tastes( $terms );
}

/**
 * 投稿に付いたテイスト（並び順表の順）。
 *
 * @param WP_Post $post 施工事例。
 * @return array<int, array{slug:string, name:string, eng:string}>
 */
function exterior_exone_works_page_post_tastes( $post ) {
	$terms = get_the_terms( $post, 'works_package' );

	return ( ! $terms || is_wp_error( $terms ) ) ? array() : exterior_exone_works_page_sort_tastes( $terms );
}

/**
 * 一覧のカード 1 枚ぶん。写真はギャラリーの 1 枚目、行き先は詳細の 1 セクション目。
 *
 * @param WP_Post $post 施工事例。
 * @return array{id:int, title:string, url:string, image_id:int, tastes:array<int, string>}
 */
function exterior_exone_works_page_card( $post ) {
	$images = exterior_exone_works_gallery_ids( $post->ID );

	return array(
		'id'       => (int) $post->ID,
		'title'    => get_the_title( $post ),
		'url'      => exterior_exone_store_link( get_permalink( $post ) . '#works-detail' ),
		'image_id' => $images ? (int) $images[0] : 0,
		'tastes'   => wp_list_pluck( exterior_exone_works_page_post_tastes( $post ), 'slug' ),
	);
}

/**
 * 全件のカード（詳細ページ内の一覧ブロック用。並びはアーカイブと同じ）。
 *
 * @return array<int, array<string, mixed>>
 */
function exterior_exone_works_page_cards() {
	$posts = get_posts(
		array(
			'post_type'        => 'works',
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'suppress_filters' => false,
		)
	);

	return array_map( 'exterior_exone_works_page_card', $posts );
}

/**
 * FV（PC 821:114 / SP 836:38・836:46・836:48）。一覧・詳細で同じ。
 *
 * @return array<string, mixed>
 */
function exterior_exone_works_page_fv() {
	return array(
		'image' => 'pc-fv-bg-821-116.jpg', // 821:116 / 836:38（1500x1049）
		'img'   => array( 1500, 1049 ),
		'title' => 'WORKS', // 821:118 / 836:48
		// 821:117（PC 1 行）/ 836:46（SP 2 行）。書式は exterior_exone_highend_lines()。
		'lead'  => array(
			array( '理想の暮らしを描く、', 'sp' ),
			'外構のご提案事例',
		),
	);
}

/**
 * 絞り込みの見出しと 0 件の文言（821:129・821:128 / 836:88・836:89）。
 *
 * @return array<string, string>
 */
function exterior_exone_works_page_filter() {
	return array(
		'eng'     => 'Filter by Design Style',
		'heading' => 'デザインテイストで絞り込む',
		'label'   => 'デザインテイスト',
		// カンプに無い状態（決定 §3。公開前にユーザー確認）。
		'empty'   => '該当する事例はありません',
	);
}

/**
 * COLUMN のコーポレート文言（821:148 / 836:95。形は exterior_exone_store_column() と同じ）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_works_page_column() {
	return array(
		'title'        => 'COLUMN',
		'jp'           => '外構づくりに役立つ情報',
		'lead'         => array(
			'外構費用の考え方やカーポートの選び方、目隠しフェンス、庭づくりなど、外構工事を検討している方に役立つ情報を発信します。',
			'地域の気候や住宅事情も踏まえながら、後悔しない外構づくりのポイントを分かりやすく解説します。',
		),
		'lead_sp_join' => array(),
	);
}

/**
 * 詳細のキャプション（画像の位置で固定）。1 枚目も、サムネに回ってきたときに
 * 名前が無いと浮くため「メイン写真」とする（ユーザー指示 2026-10-08）。
 *
 * @return array<int, string>
 */
function exterior_exone_works_page_captions() {
	return array( 'メイン写真', '外観写真', '3Dパース', '図面' );
}

/**
 * 詳細ページのデータ。
 *
 * @param WP_Post $post 施工事例。
 * @return array<string, mixed>
 */
function exterior_exone_works_page_detail( $post ) {
	$tastes   = exterior_exone_works_page_post_tastes( $post );
	$eng      = $tastes ? $tastes[0]['eng'] : '';
	$captions = exterior_exone_works_page_captions();
	$photos   = array();

	foreach ( array_slice( exterior_exone_works_gallery_ids( $post->ID ), 0, count( $captions ) ) as $index => $id ) {
		$photos[] = array(
			'attachment_id' => (int) $id,
			'caption'       => $captions[ $index ],
		);
	}

	// 空行で段落、段落内の改行はそのまま（出力側で <br>）。
	$content    = str_replace( array( "\r\n", "\r" ), "\n", (string) $post->post_content );
	$paragraphs = array_values( array_filter( array_map( 'trim', preg_split( "/\n\s*\n/", $content ) ), 'strlen' ) );

	return array(
		'eng'        => $eng,
		'title'      => get_the_title( $post ),
		'name'       => strtoupper( $eng ),
		'photos'     => $photos,
		'paragraphs' => $paragraphs,
	);
}

/**
 * WORKS ページ用の画像 URL を返す（WebP があれば優先）。
 *
 * images/works/webp/ に同名の .webp があればそちらを返す。元画像は残してあるので、
 * webp ディレクトリを削除すれば元の配信に戻る（images/plans/ と同じ）。
 *
 * @param string $file images/works/ 配下のファイル名。
 * @return string バージョンクエリ付き URL。
 */
function exterior_exone_works_page_image( $file ) {
	$webp = 'images/works/webp/' . preg_replace( '/\.(jpe?g|png)$/i', '.webp', $file );

	if ( $webp !== 'images/works/webp/' . $file && file_exists( get_theme_file_path( $webp ) ) ) {
		return exterior_exone_asset_url( $webp );
	}

	return exterior_exone_asset_url( 'images/works/' . $file );
}
