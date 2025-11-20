<?php
/**
 * BlogsHQ Logos Module Tests
 *
 * @package BlogsHQ
 */

class BlogsHQ_Logos_Test extends WP_UnitTestCase {

	public function test_logos_module_exists() {
		$this->assertTrue( class_exists( 'BlogsHQ_Logos' ) );
	}

	public function test_shortcode_registered() {
		global $shortcode_tags;
		$this->assertArrayHasKey( 'blogshq_category_logo', $shortcode_tags );
	}
}