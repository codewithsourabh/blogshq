<?php
/**
 * FAQ Block Rendering
 *
 * @package BlogsHQ
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Render FAQ block callback
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 * @param object $block Block object.
 * @return string Block HTML output.
 */
function blogshq_render_faq_block( $attributes, $content, $block ) {
	if ( ! isset( $attributes['faqs'] ) || ! is_array( $attributes['faqs'] ) ) {
		return '';
	}

	$faqs = $attributes['faqs'];

	if ( empty( $faqs ) ) {
		return '';
	}

	// Build FAQ Schema JSON-LD
	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array(),
	);

	ob_start();
	?>
	<div class="blogshq-faq-block faq-schema">
		<?php
		foreach ( $faqs as $index => $faq ) :
			$question = isset( $faq['question'] ) ? $faq['question'] : '';
			$answer   = isset( $faq['answer'] ) ? $faq['answer'] : '';
			$faq_id   = 'faq-' . sanitize_title( $question ) . '-' . $index;

			// Add to schema
			$schema['mainEntity'][] = array(
				'@type'          => 'Question',
				'name'           => strip_tags( $question ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => strip_tags( $answer ),
				),
			);
			?>
			<div class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
				<h3 class="faq-question" id="<?php echo esc_attr( $faq_id ); ?>" itemprop="name">
					<?php echo wp_kses_post( $question ); ?>
				</h3>
				<div class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
					<div itemprop="text">
						<?php echo wp_kses_post( $answer ); ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- FAQ Schema JSON-LD -->
	<script type="application/ld+json">
	<?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>
	</script>
	<?php

	return ob_get_clean();
}