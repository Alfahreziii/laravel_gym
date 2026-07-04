/**
 * Ajax Table - Reusable datatable dengan fetch API
 * Usage:
 * AjaxTable.create({
 *     url: '/endpoint',
 *     tbodyId: 'tbodyId',
 *     paginationId: 'paginationId',
 *     infoId: 'infoId',
 *     searchId: 'searchId',
 *     perPage: 5,
 *     columns: ['no', 'rfid', 'foto', 'name', 'status', 'time'], // urutan kolom
 *     renderRow: function(item) { return `<tr>...</tr>`; } // optional custom render
 * });
 */

window.AjaxTable = (function () {

    function create({
        url,
        tbodyId,
        paginationId,
        infoId,
        searchId,
        perPageId = null,
        perPage = 10,
        colSpan = 6,
        renderRow = null,
        extraParams = null,
    }) {
        let currentPage = 1;
        let currentSearch = '';
        let searchTimer = null;

        const fetchData = function () {
            const tbody = document.getElementById(tbodyId);
            if (!tbody) return;

            tbody.innerHTML = `<tr><td colspan="${colSpan}" class="text-center py-8 text-gray-400">
                <svg class="animate-spin inline w-5 h-5 mr-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Loading...
            </td></tr>`;

            const _params = new URLSearchParams({
                page: currentPage,
                search: currentSearch,
                perPage: perPage,
            });
            if (extraParams && typeof extraParams === 'function') {
                const _extra = extraParams();
                if (_extra && typeof _extra === 'object') {
                    Object.keys(_extra).forEach(k => {
                        if (_extra[k] !== undefined && _extra[k] !== null) {
                            _params.set(k, _extra[k]);
                        }
                    });
                }
            }

            fetch(`${url}?${_params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(res => {
                renderTable(res);
                renderPagination(res);
            })
            .catch((err) => {
                const tbody = document.getElementById(tbodyId);
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="${colSpan}" class="text-center py-8 text-red-400">
                        Gagal memuat data: ${err.message}
                    </td></tr>`;
                }
            });
        };

        const renderTable = function (res) {
            const tbody = document.getElementById(tbodyId);
            const info = document.getElementById(infoId);
            if (!tbody) return;

            const start = (res.page - 1) * res.perPage;

            if (!res.data || res.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${colSpan}" class="text-center py-8 text-gray-400">Tidak ada data</td></tr>`;
                if (info) info.textContent = '';
                return;
            }

            // Gunakan custom renderRow jika ada, fallback ke default
            if (renderRow) {
                tbody.innerHTML = res.data.map((item, i) => renderRow(item, start + i)).join('');
            } else {
                tbody.innerHTML = res.data.map(item => defaultRenderRow(item, colSpan)).join('');
            }

            const showing = `Menampilkan ${start + 1}–${Math.min(start + res.perPage, res.total)} dari ${res.total} data`;
            if (info) info.textContent = showing;
        };

        // Default render row — untuk kehadiran/member in room
        const defaultRenderRow = function (item) {
            const statusClass = item.status && item.status.toLowerCase() === 'in'
                ? 'bg-success-100 text-success-600'
                : 'bg-danger-100 text-danger-600';

            return `
                <tr>
                    <td class="whitespace-nowrap">${item.no ?? '-'}</td>
                    <td class="whitespace-nowrap">${item.rfid ?? '-'}</td>
                    <td class="whitespace-nowrap">
                        ${item.foto
                            ? `<img src="${item.foto}" alt="${item.name ?? ''}" class="w-10 h-10 rounded object-cover bg-gray-200" loading="lazy">`
                            : `<span class="text-gray-400 italic text-xs">No photo</span>`
                        }
                    </td>
                    <td class="whitespace-nowrap">${item.name ?? '-'}</td>
                    <td class="whitespace-nowrap">
                        <span class="px-2 py-1 rounded text-xs font-semibold ${statusClass}">
                            ${item.status ?? '-'}
                        </span>
                    </td>
                    <td class="whitespace-nowrap">${item.time ?? '-'}</td>
                </tr>
            `;
        };

        const renderPagination = function (res) {
            const container = document.getElementById(paginationId);
            if (!container) return;

            const btnBase = 'px-3 py-1.5 rounded-lg border text-sm transition-colors duration-150';
            const btnNormal = btnBase + ' border-line-light dark:border-line-dark text-ink-2 dark:text-ink-d2 hover:bg-canvas-light dark:hover:bg-canvas-dark';
            const btnActive = btnBase + ' bg-primary-500 border-primary-500 text-white font-semibold';
            const btnDisabled = btnBase + ' border-line-light dark:border-line-dark text-ink-3 dark:text-ink-d3 opacity-40 cursor-not-allowed';
            let pages = [];

            pages.push(`<button onclick="window._ajaxTables['${tbodyId}'].goTo(${res.page - 1})"
                ${res.page <= 1 ? 'disabled' : ''}
                class="${res.page <= 1 ? btnDisabled : btnNormal}">&laquo;</button>`);

            let start = Math.max(1, res.page - 2);
            let end = Math.min(res.lastPage, start + 4);
            if (end - start < 4) start = Math.max(1, end - 4);

            for (let i = start; i <= end; i++) {
                pages.push(`<button onclick="window._ajaxTables['${tbodyId}'].goTo(${i})"
                    class="${i === res.page ? btnActive : btnNormal}">${i}</button>`);
            }

            if (end < res.lastPage) {
                pages.push(`<span class="px-2 py-1 text-sm text-ink-3 dark:text-ink-d3">...</span>`);
                pages.push(`<button onclick="window._ajaxTables['${tbodyId}'].goTo(${res.lastPage})"
                    class="${btnNormal}">${res.lastPage}</button>`);
            }

            pages.push(`<button onclick="window._ajaxTables['${tbodyId}'].goTo(${res.page + 1})"
                ${res.page >= res.lastPage ? 'disabled' : ''}
                class="${res.page >= res.lastPage ? btnDisabled : btnNormal}">&raquo;</button>`);

            container.innerHTML = pages.join('');
        };

        // Search dengan debounce
        const searchEl = document.getElementById(searchId);
        if (searchEl) {
            searchEl.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    currentSearch = this.value;
                    currentPage = 1;
                    fetchData();
                }, 400);
            });
        }

        // Dropdown entries per page
        const perPageEl = perPageId ? document.getElementById(perPageId) : null;
        if (perPageEl) {
            perPage = parseInt(perPageEl.value) || perPage;
            perPageEl.addEventListener('change', function () {
                perPage = parseInt(this.value) || 10;
                currentPage = 1;
                fetchData();
            });
        }

        // Expose instance ke global
        if (!window._ajaxTables) window._ajaxTables = {};
        window._ajaxTables[tbodyId] = {
            goTo: function (page) {
                if (page < 1) return;
                currentPage = page;
                fetchData();
            },
            refresh: function () {
                fetchData();
            },
            search: function (keyword) {
                currentSearch = keyword;
                currentPage = 1;
                fetchData();
            },
            // TAMBAH INI
            setPerPage: function (newPerPage) {
                perPage = newPerPage;
                currentPage = 1;
                fetchData();
            }
        };

        // Initial load
        fetchData();
    }

    // Konvensi ID untuk x-data-table: tbody/pagination/info/search + ucfirst(tableId)
    function init(tableId, options) {
        const cap = tableId.charAt(0).toUpperCase() + tableId.slice(1);
        return create({
            url:          options.url,
            tbodyId:      'tbody'      + cap,
            paginationId: 'pagination' + cap,
            infoId:       'info'       + cap,
            searchId:     'search'     + cap,
            perPageId:    'perPage'    + cap,
            perPage:      options.perPage      || 10,
            colSpan:      options.colSpan      || 5,
            renderRow:    options.renderRow    || null,
            extraParams:  options.extraParams  || null,
        });
    }

    // Badge helper — mirrors <x-badge> design system (dot + tint bg + correct padding)
    function badge(type, text, withDot) {
        if (withDot === undefined) withDot = true;
        const colors = {
            success: 'bg-success-50 text-success-700 dark:bg-success-600/20 dark:text-success-400',
            danger:  'bg-danger-50 text-danger-700 dark:bg-danger-600/20 dark:text-danger-400',
            warning: 'bg-warning-50 text-warning-700 dark:bg-warning-600/20 dark:text-warning-400',
            info:    'bg-info-50 text-info-700 dark:bg-info-600/20 dark:text-info-400',
            primary: 'bg-primary-50 text-primary-700 dark:bg-primary-600/20 dark:text-primary-400',
            neutral: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-300',
        };
        const dotColors = {
            success: 'bg-success-500', danger: 'bg-danger-500', warning: 'bg-warning-500',
            info: 'bg-info-500', primary: 'bg-primary-500', neutral: 'bg-neutral-400',
        };
        const cls    = colors[type] || colors['neutral'];
        const dotCls = dotColors[type] || dotColors['neutral'];
        const dot    = withDot ? `<span class="w-1.5 h-1.5 rounded-full ${dotCls} flex-shrink-0"></span>` : '';
        return `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ${cls}">${dot}${text}</span>`;
    }

    return { create, init, badge };

})();