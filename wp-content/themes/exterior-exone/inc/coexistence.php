<?php
/**
 * 新テーマだけの読み取りアダプター。保存値・rewrite・旧データは変更しない。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function exterior_exone_coexistence_enabled() {
	return defined( 'EXR_COEXISTENCE' ) && true === EXR_COEXISTENCE;
}

/** 元ローカルでは従来の取得、共存時だけ専用領域を取得する。 */
function exterior_exone_content_posts( $args ) {
	return get_posts( exterior_exone_coexistence_query_args( $args ) );
}

function exterior_exone_content_archive_url( $type ) {
	if ( exterior_exone_coexistence_enabled() && in_array( $type, array( 'works', 'news', 'column' ), true ) ) {
		return home_url( '/' . $type . '/' );
	}
	return (string) get_post_type_archive_link( $type );
}

function exterior_exone_content_taxonomy( $taxonomy ) {
	return exterior_exone_coexistence_enabled() && in_array( $taxonomy, array( 'works_category', 'works_package', 'works_tag' ), true ) ? 'exr_' . $taxonomy : $taxonomy;
}

function exterior_exone_content_is_front_page() {
	$route = exterior_exone_coexistence_route();
	return exterior_exone_coexistence_enabled() && $route ? 'home' === $route['kind'] : is_front_page();
}

function exterior_exone_coexistence_route() {
	return isset( $GLOBALS['exterior_exone_coexistence_route'] ) ? $GLOBALS['exterior_exone_coexistence_route'] : null;
}

/** 追加の絞り込みは AND で結合し、呼び出し側から公開条件を緩められない。 */
function exterior_exone_coexistence_query_args( $args ) {
	if ( ! exterior_exone_coexistence_enabled() ) {
		return $args;
	}
	$type = isset( $args['post_type'] ) ? $args['post_type'] : '';
	if ( in_array( $type, array( 'page', 'works', 'news' ), true ) ) {
		$args['post_type'] = 'exr_' . $type;
	}
	$args['post_status'] = 'exr_stored';
	$args['suppress_filters'] = false;
	$args['_exr_content_query'] = true;
	$required = array(
		'relation' => 'AND',
		array( 'key' => '_exr_dataset', 'value' => 'exterior-exone-renewal:v1' ),
		array( 'key' => '_exr_source_status', 'value' => 'publish' ),
		array( 'key' => '_exr_import_state', 'value' => 'complete' ),
	);
	if ( ! empty( $args['meta_query'] ) ) {
		$required[] = $args['meta_query'];
	}
	$args['meta_query'] = $required;
	$date = array( 'before' => current_time( 'mysql' ), 'inclusive' => true );
	$args['date_query'] = empty( $args['date_query'] ) ? array( $date ) : array( 'relation' => 'AND', $args['date_query'], $date );
	return $args;
}

/** 単独 ID の解決にも SQL と同じ条件を適用する。 */
function exterior_exone_coexistence_is_public_record( $post ) {
	$post = get_post( $post );
	return $post instanceof WP_Post
		&& 'exr_stored' === $post->post_status
		&& in_array( $post->post_type, array( 'exr_page', 'exr_works', 'exr_news', 'exr_form' ), true )
		&& 'exterior-exone-renewal:v1' === get_post_meta( $post->ID, '_exr_dataset', true )
		&& 'publish' === get_post_meta( $post->ID, '_exr_source_status', true )
		&& 'complete' === get_post_meta( $post->ID, '_exr_import_state', true )
		&& $post->post_date <= current_time( 'mysql' );
}

function exterior_exone_coexistence_register_storage() {
	register_post_status( 'exr_stored', array( 'label' => '新テーマ専用', 'public' => false, 'internal' => true, 'protected' => true, 'exclude_from_search' => true, 'show_in_admin_all_list' => false, 'show_in_admin_status_list' => false ) );
	foreach ( array( 'exr_page', 'exr_works', 'exr_news', 'exr_form' ) as $type ) {
		register_post_type( $type, array( 'label' => $type, 'public' => false, 'publicly_queryable' => false, 'exclude_from_search' => true, 'show_ui' => false, 'show_in_rest' => false, 'rewrite' => false, 'query_var' => false, 'has_archive' => false, 'supports' => array(), 'hierarchical' => 'exr_page' === $type ) );
	}
	foreach ( array( 'exr_works_category', 'exr_works_package', 'exr_works_tag' ) as $taxonomy ) {
		register_taxonomy( $taxonomy, 'exr_works', array( 'public' => false, 'publicly_queryable' => false, 'show_ui' => false, 'show_in_rest' => false, 'rewrite' => false, 'query_var' => false, 'hierarchical' => 'exr_works_tag' !== $taxonomy ) );
	}
}

/** 保存元の完全パス。枝の同名 slug は混同しない。 */
function exterior_exone_coexistence_page( $path ) {
	$posts = exterior_exone_content_posts( array( 'post_type' => 'page', 'posts_per_page' => 2, 'meta_query' => array( array( 'key' => '_exr_source_path', 'value' => $path ) ) ) );
	return 1 === count( $posts ) ? $posts[0] : null;
}

function exterior_exone_coexistence_form( $role ) {
	if ( ! in_array( $role, array( 'personal', 'corporate', 'entry' ), true ) || ! class_exists( 'WPCF7_ContactForm' ) ) {
		return null;
	}
	$posts = exterior_exone_content_posts( array( 'post_type' => 'exr_form', 'posts_per_page' => 2, 'meta_query' => array( array( 'key' => '_exr_form_role', 'value' => $role ), array( 'key' => '_exr_source_key', 'compare' => 'EXISTS' ) ) ) );
	return 1 === count( $posts ) ? exterior_exone_coexistence_form_instance( $posts[0]->ID ) : null;
}

function exterior_exone_coexistence_form_role( $id ) {
	if ( ! exterior_exone_coexistence_is_public_record( $id ) || 'exr_form' !== get_post_type( $id ) || ! get_post_meta( $id, '_exr_source_key', true ) ) {
		return '';
	}
	$role = get_post_meta( $id, '_exr_form_role', true );
	return in_array( $role, array( 'personal', 'corporate', 'entry' ), true ) ? $role : '';
}

/** CF7 の通常 ID 検索からは読めない。複製だけを渡し、DB/cache の投稿タイプは変えない。 */
function exterior_exone_coexistence_form_instance( $id ) {
	$role = exterior_exone_coexistence_form_role( $id );
	if ( ! $role || ! class_exists( 'WPCF7_ContactForm' ) ) {
		return null;
	}
	$candidates = exterior_exone_content_posts( array( 'post_type' => 'exr_form', 'posts_per_page' => 2, 'meta_query' => array( array( 'key' => '_exr_form_role', 'value' => $role ), array( 'key' => '_exr_source_key', 'compare' => 'EXISTS' ) ) ) );
	if ( 1 !== count( $candidates ) || (int) $candidates[0]->ID !== (int) $id ) {
		return null;
	}
	$post = clone get_post( $id );
	$post->post_type = WPCF7_ContactForm::post_type;
	return WPCF7_ContactForm::get_instance( $post );
}

function exterior_exone_coexistence_form_html( $form, $class ) {
	$atts = array( 'html_class' => $class );
	$html = wpcf7_switch_locale( $form->locale(), static function ( $item, $options ) { return $item->form_html( $options ); }, $form, $atts );
	do_action( 'wpcf7_shortcode_callback', $form, $atts );
	return $html;
}

function exterior_exone_coexistence_form_shortcode( $output, $tag, $atts ) {
	if ( false !== $output || 'contact-form-7' !== $tag || empty( $atts['id'] ) || ! is_scalar( $atts['id'] ) || ! preg_match( '/^[1-9][0-9]*$/D', (string) $atts['id'] ) || 'exr_form' !== get_post_type( (int) $atts['id'] ) ) {
		return $output;
	}
	$form = exterior_exone_coexistence_form_instance( (int) $atts['id'] );
	return $form ? exterior_exone_coexistence_form_html( $form, isset( $atts['html_class'] ) ? (string) $atts['html_class'] : '' ) : '';
}

/** CF7 6.1 の公開応答形式を保ち、専用型はこのテーマの実行中だけ処理する。 */
function exterior_exone_coexistence_form_dispatch( $result, $request ) {
	if ( null !== $result || ! preg_match( '~^/contact-form-7/v1/contact-forms/([1-9][0-9]*)/(feedback(?:/schema)?|refill)$~D', $request->get_route(), $matches ) ) {
		return $result;
	}
	$id = (int) $matches[1];
	if ( 'exr_form' !== get_post_type( $id ) ) {
		return $result;
	}
	$form = exterior_exone_coexistence_form_instance( $id );
	if ( ! $form ) {
		return new WP_Error( 'wpcf7_not_found', 'フォームが見つかりません。', array( 'status' => 404 ) );
	}
	if ( 'feedback/schema' === $matches[2] ) {
		$schema = $form->get_schema();
		return rest_ensure_response( $schema ? $schema->to_array() : array() );
	}
	if ( 'refill' === $matches[2] ) {
		return rest_ensure_response( apply_filters( 'wpcf7_refill_response', array() ) );
	}
	if ( 0 !== strpos( (string) $request->get_header( 'Content-Type' ), 'multipart/form-data' ) ) {
		return new WP_Error( 'wpcf7_unsupported_media_type', '送信形式が正しくありません。', array( 'status' => 415 ) );
	}
	$unit = wpcf7_sanitize_unit_tag( $request->get_param( '_wpcf7_unit_tag' ) );
	if ( ! $unit || 0 !== strpos( $unit, 'wpcf7-f' . $id . '-' ) || $id !== (int) $request->get_param( '_wpcf7' ) ) {
		return new WP_Error( 'wpcf7_unit_tag_not_found', 'フォームの識別子が正しくありません。', array( 'status' => 400 ) );
	}
	$submitted = $form->submit();
	$response = array_merge( $submitted, array( 'into' => '#' . $unit, 'invalid_fields' => array() ) );
	foreach ( isset( $submitted['invalid_fields'] ) ? (array) $submitted['invalid_fields'] : array() as $name => $field ) {
		if ( wpcf7_is_name( $name ) ) {
			$name = strtr( $name, '.', '_' );
			$response['invalid_fields'][] = array( 'field' => $name, 'message' => $field['reason'], 'idref' => $field['idref'], 'error_id' => $unit . '-ve-' . $name );
		}
	}
	$response = wpcf7_apply_filters_deprecated( 'wpcf7_ajax_json_echo', array( $response, $submitted ), '5.2', 'wpcf7_feedback_response' );
	return rest_ensure_response( apply_filters( 'wpcf7_feedback_response', $response, $submitted ) );
}

/** 本番に存在する支店 URL を正規 URL とし、元パスも新テーマ内で受け付ける。 */
function exterior_exone_coexistence_aliases() {
	return array(
		'/store/aomori/' => '/store/store-aomori/',
		'/store/hirosaki/' => '/store/store-hirosaki/',
		'/store/hachinohe/' => '/store/store-hachinohe/',
		'/store/morioka/' => '/store/store-morioka/',
		'/store/totsuka/' => '/store/store-totsuka/',
	);
}

function exterior_exone_coexistence_public_path( $path ) {
	$aliases = exterior_exone_coexistence_aliases();
	return isset( $aliases[ $path ] ) ? $aliases[ $path ] : $path;
}

/** 曖昧なパスは正規化せず拒否する。クエリ文字列をルートへ混ぜない。 */
function exterior_exone_coexistence_normalize_path( $request ) {
	if ( ! is_string( $request ) || preg_match( '/[\x00-\x20\\\\?#]/', $request ) ) {
		return false;
	}
	$decoded = rawurldecode( $request );
	if ( preg_match( '/[\x00-\x20\\\\?#]/', $decoded ) || false !== strpos( $decoded, '//' ) || preg_match( '~(?:^|/)\.{1,2}(?:/|$)~', $decoded ) || preg_match( '/%[0-9a-f]{2}/i', $decoded ) ) {
		return false;
	}
	return '/' . trim( $decoded, '/' ) . ( '' === trim( $decoded, '/' ) ? '' : '/' );
}

function exterior_exone_coexistence_is_column_request( $query = null ) {
	global $wp;
	$path = isset( $wp->request ) ? trim( $wp->request, '/' ) : '';
	return 'column' === $path || 0 === strpos( $path, 'column/' )
		|| ( $query && ( 'column' === $query->get( 'post_type' ) || $query->is_tax( 'column_category' ) ) );
}

/** リクエスト中だけ振り分ける。公開 query var や rewrite option は追加しない。 */
function exterior_exone_coexistence_parse_request( $wp ) {
	$GLOBALS['exterior_exone_coexistence_route'] = null;
	$GLOBALS['exterior_exone_coexistence_sitemap_request'] = false;
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	$path = exterior_exone_coexistence_normalize_path( $wp->request );
	// ID / preview / 内部投稿タイプの指定は公開パスを迂回させない。
	$selectors = array( 'p', 'page_id', 'attachment_id', 'preview', 'post_type', 'pagename', 'name', 'attachment', 'subpost', 'subpost_id' );
	// WordPress は GET だけでなく POST の公開 query var も受理する。
	$direct = array_values( array_intersect( $selectors, array_unique( array_merge( array_keys( $_GET ), array_keys( $_POST ) ) ) ) );
	$service = ! empty( $wp->query_vars['rest_route'] ) || ! empty( $wp->query_vars['sitemap'] ) || ! empty( $wp->query_vars['sitemap-stylesheet'] ) || ! empty( $wp->query_vars['xml_sitemap'] ) || ! empty( $wp->query_vars['robots'] );
	if ( $service && ! $direct && ! array_intersect( $selectors, array_keys( $wp->query_vars ) ) ) {
		$valid = true;
		foreach ( array( 'rest_route', 'sitemap', 'sitemap-stylesheet', 'xml_sitemap', 'robots' ) as $key ) {
			if ( isset( $wp->query_vars[ $key ] ) && ! is_scalar( $wp->query_vars[ $key ] ) ) {
				$valid = false;
			}
		}
		if ( $valid ) {
			$GLOBALS['exterior_exone_coexistence_sitemap_request'] = ! empty( $wp->query_vars['sitemap'] ) || ! empty( $wp->query_vars['sitemap-stylesheet'] ) || ! empty( $wp->query_vars['xml_sitemap'] );
			return;
		}
	}
	if ( $service && ! $direct ) {
		$direct = array( 'service-selector-conflict' );
	}
	if ( $direct ) {
		$id = isset( $wp->query_vars['p'] ) ? (int) $wp->query_vars['p'] : 0;
		// WP_Query は page_id / attachment_id などで p を置換するため、混在を許可しない。
		$conflicting = array_intersect( array( 'page_id', 'attachment_id', 'preview', 'pagename', 'name', 'attachment', 'subpost', 'subpost_id' ), array_keys( $wp->query_vars ) );
		$column_id_only = ( array( 'p' ) === $direct || array( 'p', 'post_type' ) === $direct ) && '/' === $path && ! $conflicting && $id && 'column' === get_post_type( $id ) && in_array( $wp->query_vars['post_type'] ?? '', array( '', 'column' ), true );
		$column_type_only = array( 'post_type' ) === $direct && ! $conflicting && empty( $wp->query_vars['p'] ) && 'column' === ( $wp->query_vars['post_type'] ?? '' );
		if ( ! $service && ( $column_id_only || $column_type_only ) ) {
			return;
		}
	} elseif ( exterior_exone_coexistence_is_column_request() || isset( $wp->query_vars['s'] ) || ! empty( $wp->query_vars['feed'] ) || 'column' === ( $wp->query_vars['post_type'] ?? '' ) || isset( $wp->query_vars['column_category'] ) ) {
		return;
	}
	$route = array( 'kind' => 'missing', 'path' => $path, 'canonical' => $path, 'id' => 0, 'paged' => 1 );
	if ( $direct ) {
		// 下の正常パス解決へ進ませない。
	} elseif ( '/' === $path ) {
		$route['kind'] = 'home';
	} elseif ( '/works/' === $path ) {
		$route['kind'] = 'works-list';
	} elseif ( '/news/' === $path || ( $path && preg_match( '~^/news/page/([1-9][0-9]*)/$~D', $path, $matches ) ) ) {
		$route['kind'] = 'news-list';
		$route['paged'] = isset( $matches[1] ) ? (int) $matches[1] : 1;
	} elseif ( $path && preg_match( '~^/(works|news)/([^/]+)/$~D', $path, $matches ) ) {
		$posts = exterior_exone_content_posts( array( 'post_type' => $matches[1], 'name' => sanitize_title( $matches[2] ), 'posts_per_page' => 2 ) );
		if ( 1 === count( $posts ) ) {
			$route['kind'] = 'works' === $matches[1] ? 'works-single' : 'news-redirect';
			$route['id'] = (int) $posts[0]->ID;
			$route['canonical'] = 'news' === $matches[1] ? '/news/' : $path;
		}
	} elseif ( $path ) {
		$source = array_search( $path, exterior_exone_coexistence_aliases(), true );
		$page = exterior_exone_coexistence_page( false === $source ? $path : $source );
		if ( $page ) {
			$route['kind'] = 'page';
			$route['id'] = (int) $page->ID;
			$route['canonical'] = exterior_exone_coexistence_public_path( false === $source ? $path : $source );
		}
	}
	$GLOBALS['exterior_exone_coexistence_route'] = $route;
	$type = 'page' === $route['kind'] ? 'exr_page' : ( 0 === strpos( $route['kind'], 'works' ) ? 'exr_works' : 'exr_news' );
	$wp->query_vars = array( 'post_type' => $type, 'post_status' => 'exr_stored', 'paged' => $route['paged'] );
	if ( $route['id'] ) {
		$wp->query_vars['p'] = $route['id'];
	}
}

/** 正常な sitemap handler は出力後に終了する。未処理の値を HTML へ流さない。 */
function exterior_exone_coexistence_sitemap_fallback() {
	if ( empty( $GLOBALS['exterior_exone_coexistence_sitemap_request'] ) ) {
		return;
	}
	global $wp_query, $post;
	$wp_query->set_404();
	// XML Sitemap は処理途中に is_feed を立てるため、失敗時の RSS fallback も止める。
	$wp_query->is_feed = false;
	$wp_query->posts = array();
	$wp_query->post_count = 0;
	$wp_query->post = null;
	$wp_query->queried_object = null;
	$wp_query->queried_object_id = 0;
	$post = null;
	status_header( 404 );
	nocache_headers();
}

function exterior_exone_coexistence_main_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	$route = exterior_exone_coexistence_route();
	if ( ! $route ) {
		if ( $query->is_search() ) {
			$query->set( 'post_type', array( 'column', 'exr_page', 'exr_works', 'exr_news' ) );
			$query->set( 'post_status', array( 'publish', 'exr_stored' ) );
			$query->set( '_exr_search', true );
		} elseif ( $query->is_feed() && ! exterior_exone_coexistence_is_column_request( $query ) ) {
			$query->set( 'post_type', 'column' );
			$query->set( 'post_status', 'publish' );
		}
		return;
	}
	$args = exterior_exone_coexistence_query_args( $query->query_vars );
	$args['posts_per_page'] = 'news-list' === $route['kind'] ? 10 : -1;
	$args['orderby'] = 'works-list' === $route['kind'] ? exterior_exone_proposal_orderby() : 'date';
	$args['order'] = 'DESC';
	if ( in_array( $route['kind'], array( 'home', 'missing' ), true ) ) {
		$args['post__in'] = array( 0 );
	}
	if ( 'news-list' === $route['kind'] && function_exists( 'exterior_exone_news_current_ym' ) ) {
		$ym = exterior_exone_news_current_ym();
		if ( $ym ) {
			list( $year, $month ) = array_map( 'intval', explode( '-', $ym ) );
			$args['date_query'][] = array( 'year' => $year, 'month' => $month );
		}
	}
	foreach ( $args as $key => $value ) {
		$query->set( $key, $value );
	}
	$query->is_home = 'home' === $route['kind'];
	$query->is_page = 'page' === $route['kind'];
	$query->is_single = in_array( $route['kind'], array( 'works-single', 'news-redirect' ), true );
	$query->is_singular = $query->is_page || $query->is_single;
	$query->is_archive = in_array( $route['kind'], array( 'works-list', 'news-list' ), true );
	$query->is_post_type_archive = $query->is_archive;
	$query->is_attachment = false;
	$query->is_404 = 'missing' === $route['kind'];
	$query->is_paged = $route['paged'] > 1;
}

/** 検索の共有コラムは publish、新用は全公開条件を満たす場合だけ。 */
function exterior_exone_coexistence_search_where( $where, $query ) {
	if ( ! $query->get( '_exr_search' ) ) {
		return $where;
	}
	global $wpdb;
	$conditions = '';
	foreach ( array( '_exr_dataset' => 'exterior-exone-renewal:v1', '_exr_source_status' => 'publish', '_exr_import_state' => 'complete' ) as $key => $value ) {
		$conditions .= $wpdb->prepare( " AND EXISTS (SELECT 1 FROM {$wpdb->postmeta} exr_meta WHERE exr_meta.post_id = {$wpdb->posts}.ID AND exr_meta.meta_key = %s AND exr_meta.meta_value = %s)", $key, $value );
	}
	return $where . $wpdb->prepare( " AND (({$wpdb->posts}.post_type = 'column' AND {$wpdb->posts}.post_status = 'publish') OR ({$wpdb->posts}.post_type IN ('exr_page','exr_works','exr_news') AND {$wpdb->posts}.post_status = 'exr_stored' AND {$wpdb->posts}.post_date <= %s {$conditions}))", current_time( 'mysql' ) );
}

/** 表示用コピーだけを既存テンプレートに渡す。WP のキャッシュ内オブジェクトは変えない。 */
function exterior_exone_coexistence_display_posts( $posts, $query ) {
	if ( ! $query->get( '_exr_content_query' ) && ! $query->get( '_exr_search' ) ) {
		return $posts;
	}
	foreach ( $posts as $key => $post ) {
		if ( $post instanceof WP_Post && in_array( $post->post_type, array( 'exr_page', 'exr_works', 'exr_news' ), true ) ) {
			$copy = clone $post;
			$copy->post_type = substr( $post->post_type, 4 );
			$copy->post_status = 'publish';
			$posts[ $key ] = $copy;
		}
	}
	$route = exterior_exone_coexistence_route();
	if ( $query->is_main_query() && $route ) {
		if ( $query->is_archive ) {
			$type = 'works-list' === $route['kind'] ? 'works' : 'news';
			$object = get_post_type_object( 'exr_' . $type );
			if ( $object ) {
				$query->queried_object = clone $object;
				$query->queried_object->name = $type;
				$query->queried_object->label = 'works' === $type ? '提案事例' : 'お知らせ';
				if ( isset( $object->labels ) ) {
					$query->queried_object->labels = clone $object->labels;
					$query->queried_object->labels->name = $query->queried_object->label;
				}
			}
			$query->set( 'post_type', $type );
		} elseif ( $posts && $query->is_singular ) {
			$query->queried_object = $posts[0];
			$query->queried_object_id = (int) $posts[0]->ID;
		}
	}
	return $posts;
}

function exterior_exone_coexistence_handle_404( $handled, $query ) {
	$route = exterior_exone_coexistence_route();
	if ( ! $route ) {
		return $handled;
	}
	$missing = 'missing' === $route['kind'] || ( $query->is_singular && ! $query->posts ) || ( $route['paged'] > 1 && $route['paged'] > (int) $query->max_num_pages );
	if ( $missing ) {
		$query->set_404();
		status_header( 404 );
		nocache_headers();
	} else {
		$query->is_404 = false;
		status_header( 200 );
	}
	return true;
}

function exterior_exone_coexistence_template( $template ) {
	$route = exterior_exone_coexistence_route();
	if ( ! $route || is_404() ) {
		return $template;
	}
	$files = array( 'home' => 'front-page.php', 'works-list' => 'archive-works.php', 'works-single' => 'single-works.php', 'news-list' => 'archive-news.php' );
	if ( 'page' === $route['kind'] ) {
		$path = get_post_meta( $route['id'], '_exr_source_path', true );
		$pages = array( '/company/' => 'page-company.php', '/contact/' => 'page-contact.php', '/contact/thanks/' => 'page-thanks.php', '/recruit/thanks/' => 'page-thanks.php', '/recruit/' => 'page-recruit.php', '/recruit/entry/' => 'page-recruit-entry.php', '/store/' => 'page-store-index.php', '/dx/' => 'page-dx.php', '/dx/vr/' => 'page-vr.php', '/plans/' => 'page-plans.php', '/plans/high-end/' => 'page-high-end.php' );
		$file = isset( $pages[ $path ] ) ? $pages[ $path ] : ( preg_match( '~^/store/[^/]+/$~D', $path ) ? 'page-store.php' : 'index.php' );
	} else {
		$file = isset( $files[ $route['kind'] ] ) ? $files[ $route['kind'] ] : 'index.php';
	}
	$located = locate_template( $file );
	return $located ? $located : $template;
}

function exterior_exone_coexistence_permalink( $url, $post ) {
	$post = get_post( $post );
	if ( ! $post || 'exterior-exone-renewal:v1' !== get_post_meta( $post->ID, '_exr_dataset', true ) ) {
		return $url;
	}
	$path = get_post_meta( $post->ID, '_exr_source_path', true );
	if ( $path ) {
		return home_url( exterior_exone_coexistence_public_path( $path ) );
	}
	if ( in_array( $post->post_type, array( 'works', 'exr_works' ), true ) ) {
		return home_url( '/works/' . $post->post_name . '/' );
	}
	return in_array( $post->post_type, array( 'news', 'exr_news' ), true ) ? home_url( '/news/' ) : $url;
}

function exterior_exone_coexistence_page_link( $url, $id ) {
	return exterior_exone_coexistence_permalink( $url, get_post( $id ) );
}

function exterior_exone_coexistence_redirect() {
	$route = exterior_exone_coexistence_route();
	if ( ! $route || is_404() ) {
		return;
	}
	remove_action( 'wp_head', 'rel_canonical' );
	if ( 'news-redirect' === $route['kind'] || $route['path'] !== $route['canonical'] ) {
		wp_safe_redirect( exterior_exone_store_link( home_url( $route['canonical'] ) ), 301 );
		exit;
	}
}

function exterior_exone_coexistence_canonical_redirect( $url ) {
	return exterior_exone_coexistence_route() || ! empty( $GLOBALS['exterior_exone_coexistence_sitemap_request'] ) ? false : $url;
}

function exterior_exone_coexistence_canonical() {
	$route = exterior_exone_coexistence_route();
	if ( $route && ! is_404() ) {
		echo '<link rel="canonical" href="' . esc_url( home_url( $route['canonical'] ) ) . '" />' . "\n";
	}
}

function exterior_exone_coexistence_home_title( $parts ) {
	$route = exterior_exone_coexistence_route();
	return $route && 'home' === $route['kind'] ? array( 'title' => get_bloginfo( 'name' ), 'tagline' => get_bloginfo( 'description' ) ) : $parts;
}

function exterior_exone_coexistence_missing_notice() {
	if ( current_user_can( 'manage_options' ) && ! exterior_exone_content_posts( array( 'post_type' => 'page', 'posts_per_page' => 1, 'fields' => 'ids' ) ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html( '新テーマ用の取り込み済みデータが見つかりません。旧データへの自動切り替えは行いません。' ) . '</p></div>';
	}
}

/** 新しい添付は匿名 REST へ出さず、権限のある管理操作は core の検査へ渡す。 */
function exterior_exone_coexistence_rest_boundary( $result, $server, $request ) {
	$route = $request->get_route();
	if ( preg_match( '~^/wp/v2/(?:exr_[^/]+|media/(\d+))(?:/|$)~', $route, $matches ) ) {
		if ( empty( $matches[1] ) || 'exterior-exone-renewal:v1' === get_post_meta( (int) $matches[1], '_exr_dataset', true ) ) {
			if ( ! empty( $matches[1] ) && is_user_logged_in() ) {
				$id = (int) $matches[1];
				$read = in_array( $request->get_method(), array( 'GET', 'HEAD' ), true );
				if ( current_user_can( 'read_post', $id ) && ( $read || current_user_can( 'edit_post', $id ) ) ) {
					// 認証・nonce・編集/削除などの通常の permission_callback は省略しない。
					return $result;
				}
			}
			return new WP_Error( 'rest_no_route', 'この項目は公開 API の対象ではありません。', array( 'status' => 404 ) );
		}
	}
	return $result;
}

/** 新テーマの公開パスだけをサイトマップへ渡す。サンクス・下書きは含めない。 */
function exterior_exone_coexistence_sitemap_entries() {
	$entries = array();
	foreach ( array( '/', '/works/', '/news/', '/column/' ) as $path ) {
		$entries[ home_url( $path ) ] = array( 'loc' => home_url( $path ) );
	}
	foreach ( array( 'page', 'works' ) as $type ) {
		foreach ( exterior_exone_content_posts( array( 'post_type' => $type, 'posts_per_page' => -1 ) ) as $post ) {
			$path = get_post_meta( $post->ID, '_exr_source_path', true );
			if ( in_array( $path, array( '/contact/thanks/', '/recruit/thanks/' ), true ) ) {
				continue;
			}
			$url = exterior_exone_coexistence_permalink( '', $post );
			if ( $url ) {
				$entries[ $url ] = array( 'loc' => $url );
			}
		}
	}
	return array_values( $entries );
}

function exterior_exone_coexistence_sitemap_types( $types ) {
	foreach ( array_keys( $types ) as $type ) {
		if ( 'column' !== $type ) {
			unset( $types[ $type ] );
		}
	}
	return $types;
}

function exterior_exone_coexistence_sitemap_taxonomies( $taxonomies ) {
	return isset( $taxonomies['column_category'] ) ? array( 'column_category' => $taxonomies['column_category'] ) : array();
}

function exterior_exone_coexistence_sitemap_provider( $server ) {
	$server->registry->add_provider( 'exone', new class extends WP_Sitemaps_Provider {
		public function __construct() {
			$this->name = 'exone';
			$this->object_type = 'exone';
		}
		public function get_url_list( $page_num, $object_subtype = '' ) {
			return array_slice( exterior_exone_coexistence_sitemap_entries(), ( max( 1, (int) $page_num ) - 1 ) * 1000, 1000 );
		}
		public function get_max_num_pages( $object_subtype = '' ) {
			return (int) ceil( count( exterior_exone_coexistence_sitemap_entries() ) / 1000 );
		}
	} );
}

/** Author archives are not public routes in the renewal site. */
function exterior_exone_coexistence_sitemap_provider_boundary( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}

/** XML Sitemap の共有キャッシュに投稿タイプ選択を保存させず、各 URL の出力を選ぶ。 */
function exterior_exone_coexistence_xml_post_url( $url, $post ) {
	return 'column' === $post->post_type ? $url : '';
}

function exterior_exone_coexistence_xml_entries() {
	if ( ! class_exists( 'GoogleSitemapGenerator' ) ) {
		return;
	}
	$generator = GoogleSitemapGenerator::get_instance();
	foreach ( exterior_exone_coexistence_sitemap_entries() as $entry ) {
		// TOP はプラグイン自身の既存 misc エントリーを使う。
		if ( home_url( '/' ) !== $entry['loc'] ) {
			$generator->add_url( $entry['loc'] );
		}
	}
}

function exterior_exone_coexistence_xml_taxonomies( $excluded ) {
	foreach ( get_taxonomies() as $taxonomy ) {
		if ( 'column_category' !== $taxonomy ) {
			$excluded[] = $taxonomy;
		}
	}
	return array_values( array_unique( $excluded ) );
}

if ( exterior_exone_coexistence_enabled() ) {
	add_action( 'init', 'exterior_exone_coexistence_register_storage', 5 );
	add_action( 'parse_request', 'exterior_exone_coexistence_parse_request', 5 );
	add_action( 'pre_get_posts', 'exterior_exone_coexistence_main_query', 90 );
	add_filter( 'posts_where', 'exterior_exone_coexistence_search_where', 10, 2 );
	add_filter( 'the_posts', 'exterior_exone_coexistence_display_posts', 90, 2 );
	add_filter( 'pre_handle_404', 'exterior_exone_coexistence_handle_404', 90, 2 );
	add_filter( 'template_include', 'exterior_exone_coexistence_template', 90 );
	add_filter( 'post_type_link', 'exterior_exone_coexistence_permalink', 10, 2 );
	add_filter( 'page_link', 'exterior_exone_coexistence_page_link', 10, 2 );
	add_action( 'template_redirect', 'exterior_exone_coexistence_redirect', 1 );
	add_action( 'template_redirect', 'exterior_exone_coexistence_sitemap_fallback', PHP_INT_MAX );
	add_filter( 'redirect_canonical', 'exterior_exone_coexistence_canonical_redirect' );
	add_action( 'wp_head', 'exterior_exone_coexistence_canonical', 1 );
	add_filter( 'document_title_parts', 'exterior_exone_coexistence_home_title', 90 );
	add_action( 'admin_notices', 'exterior_exone_coexistence_missing_notice' );
	add_filter( 'rest_pre_dispatch', 'exterior_exone_coexistence_rest_boundary', 10, 3 );
	add_filter( 'rest_dispatch_request', 'exterior_exone_coexistence_form_dispatch', 10, 2 );
	add_filter( 'pre_do_shortcode_tag', 'exterior_exone_coexistence_form_shortcode', 10, 3 );
	add_filter( 'wp_sitemaps_post_types', 'exterior_exone_coexistence_sitemap_types', 90 );
	add_filter( 'wp_sitemaps_taxonomies', 'exterior_exone_coexistence_sitemap_taxonomies', 90 );
	add_action( 'wp_sitemaps_init', 'exterior_exone_coexistence_sitemap_provider' );
	add_filter( 'wp_sitemaps_add_provider', 'exterior_exone_coexistence_sitemap_provider_boundary', 90, 2 );
	add_filter( 'sm_xml_sitemap_post_url', 'exterior_exone_coexistence_xml_post_url', 90, 2 );
	add_action( 'sm_buildmap', 'exterior_exone_coexistence_xml_entries' );
	add_filter( 'sm_sitemap_exclude_taxonomy', 'exterior_exone_coexistence_xml_taxonomies' );
}
