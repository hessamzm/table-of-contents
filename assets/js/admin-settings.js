(function () {
    'use strict';

    var prefix = 'hessamzm_toc_settings';
    var form = document.querySelector('form[action="options.php"]');
    var preview = document.querySelector('.hessamzm-toc-live-preview .hessamzm-toc');

    if (!form || !preview) {
        return;
    }

    function field(key) {
        return form.querySelector('[name="' + prefix + '[' + key + ']"]');
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
        var root = preview;
        var style = value('style', 'paper');
        var position = value('position', 'right');
        var titleElement = root.querySelector('.hessamzm-toc__title');
        var toggle = root.querySelector('.hessamzm-toc__toggle');
        var title = value('title', '') || titleElement.dataset.defaultTitle;
        var moreText = value('more_text', '') || toggle.dataset.expandLabel;
        var lessText = value('less_text', '') || toggle.dataset.collapseLabel;
        var fontSize = value('font_size', '16px');
        var stickyFontSize = value('sticky_font_size', '14px');
        var indentation = value('indentation', '1.5rem');
        var radius = value('border_radius', '0px');

        root.classList.remove(
            'hessamzm-toc--classic',
            'hessamzm-toc--minimal',
            'hessamzm-toc--card',
            'hessamzm-toc--paper',
            'hessamzm-toc--position-left',
            'hessamzm-toc--position-right',
            'hessamzm-toc--numbered',
            'hessamzm-toc--sticky'
        );
        root.classList.add('hessamzm-toc--' + style);
        root.classList.add('hessamzm-toc--position-' + position);

        if (checked('show_numbers')) {
            root.classList.add('hessamzm-toc--numbered');
        }

        if (checked('sticky_toc')) {
            root.classList.add('hessamzm-toc--sticky');
        }

        root.style.setProperty('--hessamzm-toc-background-color', value('background_color', '#ffffff'));
        root.style.setProperty('--hessamzm-toc-text-color', value('text_color', '#1d2327'));
        root.style.setProperty('--hessamzm-toc-link-color', value('link_color', '#2271b1'));
        root.style.setProperty('--hessamzm-toc-border-color', value('border_color', '#dcdcde'));
        root.style.setProperty('--hessamzm-toc-font-size', fontSize);
        root.style.setProperty('--hessamzm-toc-sticky-font-size', stickyFontSize);
        root.style.setProperty('--hessamzm-toc-indentation', indentation);
        root.style.setProperty('--hessamzm-toc-border-radius', radius);

        root.querySelector('.hessamzm-toc__title').textContent = title;
        toggle.textContent = moreText;
        toggle.dataset.expandLabel = moreText;
        toggle.dataset.collapseLabel = lessText;
    }

    var inputs = form.querySelectorAll('input, select');
    inputs.forEach(function (input) {
        input.addEventListener('input', update);
        input.addEventListener('change', update);
    });

    update();
})();
