<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @package    BlogsHQ
 * @subpackage BlogsHQ/includes
 * @since      1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The core plugin class.
 */
class BlogsHQ {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    BlogsHQ_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->version     = BLOGSHQ_VERSION;
		$this->plugin_name = 'blogshq';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_module_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters.
		 */
		require_once BLOGSHQ_PLUGIN_DIR . 'includes/class-blogshq-loader.php';

		/**
		 * The class responsible for defining internationalization functionality.
		 */
		require_once BLOGSHQ_PLUGIN_DIR . 'includes/class-blogshq-i18n.php';

		/**
		 * The class responsible for defining all actions in the admin area.
		 */
		require_once BLOGSHQ_PLUGIN_DIR . 'admin/class-blogshq-admin.php';

		/**
		 * Helper functions.
		 */
		if ( file_exists( BLOGSHQ_PLUGIN_DIR . 'includes/helpers.php' ) ) {
			require_once BLOGSHQ_PLUGIN_DIR . 'includes/helpers.php';
		}

		$this->loader = new BlogsHQ_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {
		$plugin_i18n = new BlogsHQ_I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all hooks related to the admin area functionality.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new BlogsHQ_Admin( $this->get_plugin_name(), $this->get_version() );

		// Enqueue admin styles and scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Register admin menu
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

		// Add settings link on plugin page
		$this->loader->add_filter( 'plugin_action_links_' . BLOGSHQ_BASENAME, $plugin_admin, 'add_action_links' );
	}

	/**
	 * Register all hooks related to the public-facing functionality.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_public_hooks() {
		// Enqueue public styles and scripts
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_public_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_public_scripts' );
	}

	/**
	 * Register all hooks for plugin modules.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_module_hooks() {
		// Logo Module
		if ( class_exists( 'BlogsHQ_Logos' ) ) {
			$logos_module = new BlogsHQ_Logos();
			$this->loader->add_action( 'init', $logos_module, 'init' );
		}

		// TOC Module
		if ( class_exists( 'BlogsHQ_TOC' ) ) {
			$toc_module = new BlogsHQ_TOC();
			$this->loader->add_action( 'init', $toc_module, 'init' );
		}

		// FAQ Module
		if ( class_exists( 'BlogsHQ_FAQ_Block' ) ) {
			$faq_module = new BlogsHQ_FAQ_Block();
			$this->loader->add_action( 'init', $faq_module, 'init' );
		}

		// AI Share Module
		if ( class_exists( 'BlogsHQ_AI_Share' ) ) {
			$ai_share_module = new BlogsHQ_AI_Share();
			$this->loader->add_action( 'init', $ai_share_module, 'init' );
		}
	}

	/**
	 * Enqueue public-facing stylesheets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_public_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			BLOGSHQ_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Enqueue public-facing JavaScript.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_public_scripts() {
		// Scripts are enqueued conditionally by modules
	}

	/**
	 * Run the loader to execute all hooks.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it.
	 *
	 * @since  1.0.0
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks.
	 *
	 * @since  1.0.0
	 * @return BlogsHQ_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since  1.0.0
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}