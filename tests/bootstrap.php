<?php
/**
 * BlogsHQ Plugin Tests Bootstrap
 *
 * @package BlogsHQ
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Set up WordPress testing environment.
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Include WordPress test functions.
require_once $_tests_dir . '/includes/functions.php';

// Include plugin.
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/blogshq-admin-toolkit.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Include WordPress bootstrap.
require $_tests_dir . '/includes/bootstrap.php';