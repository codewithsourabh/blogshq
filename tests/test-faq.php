<?php
/**
 * BlogsHQ FAQ Block Tests
 *
 * @package BlogsHQ
 */

class BlogsHQ_FAQ_Test extends WP_UnitTestCase {

	/**
	 * Test FAQ block registration
	 */
	public function test_faq_block_registered() {
		$this->assertTrue( function_exists( 'register_block_type' ) );
	}

	/**
	 * Test FAQ block rendering
	 */
	public function test_faq_block_rendering() {
		if ( class_exists( 'BlogsHQ_FAQ_Block' ) ) {
			$faq_block = new BlogsHQ_FAQ_Block();
			$this->assertTrue( method_exists( $faq_block, 'render_block' ) );
		}
	}
}