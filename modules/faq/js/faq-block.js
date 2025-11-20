(function(blocks, element, blockEditor, components, i18n) {
    var el = element.createElement;
    var RichText = blockEditor.RichText;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var Button = components.Button;
    var __ = i18n.__;

    blocks.registerBlockType('blogshq/faq-block', {
        title: __('FAQ Block', 'blogshq'),
        icon: 'editor-help',
        category: 'common',
        attributes: {
            faqs: {
                type: 'array',
                default: [{
                    question: 'What is your question?',
                    answer: 'Your answer goes here.'
                }]
            }
        },

        edit: function(props) {
            var faqs = props.attributes.faqs;

            function updateQuestion(index, value) {
                var newFaqs = [...faqs];
                newFaqs[index].question = value;
                props.setAttributes({ faqs: newFaqs });
            }

            function updateAnswer(index, value) {
                var newFaqs = [...faqs];
                newFaqs[index].answer = value;
                props.setAttributes({ faqs: newFaqs });
            }

            function addFaq() {
                var newFaqs = [...faqs, { question: 'New question?', answer: 'New answer.' }];
                props.setAttributes({ faqs: newFaqs });
            }

            function removeFaq(index) {
                var newFaqs = faqs.filter(function(faq, i) { return i !== index; });
                props.setAttributes({ faqs: newFaqs });
            }

            return el('div', { className: 'blogshq-faq-block-editor' }, [
                faqs.map(function(faq, index) {
                    return el('div', { className: 'faq-item-editor', key: index }, [
                        el(RichText, {
                            tagName: 'h3',
                            className: 'faq-question',
                            value: faq.question,
                            onChange: function(value) { updateQuestion(index, value); },
                            placeholder: __('Enter your question...', 'blogshq')
                        }),
                        el(RichText, {
                            tagName: 'div',
                            className: 'faq-answer',
                            value: faq.answer,
                            onChange: function(value) { updateAnswer(index, value); },
                            placeholder: __('Enter your answer...', 'blogshq')
                        }),
                        el(Button, {
                            isDestructive: true,
                            onClick: function() { removeFaq(index); },
                            text: __('Remove FAQ', 'blogshq')
                        })
                    ]);
                }),
                el(Button, {
                    isPrimary: true,
                    onClick: addFaq,
                    text: __('Add FAQ', 'blogshq')
                })
            ]);
        },

        save: function() {
            return null; // Rendered via PHP
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n
);
