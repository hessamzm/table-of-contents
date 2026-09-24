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
})();
