if (document.getElementById("selection-table") && typeof simpleDatatables.DataTable !== 'undefined') {

    let multiSelect = true;
    let rowNavigation = false;
    let table = null;

    // Reset semua gambar ke lazy state, lalu load ulang
    const resetAndLazyLoad = function() {
        // Reset gambar yang sudah ter-load kembali ke lazy state
        const loadedImages = document.querySelectorAll('#selection-table img[src]:not([src*="svg"])');
        loadedImages.forEach(img => {
            const realSrc = img.getAttribute('data-real-src') || img.src;
            img.setAttribute('data-src', realSrc);
            img.setAttribute('data-real-src', realSrc);
            img.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='40' height='40' fill='%23e5e7eb'/%3E%3C/svg%3E";
            img.classList.add('lazy');
        });

        // Load ulang gambar yang terlihat
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.getAttribute('data-src');
                    if (src) {
                        img.src = src;
                        img.removeAttribute('data-src');
                        img.classList.remove('lazy');
                    }
                    observer.unobserve(img);
                }
            });
        }, { rootMargin: '50px' });

        document.querySelectorAll('#selection-table img.lazy[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    };

    const resetTable = function () {
        if (table) {
            table.destroy();
        }

        const options = {
            columns: [
                { select: [0, 6], sortable: false }
            ],
            perPage: 10,
            perPageSelect: [10, 25, 50, 100],
            rowRender: (row, tr, _index) => {
                if (!tr.attributes) {
                    tr.attributes = {};
                }
                if (!tr.attributes.class) {
                    tr.attributes.class = "";
                }
                if (row.selected) {
                    tr.attributes.class += " selected";
                } else {
                    tr.attributes.class = tr.attributes.class.replace(" selected", "");
                }
                return tr;
            }
        };

        if (rowNavigation) {
            options.rowNavigation = true;
            options.tabIndex = 1;
        }

        table = new simpleDatatables.DataTable("#selection-table", options);

        table.data.data.forEach(data => {
            data.selected = false;
        });

        table.on("datatable.selectrow", (rowIndex, event) => {
            event.preventDefault();
            const row = table.data.data[rowIndex];
            if (row.selected) {
                row.selected = false;
            } else {
                if (!multiSelect) {
                    table.data.data.forEach(data => {
                        data.selected = false;
                    });
                }
                row.selected = true;
            }
            table.update();
        });

        // Initial load
        resetAndLazyLoad();

        table.on("datatable.page", function() {
            setTimeout(() => resetAndLazyLoad(), 150);
        });

        table.on("datatable.sort", function() {
            setTimeout(() => resetAndLazyLoad(), 150);
        });

        table.on("datatable.search", function() {
            setTimeout(() => resetAndLazyLoad(), 150);
        });

        table.on("datatable.perpage", function() {
            setTimeout(() => resetAndLazyLoad(), 150);
        });
    };

    const isMobile = window.matchMedia("(any-pointer:coarse)").matches;
    if (isMobile) {
        rowNavigation = false;
    }

    resetTable();
}