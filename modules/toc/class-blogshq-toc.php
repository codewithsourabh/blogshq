<?php
/**
 * Table of Contents Module
 *
 * @package    BlogsHQ
 * @subpackage BlogsHQ/modules/toc
 * @since      1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class BlogsHQ_TOC {

	private static $heading_regex = null;

	public function init() {
		add_shortcode( 'blogshq_toc', array( $this, 'render_shortcode' ) );
		add_filter( 'the_content', array( $this, 'insert_toc_and_anchors' ), 10 );
		add_action( 'save_post', array( $this, 'clear_toc_cache' ) );
		add_action( 'update_option_blogshq_toc_headings', array( $this, 'clear_heading_regex_cache' ) );
		add_action( 'wp_footer', array( $this, 'enqueue_link_icon_script' ) );
		add_action( 'update_option_blogshq_toc_headings', array( $this, 'clear_settings_cache' ) );
		add_action( 'update_option_blogshq_toc_link_icon_enabled', array( $this, 'clear_settings_cache' ) );
		add_action( 'update_option_blogshq_toc_link_icon_headings', array( $this, 'clear_settings_cache' ) );
		add_action( 'update_option_blogshq_toc_link_icon_color', array( $this, 'clear_settings_cache' ) );
	}

	public function enqueue_frontend_styles() {
		global $post;
		
		$has_logo = has_shortcode( $post->post_content ?? '', 'blogshq_category_logo' );
		$has_toc = has_shortcode( $post->post_content ?? '', 'blogshq_toc' );
		$has_ai = has_shortcode( $post->post_content ?? '', 'ai_share' );
		$is_post = is_singular( 'post' );
	}

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

	public function clear_heading_regex_cache() {
		self::$heading_regex = null;
	}

	private function get_toc_settings() {
		$cache_key = 'blogshq_toc_settings_cache';
		$settings = wp_cache_get( $cache_key );
		
		if ( false === $settings ) {
			$settings = array(
				'headings'      => get_option( 'blogshq_toc_headings', array( 'h2', 'h3', 'h4', 'h5', 'h6' ) ),
				'link_icon'     => get_option( 'blogshq_toc_link_icon_enabled', false ),
				'icon_headings' => get_option( 'blogshq_toc_link_icon_headings', array( 'h2' ) ),
				'icon_color'    => get_option( 'blogshq_toc_link_icon_color', '#2E62E9' ),
			);
			wp_cache_set( $cache_key, $settings, '', HOUR_IN_SECONDS );
		}
		
		return $settings;
	}

	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'blogshq' ) );
		}

		if ( isset( $_POST['blogshq_save_toc'] ) ) {
			$this->save_settings();
		}

		$settings = $this->get_toc_settings();
		$selected_headings  = $settings['headings'];
		$link_icon_enabled  = $settings['link_icon'];
		$link_icon_headings = $settings['icon_headings'];
		$link_icon_color    = $settings['icon_color'];

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
				<input type="hidden" name="form_type" value="toc">
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

	private function save_settings() {
		if ( ! isset( $_POST['blogshq_toc_nonce'] ) || 
			 ! wp_verify_nonce( $_POST['blogshq_toc_nonce'], 'blogshq_toc_settings' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'blogshq' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'blogshq' ) );
		}

		try {
			$checked = isset( $_POST['toc_headings'] ) && is_array( $_POST['toc_headings'] )
				? array_intersect( $_POST['toc_headings'], array( 'h2', 'h3', 'h4', 'h5', 'h6' ) )
				: array();
			update_option( 'blogshq_toc_headings', $checked );

			$link_icon_enabled = isset( $_POST['link_icon_enabled'] );
			update_option( 'blogshq_toc_link_icon_enabled', $link_icon_enabled );

			$icon_headings = isset( $_POST['link_icon_headings'] ) && is_array( $_POST['link_icon_headings'] )
				? array_intersect( $_POST['link_icon_headings'], array( 'h2', 'h3', 'h4', 'h5', 'h6' ) )
				: array();
			update_option( 'blogshq_toc_link_icon_headings', $icon_headings );

			$color = isset( $_POST['link_icon_color'] ) ? sanitize_hex_color( $_POST['link_icon_color'] ) : '#2E62E9';
			update_option( 'blogshq_toc_link_icon_color', $color );

			$this->clear_heading_regex_cache();
			$this->clear_settings_cache();

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

	private function get_cached_toc( $post_id ) {
		$transient_key = 'blogshq_toc_' . $post_id;
		$toc           = get_transient( $transient_key );

		if ( false === $toc ) {
			$toc = $this->generate_toc( $post_id );
			set_transient( $transient_key, $toc, HOUR_IN_SECONDS );
		}

		return $toc;
	}

	public function clear_settings_cache() {
		wp_cache_delete( 'blogshq_toc_settings' );
		wp_cache_delete( 'blogshq_toc_settings_cache' );
		$this->clear_heading_regex_cache();
	}

	public function clear_toc_cache( $post_id ) {
		delete_transient( 'blogshq_toc_' . $post_id );
	}

	public function render_shortcode( $atts ) {
		global $post;
		
		if ( empty( $post ) ) {
			return '';
		}

		return $this->get_cached_toc( $post->ID );
	}

	public function insert_toc_and_anchors( $content ) {
		if ( ! is_singular( 'post' ) || is_admin() ) {
			return $content;
		}

		$tag_regex = $this->get_heading_regex();
		if ( empty( $tag_regex ) ) {
			return $content;
		}

		$used_ids = array();

		$content = preg_replace_callback(
			'/<(' . $tag_regex . ')([^>]*)>(.*?)<\/\1>/i',
			function ( $m ) use ( &$used_ids ) {
				$heading_text = strip_tags( $m[3] );
				$anchor = sanitize_title( remove_accents( $heading_text ) );
				$anchor = preg_replace( '/[^a-z0-9-]/', '', strtolower( $anchor ) );
				
				$original_anchor = $anchor;
				$counter = 1;
				while ( in_array( $anchor, $used_ids, true ) ) {
					$anchor = $original_anchor . '-' . $counter;
					$counter++;
				}
				$used_ids[] = $anchor;
				
				if ( preg_match( '/id=["\']([^"\']+)["\']/', $m[2], $id_match ) ) {
					return '<' . $m[1] . $m[2] . '>' . $m[3] . '</' . $m[1] . '>';
				}
				
				return '<' . $m[1] . $m[2] . ' id="' . esc_attr( $anchor ) . '">' . $m[3] . '</' . $m[1] . '>';
			},
			$content
		);

		if ( wp_is_mobile() ) {
			$toc     = do_shortcode( '[blogshq_toc]' );
			$content = preg_replace( '/(<(' . $tag_regex . ')[^>]*>)/i', $toc . '$1', $content, 1 );
		}

		return $content;
	}

	public function enqueue_link_icon_script() {
		if ( ! is_singular( 'post' ) ) {
			return;
		}

		$settings = $this->get_toc_settings();
		
		if ( ! $settings['link_icon'] ) {
			return;
		}

		if ( ! is_array( $settings['icon_headings'] ) || empty( $settings['icon_headings'] ) ) {
			return;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_script(
			'blogshq-link-icon',
			BLOGSHQ_PLUGIN_URL . "assets/js/link-icon{$suffix}.js",
			array(),
			BLOGSHQ_VERSION,
			true
		);

		wp_localize_script(
			'blogshq-link-icon',
			'blogshqLinkIcon',
			array(
				'headings'   => $settings['icon_headings'],
				'iconColor'  => $settings['icon_color'],
				'copiedText' => __( 'The link has been copied to your clipboard.', 'blogshq' ),
				'copyLabel'  => __( 'Copy link to this section', 'blogshq' ),
			)
		);
	}

	public function clear_all_caches() {
		global $wpdb;
		
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				OR option_name LIKE %s",
				$wpdb->esc_like( '_transient_blogshq_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_blogshq_' ) . '%'
			)
		);
		
		wp_cache_flush();
		$this->clear_heading_regex_cache();
		$this->clear_settings_cache();
	}
}