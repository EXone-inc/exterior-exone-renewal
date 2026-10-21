<?php
/**
 * サンクスページ（送信完了）の判定・文言・画像・並び。
 *
 * カンプ: PC 964:47 / SP 964:78（どちらもボード 853:582 の中）
 * 設計メモ: docs/figma-contact-thanks-page.md
 * 仕様書: docs/spec-20261015-thanks-page.md（決定事項 docs/spec-20261015-thanks-page-decisions.md）
 *
 * 固定ページ thanks を contact / recruit の子として置き、どちらのサンクスかは親ページの
 * スラッグで判定する（exterior_exone_is_recruit_entry_page() と同じ作法）。テンプレートは
 * page-thanks.php 1 本で、版ごとの違いは exterior_exone_thanks_versions() の文言だけ。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 版ごとの文言（決定事項 §2「文言（確定）」。改行は設計メモ §4 の行割り）。
 *
 * キーは親ページのスラッグ。本文・説明の各行は array( 文言, 改行, 太字 ):
 * 改行 = both（PC・SP とも）/ pc / sp / none（行末）。太字は true のときだけ <strong>。
 *
 * @return array<string, array<string, mixed>>
 */
function exterior_exone_thanks_versions() {
	return array(
		'contact' => array(
			// 966:111 / 966:190
			'message' => 'お問い合わせ、ありがとうございます。',
			// 966:114（PC 5 行）/ 966:194（SP 7 行）。空行（U+200B）は段落の間の空きにする
			'body'    => array(
				array(
					array( 'この度は、EXoneへお問い合わせいただき、', 'sp' ),
					array( '誠にありがとうございます。', 'both' ),
					array( 'お問い合わせを正常に受け付けました。', 'none' ),
				),
				array(
					array( 'ご入力いただいた内容を確認のうえ、', 'sp' ),
					array( '通常2営業日以内に担当スタッフよりご連絡いたします。', 'both', true ),
					array( 'ご連絡まで、今しばらくおまちください。', 'none' ),
				),
			),
			// 966:124・966:130・966:132 / 966:199・966:205・966:219
			'steps'   => array(
				array(
					'title' => 'お問い合わせ完了',
					'text'  => array(
						array( 'お問い合わせを正常に受け付けました。', 'none' ),
					),
				),
				array(
					'title' => '担当スタッフよりご連絡',
					'text'  => array(
						array( '通常2営業日以内に、', 'both' ),
						array( '担当スタッフよりご連絡いたします。', 'none' ),
					),
				),
				// SP 966:220 はカンプの不備（02 と同文）なので PC 966:141 の文言。
				// SP の 2 行目は文字枠に収まらないため「進め方を」の後でも割る。
				array(
					'title' => 'ご相談内容の確認・ご案内',
					'text'  => array(
						array( 'ご要望を詳しくお伺いし、', 'both' ),
						array( '内容に応じて今後の進め方を', 'sp' ),
						array( 'ご案内いたします。', 'none' ),
					),
				),
			),
		),
		// 採用版（カンプに無い。決定事項 §2 の採用列。改行・太字はお問い合わせ版と対応する位置 = §4 Q6）
		'recruit' => array(
			'message' => 'ご応募、ありがとうございます。',
			'body'    => array(
				array(
					array( 'この度は、EXoneの採用にご応募いただき、', 'sp' ),
					array( '誠にありがとうございます。', 'both' ),
					array( 'ご応募を正常に受け付けました。', 'none' ),
				),
				array(
					array( 'ご入力いただいた内容を確認のうえ、', 'sp' ),
					array( '追って採用担当よりご連絡いたします。', 'both', true ),
					array( 'ご連絡まで、今しばらくおまちください。', 'none' ),
				),
			),
			'steps'   => array(
				array(
					'title' => 'ご応募完了',
					'text'  => array(
						array( 'ご応募を正常に受け付けました。', 'none' ),
					),
				),
				array(
					'title' => '採用担当よりご連絡',
					'text'  => array(
						array( '内容を確認のうえ、', 'both' ),
						array( '追って採用担当よりご連絡いたします。', 'none' ),
					),
				),
				// SP の 2 行目はお問い合わせ版 03 と同じく文字枠に収まらないため「流れを」の後でも割る。
				array(
					'title' => '選考のご案内',
					'text'  => array(
						array( '書類選考のうえ、', 'both' ),
						array( '面接日程など今後の流れを', 'sp' ),
						array( 'ご案内いたします。', 'none' ),
					),
				),
			),
		),
	);
}

/**
 * 版ごとのサンクスページの固定ページ ID（公開中のものだけ。無ければ 0）。
 *
 * @param string $version 版（親ページのスラッグ。contact / recruit）。
 * @return int
 */
function exterior_exone_thanks_page_id( $version ) {
	if ( ! array_key_exists( $version, exterior_exone_thanks_versions() ) ) {
		return 0;
	}

	if ( exterior_exone_coexistence_enabled() ) {
		$page = exterior_exone_coexistence_page( '/' . $version . '/thanks/' );
		return $page ? (int) $page->ID : 0;
	}

	$page = get_page_by_path( $version . '/thanks', OBJECT, 'page' );

	return $page instanceof WP_Post && 'publish' === $page->post_status ? (int) $page->ID : 0;
}

/**
 * フォームの送信成功後の遷移先（決定事項 §3）。サンクスページが無い・非公開なら空文字（遷移しない = §4 Q-A）。
 *
 * お問い合わせは支店モードの ?store= を引き継ぐ。採用は引き継がない。
 *
 * @param string $version 版（contact / recruit）。
 * @return string
 */
function exterior_exone_thanks_url( $version ) {
	$id = exterior_exone_thanks_page_id( $version );

	if ( ! $id ) {
		return '';
	}

	$url = (string) get_permalink( $id );

	return 'contact' === $version ? exterior_exone_store_link( $url ) : $url;
}

/**
 * サンクスページに noindex を付ける（follow は既定のまま。直接アクセスは普通に表示）。
 *
 * @param array<string, bool|string> $robots robots の指定。
 * @return array<string, bool|string>
 */
function exterior_exone_thanks_robots( $robots ) {
	if ( exterior_exone_is_thanks_page() ) {
		$robots['noindex'] = true;
	}

	return $robots;
}
add_filter( 'wp_robots', 'exterior_exone_thanks_robots' );

/**
 * 標準のサイトマップ（固定ページ）からサンクスページを外す。
 *
 * @param array  $args      サイトマップの WP_Query の引数。
 * @param string $post_type 投稿タイプ。
 * @return array
 */
function exterior_exone_thanks_sitemap_query_args( $args, $post_type ) {
	if ( 'page' !== $post_type ) {
		return $args;
	}

	$ids = array_filter( array_map( 'exterior_exone_thanks_page_id', array_keys( exterior_exone_thanks_versions() ) ) );

	if ( $ids ) {
		$args['post__not_in'] = array_merge( isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(), $ids );
	}

	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'exterior_exone_thanks_sitemap_query_args', 10, 2 );

/**
 * いま表示しているサンクスページの版（親ページのスラッグ）。サンクスページでなければ空文字。
 *
 * スラッグ thanks でも、親が版の無いページ（または親が無い）なら空文字（通常の固定ページ扱い）。
 *
 * @return string
 */
function exterior_exone_thanks_version() {
	if ( ! is_page( 'thanks' ) ) {
		return '';
	}

	$parent = (int) wp_get_post_parent_id( get_queried_object_id() );

	if ( $parent <= 0 ) {
		return '';
	}

	$slug = (string) get_post_field( 'post_name', $parent );

	return array_key_exists( $slug, exterior_exone_thanks_versions() ) ? $slug : '';
}

/**
 * サンクスページかどうか。
 *
 * @return bool
 */
function exterior_exone_is_thanks_page() {
	return '' !== exterior_exone_thanks_version();
}

/**
 * 表示中の版の文言。サンクスページでなければ空配列。
 *
 * @return array<string, mixed>
 */
function exterior_exone_thanks_texts() {
	$version  = exterior_exone_thanks_version();
	$versions = exterior_exone_thanks_versions();

	return '' !== $version ? $versions[ $version ] : array();
}

/**
 * FV（CONTACT の FV と同じ写真・切り抜き。文言だけ差し替え。964:59 / 964:79）。
 *
 * template-parts/contact/section-fv.php にそのまま渡す。
 *
 * @return array<string, mixed>
 */
function exterior_exone_thanks_fv() {
	$fv = exterior_exone_contact_fv();

	return array(
		'image' => $fv['image'],
		'img'   => $fv['img'],
		'title' => 'THANK YOU', // 964:64 / 964:94
		'sub'   => '送信完了', // 964:63 / 964:92
	);
}

/**
 * 見出し（英字 + 和文。966:121・966:120 / 966:157・966:156）。
 *
 * @return array<string, array{en: string, ja: array}>
 */
function exterior_exone_thanks_headings() {
	return array(
		'steps'   => array(
			'en' => 'Next Step',
			'ja' => array(
				array( '今後のご案内', 'none' ),
			),
		),
		'explore' => array(
			'en' => 'Explore EXone',
			// SP 966:227 は「、」の後で 2 行
			'ja' => array(
				array( '理想の外構づくりを、', 'sp' ),
				array( 'もっと身近に', 'none' ),
			),
		),
	);
}

/**
 * Next Step のアイコン（966:152・966:150・966:187。描画部分でトリミングした WebP。
 * AI 生成の差し替え前提の仮画像）。並びは exterior_exone_thanks_versions() の steps と同じ。
 *
 * size は画像の実寸（PC 表示の 2 倍）。表示寸法は css/thanks.css のトークン。
 *
 * @return array<int, array{file: string, size: int[], key: string}>
 */
function exterior_exone_thanks_step_icons() {
	return array(
		array(
			'key'  => 'mail',
			'file' => 'nextstep-mail-966-152.webp',
			'size' => array( 276, 199 ),
		),
		array(
			'key'  => 'operator',
			'file' => 'nextstep-operator-966-150.webp',
			'size' => array( 198, 233 ),
		),
		array(
			'key'  => 'house',
			'file' => 'nextstep-house-966-187.webp',
			'size' => array( 384, 198 ),
		),
	);
}

/**
 * Explore EXone の 3 枚（966:164・966:177・966:172 / 966:229・966:233・966:243）。
 *
 * 写真は各ページの既存画像を流用（決定事項 §3）。DX はカンプのレイヤー名が video でも静止画。
 * リンクは支店モードなら ?store=<slug> が付く。
 *
 * @return array<int, array<string, mixed>>
 */
function exterior_exone_thanks_explore() {
	return array(
		array(
			'key'   => 'works',
			'en'    => 'OUR WORKS',
			'ja'    => '理想の暮らしを形にした事例を見る',
			'url'   => exterior_exone_store_link( home_url( '/works/' ) ),
			'image' => 'images/works/pc-fv-bg-821-116.jpg',
			'img'   => array( 1500, 1049 ),
			'scrim' => false,
		),
		array(
			'key'   => 'plans',
			'en'    => 'OUR PLANS',
			'ja'    => 'あなたに合った外構プランを探す',
			'url'   => exterior_exone_store_link( home_url( '/plans/' ) ),
			'image' => 'images/plans/pc-fv-bg-718-242.jpg',
			'img'   => array( 1920, 1080 ),
			'scrim' => false,
		),
		array(
			'key'   => 'dx',
			'en'    => 'DX EXPERIENCE',
			'ja'    => 'EXoneの新しい外構体験を知る',
			'url'   => exterior_exone_store_link( home_url( '/dx/' ) ),
			'image' => 'images/top/dx_video-poster.jpg',
			'img'   => array( 1280, 720 ),
			'scrim' => true, // 966:183 / 966:244（DX だけ 10% の暗幕）
		),
	);
}

/**
 * テーマ内の画像 URL を返す（同じ階層の webp/ に同名の .webp があれば優先）。
 *
 * @param string $relative_path テーマルートからの相対パス（images/<dir>/<file>）。
 * @return string バージョンクエリ付き URL。
 */
function exterior_exone_thanks_image( $relative_path ) {
	$webp = dirname( $relative_path ) . '/webp/' . preg_replace( '/\.(jpe?g|png)$/i', '.webp', basename( $relative_path ) );

	if ( substr( $relative_path, -5 ) !== '.webp' && file_exists( get_theme_file_path( $webp ) ) ) {
		return exterior_exone_asset_url( $webp );
	}

	return exterior_exone_asset_url( $relative_path );
}

/**
 * 行の配列を <br> と <strong> つきの HTML にする（exterior_exone_highend_lines() の太字対応版）。
 *
 * 各行は array( 文言, 改行, 太字 )。改行 = both / pc / sp / none。
 *
 * @param array $lines 行の配列。
 * @return string wp_kses 済みの HTML。
 */
function exterior_exone_thanks_lines( $lines ) {
	$brs  = array(
		'both' => '<br>',
		'pc'   => '<br class="u-br-pc">',
		'sp'   => '<br class="u-br-sp">',
	);
	$html = '';

	foreach ( (array) $lines as $line ) {
		$text = esc_html( (string) $line[0] );
		$br   = isset( $line[1] ) ? $line[1] : 'none';

		$html .= ! empty( $line[2] ) ? '<strong>' . $text . '</strong>' : $text;

		if ( isset( $brs[ $br ] ) ) {
			$html .= $brs[ $br ];
		}
	}

	return wp_kses(
		$html,
		array(
			'br'     => array( 'class' => array() ),
			'strong' => array(),
		)
	);
}
