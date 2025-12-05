<?php
/**
 * Category Logos Module
 *
 * @package    BlogsHQ
 * @subpackage BlogsHQ/modules/logos
 * @since      1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Category Logos functionality.
 */
class BlogsHQ_Logos {

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		// Register shortcode
		add_shortcode( 'blogshq_category_logo', array( $this, 'render_shortcode' ) );

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
	 * Get category logos with caching.
	 * PERFORMANCE: Warm up term meta cache to avoid N+1 queries
	 *
	 * @since 1.0.0
	 * @param int $category_id The category ID.
	 * @return array Array with 'light' and 'dark' logo URLs.
	 */
	private function get_category_logos( $category_id ) {
		$light = get_term_meta( $category_id, 'blogshq_logo_url_light', true );
		$dark  = get_term_meta( $category_id, 'blogshq_logo_url_dark', true );
		
		return array(
			'light' => $light ? esc_url( $light ) : '',
			'dark'  => $dark ? esc_url( $dark ) : '',
		);
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
		if ( isset( $_POST['blogshq_save_logos'] ) && 
		isset( $_POST['blogshq_logos_nonce'] ) && 
		wp_verify_nonce( $_POST['blogshq_logos_nonce'], 'blogshq_logos_settings' ) ) {
		$this->save_logos();
}

		$categories = get_transient( 'blogshq_categories' );
		if ( false === $categories ) {
			$categories = get_categories( array( 'hide_empty' => false ) );
			// PERFORMANCE: Extend cache duration since categories are rarely modified
			set_transient( 'blogshq_categories', $categories, DAY_IN_SECONDS );
		}


		?>
		<div class="blogshq-logos-settings">
			<h2><?php esc_html_e( 'Category Logos', 'blogshq' ); ?></h2>
			<p><?php esc_html_e( 'Assign light and dark mode logos to your categories. Use the shortcode <code> [blogshq_category_logo] </code> to display them.', 'blogshq' ); ?></p>
			
			<form method="post" action="">
				<?php wp_nonce_field( 'blogshq_logos_settings', 'blogshq_logos_nonce' ); ?>
				<input type="hidden" name="form_type" value="category_logo">
				<table class="widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 20%;"><?php esc_html_e( 'Category', 'blogshq' ); ?></th>
							<th style="width: 15%;"><?php esc_html_e( 'Slug', 'blogshq' ); ?></th>
							<th style="width: 45%;"><?php esc_html_e( 'Logo URLs', 'blogshq' ); ?></th>
							<th style="width: 20%;"><?php esc_html_e( 'Preview', 'blogshq' ); ?></th>
						</tr>
					</thead>
				<tbody>
					<?php foreach ( $categories as $cat ) : 
						// PERFORMANCE: Use helper method for consistent logo retrieval
						$logos  = $this->get_category_logos( $cat->term_id );
						$logo_light = $logos['light'];
						$logo_dark  = $logos['dark'];
						$cat_id     = absint( $cat->term_id );
						?>
							<tr>
								<td><strong><?php echo esc_html( $cat->name ); ?></strong></td>
								<td><code><?php echo esc_html( $cat->slug ); ?></code></td>
								<td>
									<label style="display: block; margin-bottom: 8px;">
										<span style="display: inline-block; width: 60px;"><?php esc_html_e( 'Light:', 'blogshq' ); ?></span>
										<input type="url" 
											   placeholder="<?php esc_attr_e( 'Light mode logo URL', 'blogshq' ); ?>"
											   name="logo_url_light[<?php echo esc_attr( $cat_id ); ?>]"
											   value="<?php echo esc_attr( $logo_light ); ?>" 
											   style="width: calc(100% - 70px);" />
									</label>
									<label style="display: block;">
										<span style="display: inline-block; width: 60px;"><?php esc_html_e( 'Dark:', 'blogshq' ); ?></span>
										<input type="url" 
											   placeholder="<?php esc_attr_e( 'Dark mode logo URL', 'blogshq' ); ?>"
											   name="logo_url_dark[<?php echo esc_attr( $cat_id ); ?>]"
											   value="<?php echo esc_attr( $logo_dark ); ?>" 
											   style="width: calc(100% - 70px);" />
									</label>
								</td>
								<td>
									<div style="display: flex; gap: 8px; align-items: center;">
										<?php if ( $logo_light ) : ?>
											<div title="<?php esc_attr_e( 'Light Logo', 'blogshq' ); ?>">
												<img src="<?php echo esc_url( $logo_light ); ?>" 
													 style="max-width: 48px; height: auto; border: 1px solid #ddd; padding: 4px;" 
													 alt="<?php esc_attr_e( 'Light logo', 'blogshq' ); ?>" />
											</div>
										<?php endif; ?>
										<?php if ( $logo_dark ) : ?>
											<div title="<?php esc_attr_e( 'Dark Logo', 'blogshq' ); ?>">
												<img src="<?php echo esc_url( $logo_dark ); ?>" 
													 style="max-width: 48px; height: auto; border: 1px solid #333; padding: 4px; background: #1e1e1e;" 
													 alt="<?php esc_attr_e( 'Dark logo', 'blogshq' ); ?>" />
											</div>
										<?php endif; ?>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				
				<p class="submit">
					<input type="submit" 
						   class="button-primary" 
						   name="blogshq_save_logos" 
						   value="<?php esc_attr_e( 'Save Logos', 'blogshq' ); ?>">
				</p>
			</form>

			<div class="blogshq-info-box" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #2E62E9;">
				<h3><?php esc_html_e( 'Shortcode Usage:', 'blogshq' ); ?></h3>
				<p><strong><?php esc_html_e( 'Display logo for current category or post:', 'blogshq' ); ?></strong></p>
				<code>[blogshq_category_logo]</code>
				
				<p style="margin-top: 15px;"><strong><?php esc_html_e( 'Display logo by category ID:', 'blogshq' ); ?></strong></p>
				<code>[blogshq_category_logo id="123"]</code>
				
				<p style="margin-top: 15px;"><strong><?php esc_html_e( 'Display logo by category slug:', 'blogshq' ); ?></strong></p>
				<code>[blogshq_category_logo slug="technology"]</code>
			</div>
		</div>
		<?php
	}

	/**
	 * Save logos from form submission.
	 *
	 * @since 1.0.0
	 */
	private function save_logos() {
		// Verify nonce
		if ( ! isset( $_POST['blogshq_logos_nonce'] ) || 
			 ! wp_verify_nonce( $_POST['blogshq_logos_nonce'], 'blogshq_logos_settings' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'blogshq' ) );
		}

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'blogshq' ) );
		}

		$categories = get_categories( array( 'hide_empty' => false ) );

		foreach ( $categories as $cat ) {
			$cat_id = absint( $cat->term_id );
			
			// Sanitize and save light logo
			$light_url = isset( $_POST['logo_url_light'][ $cat_id ] ) 
				? esc_url_raw( $_POST['logo_url_light'][ $cat_id ] ) 
				: '';
			
			// Sanitize and save dark logo
			$dark_url = isset( $_POST['logo_url_dark'][ $cat_id ] ) 
				? esc_url_raw( $_POST['logo_url_dark'][ $cat_id ] ) 
				: '';

			update_term_meta( $cat_id, 'blogshq_logo_url_light', $light_url );
			update_term_meta( $cat_id, 'blogshq_logo_url_dark', $dark_url );
		}

		// Show success message
		add_settings_error(
			'blogshq_messages',
			'blogshq_message',
			__( 'Logos saved successfully.', 'blogshq' ),
			'updated'
		);

		settings_errors( 'blogshq_messages' );
	}

	/**
	 * Render shortcode.
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'   => '',
				'slug' => '',
			),
			$atts,
			'blogshq_category_logo'
		);

		$cat = $this->get_category( $atts );

		if ( empty( $cat ) || ! isset( $cat->term_id ) ) {
			return '';
		}

		$logo_light = esc_url( get_term_meta( $cat->term_id, 'blogshq_logo_url_light', true ) );
		$logo_dark  = esc_url( get_term_meta( $cat->term_id, 'blogshq_logo_url_dark', true ) );

		if ( empty( $logo_light ) && empty( $logo_dark ) ) {
			return '';
		}

		$output = '<div class="blogshq-category-logo-wrapper">';

		if ( ! empty( $logo_light ) ) {
			$output .= sprintf(
				'<img src="%s" alt="%s" class="blogshq-category-logo blogshq-logo-light" loading="lazy" />',
				esc_url( $logo_light ),
				esc_attr( $cat->name )
			);
		}

		if ( ! empty( $logo_dark ) ) {
			$output .= sprintf(
				'<img src="%s" alt="%s" class="blogshq-category-logo blogshq-logo-dark" loading="lazy" />',
				esc_url( $logo_dark ),
				esc_attr( $cat->name )
			);
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Get category based on shortcode attributes.
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes.
	 * @return object|null Category object or null.
	 */
	private function get_category( $atts ) {
		// Get by ID
		if ( ! empty( $atts['id'] ) ) {
			$cat_id = absint( $atts['id'] );
			return get_category( $cat_id );
		}

		// Get by slug
		if ( ! empty( $atts['slug'] ) ) {
			return get_category_by_slug( $atts['slug'] );
		}

		// Get from current context
		if ( is_category() ) {
			return get_queried_object();
		}

		if ( is_single() ) {
			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				return $categories[0];
			}
		}

		return null;
	}
}