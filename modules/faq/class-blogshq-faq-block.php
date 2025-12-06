<?php
/**
 * FAQ Block Module
 *
 * @package    BlogsHQ
 * @subpackage BlogsHQ/modules/faq
 * @since      1.0.0
 */

if (!defined('WPINC')) {
    die;
}

/**
 * FAQ Block functionality.
 */
class BlogsHQ_FAQ_Block {

    /**
     * Initialize the class.
     *
     * @since 1.0.0
     */
    public function init() {
        add_action('init', array($this, 'register_block'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));
    }

    /**
     * Register FAQ block.
     *
     * @since 1.0.0
     */
    public function register_block() {
        register_block_type('blogshq/faq-block', array(
            'render_callback' => array($this, 'render_block'),
            'attributes'      => array(
                'faqs' => array(
                    'type'    => 'array',
                    'default' => array(
                        array(
                            'question' => __('What is your question?', 'blogshq'),
                            'answer'   => __('Your answer goes here.', 'blogshq'),
                        ),
                    ),
                ),
            ),
        ));
    }

    /**
     * Enqueue block editor assets.
     *
     * @since 1.0.0
     */
    public function enqueue_editor_assets() {
        wp_enqueue_script(
            'blogshq-faq-block',
            BLOGSHQ_PLUGIN_URL . 'modules/faq/js/faq-block.js',
            array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n'),
            BLOGSHQ_VERSION,
            true
        );

        wp_enqueue_style(
            'blogshq-faq-block-editor',
            BLOGSHQ_PLUGIN_URL . 'modules/faq/css/faq-block-editor.css',
            array('wp-edit-blocks'),
            BLOGSHQ_VERSION
        );
    }

    /**
     * Render FAQ block on frontend.
     *
     * @since 1.0.0
     * @param array $attributes Block attributes.
     * @return string Block HTML output.
     */
    public function render_block($attributes) {
        $faqs = isset($attributes['faqs']) ? $attributes['faqs'] : array();

        if (empty($faqs)) {
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
            foreach ($faqs as $index => $faq):
                $question = isset($faq['question']) ? $faq['question'] : '';
                $answer   = isset($faq['answer']) ? $faq['answer'] : '';
                $faq_id   = 'faq-' . sanitize_title($question) . '-' . $index;

                // Add to schema
                $schema['mainEntity'][] = array(
                    '@type'          => 'Question',
                    'name'           => strip_tags($question),
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => strip_tags($answer),
                    ),
                );
                ?>
                <div class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                    <h3 class="faq-question" id="<?php echo esc_attr($faq_id); ?>" itemprop="name">
                        <?php echo wp_kses_post($question); ?>
                    </h3>
                    <div class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                        <div itemprop="text">
                            <?php echo wp_kses_post($answer); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- FAQ Schema JSON-LD -->
        <script type="application/ld+json">
        <?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
        </script>
        <?php

        return ob_get_clean();
    }
}