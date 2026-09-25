(function () {
    'use strict';

    function initToc(toc) {
        var toggle = toc.querySelector('.hessamzm-toc__toggle');
        var body = toc.querySelector('.hessamzm-toc__body');
        var footer = toc.querySelector('.hessamzm-toc__footer');

        if (!toggle || !body || !footer) {
            return;
        }

        /*
         * Keep initialization idempotent without preventing visibility
         * recalculation. This matters when theme/plugin scripts change the
         * layout after the first pass.
         */
        if (toc.dataset.tocInitialized !== 'true') {
            toc.dataset.tocInitialized = 'true';
            toc.classList.add('hessamzm-toc--js-ready');

            toggle.addEventListener('click', function () {
                var expanded = toc.classList.toggle('is-expanded');

                toggle.setAttribute(
                    'aria-expanded',
                    expanded ? 'true' : 'false'
                );

                toggle.textContent = expanded
                    ? toggle.dataset.collapseLabel
                    : toggle.dataset.expandLabel;
            });
        } else {
            toc.classList.add('hessamzm-toc--js-ready');
        }

        function updateToggleVisibility() {
            if (toc.classList.contains('is-expanded')) {
                footer.hidden = false;
                return;
            }

            var needsToggle = body.scrollHeight > body.clientHeight + 2;
            footer.hidden = !needsToggle;
        }

        var refreshToggleVisibility = function () {
            window.requestAnimationFrame(updateToggleVisibility);
        };

        updateToggleVisibility();
        refreshToggleVisibility();

        if (!toc._hessamzmTocResizeObserver && 'ResizeObserver' in window) {
            toc._hessamzmTocResizeObserver = new ResizeObserver(
                updateToggleVisibility
            );

            toc._hessamzmTocResizeObserver.observe(body);
        } else if (!toc._hessamzmTocResizeObserver) {
            window.addEventListener('resize', refreshToggleVisibility);
        }

        window.setTimeout(refreshToggleVisibility, 0);
        window.setTimeout(refreshToggleVisibility, 100);

        var links = Array.prototype.slice.call(
            toc.querySelectorAll('.hessamzm-toc__link[href^="#"]')
        );

        if (!links.length || !('IntersectionObserver' in window)) {
            return;
        }

        var headings = links.map(function (link) {
            var id = link.getAttribute('href').slice(1);
            return document.getElementById(id);
        }).filter(Boolean);

        if (!headings.length) {
            return;
        }

        var linkById = new Map();

        links.forEach(function (link) {
            var id = link.getAttribute('href').slice(1);
            linkById.set(id, link);
        });

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.target.id) {
                    return;
                }

                var link = linkById.get(entry.target.id);

                if (!link) {
                    return;
                }

                link.classList.toggle('is-active', entry.isIntersecting);

                if (entry.isIntersecting) {
                    links.forEach(function (item) {
                        if (item !== link) {
                            item.removeAttribute('aria-current');
                        }
                    });
                    link.setAttribute('aria-current', 'location');
                }
            });
        }, {
            rootMargin: '-10% 0px -70% 0px',
            threshold: [0, 1]
        });

        headings.forEach(function (heading) {
            observer.observe(heading);
        });

        links.forEach(function (link) {
            if (link.dataset.tocScrollInitialized === 'true') {
                return;
            }

            link.dataset.tocScrollInitialized = 'true';

            link.addEventListener('click', function () {
                var id = link.getAttribute('href').slice(1);
                var heading = document.getElementById(id);

                if (!heading) {
                    return;
                }

                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }

                heading.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });
    }

    function boot() {
        document.querySelectorAll('.hessamzm-toc').forEach(initToc);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();