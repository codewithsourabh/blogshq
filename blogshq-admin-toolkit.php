<?php
/**
 * Plugin Name:       BlogsHQ Admin Toolkit
 * Plugin URI:        https://github.com/codewithsourabh/blogshq
 * Description:       Comprehensive admin tools for BlogsHQ including category logos, TOC, FAQ blocks, and AI share functionality.
 * Version: 1.2.2
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

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin version number.
 * Used for cache busting and version compatibility checks.
 */
define( 'BLOGSHQ_VERSION', '1.2.2' );

/**
 * Asset version for CSS/JS files.
 * Automatically updates when plugin version changes for cache invalidation.
 */
define( 'BLOGSHQ_ASSET_VERSION', BLOGSHQ_VERSION );

/**
 * Plugin directory absolute path.
 * Example: /var/www/html/wp-content/plugins/blogshq/
 */
define( 'BLOGSHQ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 * Example: https://example.com/wp-content/plugins/blogshq/
 */
define( 'BLOGSHQ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 * Example: blogshq/blogshq-admin-toolkit.php
 */
define( 'BLOGSHQ_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Text domain for internationalization.
 */
define( 'BLOGSHQ_TEXT_DOMAIN', 'blogshq' );

/**
 * Minimum required PHP version.
 */
define( 'BLOGSHQ_MIN_PHP_VERSION', '7.4' );

/**
 * Minimum required WordPress version.
 */
define( 'BLOGSHQ_MIN_WP_VERSION', '5.8' );

require_once BLOGSHQ_PLUGIN_DIR . 'lib/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Initialize GitHub Updates
 */
function blogshq_init_github_updater() {
	$update_checker = PucFactory::buildUpdateChecker(
		'https://github.com/codewithsourabh/blogshq/',
		__FILE__,
		'blogshq-admin-toolkit'
	);
	
	$update_checker->setBranch( 'master' );
	$update_checker->getVcsApi()->enableReleaseAssets();
}

if ( class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
	add_action( 'plugins_loaded', 'blogshq_init_github_updater' );
}

/**
 * Check PHP and WordPress versions before loading plugin.
 */
function blogshq_check_requirements() {
	if ( version_compare( PHP_VERSION, BLOGSHQ_MIN_PHP_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'blogshq_php_version_notice' );
		return false;
	}

	global $wp_version;
	if ( version_compare( $wp_version, BLOGSHQ_MIN_WP_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'blogshq_wp_version_notice' );
		return false;
	}

	return true;
}

/**
 * Custom error handler for debugging.
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
	 * Class map for faster autoloading
	 */
	function blogshq_get_class_map() {
		static $class_map = null;
		
		if ( null === $class_map ) {
			$class_map = array(
				'BlogsHQ'              => 'includes/class-blogshq.php',
				'BlogsHQ_Loader'       => 'includes/class-blogshq-loader.php',
				'BlogsHQ_I18n'         => 'includes/class-blogshq-i18n.php',
				'BlogsHQ_Activator'    => 'includes/class-blogshq-activator.php',
				'BlogsHQ_Deactivator'  => 'includes/class-blogshq-deactivator.php',
				'BlogsHQ_Admin'        => 'admin/class-blogshq-admin.php',
				'BlogsHQ_Logos'        => 'modules/logos/class-blogshq-logos.php',
				'BlogsHQ_TOC'          => 'modules/toc/class-blogshq-toc.php',
				'BlogsHQ_FAQ_Block'    => 'modules/faq/class-blogshq-faq-block.php',
				'BlogsHQ_AI_Share'     => 'modules/ai-share/class-blogshq-ai-share.php',
			);
		}
		
		return $class_map;
	}

	/**
	 * Optimized autoloader using class map
	 */
	function blogshq_autoloader( $class_name ) {
		if ( strpos( $class_name, 'BlogsHQ_' ) !== 0 ) {
			return;
		}
		
		$class_map = blogshq_get_class_map();
		
		if ( isset( $class_map[ $class_name ] ) ) {
			$file = BLOGSHQ_PLUGIN_DIR . $class_map[ $class_name ];
			
			if ( file_exists( $file ) ) {
				require_once $file;
				return;
			}
		}
		
		$class_file = 'class-' . str_replace( '_', '-', strtolower( $class_name ) ) . '.php';
		$paths = array(
			'includes/',
			'admin/',
			'modules/logos/',
			'modules/toc/',
			'modules/faq/',
			'modules/ai-share/',
		);
		
		foreach ( $paths as $path ) {
			$file = BLOGSHQ_PLUGIN_DIR . $path . $class_file;
			if ( file_exists( $file ) ) {
				require_once $file;
				return;
			}
		}
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'BlogsHQ: Failed to autoload class ' . $class_name );
		}
	}

	spl_autoload_register( 'blogshq_autoloader' );

	if ( file_exists( BLOGSHQ_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
		require_once BLOGSHQ_PLUGIN_DIR . 'vendor/autoload.php';
	}

	/**
	 * Handle plugin version upgrades.
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
	 */
	function blogshq_run() {
		require_once BLOGSHQ_PLUGIN_DIR . 'includes/class-blogshq.php';
		$plugin = new BlogsHQ();
		$plugin->run();
	}

	blogshq_run();
}