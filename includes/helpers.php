<?php
/**
 * FILE: includes/helpers.php
 * Helper functions used throughout the plugin.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Check if current user has required capability.
 *
 * @since 1.0.0
 * @param string $capability The capability to check.
 * @return bool
 */
function blogshq_current_user_can( string $capability = 'manage_options' ): bool {
	return current_user_can( $capability );
}

/**
 * Get plugin option with default fallback.
 *
 * @since 1.0.0
 * @param string $option_name The option name.
 * @param mixed  $default     Default value if option doesn't exist.
 * @return mixed
 */
function blogshq_get_option( string $option_name, $default = false ) {
	return get_option( $option_name, $default );
}

/**
 * Sanitize hex color.
 *
 * @since 1.0.0
 * @param string $color The color to sanitize.
 * @return string
 */
function blogshq_sanitize_hex_color( string $color ): string {
	if ( '' === $color ) {
		return '';
	}

	// Remove # if present
	$color = ltrim( $color, '#' );

	// Validate hex color
	if ( preg_match( '/^[a-fA-F0-9]{6}$/', $color ) ) {
		return '#' . $color;
	} elseif ( preg_match( '/^[a-fA-F0-9]{3}$/', $color ) ) {
		return '#' . $color;
	}

	return '#f2a200'; // Default color
}

/**
 * Log debug message (only if WP_DEBUG is true).
 *
 * @since 1.0.0
 * @param mixed $message The message to log.
 */
function blogshq_log( $message ): void {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
		if ( is_array( $message ) || is_object( $message ) ) {
			error_log( 'BlogsHQ: ' . print_r( $message, true ) );
		} else {
			error_log( 'BlogsHQ: ' . $message );
		}
	}
}

/**
 * Get template part.
 *
 * @since 1.0.0
 * @param string $slug The template slug.
 * @param string $name Optional. Template name.
 * @param array  $args Optional. Arguments to pass to template.
 */
function blogshq_get_template( $slug, $name = null, $args = array() ) {
	// Whitelist allowed templates
	$allowed_templates = array( 'dashboard', 'logos', 'toc', 'faq', 'ai-share' );
	
	if ( ! in_array( $slug, $allowed_templates, true ) ) {
		blogshq_log( 'Attempted to load unauthorized template: ' . $slug );
		return;
	}

	$templates = array();
	$name      = (string) $name;

	// Validate name parameter - only alphanumeric and hyphens
	if ( '' !== $name && ! preg_match( '/^[a-z0-9-]+$/i', $name ) ) {
		blogshq_log( 'Invalid template name: ' . $name );
		return;
	}

	if ( '' !== $name ) {
		$templates[] = "{$slug}-{$name}.php";
	}

	$templates[] = "{$slug}.php";

	if ( ! empty( $args ) && is_array( $args ) ) {
		foreach ( $args as $key => $value ) {
			${$key} = $value;
		}
	}

	$located = false;

	foreach ( $templates as $template ) {
		$file = BLOGSHQ_PLUGIN_DIR . 'admin/views/' . $template;
		if ( file_exists( $file ) ) {
			$located = $file;
			break;
		}
	}

	if ( $located ) {
		include $located;
	}
}

/**
 * Validate and sanitize settings array.
 *
 * @since 1.0.0
 * @param array $settings    Settings array to validate.
 * @param array $allowed_keys Allowed setting keys.
 * @return array Sanitized settings array.
 */
function blogshq_validate_settings( array $settings, array $allowed_keys ): array {
	$validated = array();

	foreach ( $allowed_keys as $key ) {
		if ( isset( $settings[ $key ] ) ) {
			$validated[ $key ] = $settings[ $key ];
		}
	}

	return $validated;
}