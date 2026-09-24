(function (wp) {
    'use strict';

    var registerBlockType = wp.blocks.registerBlockType;
    var createElement = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var __ = wp.i18n.__;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var PanelBody = wp.components.PanelBody;
    var CheckboxControl = wp.components.CheckboxControl;
    var SelectControl = wp.components.SelectControl;
    var TextControl = wp.components.TextControl;
    var ToggleControl = wp.components.ToggleControl;

    var levels = [1, 2, 3, 4, 5, 6];

    function Edit(props) {
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;
        var selectedLevels = attributes.headingLevels || [];

        function toggleLevel(level, checked) {
            var next = selectedLevels.filter(function (value) {
                return value !== level;
            });

            if (checked) {
                next.push(level);
            }

            next.sort(function (a, b) {
                return a - b;
            });

            setAttributes({ headingLevels: next });
        }

        return createElement(
            Fragment,
            null,
            createElement(
                InspectorControls,
                null,
                createElement(
                    PanelBody,
                    {
                        title: __('Table of Contents', 'table-of-contents'),
                        initialOpen: true
                    },
                    createElement(TextControl, {
                        label: __('Title', 'table-of-contents'),
                        value: attributes.title || '',
                        onChange: function (value) {
                            setAttributes({ title: value });
                        },
                        help: __('Leave empty to use the global plugin title.', 'table-of-contents')
                    }),
                    createElement(SelectControl, {
                        label: __('Style', 'table-of-contents'),
                        value: attributes.style || '',
                        options: [
                            { label: __('Global setting', 'table-of-contents'), value: '' },
                            { label: __('Classic', 'table-of-contents'), value: 'classic' },
                            { label: __('Minimal', 'table-of-contents'), value: 'minimal' },
                            { label: __('Card', 'table-of-contents'), value: 'card' },
                            { label: __('Paper Menu', 'table-of-contents'), value: 'paper' }
                        ],
                        onChange: function (value) {
                            setAttributes({ style: value });
                        }
                    }),
                    createElement(ToggleControl, {
                        label: __('Show numbers', 'table-of-contents'),
                        checked: !!attributes.showNumbers,
                        onChange: function (value) {
                            setAttributes({ showNumbers: value });
                        },
                        help: __('Override the global numbering setting.', 'table-of-contents')
                    }),
                    createElement(
                        'p',
                        { className: 'components-base-control__help' },
                        __('Leave all heading levels unchecked to use the global selection.', 'table-of-contents')
                    ),
                    levels.map(function (level) {
                        return createElement(CheckboxControl, {
                            key: level,
                            label: 'H' + level,
                            checked: selectedLevels.indexOf(level) !== -1,
                            onChange: function (checked) {
                                toggleLevel(level, checked);
                            }
                        });
                    })
                )
            ),
            createElement(
                'div',
                useBlockProps({ className: 'hessamzm-toc-block-editor' }),
                createElement(
                    'strong',
                    null,
                    __('Table of Contents', 'table-of-contents')
                ),
                createElement(
                    'p',
                    null,
                    __('The table of contents will be generated on the front end from this post’s headings.', 'table-of-contents')
                )
            )
        );
    }

    registerBlockType('hessamzm/table-of-contents', {
        edit: Edit,
        save: function () {
            return null;
        }
    });
})(window.wp);
