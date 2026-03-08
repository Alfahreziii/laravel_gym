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
        perPage = 10,
        colSpan = 6,
        renderRow = null,
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

            fetch(`${url}?page=${currentPage}&search=${encodeURIComponent(currentSearch)}&perPage=${perPage}`, {
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

            const showing = `Showing ${start + 1} to ${Math.min(start + res.perPage, res.total)} of ${res.total} entries`;
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

            const btnClass = 'px-3 py-1 rounded border text-sm';
            let pages = [];

            pages.push(`<button onclick="window._ajaxTables['${tbodyId}'].goTo(${res.page - 1})" 
                ${res.page <= 1 ? 'disabled' : ''}
                class="${btnClass} ${res.page <= 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100'}">&laquo;</button>`);

            let start = Math.max(1, res.page - 2);
            let end = Math.min(res.lastPage, start + 4);
            if (end - start < 4) start = Math.max(1, end - 4);

            for (let i = start; i <= end; i++) {
                pages.push(`<button onclick="window._ajaxTables['${tbodyId}'].goTo(${i})"
                    class="${btnClass} ${i === res.page ? 'bg-primary-600 text-white border-primary-600' : 'hover:bg-gray-100'}">${i}</button>`);
            }

            if (end < res.lastPage) {
                pages.push(`<span class="px-2 py-1 text-sm text-gray-400">...</span>`);
                pages.push(`<button onclick="window._ajaxTables['${tbodyId}'].goTo(${res.lastPage})"
                    class="${btnClass} hover:bg-gray-100">${res.lastPage}</button>`);
            }

            pages.push(`<button onclick="window._ajaxTables['${tbodyId}'].goTo(${res.page + 1})"
                ${res.page >= res.lastPage ? 'disabled' : ''}
                class="${btnClass} ${res.page >= res.lastPage ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100'}">&raquo;</button>`);

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

    return { create };

})();