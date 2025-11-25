<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    BlogsHQ
 * @subpackage BlogsHQ/admin
 * @since      1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The admin-specific functionality of the plugin.
 */
class BlogsHQ_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		// Only load on plugin pages
		if ( ! $this->is_plugin_page() ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			BLOGSHQ_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			$this->version,
			'all'
		);

		// WordPress color picker
		wp_enqueue_style( 'wp-color-picker' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		// Only load on plugin pages
		if ( ! $this->is_plugin_page() ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			BLOGSHQ_PLUGIN_URL . 'admin/js/admin.min.js',
			array( 'jquery', 'wp-color-picker' ),
			$this->version,
			false
		);

		// Localize script
		wp_localize_script(
			$this->plugin_name,
			'blogshqAdmin',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'blogshq_admin_nonce' ),
				'strings' => array(
					'confirm_delete' => __( 'Are you sure you want to delete this?', 'blogshq' ),
					'saved'          => __( 'Settings saved successfully.', 'blogshq' ),
					'error'          => __( 'An error occurred. Please try again.', 'blogshq' ),
				),
			)
		);
	}

	/**
	 * Check if current page is a plugin admin page.
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	private function is_plugin_page() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		return strpos( $screen->id, 'blogshq' ) !== false;
	}

	/**
	 * Register the administration menu for this plugin.
	 *
	 * @since 1.0.0
	 */
	public function add_plugin_admin_menu() {
		// Main menu
		add_menu_page(
			__( 'BlogsHQ Admin Toolkit', 'blogshq' ),
			__( 'BlogsHQ', 'blogshq' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_dashboard' ),
			'dashicons-admin-generic',
			26
		);

		// Dashboard submenu (same as parent)
		add_submenu_page(
			$this->plugin_name,
			__( 'Dashboard', 'blogshq' ),
			__( 'Dashboard', 'blogshq' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_dashboard' )
		);
	}

	/**
	 * Display the plugin dashboard page.
	 *
	 * @since 1.0.0
	 */
	public function display_plugin_dashboard() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'blogshq' ) );
		}

		// Get active tab
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'logos';

		// Valid tabs
		$valid_tabs = array( 'logos', 'toc', 'faq', 'ai-share' );
		if ( ! in_array( $active_tab, $valid_tabs, true ) ) {
			$active_tab = 'logos';
		}

		// Display dashboard template
		blogshq_get_template( 'dashboard', null, array( 'active_tab' => $active_tab ) );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since 1.0.0
	 * @param array $links Existing plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function add_action_links( $links ) {
		$settings_link = array(
			'<a href="' . admin_url( 'admin.php?page=' . $this->plugin_name ) . '">' . __( 'Settings', 'blogshq' ) . '</a>',
		);

		return array_merge( $settings_link, $links );
	}

	/**
	 * Process form submissions.
	 *
	 * @since 1.0.0
	 */
	public function process_form_submission() {
		// Check if form is submitted
		if ( ! isset( $_POST['blogshq_action'] ) ) {
			return;
		}

		// Verify nonce
		if ( ! isset( $_POST['blogshq_nonce'] ) || ! wp_verify_nonce( $_POST['blogshq_nonce'], 'blogshq_settings' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'blogshq' ) );
		}

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'blogshq' ) );
		}

		$action = sanitize_key( $_POST['blogshq_action'] );

		/**
		 * Allow modules to sanitize their data before processing.
		 *
		 * @since 1.0.0
		 */
		do_action( 'blogshq_before_form_processing', $action );

		// Route to appropriate handler
		switch ( $action ) {
			case 'save_logos':
				$this->handle_logos_save();
				break;
			case 'save_toc':
				$this->handle_toc_save();
				break;
			default:
				do_action( 'blogshq_handle_form_action', $action );
				break;
		}
	}

	/**
	 * Handle logos form submission.
	 *
	 * @since 1.0.0
	 */
	private function handle_logos_save() {
		// This will be handled by the logos module
		do_action( 'blogshq_save_logos' );
	}

	/**
	 * Handle TOC form submission.
	 *
	 * @since 1.0.0
	 */
	private function handle_toc_save() {
		// This will be handled by the TOC module
		do_action( 'blogshq_save_toc' );
	}
}