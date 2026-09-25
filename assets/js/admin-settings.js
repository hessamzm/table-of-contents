(function () {
    'use strict';

    var prefix = 'hessamzm_toc_settings';
    var form = document.querySelector('form[action="options.php"]');
    var preview = document.querySelector('.hessamzm-toc-live-preview .hessamzm-toc');
    var previewRoot = document.querySelector('.hessamzm-toc-live-preview');

    if (!form || !preview || !previewRoot) {
        return;
    }

    var profile = previewRoot.dataset.previewProfile || 'post';
    var nested = profile === 'post' || profile === 'product';

    function field(key) {
        var name = nested
            ? prefix + '[' + profile + '_toc][' + key + ']'
            : prefix + '[' + key + ']';
        return form.querySelector('[name="' + name + '"]');
    }

    function checked(key) {
        var input = field(key);
        return !!input && input.checked;
    }

    function value(key, fallback) {
        var input = field(key);
        return input && input.value ? input.value : fallback;
    }

    function update() {
        var style = value('style', 'paper');
        var position = value('position', 'right');
        var titleElement = preview.querySelector('.hessamzm-toc__title');
        var toggle = preview.querySelector('.hessamzm-toc__toggle');
        var title = value('title', '') || titleElement.dataset.defaultTitle;
        var moreText = value('more_text', '') || toggle.dataset.expandLabel;
        var lessText = value('less_text', '') || toggle.dataset.collapseLabel;

        preview.classList.remove(
            'hessamzm-toc--classic',
            'hessamzm-toc--minimal',
            'hessamzm-toc--card',
            'hessamzm-toc--paper',
            'hessamzm-toc--position-left',
            'hessamzm-toc--position-right',
            'hessamzm-toc--numbered',
            'hessamzm-toc--sticky'
        );
        preview.classList.add('hessamzm-toc--' + style);
        preview.classList.add('hessamzm-toc--position-' + position);

        if (checked('show_numbers')) {
            preview.classList.add('hessamzm-toc--numbered');
        }

        if (checked('sticky_toc')) {
            preview.classList.add('hessamzm-toc--sticky');
        }

        preview.style.setProperty('--hessamzm-toc-background-color', value('background_color', '#ffffff'));
        preview.style.setProperty('--hessamzm-toc-text-color', value('text_color', '#1d2327'));
        preview.style.setProperty('--hessamzm-toc-link-color', value('link_color', '#2271b1'));
        preview.style.setProperty('--hessamzm-toc-border-color', value('border_color', '#dcdcde'));
        preview.style.setProperty('--hessamzm-toc-font-size', value('font_size', '16px'));
        preview.style.setProperty('--hessamzm-toc-sticky-font-size', value('sticky_font_size', '14px'));
        preview.style.setProperty('--hessamzm-toc-indentation', value('indentation', '1.5rem'));
        preview.style.setProperty('--hessamzm-toc-border-radius', value('border_radius', '0px'));

        titleElement.textContent = title;
        toggle.textContent = moreText;
        toggle.dataset.expandLabel = moreText;
        toggle.dataset.collapseLabel = lessText;
    }

    form.querySelectorAll('input, select').forEach(function (input) {
        input.addEventListener('input', update);
        input.addEventListener('change', update);
    });

    update();

    function initializeShortcodeGenerators() {
        document.querySelectorAll('.hessamzm-toc-shortcode-generator').forEach(function (generator) {
            var tag = generator.dataset.shortcodeTag || '';
            var defaultLevels = [];

            try {
                defaultLevels = JSON.parse(generator.dataset.defaultLevels || '[]');
            } catch (error) {
                defaultLevels = [];
            }

            var defaults = {
                title: generator.dataset.defaultTitle || '',
                style: generator.dataset.defaultStyle || 'paper',
                numbers: generator.dataset.defaultNumbers === '1',
                sticky: generator.dataset.defaultSticky === '1',
                position: generator.dataset.defaultPosition || 'right',
                placement: generator.dataset.defaultPlacement || '',
                background: generator.dataset.defaultBackground || '#ffffff',
                text_color: generator.dataset.defaultTextColor || '#1d2327',
                link_color: generator.dataset.defaultLinkColor || '#2271b1',
                border_color: generator.dataset.defaultBorderColor || '#dcdcde',
                font_size: generator.dataset.defaultFontSize || '16px',
                sticky_font_size: generator.dataset.defaultStickyFontSize || '14px',
                indentation: generator.dataset.defaultIndentation || '1.5rem',
                border_radius: generator.dataset.defaultBorderRadius || '0px',
                more_text: generator.dataset.defaultMoreText || '',
                less_text: generator.dataset.defaultLessText || ''
            };

            var output = generator.querySelector('.hessamzm-toc-shortcode-output');
            var copyButton = generator.querySelector('.hessamzm-toc-shortcode-copy');

            if (!tag || !output) {
                return;
            }

            function currentLevels() {
                return Array.prototype.map.call(
                    generator.querySelectorAll('.hessamzm-toc-shortcode-level:checked'),
                    function (input) {
                        return parseInt(input.value, 10);
                    }
                ).filter(function (level) {
                    return level >= 1 && level <= 6;
                }).sort(function (a, b) {
                    return a - b;
                });
            }

            function sameLevels(left, right) {
                if (left.length !== right.length) {
                    return false;
                }

                return left.every(function (level, index) {
                    return level === right[index];
                });
            }

            function escapeAttribute(value) {
                return String(value).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
            }

            function inputValue(selector, fallback) {
                var input = generator.querySelector(selector);
                return input ? input.value : fallback;
            }

            function checked(selector, fallback) {
                var input = generator.querySelector(selector);
                return input ? !!input.checked : fallback;
            }

            function addIfChanged(attributes, name, value, defaultValue) {
                if (String(value) !== String(defaultValue)) {
                    attributes.push(name + '="' + escapeAttribute(value) + '"');
                }
            }

            function build() {
                var attributes = [];
                var levels = currentLevels();

                if (!sameLevels(levels, defaultLevels)) {
                    attributes.push('levels="' + escapeAttribute(levels.join(',')) + '"');
                }

                addIfChanged(attributes, 'title', inputValue('.hessamzm-toc-shortcode-title', defaults.title), defaults.title);
                addIfChanged(attributes, 'style', inputValue('.hessamzm-toc-shortcode-style', defaults.style), defaults.style);

                var numbers = checked('.hessamzm-toc-shortcode-numbers', defaults.numbers);
                if (numbers !== defaults.numbers) {
                    attributes.push('numbers="' + (numbers ? 'true' : 'false') + '"');
                }

                var sticky = checked('.hessamzm-toc-shortcode-sticky', defaults.sticky);
                if (sticky !== defaults.sticky) {
                    attributes.push('sticky="' + (sticky ? 'true' : 'false') + '"');
                }

                addIfChanged(attributes, 'position', inputValue('.hessamzm-toc-shortcode-position', defaults.position), defaults.position);
                addIfChanged(attributes, 'placement', inputValue('.hessamzm-toc-shortcode-placement', defaults.placement), defaults.placement);
                addIfChanged(attributes, 'background', inputValue('.hessamzm-toc-shortcode-background_color', defaults.background), defaults.background);
                addIfChanged(attributes, 'text_color', inputValue('.hessamzm-toc-shortcode-text_color', defaults.text_color), defaults.text_color);
                addIfChanged(attributes, 'link_color', inputValue('.hessamzm-toc-shortcode-link_color', defaults.link_color), defaults.link_color);
                addIfChanged(attributes, 'border_color', inputValue('.hessamzm-toc-shortcode-border_color', defaults.border_color), defaults.border_color);
                addIfChanged(attributes, 'font_size', inputValue('.hessamzm-toc-shortcode-font_size', defaults.font_size), defaults.font_size);
                addIfChanged(attributes, 'sticky_font_size', inputValue('.hessamzm-toc-shortcode-sticky_font_size', defaults.sticky_font_size), defaults.sticky_font_size);
                addIfChanged(attributes, 'indentation', inputValue('.hessamzm-toc-shortcode-indentation', defaults.indentation), defaults.indentation);
                addIfChanged(attributes, 'border_radius', inputValue('.hessamzm-toc-shortcode-border_radius', defaults.border_radius), defaults.border_radius);
                addIfChanged(attributes, 'more_text', inputValue('.hessamzm-toc-shortcode-more-text', defaults.more_text), defaults.more_text);
                addIfChanged(attributes, 'less_text', inputValue('.hessamzm-toc-shortcode-less-text', defaults.less_text), defaults.less_text);

                output.value = attributes.length
                    ? '[' + tag + ' ' + attributes.join(' ') + ']'
                    : '[' + tag + ']';
            }

            function copy() {
                if (!copyButton || copyButton.disabled) {
                    return;
                }

                var value = output.value;
                var copied = false;

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(value).then(function () {
                        copyButton.textContent = copyButton.dataset.copiedLabel || copyButton.textContent;
                        window.setTimeout(function () {
                            copyButton.textContent = copyButton.dataset.copyLabel || copyButton.textContent;
                        }, 1600);
                    }).catch(function () {});
                    return;
                }

                output.focus();
                output.select();

                if (document.execCommand) {
                    try {
                        copied = document.execCommand('copy');
                    } catch (error) {
                        copied = false;
                    }
                }

                if (copied) {
                    copyButton.textContent = copyButton.dataset.copiedLabel || copyButton.textContent;
                    window.setTimeout(function () {
                        copyButton.textContent = copyButton.dataset.copyLabel || copyButton.textContent;
                    }, 1600);
                }
            }

            generator.querySelectorAll('input, select').forEach(function (input) {
                input.addEventListener('input', build);
                input.addEventListener('change', build);
            });

            if (copyButton) {
                copyButton.addEventListener('click', copy);
            }

            build();
        });
    }

    initializeShortcodeGenerators();

})();
