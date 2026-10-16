(() => {
    const menuSection = () => document.querySelector('.market-section');
    let activeRequest;
    let loadingMore = false;
    let lastLoadScrollY = Number.NEGATIVE_INFINITY;
    let scrollCheckPending = false;

    function prepareIncrementalLoading(section = menuSection(), reset = false) {
        if (!section) return;
        let grid = section.querySelector('.menu-grid');
        if (!grid) return;

        if (reset) {
            section.querySelector('.menu-load-sentinel')?.remove();
            section.querySelector('.menu-loading')?.remove();
            lastLoadScrollY = Number.NEGATIVE_INFINITY;
            loadingMore = false;
        }

        let sentinel = section.querySelector('.menu-load-sentinel');
        if (!sentinel) {
            sentinel = document.createElement('div');
            sentinel.className = 'menu-load-sentinel';
            sentinel.setAttribute('aria-hidden', 'true');
        }

        // Sentinel dan status pemuatan ditempatkan di luar grid agar tidak
        // mengambil kolom atau menggeser kartu menu saat indikator muncul.
        if (sentinel.parentElement !== section || sentinel.previousElementSibling !== grid) {
            grid.insertAdjacentElement('afterend', sentinel);
        }

        if (!section.querySelector('.menu-loading')) {
            const message = document.createElement('p');
            message.className = 'menu-loading';
            message.setAttribute('role', 'status');
            message.setAttribute('aria-live', 'polite');
            message.hidden = true;
            message.textContent = 'Memuat menu selanjutnya...';
            sentinel.insertAdjacentElement('afterend', message);
        }
    }

    async function loadMenuSection(target, addHistory = true) {
        const currentSection = menuSection();
        if (!currentSection) return;

        activeRequest?.abort();
        activeRequest = new AbortController();
        currentSection.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(target, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: activeRequest.signal,
            });
            if (!response.ok) throw new Error('Daftar menu gagal dimuat.');

            const html = await response.text();
            const parsedPage = new DOMParser().parseFromString(html, 'text/html');
            const updatedSection = parsedPage.querySelector('.market-section');
            if (!updatedSection) throw new Error('Bagian menu tidak ditemukan.');

            currentSection.replaceWith(updatedSection);
            const newSearch = updatedSection.querySelector('.search-bar');
            if (newSearch) {
                ['q', 'location', 'type'].forEach((name) => {
                    const value = new URL(target, window.location.origin).searchParams.get(name);
                    const field = newSearch.elements.namedItem(name);
                    if (field) field.value = value || '';
                });
            }

            if (addHistory) history.pushState({}, '', target);
            prepareIncrementalLoading(updatedSection, true);
        } catch (error) {
            if (error.name === 'AbortError') return;
            window.location.assign(target);
        }
    }

    async function loadMoreMenus(target, addHistory = false) {
        const section = menuSection();
        const grid = section?.querySelector('.menu-grid');
        const pagination = section?.querySelector('.pagination');
        const message = section?.querySelector('.menu-loading');
        if (!section || !grid || loadingMore) return;

        loadingMore = true;
        section.setAttribute('aria-busy', 'true');
        if (message) {
            message.hidden = false;
            message.textContent = 'Memuat menu selanjutnya...';
        }

        try {
            const response = await fetch(target, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) throw new Error('Menu selanjutnya gagal dimuat.');

            const html = await response.text();
            const parsedPage = new DOMParser().parseFromString(html, 'text/html');
            const nextSection = parsedPage.querySelector('.market-section');
            const nextGrid = nextSection?.querySelector('.menu-grid');
            if (!nextSection || !nextGrid) throw new Error('Data menu tidak ditemukan.');

            const nextCards = [...nextGrid.querySelectorAll('.menu-card')];
            nextCards.forEach((card) => grid.append(document.importNode(card, true)));

            const nextPagination = nextSection.querySelector('.pagination');
            if (pagination && nextPagination) {
                pagination.replaceWith(document.importNode(nextPagination, true));
            } else if (pagination && !nextPagination) {
                pagination.remove();
            }

            if (addHistory) history.pushState({}, '', target);
            lastLoadScrollY = window.scrollY;
            if (message) message.hidden = true;
        } catch (error) {
            if (message) {
                message.hidden = false;
                message.textContent = 'Menu selanjutnya gagal dimuat. Gulir lagi atau pilih halaman berikutnya.';
            }
        } finally {
            section.removeAttribute('aria-busy');
            loadingMore = false;
        }
    }

    function checkForNextMenus() {
        if (scrollCheckPending) return;
        scrollCheckPending = true;
        window.requestAnimationFrame(() => {
            scrollCheckPending = false;
            const section = menuSection();
            const sentinel = section?.querySelector('.menu-load-sentinel');
            const nextLink = section?.querySelector('.pagination a[rel="next"]');
            if (!sentinel || !nextLink || loadingMore) return;

            const nearEnd = sentinel.getBoundingClientRect().top <= window.innerHeight + 100;
            const movedSinceLastBatch = window.scrollY >= lastLoadScrollY + 80;
            if (nearEnd && movedSinceLastBatch) loadMoreMenus(nextLink.href);
        });
    }

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('.search-bar, .sort-form');
        if (!form) return;

        event.preventDefault();
        const target = new URL(form.action, window.location.origin);
        const parameters = new URLSearchParams();
        for (const [key, value] of new FormData(form).entries()) {
            if (String(value).trim()) parameters.append(key, value);
        }
        if (form.matches('.search-bar')) {
            const currentSort = new URL(window.location.href).searchParams.get('sort');
            if (currentSort && !parameters.has('sort')) parameters.set('sort', currentSort);
        }
        target.search = parameters.toString();
        loadMenuSection(target);
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('.market-section .pagination a, .market-section .category-row a');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();

        if (link.closest('.pagination')) {
            loadMoreMenus(link.href, true);
            return;
        }
        loadMenuSection(link.href);
    });

    window.addEventListener('scroll', checkForNextMenus, { passive: true });
    window.addEventListener('resize', checkForNextMenus);
    window.addEventListener('popstate', () => loadMenuSection(window.location.href, false));

    prepareIncrementalLoading();
})();