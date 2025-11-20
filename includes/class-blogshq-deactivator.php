<?php

/**
 * FILE: includes/class-blogshq-deactivator.php
 * Fired during plugin deactivation.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class BlogsHQ_Deactivator {

	/**
	 * Deactivation tasks.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Clear all transients
		self::clear_transients();

		// Flush rewrite rules
		flush_rewrite_rules();

		// Clear scheduled cron jobs if any
		// wp_clear_scheduled_hook( 'blogshq_cron_hook' );
	}

	/**
	 * Clear all plugin transients.
	 *
	 * @since 1.0.0
	 */
	private static function clear_transients() {
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
	}
}

// ============================================================================
