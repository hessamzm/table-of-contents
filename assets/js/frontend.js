(function () {
    'use strict';

    function initToc(toc) {
        if (toc.dataset.tocInitialized === 'true') {
            return;
        }

        toc.dataset.tocInitialized = 'true';

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
            link.addEventListener('click', function () {
                var id = link.getAttribute('href').slice(1);
                var heading = document.getElementById(id);

                if (!heading) {
                    return;
                }

                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }

                heading.scrollIntoView({behavior: 'smooth', block: 'start'});
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