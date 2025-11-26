<?php
/**
 * Table of Contents Module
 *
 * @package    BlogsHQ
 * @subpackage BlogsHQ/modules/toc
 * @since      1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Table of Contents functionality.
 */
class BlogsHQ_TOC {

	/**
	 * Cached heading regex pattern.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string
	 */
	private static $heading_regex = null;

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		// Register shortcode
		add_shortcode( 'blogshq_toc', array( $this, 'render_shortcode' ) );

		// Add TOC and anchors to content
		add_filter( 'the_content', array( $this, 'insert_toc_and_anchors' ), 10 );

		// Clear cache on post save
		add_action( 'save_post', array( $this, 'clear_toc_cache' ) );

		// Invalidate heading regex cache when TOC settings update
		add_action( 'update_option_blogshq_toc_headings', array( $this, 'clear_heading_regex_cache' ) );

		// Enqueue link icon script
		add_action( 'wp_footer', array( $this, 'enqueue_link_icon_script' ) );

		add_action( 'update_option_blogshq_toc_headings', array( $this, 'clear_settings_cache' ) );
		add_action( 'update_option_blogshq_toc_link_icon_enabled', array( $this, 'clear_settings_cache' ) );
	}

	/**
	 * Enqueue frontend styles.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_frontend_styles() {
		global $post;
		
		// Check if any shortcode is present
		$has_logo = has_shortcode( $post->post_content ?? '', 'blogshq_category_logo' );
		$has_toc = has_shortcode( $post->post_content ?? '', 'blogshq_toc' );
		$has_ai = has_shortcode( $post->post_content ?? '', 'ai_share' );
		
		// Or if it's a post (TOC auto-inserts)
		$is_post = is_singular( 'post' );
		
	}

	/**
	 * Get cached heading regex pattern.
	 *
	 * @since 1.0.0
	 * @return string Regex pattern for selected headings.
	 */
	private function get_heading_regex() {
		if ( null === self::$heading_regex ) {
			$selected_headings = get_option( 'blogshq_toc_headings', array( 'h2', 'h3', 'h4', 'h5', 'h6' ) );
			
			if ( is_array( $selected_headings ) && ! empty( $selected_headings ) ) {
				self::$heading_regex = implode( '|', array_map( 'preg_quote', $selected_headings ) );
			} else {
				self::$heading_regex = '';
			}
		}

		return self::$heading_regex;
	}

	/**
	 * Clear heading regex cache.
	 *
	 * @since 1.0.0
	 */
	public function clear_heading_regex_cache() {
		self::$heading_regex = null;
	}

	/**
	 * Render admin page.
	 *
	 * @since 1.0.0
	 */
	public function render_admin_page() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'blogshq' ) );
		}

		// Handle form submission
		if ( isset( $_POST['blogshq_save_toc'] ) ) {
			$this->save_settings();
		}

		$selected_headings  = get_option( 'blogshq_toc_headings', array( 'h2', 'h3', 'h4', 'h5', 'h6' ) );
		$link_icon_enabled  = get_option( 'blogshq_toc_link_icon_enabled', false );
		$link_icon_headings = get_option( 'blogshq_toc_link_icon_headings', array( 'h2' ) );
		$link_icon_color    = get_option( 'blogshq_toc_link_icon_color', '#2E62E9' );

		if ( ! is_array( $selected_headings ) ) {
			$selected_headings = array();
		}
		if ( ! is_array( $link_icon_headings ) ) {
			$link_icon_headings = array();
		}
		?>
		<div class="blogshq-toc-settings">
			<h2><?php esc_html_e( 'Table of Contents Settings', 'blogshq' ); ?></h2>
			
			<form method="post" action="">
				<?php wp_nonce_field( 'blogshq_toc_settings', 'blogshq_toc_nonce' ); ?>
				
				<table class="form-table">
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Include Headings in TOC:', 'blogshq' ); ?>
						</th>
						<td>
							<?php foreach ( array( 'h2', 'h3', 'h4', 'h5', 'h6' ) as $tag ) : ?>
								<label style="margin-right: 16px;">
									<input type="checkbox" 
										   name="toc_headings[]" 
										   value="<?php echo esc_attr( $tag ); ?>" 
										   <?php checked( in_array( $tag, $selected_headings, true ) ); ?> />
									<?php echo esc_html( strtoupper( $tag ) ); ?>
								</label>
							<?php endforeach; ?>
							<p class="description">
								<?php esc_html_e( 'Select which heading levels to include in the table of contents.', 'blogshq' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( 'Enable Link Icon:', 'blogshq' ); ?>
						</th>
						<td>
							<label>
								<input type="checkbox" 
									   name="link_icon_enabled" 
									   value="1" 
									   <?php checked( $link_icon_enabled ); ?> />
								<?php esc_html_e( 'Show "copy link" icon after headings', 'blogshq' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Displays a clickable icon next to headings that copies the heading link to clipboard.', 'blogshq' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( 'Show Link Icon After:', 'blogshq' ); ?>
						</th>
						<td>
							<?php foreach ( array( 'h2', 'h3', 'h4', 'h5', 'h6' ) as $tag ) : ?>
								<label style="margin-right: 16px;">
									<input type="checkbox" 
										   name="link_icon_headings[]" 
										   value="<?php echo esc_attr( $tag ); ?>" 
										   <?php checked( in_array( $tag, $link_icon_headings, true ) ); ?> />
									<?php echo esc_html( strtoupper( $tag ) ); ?>
								</label>
							<?php endforeach; ?>
							<p class="description">
								<?php esc_html_e( 'Select which heading levels should display the link icon.', 'blogshq' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( 'Link Icon Color:', 'blogshq' ); ?>
						</th>
						<td>
							<input type="text" 
								   name="link_icon_color" 
								   value="<?php echo esc_attr( $link_icon_color ); ?>" 
								   class="blogshq-color-picker" />
							<p class="description">
								<?php esc_html_e( 'Choose the color for the link icon.', 'blogshq' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit" 
						   class="button-primary" 
						   name="blogshq_save_toc" 
						   value="<?php esc_attr_e( 'Save Settings', 'blogshq' ); ?>">
				</p>
			</form>

			<div class="blogshq-info-box" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #2E62E9;">
				<h3><?php esc_html_e( 'Shortcode Usage:', 'blogshq' ); ?></h3>
				<p><?php esc_html_e( 'Use this shortcode to manually insert a table of contents:', 'blogshq' ); ?></p>
				<code>[blogshq_toc]</code>
				<p style="margin-top: 10px;">
					<strong><?php esc_html_e( 'Note:', 'blogshq' ); ?></strong>
					<?php esc_html_e( 'On mobile devices, the TOC is automatically inserted before the first heading.', 'blogshq' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Save TOC settings.
	 *
	 * @since 1.0.0
	 */
	private function save_settings() {
		// Verify nonce
		if ( ! isset( $_POST['blogshq_toc_nonce'] ) || 
			 ! wp_verify_nonce( $_POST['blogshq_toc_nonce'], 'blogshq_toc_settings' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'blogshq' ) );
		}

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'blogshq' ) );
		}

		try {
			// Save TOC headings
			$checked = isset( $_POST['toc_headings'] ) && is_array( $_POST['toc_headings'] )
				? array_intersect( $_POST['toc_headings'], array( 'h2', 'h3', 'h4', 'h5', 'h6' ) )
				: array();
			update_option( 'blogshq_toc_headings', $checked );

			// Save link icon options
			$link_icon_enabled = isset( $_POST['link_icon_enabled'] );
			update_option( 'blogshq_toc_link_icon_enabled', $link_icon_enabled );

			$icon_headings = isset( $_POST['link_icon_headings'] ) && is_array( $_POST['link_icon_headings'] )
				? array_intersect( $_POST['link_icon_headings'], array( 'h2', 'h3', 'h4', 'h5', 'h6' ) )
				: array();
			update_option( 'blogshq_toc_link_icon_headings', $icon_headings );

			$color = isset( $_POST['link_icon_color'] ) ? sanitize_hex_color( $_POST['link_icon_color'] ) : '#2E62E9';
			update_option( 'blogshq_toc_link_icon_color', $color );

			// Clear heading regex cache
			$this->clear_heading_regex_cache();

			add_settings_error(
				'blogshq_messages',
				'blogshq_message',
				__( 'TOC settings saved successfully.', 'blogshq' ),
				'updated'
			);
		} catch ( Exception $e ) {
			add_settings_error(
				'blogshq_messages',
				'blogshq_message',
				__( 'Error saving TOC settings.', 'blogshq' ),
				'error'
			);
		}

		settings_errors( 'blogshq_messages' );
	}

	/**
	 * Generate TOC HTML.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @return string TOC HTML output.
	 */
	private function generate_toc( $post_id ) {
		$post = get_post( $post_id );
		if ( empty( $post ) ) {
			return '';
		}

		$tag_regex = $this->get_heading_regex();
		if ( empty( $tag_regex ) ) {
			return '';
		}

		$content = $post->post_content;
		preg_match_all( '/<(' . $tag_regex . ')[^>]*>(.*?)<\/\1>/i', $content, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return '';
		}

		$toc_output = '<div class="blogshq-toc"><strong>' . esc_html__( 'In This Article', 'blogshq' ) . '</strong><ul>';
		
		foreach ( $matches as $heading ) {
			$heading_text = strip_tags( $heading[2] );
			$anchor       = sanitize_title( $heading_text );
			$toc_output  .= '<li><a href="#' . esc_attr( $anchor ) . '">' . esc_html( $heading_text ) . '</a></li>';
		}
		
		$toc_output .= '</ul></div>';

		return apply_filters( 'blogshq_toc_output', $toc_output, $matches );
	}

	/**
	 * Get cached TOC.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @return string Cached or generated TOC.
	 */
	private function get_cached_toc( $post_id ) {
		$transient_key = 'blogshq_toc_' . $post_id;
		$toc           = get_transient( $transient_key );

		if ( false === $toc ) {
			$toc = $this->generate_toc( $post_id );
			set_transient( $transient_key, $toc, HOUR_IN_SECONDS );
		}

		return $toc;
	}

	/**
	 * Get cached TOC settings
	 *
	 * @return array TOC settings
	 */
	private function get_toc_settings() {
		$cache_key = 'blogshq_toc_settings';
		$settings = wp_cache_get( $cache_key );
		
		if ( false === $settings ) {
			$settings = array(
				'headings'   => get_option( 'blogshq_toc_headings', array( 'h2', 'h3', 'h4', 'h5', 'h6' ) ),
				'link_icon_enabled' => get_option( 'blogshq_toc_link_icon_enabled', false ),
				'link_icon_headings' => get_option( 'blogshq_toc_link_icon_headings', array( 'h2' ) ),
				'link_icon_color' => get_option( 'blogshq_toc_link_icon_color', '#2E62E9' ),
			);
			
			wp_cache_set( $cache_key, $settings, '', HOUR_IN_SECONDS );
		}
		
		return $settings;
	}

	/**
	 * Clear settings cache when updated
	 */
	public function clear_settings_cache() {
		wp_cache_delete( 'blogshq_toc_settings' );
		$this->clear_heading_regex_cache();
	}

	/**
	 * Clear TOC cache on post update.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 */
	public function clear_toc_cache( $post_id ) {
		delete_transient( 'blogshq_toc_' . $post_id );
	}

	/**
	 * Render shortcode.
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes.
	 * @return string TOC HTML.
	 */
	public function render_shortcode( $atts ) {
		global $post;
		
		if ( empty( $post ) ) {
			return '';
		}

		return $this->get_cached_toc( $post->ID );
	}

	/**
 * Insert TOC before first heading and add anchors.
 *
 * @since 1.0.0
 * @param string $content Post content.
 * @return string Modified content.
 */
public function insert_toc_and_anchors( $content ) {
	if ( ! is_singular( 'post' ) || is_admin() ) {
		return $content;
	}

	$tag_regex = $this->get_heading_regex();
	if ( empty( $tag_regex ) ) {
		return $content;
	}

	// Add anchors with clean IDs
	$content = preg_replace_callback(
		'/<(' . $tag_regex . ')([^>]*)>(.*?)<\/\1>/i',
		function ( $m ) {
			$heading_text = strip_tags( $m[3] );
			$anchor = sanitize_title( remove_accents( $heading_text ) );
			
			// Clean up the anchor
			$anchor = preg_replace( '/^(aioseo-|toc-)/', '', $anchor );
			$anchor = preg_replace( '/-\d+$/', '', $anchor );
			$anchor = preg_replace( '/[^a-z0-9-]/', '', strtolower( $anchor ) );
			
			// Check if heading already has an ID
			if ( preg_match( '/id=["\']([^"\']+)["\']/', $m[2], $id_match ) ) {
				// Use existing ID but clean it
				$existing_id = preg_replace( '/^(aioseo-|toc-)/', '', $id_match[1] );
				$existing_id = preg_replace( '/-\d+$/', '', $existing_id );
				$existing_id = preg_replace( '/[^a-z0-9-]/', '', strtolower( $existing_id ) );
				
				// Replace with cleaned ID
				$attributes = preg_replace( 
					'/id=["\'][^"\']+["\']/', 
					'id="' . esc_attr( $existing_id ) . '"', 
					$m[2] 
				);
				return '<' . $m[1] . $attributes . '>' . $m[3] . '</' . $m[1] . '>';
			} else {
				// Add new ID
				return '<' . $m[1] . $m[2] . ' id="' . esc_attr( $anchor ) . '">' . $m[3] . '</' . $m[1] . '>';
			}
		},
		$content
	);

	// Insert TOC before first heading (only on mobile)
	if ( wp_is_mobile() ) {
		$toc     = do_shortcode( '[blogshq_toc]' );
		$content = preg_replace( '/(<(' . $tag_regex . ')[^>]*>)/i', $toc . '$1', $content, 1 );
	}

	return $content;
}

	/**
	 * Enqueue link icon script.
	 *
	 * @since 1.0.0
	 */
public function enqueue_link_icon_script() {
	if ( ! is_singular( 'post' ) || ! get_option( 'blogshq_toc_link_icon_enabled', false ) ) {
		return;
	}

	// Cache all TOC settings in one query
	$toc_settings = wp_cache_get( 'blogshq_toc_settings' );
	
	if ( false === $toc_settings ) {
		$toc_settings = array(
			'headings'   => get_option( 'blogshq_toc_link_icon_headings', array( 'h2' ) ),
			'icon_color' => get_option( 'blogshq_toc_link_icon_color', '#2E62E9' ),
		);
		wp_cache_set( 'blogshq_toc_settings', $toc_settings, '', HOUR_IN_SECONDS );
	}

	if ( ! is_array( $toc_settings['headings'] ) || empty( $toc_settings['headings'] ) ) {
		return;
	}

	// Enqueue the JavaScript file
	wp_enqueue_script(
		'blogshq-link-icon',
		BLOGSHQ_PLUGIN_URL . 'assets/js/link-icon.min.js',
		array(),
		BLOGSHQ_VERSION,
		true
	);

	// Localize script with settings
	wp_localize_script(
		'blogshq-link-icon',
		'blogshqLinkIcon',
		array(
			'headings'   => $toc_settings['headings'],
			'iconColor'  => $toc_settings['icon_color'],
			'copiedText' => __( 'The link has been copied to your clipboard.', 'blogshq' ),
			'copyLabel'  => __( 'Copy link to this section', 'blogshq' ),
		)
	);
}

/**
 * Clear all plugin caches.
 *
 * @since 1.0.0
 */
public function clear_all_caches() {
    // Clear transients
    global $wpdb;
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_blogshq_%' 
        OR option_name LIKE '_transient_timeout_blogshq_%'"
    );
    
    // Clear object cache
    wp_cache_flush();
    
    // Clear heading regex cache
    $this->clear_heading_regex_cache();
}

} 
