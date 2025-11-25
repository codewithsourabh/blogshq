<?php
/**
 * Plugin Name:       BlogsHQ Admin Toolkit
 * Plugin URI:        https://github.com/codewithsourabh/blogshq
 * Description:       Comprehensive admin tools for BlogsHQ including category logos, TOC, FAQ blocks, and AI share functionality.
 * Version:           1.2.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Sourabh
 * Author URI:        https://blogshq.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       blogshq
 * Domain Path:       /languages
 * Update URI:        https://github.com/codewithsourabh/blogshq
 *
 * @package BlogsHQ
 * @link    https://blogshq.com
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'BLOGSHQ_VERSION', '1.1.0' );

/**
 * Asset version with hash for better cache busting.
 */
define( 'BLOGSHQ_ASSET_VERSION', BLOGSHQ_VERSION );

/**
 * Plugin directory path.
 */
define( 'BLOGSHQ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'BLOGSHQ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'BLOGSHQ_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Text domain for translations.
 */
define( 'BLOGSHQ_TEXT_DOMAIN', 'blogshq' );

/**
 * Minimum PHP version required.
 */
define( 'BLOGSHQ_MIN_PHP_VERSION', '7.4' );

/**
 * Minimum WordPress version required.
 */
define( 'BLOGSHQ_MIN_WP_VERSION', '5.8' );

/**
 * Check PHP and WordPress versions before loading plugin.
 */
function blogshq_check_requirements() {
	// Check PHP version
	if ( version_compare( PHP_VERSION, BLOGSHQ_MIN_PHP_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'blogshq_php_version_notice' );
		return false;
	}

	// Check WordPress version
	global $wp_version;
	if ( version_compare( $wp_version, BLOGSHQ_MIN_WP_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'blogshq_wp_version_notice' );
		return false;
	}

	return true;
}

/**
 * Custom error handler for debugging.
 *
 * @since 1.0.0
 * @param int    $errno      Error number.
 * @param string $errstr     Error message.
 * @param string $errfile    Error file.
 * @param int    $errline    Error line.
 */
function blogshq_error_handler( $errno, $errstr, $errfile, $errline ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
		$error_msg = sprintf(
			'BlogsHQ Error [%d]: %s in %s:%d',
			$errno,
			$errstr,
			$errfile,
			$errline
		);
		error_log( $error_msg );
	}
}

set_error_handler( 'blogshq_error_handler' );

/**
 * Display PHP version notice.
 */
function blogshq_php_version_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: 1: Required PHP version, 2: Current PHP version */
				esc_html__( 'BlogsHQ Admin Toolkit requires PHP version %1$s or higher. You are running version %2$s. Please upgrade PHP.', 'blogshq' ),
				esc_html( BLOGSHQ_MIN_PHP_VERSION ),
				esc_html( PHP_VERSION )
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Display WordPress version notice.
 */
function blogshq_wp_version_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: 1: Required WordPress version, 2: Current WordPress version */
				esc_html__( 'BlogsHQ Admin Toolkit requires WordPress version %1$s or higher. You are running version %2$s. Please upgrade WordPress.', 'blogshq' ),
				esc_html( BLOGSHQ_MIN_WP_VERSION ),
				esc_html( $GLOBALS['wp_version'] )
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Check requirements and load plugin.
 */
if ( blogshq_check_requirements() ) {
	/**
	 * The code that runs during plugin activation.
	 */
	function blogshq_activate() {
		require_once BLOGSHQ_PLUGIN_DIR . 'includes/class-blogshq-activator.php';
		BlogsHQ_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 */
	function blogshq_deactivate() {
		require_once BLOGSHQ_PLUGIN_DIR . 'includes/class-blogshq-deactivator.php';
		BlogsHQ_Deactivator::deactivate();
	}

	register_activation_hook( __FILE__, 'blogshq_activate' );
	register_deactivation_hook( __FILE__, 'blogshq_deactivate' );

	/**
	 * Autoloader for plugin classes.
	 *
	 * @param string $class_name The class name to load.
	 */
	function blogshq_autoloader( $class_name ) {
		// Only autoload classes from this plugin
		if ( strpos( $class_name, 'BlogsHQ_' ) !== 0 ) {
			return;
		}

		// Convert class name to file name
		$class_file = 'class-' . str_replace( '_', '-', strtolower( $class_name ) ) . '.php';

		// Define possible paths
		$paths = array(
			BLOGSHQ_PLUGIN_DIR . 'includes/',
			BLOGSHQ_PLUGIN_DIR . 'admin/',
			BLOGSHQ_PLUGIN_DIR . 'modules/logos/',
			BLOGSHQ_PLUGIN_DIR . 'modules/toc/',
			BLOGSHQ_PLUGIN_DIR . 'modules/faq/',
			BLOGSHQ_PLUGIN_DIR . 'modules/ai-share/',
		);

		// Try to load the class file
		foreach ( $paths as $path ) {
			$file = $path . $class_file;
			if ( file_exists( $file ) ) {
				require_once $file;
				return;
			}
		}

		// Fallback warning if class not found
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
			error_log( 'BlogsHQ: Failed to autoload class ' . $class_name );
		}
	}
	spl_autoload_register( 'blogshq_autoloader' );

	/**
	 * Load Composer autoloader if exists.
	 */
	if ( file_exists( BLOGSHQ_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
		require_once BLOGSHQ_PLUGIN_DIR . 'vendor/autoload.php';
	}


	/**
	 * Handle plugin version upgrades.
	 *
	 * @since 1.0.0
	 */
	function blogshq_handle_version_upgrade() {
		$current_version = get_option( 'blogshq_version' );
		$new_version     = BLOGSHQ_VERSION;

		if ( version_compare( $current_version, $new_version, '<' ) ) {
			do_action( 'blogshq_version_upgrade', $current_version, $new_version );
			update_option( 'blogshq_version', $new_version );
		}
	}

	add_action( 'admin_init', 'blogshq_handle_version_upgrade' );

	/**
	 * Begin execution of the plugin.
	 *
	 * @since 1.0.0
	 */
	function blogshq_run() {
		require_once BLOGSHQ_PLUGIN_DIR . 'includes/class-blogshq.php';
		$plugin = new BlogsHQ();
		$plugin->run();
	}

	blogshq_run();
}
