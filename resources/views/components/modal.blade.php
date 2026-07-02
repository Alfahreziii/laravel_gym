{{--
    Komponen modal reusable untuk HexaGym app.
    Props  : id (required), title (required), maxWidth (default 'max-w-2xl')
    Slots  : $body (required), $footer (optional)
    Kontrol: HexaModal.show(id) / HexaModal.hide(id) dari JS eksternal
             Tombol tutup pakai [data-close-modal="id"], backdrop klik, atau Escape.
--}}
@props([
    'id',
    'title',
    'maxWidth' => 'max-w-2xl',
])

<div
    id="{{ $id }}"
    tabindex="-1"
    aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="{{ $maxWidth }} w-full mx-4 my-4">
        <div class="rounded-2xl bg-white dark:bg-neutral-800 shadow-xl flex flex-col max-h-[90vh]">

            {{-- Header --}}
            <div class="py-4 px-6 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between flex-shrink-0">
                <h2 id="{{ $id }}-title" class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">
                    {{ $title }}
                </h2>
                <button type="button" data-close-modal="{{ $id }}"
                    class="text-neutral-400 bg-transparent hover:bg-neutral-100 dark:hover:bg-neutral-700
                           hover:text-neutral-900 dark:hover:text-white rounded-lg text-sm w-8 h-8
                           inline-flex justify-center items-center transition-colors">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6 overflow-y-auto flex-1">
                {{ $body }}
            </div>

            {{-- Footer (opsional) --}}
            @if(isset($footer) && $footer->isNotEmpty())
            <div class="flex justify-end gap-3 px-6 pb-6 flex-shrink-0 border-t border-neutral-200 dark:border-neutral-700 pt-4">
                {{ $footer }}
            </div>
            @endif

        </div>
    </div>
</div>

@once
<script>
/**
 * HexaModal — pure-JS show/hide tanpa Flowbite.
 * Diinisialisasi satu kali (directive once) pada x-modal pertama di halaman.
 *
 * API:
 *   HexaModal.show('modal-id')  — tampilkan modal + backdrop
 *   HexaModal.hide('modal-id')  — sembunyikan modal + backdrop
 *
 * Tombol tutup: pakai atribut [data-close-modal="modal-id"].
 * Backdrop klik dan Escape key juga menutup modal.
 */
window.HexaModal = (function () {
    var BD_ID = '__hexa-modal-bd__';
    var _currentId = null;

    function show(id) {
        // Tutup backdrop lama jika ada (ganti modal)
        _removeBackdrop();

        var el = document.getElementById(id);
        if (!el) return;

        _currentId = id;
        el.classList.remove('hidden');
        el.classList.add('flex');
        el.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');

        var bd = document.createElement('div');
        bd.id = BD_ID;
        bd.className = 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40';
        bd.addEventListener('click', function () { hide(id); });
        document.body.appendChild(bd);
    }

    function hide(id) {
        var el = document.getElementById(id);
        if (el) {
            el.classList.add('hidden');
            el.classList.remove('flex');
            el.setAttribute('aria-hidden', 'true');
        }
        _currentId = null;
        document.body.classList.remove('overflow-hidden');
        _removeBackdrop();
    }

    function _removeBackdrop() {
        var bd = document.getElementById(BD_ID);
        if (bd) bd.remove();
    }

    // Delegation untuk [data-close-modal] — menutup modal dari tombol X, Batal, dll.
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-close-modal]');
        if (btn) hide(btn.getAttribute('data-close-modal'));
    });

    // Tutup modal dengan Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && _currentId) hide(_currentId);
    });

    return { show: show, hide: hide };
}());
</script>
@endonce
