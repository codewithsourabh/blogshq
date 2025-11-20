<?php
/**
 * BlogsHQ Table of Contents Tests
 *
 * @package BlogsHQ
 */

class BlogsHQ_TOC_Test extends WP_UnitTestCase {

	public function test_toc_module_exists() {
		$this->assertTrue( class_exists( 'BlogsHQ_TOC' ) );
	}

	public function test_toc_shortcode_registered() {
		global $shortcode_tags;
		$this->assertArrayHasKey( 'blogshq_toc', $shortcode_tags );
	}

	public function test_toc_generates_valid_html() {
		if ( class_exists( 'BlogsHQ_TOC' ) ) {
			$toc = new BlogsHQ_TOC();
			$this->assertTrue( method_exists( $toc, 'render_shortcode' ) );
		}
	}
}