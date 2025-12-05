<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    BlogsHQ
 * @subpackage BlogsHQ/admin
 * @since      1.0.0
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
	die;
}

/**
 * The admin-specific functionality of the plugin.
 */
class BlogsHQ_Admin
{

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
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// Initialize AJAX handlers
		$this->init_ajax_handlers();
	}

	/**
	 * Initialize AJAX handlers
	 *
	 * @since 1.0.0
	 */
	public function init_ajax_handlers()
	{
		add_action('wp_ajax_blogshq_load_tab', array($this, 'ajax_load_tab'));
		add_action('wp_ajax_blogshq_save_settings', array($this, 'ajax_save_settings'));
	}

	/**
	 * AJAX handler for loading tab content
	 *
	 * @since 1.0.0
	 */
	public function ajax_load_tab()
	{
		// Verify nonce
		check_ajax_referer('blogshq_admin_nonce', 'nonce');

		// Check permissions
		if (! current_user_can('manage_options')) {
			wp_send_json_error(array(
				'message' => __('You do not have permission to perform this action.', 'blogshq')
			));
		}

		// Get and validate tab
		$tab = isset($_POST['tab']) ? sanitize_key($_POST['tab']) : 'logos';
		$valid_tabs = array('logos', 'toc', 'faq', 'ai-share');

		if (! in_array($tab, $valid_tabs, true)) {
			wp_send_json_error(array(
				'message' => __('Invalid tab selected.', 'blogshq')
			));
		}

		// Capture tab content
		ob_start();
		blogshq_get_template($tab);
		$content = ob_get_clean();

		wp_send_json_success(array(
			'content' => $content,
			'tab' => $tab
		));
	}

	/**
	 * AJAX handler for saving settings
	 *
	 * @since 1.0.0
	 */
	public function ajax_save_settings()
	{
		// Verify nonce based on form type
		$nonce_actions = array(
			'blogshq_logos_nonce' => 'blogshq_logos_settings',
			'blogshq_toc_nonce' => 'blogshq_toc_settings',
		);

		$nonce_verified = false;
		foreach ($nonce_actions as $nonce_field => $nonce_action) {
			if (isset($_POST[$nonce_field]) && wp_verify_nonce($_POST[$nonce_field], $nonce_action)) {
				$nonce_verified = true;
				break;
			}
		}

		if (! $nonce_verified) {
			wp_send_json_error(array(
				'message' => __('Security check failed.', 'blogshq')
			));
		}

		// Check permissions
		if (! current_user_can('manage_options')) {
			wp_send_json_error(array(
				'message' => __('You do not have permission to perform this action.', 'blogshq')
			));
		}

		// Process form submission based on type
		// Process form submission based on type
		try {
			if (isset($_POST['form_type'])) {
				switch ($_POST['form_type']) {
					case 'category_logo':
						$this->process_logos_save();
						$message = __('Logos saved successfully.', 'blogshq');
						break;
					case 'toc':
						$this->process_toc_save();
						$message = __('TOC settings saved successfully.', 'blogshq');
						break;
					default:
						wp_send_json_error(array(
							'message' => __('Unknown form type.', 'blogshq')
						));
				}
			} else {
				wp_send_json_error(array(
					'message' => __('Unknown form type.', 'blogshq')
				));
			}

			wp_send_json_success(array(
				'message' => $message
			));
		} catch (Exception $e) {
			wp_send_json_error(array(
				'message' => $e->getMessage()
			));
		}
	}

	/**
	 * Process logos save (extracted from BlogsHQ_Logos)
	 *
	 * @since 1.0.0
	 */
	private function process_logos_save()
	{
		$categories = get_categories(array('hide_empty' => false));

		foreach ($categories as $cat) {
			$cat_id = absint($cat->term_id);

			// Sanitize and save light logo with image extension validation
			$light_url = '';
			if ( isset($_POST['logo_url_light'][$cat_id]) ) {
				$url = esc_url_raw($_POST['logo_url_light'][$cat_id]);
				// SECURITY: Validate image file extensions
				if ( $url && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $url) ) {
					$light_url = $url;
				}
			}

			// Sanitize and save dark logo with image extension validation
			$dark_url = '';
			if ( isset($_POST['logo_url_dark'][$cat_id]) ) {
				$url = esc_url_raw($_POST['logo_url_dark'][$cat_id]);
				// SECURITY: Validate image file extensions
				if ( $url && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $url) ) {
					$dark_url = $url;
				}
			}

			update_term_meta($cat_id, 'blogshq_logo_url_light', $light_url);
			update_term_meta($cat_id, 'blogshq_logo_url_dark', $dark_url);
		}

		// Clear cache
		delete_transient('blogshq_categories');
	}

	/**
	 * Process TOC save (extracted from BlogsHQ_TOC)
	 *
	 * @since 1.0.0
	 */
	private function process_toc_save()
	{
		// Save TOC headings with explicit sanitization
		$headings = isset($_POST['toc_headings']) && is_array($_POST['toc_headings'])
			? array_map('sanitize_key', $_POST['toc_headings'])
			: array();
		// SECURITY: Whitelist allowed heading tags
		$checked = array_intersect($headings, array('h2', 'h3', 'h4', 'h5', 'h6'));
		update_option('blogshq_toc_headings', $checked);

		// Save link icon options
		$link_icon_enabled = isset($_POST['link_icon_enabled']);
		update_option('blogshq_toc_link_icon_enabled', $link_icon_enabled);

		// SECURITY: Sanitize heading tags with explicit whitelist
		$icon_headings_raw = isset($_POST['link_icon_headings']) && is_array($_POST['link_icon_headings'])
			? array_map('sanitize_key', $_POST['link_icon_headings'])
			: array();
		$icon_headings = array_intersect($icon_headings_raw, array('h2', 'h3', 'h4', 'h5', 'h6'));
		update_option('blogshq_toc_link_icon_headings', $icon_headings);

		$color = isset($_POST['link_icon_color']) ? sanitize_hex_color($_POST['link_icon_color']) : '#2E62E9';
		update_option('blogshq_toc_link_icon_color', $color);

		// Clear cache
		wp_cache_delete('blogshq_toc_settings');
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles()
	{
		// Only load on plugin pages
		if (! $this->is_plugin_page()) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			BLOGSHQ_PLUGIN_URL . 'admin/css/admin.min.css',
			array(),
			$this->version,
			'all'
		);

		// WordPress color picker
		wp_enqueue_style('wp-color-picker');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts()
	{
		// Only load on plugin pages
		if (! $this->is_plugin_page()) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			BLOGSHQ_PLUGIN_URL . 'admin/js/admin.min.js',
			array('jquery', 'wp-color-picker'),
			$this->version,
			false
		);

		// Localize script
		wp_localize_script(
			$this->plugin_name,
			'blogshqAdmin',
			array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'nonce'   => wp_create_nonce('blogshq_admin_nonce'),
				'strings' => array(
					'confirm_delete' => __('Are you sure you want to delete this?', 'blogshq'),
					'saved'          => __('Settings saved successfully.', 'blogshq'),
					'saving'         => __('Saving...', 'blogshq'),
					'error'          => __('An error occurred. Please try again.', 'blogshq'),
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
	private function is_plugin_page()
	{
		$screen = get_current_screen();
		if (! $screen) {
			return false;
		}

		return strpos($screen->id, 'blogshq') !== false;
	}

	/**
	 * Register the administration menu for this plugin.
	 *
	 * @since 1.0.0
	 */
	public function add_plugin_admin_menu()
	{
		// Main menu
		add_menu_page(
			__('BlogsHQ Admin Toolkit', 'blogshq'),
			__('BlogsHQ', 'blogshq'),
			'manage_options',
			$this->plugin_name,
			array($this, 'display_plugin_dashboard'),
			'dashicons-admin-generic',
			26
		);

		// Dashboard submenu (same as parent)
		add_submenu_page(
			$this->plugin_name,
			__('Dashboard', 'blogshq'),
			__('Dashboard', 'blogshq'),
			'manage_options',
			$this->plugin_name,
			array($this, 'display_plugin_dashboard')
		);
	}

	/**
	 * Display the plugin dashboard page.
	 *
	 * @since 1.0.0
	 */
	public function display_plugin_dashboard()
	{
		// Check user capabilities
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'blogshq'));
		}

		// Get active tab
		$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'logos';

		// Valid tabs
		$valid_tabs = array('logos', 'toc', 'faq', 'ai-share');
		if (! in_array($active_tab, $valid_tabs, true)) {
			$active_tab = 'logos';
		}

		// Display dashboard template
		blogshq_get_template('dashboard', null, array('active_tab' => $active_tab));
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since 1.0.0
	 * @param array $links Existing plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function add_action_links($links)
	{
		$settings_link = array(
			'<a href="' . admin_url('admin.php?page=' . $this->plugin_name) . '">' . __('Settings', 'blogshq') . '</a>',
		);

		return array_merge($settings_link, $links);
	}

	/**
	 * Process form submissions (DEPRECATED - kept for backward compatibility)
	 *
	 * @since 1.0.0
	 * @deprecated Use AJAX handlers instead
	 */
	public function process_form_submission()
	{
		// Check if form is submitted
		if (! isset($_POST['blogshq_action'])) {
			return;
		}

		// Verify nonce
		if (! isset($_POST['blogshq_nonce']) || ! wp_verify_nonce($_POST['blogshq_nonce'], 'blogshq_settings')) {
			wp_die(esc_html__('Security check failed.', 'blogshq'));
		}

		// Check user capabilities
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions.', 'blogshq'));
		}

		$action = sanitize_key($_POST['blogshq_action']);

		do_action('blogshq_before_form_processing', $action);

		// Route to appropriate handler
		switch ($action) {
			case 'save_logos':
				$this->handle_logos_save();
				break;
			case 'save_toc':
				$this->handle_toc_save();
				break;
			default:
				do_action('blogshq_handle_form_action', $action);
				break;
		}
	}

	/**
	 * Handle logos form submission (DEPRECATED)
	 *
	 * @since 1.0.0
	 * @deprecated
	 */
	private function handle_logos_save()
	{
		do_action('blogshq_save_logos');
	}

	/**
	 * Handle TOC form submission (DEPRECATED)
	 *
	 * @since 1.0.0
	 * @deprecated
	 */
	private function handle_toc_save()
	{
		do_action('blogshq_save_toc');
	}
}
