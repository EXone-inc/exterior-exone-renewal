<?php
/** Actual WordPress sitemap dispatcher, synthetic provider; no WP bootstrap/DB/network. */
if ( PHP_SAPI !== 'cli' ) { exit; }
require __DIR__ . '/run.php';
function sanitize_text_field( $value ) { return (string) $value; }
function absint( $value ) { return abs( (int) $value ); }
function get_query_var( $key ) { return $GLOBALS['sitemap_vars'][ $key ] ?? ''; }
require dirname( __DIR__, 6 ) . '/wp-includes/sitemaps/class-wp-sitemaps.php';

$provider = new stdClass();
check( false === exterior_exone_coexistence_sitemap_provider_boundary( $provider, 'users' ), 'Legacy author sitemap provider is excluded' );
check( $provider === exterior_exone_coexistence_sitemap_provider_boundary( $provider, 'posts' ), 'Shared column post provider remains available' );
$known = in_array( '--known', $argv, true );
$vars = array( 'sitemap' => $known ? 'exone' : 'unregistered', 'paged' => 1 );
$_POST = array();
check( route( '', $vars, $vars ) === null, 'Sitemap dispatcher receives service request' );
check( false === exterior_exone_coexistence_canonical_redirect( 'old-canonical' ), 'Service never redirects through old canonical' );
$GLOBALS['sitemap_vars'] = $vars;
$GLOBALS['status_code'] = 200;
$GLOBALS['wp_query'] = new FixtureQuery();
$GLOBALS['wp_query']->posts = array( $GLOBALS['posts'][1] );
$GLOBALS['wp_query']->post = $GLOBALS['posts'][1];
$GLOBALS['wp_query']->post_count = 1;
$GLOBALS['wp_query']->queried_object = $GLOBALS['posts'][1];
$GLOBALS['wp_query']->queried_object_id = 1;
$GLOBALS['post'] = $GLOBALS['posts'][1];
$server = new class( $known ) extends WP_Sitemaps {
	public function __construct( $known ) {
		$this->registry = new class( $known ) {
			private $known;
			public function __construct( $known ) { $this->known = $known; }
			public function get_provider( $name ) {
				return $this->known ? new class {
					public function get_url_list( $page, $subtype ) { return array( array( 'loc' => 'https://example.invalid/' ) ); }
				} : false;
			}
		};
		$this->renderer = new class {
			public function render_sitemap( $entries ) { $GLOBALS['core_sitemap_rendered'] = count( $entries ) === 1; }
		};
	}
	public function sitemaps_enabled() { return true; }
};
if ( $known ) {
	register_shutdown_function( static function () {
		if ( empty( $GLOBALS['core_sitemap_rendered'] ) || $GLOBALS['wp_query']->is_404 ) { fwrite( STDERR, "Known core sitemap failed.\n" ); exit( 1 ); }
		echo "PASS actual core known sitemap exits before fallback; DB/network 0.\n";
	} );
	$server->render_sitemaps();
	throw new RuntimeException( 'Core known sitemap did not terminate its response' );
}
$server->render_sitemaps();
check( ! $GLOBALS['wp_query']->is_404, 'Actual core leaves an unknown provider unhandled' );
exterior_exone_coexistence_sitemap_fallback();
check( $GLOBALS['wp_query']->is_404 && 404 === $GLOBALS['status_code'], 'Unknown provider becomes 404 before HTML' );
check( array() === $GLOBALS['wp_query']->posts && 0 === $GLOBALS['wp_query']->post_count && null === $GLOBALS['post'] && null === $GLOBALS['wp_query']->queried_object, 'Old loop and queried object are unavailable' );
// The XML Sitemap loader sets is_feed before attempting to load/render its handler.
$GLOBALS['wp_query']->is_feed = true;
exterior_exone_coexistence_sitemap_fallback();
check( false === $GLOBALS['wp_query']->is_feed, 'Unfinished plugin sitemap cannot fall through to RSS' );
foreach ( array( 'sitemap', 'sitemap-stylesheet', 'xml_sitemap', 'rest_route', 'robots' ) as $key ) {
	check( 'missing' === route( '', array( $key => array( 'invalid' ) ), array( $key => array( 'invalid' ) ) )['kind'], 'Array-valued service fails closed' );
}
route( 'works/shared-slug' );
check( empty( $GLOBALS['exterior_exone_coexistence_sitemap_request'] ), 'Ordinary requests clear service fallback state' );
echo "PASS actual core unknown sitemap + service shape/fallback regressions; DB/network 0.\n";
