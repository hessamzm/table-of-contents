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

            var defaultTitle = generator.dataset.defaultTitle || '';
            var defaultStyle = generator.dataset.defaultStyle || 'paper';
            var defaultNumbers = generator.dataset.defaultNumbers === '1';
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

            function build() {
                var attributes = [];
                var levels = currentLevels();
                var titleInput = generator.querySelector('.hessamzm-toc-shortcode-title');
                var styleInput = generator.querySelector('.hessamzm-toc-shortcode-style');
                var numbersInput = generator.querySelector('.hessamzm-toc-shortcode-numbers');
                var title = titleInput ? titleInput.value.trim() : '';
                var style = styleInput ? styleInput.value : defaultStyle;
                var numbers = !!(numbersInput && numbersInput.checked);

                if (!sameLevels(levels, defaultLevels)) {
                    attributes.push('levels="' + escapeAttribute(levels.join(',')) + '"');
                }

                if (title !== defaultTitle) {
                    attributes.push('title="' + escapeAttribute(title) + '"');
                }

                if (style !== defaultStyle) {
                    attributes.push('style="' + escapeAttribute(style) + '"');
                }

                if (numbers !== defaultNumbers) {
                    attributes.push('numbers="' + (numbers ? 'true' : 'false') + '"');
                }

                output.value = attributes.length
                    ? '[' + tag + ' ' + attributes.join(' ') + ']'
                    : '[' + tag + ']';
            }

            function copy() {
                output.focus();
                output.select();

                var copied = false;

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(output.value).then(function () {
                        copied = true;
                        copyButton.textContent = copyButton.dataset.copiedLabel || copyButton.textContent;
                    }).catch(function () {});
                }

                if (!copied && document.execCommand) {
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
