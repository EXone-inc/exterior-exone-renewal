<?php
/** Existing production page 3474 remains the owner of /column/. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'exterior_exone_coexistence_column_is_list' ) && exterior_exone_coexistence_column_is_list() ) {
	require __DIR__ . '/archive-column.php';
} else {
	// The ordinary local installation keeps its existing fallback behavior.
	require __DIR__ . '/index.php';
}
