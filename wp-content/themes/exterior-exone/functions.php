<?php
/**
 * exterior-exone テーマの基本設定。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_theme_file_path( 'inc/menus.php' );
require_once get_theme_file_path( 'inc/post-types.php' );
require_once get_theme_file_path( 'inc/meta-boxes.php' );
require_once get_theme_file_path( 'inc/top-data.php' );
require_once get_theme_file_path( 'inc/company-data.php' );

/**
 * テーマサポートの登録。
 */
function exterior_exone_setup() {
	// <title> を WordPress に出力させる。
	add_theme_support( 'title-tag' );

	// アイキャッチ画像。
	add_theme_support( 'post-thumbnails' );

	// 検索フォーム・コメント等を HTML5 で出力。
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	// フィードのリンクを自動出力。
	add_theme_support( 'automatic-feed-links' );

	// ナビゲーションメニュー。
	register_nav_menus(
		array(
			'global' => 'グローバルナビ（ヘッダー）',
			'footer' => 'フッターメニュー',
			'store'  => 'フッター STORE（店舗一覧）',
		)
	);
}
add_action( 'after_setup_theme', 'exterior_exone_setup' );

/**
 * テーマ内ファイルの URL を filemtime 付きで返す。
 *
 * @param string $relative_path テーマルートからの相対パス。
 * @return string バージョンクエリ付き URL。
 */
function exterior_exone_asset_url( $relative_path ) {
	$url = get_theme_file_uri( $relative_path );
	$ver = exterior_exone_asset_version( $relative_path );

	return $ver ? add_query_arg( 'ver', $ver, $url ) : $url;
}

/**
 * テーマ内ファイルの更新時刻をバージョン文字列として返す。
 *
 * @param string $relative_path テーマルートからの相対パス。
 * @return string|null 存在しなければ null。
 */
function exterior_exone_asset_version( $relative_path ) {
	$path = get_theme_file_path( $relative_path );

	return file_exists( $path ) ? (string) filemtime( $path ) : null;
}

/**
 * スタイル・スクリプトの読み込み。
 */
function exterior_exone_enqueue_assets() {
	// Web フォント（テーマ同梱。style.css より先に読む）。
	wp_enqueue_style(
		'exterior-exone-fonts',
		get_theme_file_uri( 'css/fonts.css' ),
		array(),
		exterior_exone_asset_version( 'css/fonts.css' )
	);

	// Swiper（テーマ同梱）。
	wp_register_style(
		'swiper',
		get_theme_file_uri( 'js/vendor/swiper/swiper-bundle.min.css' ),
		array(),
		exterior_exone_asset_version( 'js/vendor/swiper/swiper-bundle.min.css' )
	);
	wp_register_script(
		'swiper',
		get_theme_file_uri( 'js/vendor/swiper/swiper-bundle.min.js' ),
		array(),
		exterior_exone_asset_version( 'js/vendor/swiper/swiper-bundle.min.js' ),
		true
	);

	// ベンダー CSS はテーマ本体より先に読み込む。
	// （Swiper の .swiper{padding:0} などがテーマ側の指定を打ち消すため）
	$exterior_exone_style_deps = array( 'exterior-exone-fonts' );

	if ( is_front_page() ) {
		wp_enqueue_style( 'swiper' );
		$exterior_exone_style_deps[] = 'swiper';
	}

	// テーマ本体スタイル。
	wp_enqueue_style(
		'exterior-exone-style',
		get_stylesheet_uri(),
		$exterior_exone_style_deps,
		exterior_exone_asset_version( 'style.css' )
	);

	// 企業情報ページ専用アセット（CSS はテーマ本体の後に読む）。
	if ( is_page_template( 'page-company.php' ) || is_page( 'company' ) ) {
		wp_enqueue_style(
			'exterior-exone-company',
			get_theme_file_uri( 'css/company.css' ),
			array( 'exterior-exone-style' ),
			exterior_exone_asset_version( 'css/company.css' )
		);

		// FV の暗幕（TOP と同じ演出。対象は .l-pin の中身で決まる）。
		wp_enqueue_script(
			'exterior-exone-fv-mask',
			get_theme_file_uri( 'js/fv-mask.js' ),
			array(),
			exterior_exone_asset_version( 'js/fv-mask.js' ),
			true
		);
	}

	// ハンバーガーメニューの開閉（全ページ共通）。
	wp_enqueue_script(
		'exterior-exone-nav-toggle',
		get_theme_file_uri( 'js/nav-toggle.js' ),
		array(),
		exterior_exone_asset_version( 'js/nav-toggle.js' ),
		true
	);

	// 追従ヘッダーの背景切り替え（全ページ共通）。
	wp_enqueue_script(
		'exterior-exone-header-scroll',
		get_theme_file_uri( 'js/header-scroll.js' ),
		array(),
		exterior_exone_asset_version( 'js/header-scroll.js' ),
		true
	);

	// スクロール連動アニメーション（全ページ共通）。
	wp_enqueue_script(
		'exterior-exone-scroll-reveal',
		get_theme_file_uri( 'js/scroll-reveal.js' ),
		array(),
		exterior_exone_asset_version( 'js/scroll-reveal.js' ),
		true
	);

	// TOP ページで使うスライダー類。
	if ( is_front_page() ) {
		wp_enqueue_script( 'swiper' );

		foreach ( exterior_exone_front_page_scripts() as $handle => $relative_path ) {
			if ( ! file_exists( get_theme_file_path( $relative_path ) ) ) {
				continue;
			}

			wp_enqueue_script(
				$handle,
				get_theme_file_uri( $relative_path ),
				array( 'swiper' ),
				exterior_exone_asset_version( $relative_path ),
				true
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'exterior_exone_enqueue_assets' );

/**
 * TOP ページで読み込む機能別 JS の一覧。
 *
 * 1 機能 = 1 ファイル。実ファイルが存在するものだけ enqueue される。
 *
 * @return array<string, string> ハンドル => テーマ相対パス。
 */
function exterior_exone_front_page_scripts() {
	return array(
		'exterior-exone-fv-slider'      => 'js/fv-slider.js',
		'exterior-exone-fv-mask'        => 'js/fv-mask.js',
		'exterior-exone-dx-slider'      => 'js/dx-slider.js',
		'exterior-exone-works-carousel' => 'js/works-carousel.js',
		'exterior-exone-scroll-row'     => 'js/scroll-row.js',
		'exterior-exone-plans-switch'   => 'js/plans-switch.js',
		'exterior-exone-copy-video'     => 'js/copy-video.js',
	);
}

/**
 * TOP 用の動画 URL を filemtime 付きで返すショートヘルパ。
 *
 * @param string $file videos/top/ 配下のファイル名。
 * @return string バージョンクエリ付き URL。
 */
function exterior_exone_top_video( $file ) {
	return exterior_exone_asset_url( 'videos/top/' . $file );
}

/**
 * 文字列を 1 文字ずつ span で包む（文字送りアニメーション用）。
 *
 * --char-index に通し番号を入れ、CSS 側が transition-delay に掛けて 1 文字ずつ遅らせる。
 * 半角スペースはそのままだと行末で潰れて字送りの間隔が崩れるため &nbsp; にする。
 *
 * @param string $text  対象の文字列。
 * @param string $class 1 文字ずつの span に付けるクラス（送りの見た目は CSS 側が持つ）。
 * @return string エスケープ済みの HTML。
 */
function exterior_exone_split_chars( $text, $class = 'p-fv__char' ) {
	$chars = preg_split( '//u', $text, -1, PREG_SPLIT_NO_EMPTY );
	$html  = '';

	foreach ( (array) $chars as $index => $char ) {
		$html .= sprintf(
			'<span class="%1$s" style="--char-index:%2$d">%3$s</span>',
			esc_attr( $class ),
			(int) $index,
			' ' === $char ? '&nbsp;' : esc_html( $char )
		);
	}

	return $html;
}

/**
 * TOP 用の画像 URL を filemtime 付きで返すショートヘルパ。
 *
 * images/top/webp/ に同名の .webp があればそちらを返す（合計で約 88% 軽くなる）。
 * 元画像は残してあるので、webp ディレクトリを削除すれば元の配信に戻る。
 *
 * @param string $file images/top/ 配下のファイル名。
 * @return string バージョンクエリ付き URL。
 */
function exterior_exone_top_image( $file ) {
	$webp = 'images/top/webp/' . preg_replace( '/\.(jpe?g|png)$/i', '.webp', $file );

	if ( $webp !== 'images/top/webp/' . $file && file_exists( get_theme_file_path( $webp ) ) ) {
		return exterior_exone_asset_url( $webp );
	}

	return exterior_exone_asset_url( 'images/top/' . $file );
}

/**
 * 開発環境ではフロント側の WordPress 管理バーを出さない。
 *
 * カンプと同じ見え方で確認するため。判定は wp-config.php の
 * WP_ENVIRONMENT_TYPE（このサイトは 'local'）に任せているので、
 * 本番へ反映しても管理バーは通常どおり表示される。
 *
 * @param bool $show 現在の表示可否。
 * @return bool
 */
function exterior_exone_hide_admin_bar( $show ) {
	// 管理画面側は WordPress の操作導線なのでそのまま残す。
	if ( is_admin() ) {
		return $show;
	}

	if ( in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) ) {
		return false;
	}

	return $show;
}
add_filter( 'show_admin_bar', 'exterior_exone_hide_admin_bar' );

/**
 * ヘッダーを FV に重ねるページかどうか。
 *
 * 全幅の FV から始まるページは、ヘッダーを写真の上に半透明で重ねる
 * （カンプ TOP 917:2733 / 企業情報 393:546）。それ以外は黒帯のまま。
 * 下層ページが増えたらここに追加する。
 *
 * @return bool
 */
function exterior_exone_has_fv_header() {
	return is_front_page() || is_page( 'company' );
}

/**
 * body_class に判定用クラスを足す。
 *
 * @param array $classes 既存クラス。
 * @return array
 */
function exterior_exone_body_class( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'is-front-page';
	}

	if ( exterior_exone_has_fv_header() ) {
		$classes[] = 'has-fv-header';
	}

	return $classes;
}
add_filter( 'body_class', 'exterior_exone_body_class' );
