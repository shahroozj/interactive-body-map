/**
 * The "Body Map" block.
 *
 * No build step: plain ES5 against the wp.* globals, so the plugin can be
 * installed from a zip and edited in place.
 */
(function (blocks, element, blockEditor, components, serverSideRender, i18n) {
    'use strict';

    var el = element.createElement;
    var __ = i18n.__;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var ToggleControl = components.ToggleControl;
    var ExternalLink = components.ExternalLink;

    var config = window.BODYMAP_BLOCK || { modes: {}, settingsUrl: '' };

    function modeOptions() {
        var options = [{ label: __('Use the saved setting', 'interactive-body-map'), value: '' }];

        Object.keys(config.modes).forEach(function (value) {
            options.push({ label: config.modes[value], value: value });
        });

        return options;
    }

    function toggle(props, attribute, label, help) {
        return el(ToggleControl, {
            label: label,
            help: help,
            checked: !!props.attributes[attribute],
            onChange: function (value) {
                var next = {};
                next[attribute] = value;
                props.setAttributes(next);
            }
        });
    }

    blocks.registerBlockType('interactive-body-map/body-map', {
        title: __('Body Map', 'interactive-body-map'),
        description: __('A clickable body diagram. Links are set once under Settings > Body Map.', 'interactive-body-map'),
        icon: 'universal-access',
        category: 'widgets',
        keywords: [
            __('anatomy', 'interactive-body-map'),
            __('body', 'interactive-body-map'),
            __('medical', 'interactive-body-map')
        ],
        supports: { html: false },

        edit: function (props) {
            var attributes = props.attributes;

            var inspector = el(
                InspectorControls,
                null,
                el(
                    PanelBody,
                    { title: __('Layout', 'interactive-body-map') },
                    el(SelectControl, {
                        label: __('Regions', 'interactive-body-map'),
                        value: attributes.mode,
                        options: modeOptions(),
                        onChange: function (value) { props.setAttributes({ mode: value }); }
                    }),
                    el(TextControl, {
                        label: __('Maximum width', 'interactive-body-map'),
                        help: __('Any CSS length, for example 340px. Leave empty for the saved setting.', 'interactive-body-map'),
                        value: attributes.maxWidth,
                        onChange: function (value) { props.setAttributes({ maxWidth: value }); }
                    }),
                    el(SelectControl, {
                        label: __('Alignment', 'interactive-body-map'),
                        value: attributes.align,
                        options: [
                            { label: __('Use the saved setting', 'interactive-body-map'), value: '' },
                            { label: __('Left', 'interactive-body-map'), value: 'left' },
                            { label: __('Centre', 'interactive-body-map'), value: 'center' },
                            { label: __('Right', 'interactive-body-map'), value: 'right' }
                        ],
                        onChange: function (value) { props.setAttributes({ align: value }); }
                    })
                ),
                el(
                    PanelBody,
                    { title: __('Behaviour', 'interactive-body-map') },
                    toggle(props, 'tooltip', __('Tooltip', 'interactive-body-map'),
                        __('Show the region name on hover and on focus.', 'interactive-body-map')),
                    toggle(props, 'selectable', __('Keep the clicked region highlighted', 'interactive-body-map')),
                    toggle(props, 'showDisabled', __('Draw regions with no link', 'interactive-body-map')),
                    toggle(props, 'showList', __('Add a plain list of links below', 'interactive-body-map')),
                    toggle(props, 'outline', __('Outline style', 'interactive-body-map'),
                        __('Fill regions only while they are hovered.', 'interactive-body-map'))
                ),
                el(
                    PanelBody,
                    { title: __('Links', 'interactive-body-map'), initialOpen: false },
                    el('p', null, __('Each part of the body is linked once for the whole site.', 'interactive-body-map')),
                    el(ExternalLink, { href: config.settingsUrl },
                        __('Edit the links', 'interactive-body-map'))
                )
            );

            return el(
                'div',
                blockEditor.useBlockProps ? blockEditor.useBlockProps() : {},
                inspector,
                el(serverSideRender, {
                    block: 'interactive-body-map/body-map',
                    attributes: attributes
                })
            );
        },

        // Rendered by PHP on every request, so nothing is stored in post content.
        save: function () { return null; }
    });
}(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.serverSideRender,
    window.wp.i18n
));
