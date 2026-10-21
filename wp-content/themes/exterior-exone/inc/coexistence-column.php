<?php
/** Read-only compatibility for the shared production columns. PHP 7.4. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function exterior_exone_coexistence_column_enabled() {
	return defined( 'EXR_COEXISTENCE' ) && true === EXR_COEXISTENCE;
}

function exterior_exone_coexistence_column_page() {
	$page = get_post( 3474 );
	return $page instanceof WP_Post && 'page' === $page->post_type && 'column' === $page->post_name && 'publish' === $page->post_status ? $page : null;
}

function exterior_exone_coexistence_column_is_list() {
	return exterior_exone_coexistence_column_enabled() && is_page( 3474 ) && null !== exterior_exone_coexistence_column_page();
}

function exterior_exone_coexistence_column_is_request() {
	return exterior_exone_coexistence_column_enabled() && ( exterior_exone_coexistence_column_is_list() || is_singular( 'column' ) );
}

function exterior_exone_coexistence_column_list_base_url() {
	$page = exterior_exone_coexistence_column_page();
	return $page ? (string) get_permalink( $page ) : home_url( '/column/' );
}

/** Resolve only this request. No add_rewrite_rule(), flush or option write. */
function exterior_exone_coexistence_column_parse_request( $wp ) {
	if ( ! exterior_exone_coexistence_column_enabled() || is_admin() ) {
		return;
	}
	if ( isset( $wp->query_vars['s'] ) || ! empty( $wp->query_vars['feed'] ) || ! empty( $wp->query_vars['rest_route'] ) ) {
		return;
	}
	if ( ! preg_match( '#^column(?:/page/([0-9]+))?/?$#D', (string) $wp->request, $matches ) ) {
		return;
	}
	$page = isset( $matches[1] ) ? (int) $matches[1] : 1;
	if ( ! exterior_exone_coexistence_column_page() || $page < 1 ) {
		$wp->query_vars = array( 'error' => '404' );
		return;
	}
	// cat is reserved for numeric post categories; the list query reads its slug separately.
	$wp->query_vars = array( 'page_id' => 3474, 'paged' => $page );
}

function exterior_exone_coexistence_column_list_query() {
	static $queries = array();
	$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter.
	$cat = isset( $_GET['cat'] ) && is_string( $_GET['cat'] ) ? sanitize_title( wp_unslash( $_GET['cat'] ) ) : '';
	$key = $paged . ':' . $cat;
	if ( ! isset( $queries[ $key ] ) ) {
		$args = array(
			'post_type' => 'column', 'post_status' => 'publish', 'posts_per_page' => 10,
			'paged' => $paged, 'orderby' => 'date', 'order' => 'DESC', 'ignore_sticky_posts' => true,
		);
		if ( '' !== $cat ) {
			// Unknown slugs yield an empty result, matching the existing page-column.php.
			$args['tax_query'] = array( array( 'taxonomy' => 'column_category', 'field' => 'slug', 'terms' => $cat ) );
		}
		$queries[ $key ] = new WP_Query( $args );
	}
	return $queries[ $key ];
}

function exterior_exone_coexistence_column_out_of_range() {
	if ( ! exterior_exone_coexistence_column_is_list() ) {
		return;
	}
	$paged = max( 1, (int) get_query_var( 'paged' ) );
	if ( $paged > 1 && $paged > (int) exterior_exone_coexistence_column_list_query()->max_num_pages ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}

/** Do not let core turn an explicit out-of-range 404 into a guessed list redirect. */
function exterior_exone_coexistence_column_redirect_canonical( $redirect ) {
	if ( exterior_exone_coexistence_column_enabled() && is_404() && 3474 === (int) get_query_var( 'page_id' ) && (int) get_query_var( 'paged' ) > 1 ) {
		return false;
	}
	return $redirect;
}

function exterior_exone_coexistence_column_canonical( $url, $post ) {
	if ( ! exterior_exone_coexistence_column_is_list() || 3474 !== (int) $post->ID ) {
		return $url;
	}
	$paged = max( 1, (int) get_query_var( 'paged' ) );
	return $paged > 1 ? trailingslashit( get_permalink( $post ) ) . user_trailingslashit( 'page/' . $paged, 'paged' ) : get_permalink( $post );
}

/** AFFINGER's title settings are read, never migrated into the new theme's options. */
function exterior_exone_coexistence_column_title( $parts ) {
	if ( ! exterior_exone_coexistence_column_is_request() || '' !== trim( (string) get_option( 'st-data95', '' ) ) ) {
		return $parts;
	}
	$id = get_queried_object_id();
	$custom = trim( (string) get_post_meta( $id, 'st_titlewords', true ) );
	$title = '' !== $custom ? $custom : get_the_title( $id );
	$separator = trim( (string) get_option( 'st-data219', '' ) );
	$separator = '' !== $separator ? $separator : '-';
	if ( '' === $custom && '' === trim( (string) get_option( 'st-data94', '' ) ) ) {
		$site = trim( (string) get_option( 'st-data220', '' ) );
		$title .= ' ' . $separator . ' ' . ( '' !== $site ? $site : get_bloginfo( 'name', 'display' ) );
	}
	$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	if ( $paged > 1 ) {
		$title .= ' ' . $separator . ' ' . $paged . 'ページ';
	}
	return array( 'title' => (string) get_option( 'st-data652', '' ) . $title );
}

/** Run after core's typography filters so AFFINGER's literal separators survive. */
function exterior_exone_coexistence_column_document_title( $title ) {
	if ( ! exterior_exone_coexistence_column_is_request() || '' !== trim( (string) get_option( 'st-data95', '' ) ) ) {
		return $title;
	}
	$parts = exterior_exone_coexistence_column_title( array( 'title' => $title ) );
	return esc_html( $parts['title'] );
}

/** Preserve shared article punctuation at render time; stored content stays untouched. */
function exterior_exone_coexistence_column_text_filters() {
	if ( ! exterior_exone_coexistence_column_is_request() ) {
		return;
	}
	remove_filter( 'the_title', 'wptexturize' );
	if ( is_singular( 'column' ) ) {
		remove_filter( 'the_content', 'wptexturize' );
	}
}

function exterior_exone_coexistence_column_description( $post ) {
	$custom = trim( (string) get_post_meta( $post->ID, 'st_description', true ) );
	if ( '' !== $custom ) {
		return $custom;
	}
	if ( 3474 === (int) $post->ID ) {
		return '外構・エクステリアのコラム一覧。EXoneが、お庭・カーポート・ウッドデッキ・フェンスなどの選び方や費用、メンテナンスに役立つ情報をわかりやすくお届けします。';
	}
	$raw = '' !== trim( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;
	$raw = wp_strip_all_tags( strip_shortcodes( $raw ) );
	return mb_substr( trim( (string) preg_replace( '/\s+/u', ' ', $raw ) ), 0, 120 );
}

/** Matches AFFINGER's excerpt algorithm without executing stored shortcodes or content hooks. */
function exterior_exone_coexistence_column_legacy_excerpt( $post, $length = 100 ) {
	if ( '' !== $post->post_excerpt ) {
		return wp_strip_all_tags( (string) preg_replace( '@<(noscript)[^>]*?>.*?</\\1>@siu', '', $post->post_excerpt ) );
	}
	return wp_trim_words( str_replace( ']]>', ']]&gt;', strip_shortcodes( $post->post_content ) ), $length > 0 ? $length : 100, ' ... ' );
}

function exterior_exone_coexistence_column_og_description( $post ) {
	if ( 3474 === (int) $post->ID ) {
		return exterior_exone_coexistence_column_description( $post );
	}
	$text = exterior_exone_coexistence_column_legacy_excerpt( $post, (int) get_option( 'st-data73', 100 ) );
	$text = str_replace( array( "\r", "\n" ), '', strip_tags( $text ) );
	$text = (string) preg_replace( '/\A[\0\s\p{Zs}\p{Zl}\p{Zp}]++|[\0\s\p{Zs}\p{Zl}\p{Zp}]++\z/u', '', $text );
	// The legacy output truncates the escaped string; decode before the common output escape.
	return html_entity_decode( mb_substr( esc_attr( $text ), 0, 100 ), ENT_QUOTES, 'UTF-8' );
}

function exterior_exone_coexistence_column_robots( $robots ) {
	if ( ! exterior_exone_coexistence_column_is_request() ) {
		return $robots;
	}
	$value = trim( (string) get_post_meta( get_queried_object_id(), 'stmeta_robots', true ) );
	$value = in_array( $value, array( 'index, follow', 'index, nofollow', 'noindex, follow', 'noindex, nofollow' ), true ) ? $value : 'index, follow';
	// An isolation/privacy noindex must never be reversed by shared production metadata.
	$blocked = ! empty( $robots['noindex'] ) || '0' === (string) get_option( 'blog_public', '1' );
	unset( $robots['index'], $robots['follow'], $robots['nofollow'], $robots['noindex'] );
	foreach ( explode( ', ', $value ) as $directive ) {
		$robots[ $directive ] = true;
	}
	if ( $blocked ) {
		unset( $robots['index'] );
		$robots['noindex'] = true;
	}
	return $robots;
}

/** Existing article JSON-LD is retained verbatim by the display filter. */
function exterior_exone_coexistence_column_has_schema( $content, $type ) {
	preg_match_all( '#<script\b[^>]*type\s*=\s*["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $content, $matches );
	foreach ( $matches[1] as $json ) {
		$pending = array( json_decode( $json, true, 64 ) );
		while ( $pending ) {
			$node = array_pop( $pending );
			if ( ! is_array( $node ) ) {
				continue;
			}
			if ( isset( $node['@type'] ) && in_array( $type, (array) $node['@type'], true ) ) {
				return true;
			}
			foreach ( $node as $child ) {
				if ( is_array( $child ) ) {
					$pending[] = $child;
				}
			}
		}
	}
	return false;
}

function exterior_exone_coexistence_column_head() {
	if ( ! exterior_exone_coexistence_column_is_request() ) {
		return;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	$list = exterior_exone_coexistence_column_is_list();
	$description = exterior_exone_coexistence_column_description( $post );
	$og_description = exterior_exone_coexistence_column_og_description( $post );
	$og_title = $list ? 'コラム｜外構・エクステリアのお役立ち情報｜EXone' : get_the_title( $post );
	$image = get_the_post_thumbnail_url( $post, 'full' );
	if ( ! $image ) {
		$image = get_theme_root_uri( 'affinger' ) . '/affinger/images/no-img.png';
	}
	echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	$properties = array(
		'og:type' => $list ? 'website' : 'article', 'og:title' => $og_title,
		'og:url' => get_permalink( $post ), 'og:description' => $og_description,
		'og:site_name' => get_bloginfo( 'name' ), 'og:image' => $image,
		'article:published_time' => get_the_time( DATE_ATOM, $post ),
		'article:author' => get_the_author_meta( 'nickname', $post->post_author ),
	);
	$app_id = trim( (string) get_option( 'st-data123', '' ) );
	$admins = trim( (string) get_option( 'st-data50', '' ) );
	if ( '' !== $app_id || '' !== $admins ) {
		$properties['og:locale'] = 'ja_JP';
		$properties[ '' !== $app_id ? 'fb:app_id' : 'fb:admins' ] = '' !== $app_id ? $app_id : $admins;
		$publisher = trim( (string) get_option( 'st-data51', '' ) );
		if ( '' !== $publisher ) {
			$properties['article:publisher'] = $publisher;
		}
	}
	if ( 'yes' === get_option( 'st-data140', '' ) ) {
		$properties['article:modified_time'] = get_the_modified_time( DATE_ATOM, $post );
	}
	foreach ( $properties as $name => $value ) {
		echo '<meta property="' . esc_attr( $name ) . '" content="' . esc_attr( (string) $value ) . '">' . "\n";
	}
	$twitter = trim( (string) get_option( 'st-data25', '' ) );
	$twitter_meta = array( 'card' => '' !== $twitter && 'small' === get_option( 'st-data333', '' ) ? 'summary' : 'summary_large_image', 'title' => $og_title, 'description' => $og_description, 'image' => $image );
	if ( '' !== $twitter ) {
		$twitter_meta['site'] = '@' . $twitter;
	}
	foreach ( $twitter_meta as $name => $value ) {
		echo '<meta name="twitter:' . esc_attr( $name ) . '" content="' . esc_attr( (string) $value ) . '">' . "\n";
	}
	// Thirteen existing articles already contain this graph; do not create a duplicate.
	if ( ! exterior_exone_coexistence_column_has_schema( $post->post_content, 'BreadcrumbList' ) ) {
		$items = array(
			array( '@type' => 'ListItem', 'position' => 1, 'name' => $list ? 'ホーム' : 'HOME', 'item' => home_url( '/' ) ),
			array( '@type' => 'ListItem', 'position' => 2, 'name' => 'コラム', 'item' => exterior_exone_coexistence_column_list_base_url() ),
		);
		if ( ! $list ) {
			$items[] = array( '@type' => 'ListItem', 'position' => 3, 'name' => get_the_title( $post ), 'item' => get_permalink( $post ) );
		}
		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . '</script>' . "\n";
	}
}

function exterior_exone_coexistence_column_body_class( $classes ) {
	if ( exterior_exone_coexistence_column_is_request() ) {
		$classes[] = 'exr-column-compat';
	}
	return $classes;
}

/** ImageObject shape used by the legacy theme, with the original attachment IDs. */
function exterior_exone_coexistence_column_schema_image( $id = 0, $full_thumbnail = false ) {
	$source = $id ? wp_get_attachment_image_src( $id, 'full' ) : false;
	$thumbnail = $id ? wp_get_attachment_image_src( $id, $full_thumbnail ? 'full' : 'thumbnail' ) : false;
	if ( ! $source ) {
		$path = get_theme_root( 'affinger' ) . '/affinger/images/no-img.png';
		$size = is_file( $path ) ? @getimagesize( $path ) : false;
		$source = array( get_theme_root_uri( 'affinger' ) . '/affinger/images/no-img.png', $size ? $size[0] : 300, $size ? $size[1] : 300 );
	}
	$thumbnail = $thumbnail ? $thumbnail : $source;
	$image = array( '@context' => 'https://schema.org', '@type' => 'ImageObject', 'url' => $source[0], 'contentUrl' => $source[0], 'height' => $source[2], 'width' => $source[1] );
	$thumb = array( '@context' => 'https://schema.org', '@type' => 'ImageObject', 'url' => $thumbnail[0], 'contentUrl' => $thumbnail[0], 'height' => $thumbnail[2], 'width' => $thumbnail[1] );
	if ( $id && get_post( $id ) ) {
		$attachment = get_post( $id );
		$name = trim( get_the_title( $id ) );
		$name = '' !== $name ? $name : trim( strip_tags( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) ) );
		$extras = array( 'name' => $name, 'description' => trim( strip_tags( apply_filters( 'the_content', $attachment->post_content ) ) ), 'caption' => trim( (string) wp_get_attachment_caption( $id ) ) );
		foreach ( $extras as $key => $value ) {
			if ( '' !== $value ) {
				$image[ $key ] = $value;
				$thumb[ $key ] = $value;
			}
		}
	}
	// AFFINGER adds names/captions to the nested image objects, not the outer object.
	return array( '@context' => 'https://schema.org', '@type' => 'ImageObject', 'image' => $image, 'url' => $source[0], 'contentUrl' => $source[0], 'height' => $source[2], 'width' => $source[1], 'thumbnail' => $thumb );
}

function exterior_exone_coexistence_column_schema_person( $id ) {
	$user = get_userdata( $id );
	if ( ! $user ) {
		return null;
	}
	$url = get_author_posts_url( $id );
	$person = array( '@context' => 'https://schema.org', '@type' => 'Person', 'mainEntityOfPage' => $url, 'name' => apply_filters( 'the_author', $user->display_name ), 'url' => $url );
	$description = trim( (string) get_the_author_meta( 'description', $id ) );
	if ( '' !== $description ) {
		$person['description'] = $description;
	}
	$publisher = (int) get_option( 'st-data437', 1 );
	$publisher = $publisher > 0 ? $publisher : 1;
	$mods = get_option( 'theme_mods_affinger-child', array() );
	$avatar_url = trim( (string) get_option( 'st_author_profile_avatar', '' ) );
	$avatar_id = '' !== $avatar_url ? attachment_url_to_postid( $avatar_url ) : 0;
	if ( $id === $publisher && is_array( $mods ) && ! empty( $mods['st_author_profile'] ) && $avatar_id ) {
		$person['image'] = exterior_exone_coexistence_column_schema_image( $avatar_id, true );
	} else {
		$avatar = get_avatar_data( $id, array( 'size' => 96 ) );
		if ( ! empty( $avatar['found_avatar'] ) ) {
			$image = array( '@context' => 'https://schema.org', '@type' => 'ImageObject', 'url' => $avatar['url'], 'contentUrl' => $avatar['url'], 'height' => 96, 'width' => 96 );
			$person['image'] = array( '@context' => 'https://schema.org', '@type' => 'ImageObject', 'image' => $image, 'url' => $avatar['url'], 'contentUrl' => $avatar['url'], 'height' => 96, 'width' => 96 );
		} else {
			$person['image'] = exterior_exone_coexistence_column_schema_image();
		}
	}
	$same_as = array();
	foreach ( array( 'twitter', 'facebook', 'instagram', 'youtube', 'user_url' ) as $name ) {
		$value = trim( (string) get_the_author_meta( $name, $id ) );
		if ( '' !== $value ) {
			$same_as[] = $value;
		}
	}
	if ( $same_as ) {
		$person['sameAs'] = $same_as;
	}
	return $person;
}

/** Reproduce enabled legacy graphs; existing in-body graphs remain the source of truth. */
function exterior_exone_coexistence_column_schema_graphs( $post, $list ) {
	$graphs = array();
	$site_name = trim( (string) get_option( 'st-data33', '' ) );
	$site_description = trim( (string) get_option( 'st-data34', '' ) );
	$site = array( 'description' => '' !== $site_description ? $site_description : get_bloginfo( 'description' ), 'name' => '' !== $site_name ? $site_name : get_bloginfo( 'name' ), 'url' => home_url() );
	$publisher_id = (int) get_option( 'st-data437', 1 );
	$publisher_id = $publisher_id > 0 ? $publisher_id : 1;
	$logo_url = (string) get_option( 'st_logo_image', '' );
	$logo_id = '' !== $logo_url ? attachment_url_to_postid( $logo_url ) : 0;
	$logo = exterior_exone_coexistence_column_schema_image( $logo_id, true );
	if ( 'yes' === get_option( 'st-data406', '' ) && ! exterior_exone_coexistence_column_has_schema( $post->post_content, 'WebSite' ) ) {
		$website = array_merge( array( '@context' => 'https://schema.org', '@type' => 'WebSite' ), $site );
		$publisher = exterior_exone_coexistence_column_schema_person( $publisher_id );
		if ( $publisher ) {
			$website['publisher'] = $publisher;
		}
		$website['image'] = $logo;
		$website['thumbnailUrl'] = $logo['url'];
		$graphs[] = $website;
	}
	if ( ! $list && 'yes' === get_option( 'st-data407', '' ) && ! exterior_exone_coexistence_column_has_schema( $post->post_content, 'Article' ) ) {
		$image = exterior_exone_coexistence_column_schema_image( get_post_thumbnail_id( $post ) );
		$article = array(
			'@context' => 'https://schema.org', '@type' => 'Article',
			'description' => trim( apply_filters( 'the_excerpt', exterior_exone_coexistence_column_legacy_excerpt( $post ) ) ),
			'mainEntityOfPage' => wp_get_canonical_url( $post ), 'name' => get_the_title( $post ), 'url' => get_permalink( $post ), 'headline' => get_the_title( $post ),
			'dateModified' => get_the_modified_time( DATE_ATOM, $post ), 'datePublished' => get_the_time( DATE_ATOM, $post ), 'image' => $image, 'thumbnailUrl' => $image['url'],
		);
		$author = exterior_exone_coexistence_column_schema_person( (int) $post->post_author );
		if ( $author ) {
			$article['author'] = $author;
		}
		if ( get_userdata( $publisher_id ) ) {
			$article['publisher'] = array_merge( array( '@context' => 'https://schema.org', '@type' => 'Organization' ), $site, array( 'image' => $logo, 'logo' => $logo ) );
		}
		$graphs[] = $article;
	}
	return $graphs;
}

function exterior_exone_coexistence_column_footer_schema() {
	if ( ! exterior_exone_coexistence_column_is_request() ) {
		return;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	$graphs = exterior_exone_coexistence_column_schema_graphs( $post, exterior_exone_coexistence_column_is_list() );
	if ( $graphs ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $graphs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . '</script>' . "\n";
	}
}

if ( exterior_exone_coexistence_column_enabled() ) {
	add_action( 'parse_request', 'exterior_exone_coexistence_column_parse_request', 30 );
	add_action( 'template_redirect', 'exterior_exone_coexistence_column_out_of_range', 5 );
	add_filter( 'redirect_canonical', 'exterior_exone_coexistence_column_redirect_canonical' );
	add_filter( 'get_canonical_url', 'exterior_exone_coexistence_column_canonical', 20, 2 );
	add_filter( 'document_title', 'exterior_exone_coexistence_column_document_title', 20 );
	add_action( 'wp', 'exterior_exone_coexistence_column_text_filters', 20 );
	add_filter( 'wp_robots', 'exterior_exone_coexistence_column_robots', 20 );
	add_action( 'wp_head', 'exterior_exone_coexistence_column_head', 20 );
	add_action( 'wp_footer', 'exterior_exone_coexistence_column_footer_schema', 20 );
	add_filter( 'body_class', 'exterior_exone_coexistence_column_body_class' );
}
