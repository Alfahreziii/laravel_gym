@extends('layout.layout')

@php
    $title    = 'Kasir';
    $subTitle = 'Point of Sale';

    // Hex colors for fallback card backgrounds — inline styles to avoid Tailwind JIT missing dynamic classes
    $catColors = ['#f97316','#3b82f6','#10b981','#8b5cf6','#ec4899',
                  '#f59e0b','#14b8a6','#f43f5e','#6366f1','#06b6d4'];
@endphp

@section('content')

{{-- Full-bleed POS layout that fills the dashboard-main-body area --}}
<style>
@media (min-width:1536px){.pos-shell{margin-bottom:-1.5rem}}
.pos-tabs{display:none}
.cart-produk{width:22rem;flex-shrink:0;overflow:hidden}
@media (max-width:699.98px){
  .pos-shell{flex-direction:column}
  .pos-tabs{display:flex}
  #pos-panel-produk,.cart-produk{flex:1 1 auto;width:100%;min-height:0}
  .pos-shell[data-active="produk"] .cart-produk{display:none}
  .pos-shell[data-active="cart"] #pos-panel-produk{display:none}
}
</style>
<div class="pos-shell"
     style="display:flex; height:calc(100vh - 4.5rem);height:calc(100dvh - 4.5rem); overflow:hidden;
            margin-left:-0.9375rem; margin-right:-0.9375rem;
            margin-top:-0.9375rem; margin-bottom:-0.9375rem;">

    <div class="pos-tabs bg-white dark:bg-surface-dark border-b border-neutral-200 dark:border-line-dark" style="flex-shrink:0;">
        <button type="button" class="pos-tab flex-1 py-3 text-sm font-semibold text-center border-b-2 border-transparent text-neutral-500 dark:text-ink-d2" data-tab="produk">Produk</button>
        <button type="button" class="pos-tab flex-1 py-3 text-sm font-semibold text-center border-b-2 border-transparent text-neutral-500 dark:text-ink-d2" data-tab="cart">
            Keranjang
            <span id="pos-tab-cart-badge" class="ml-1 inline-flex items-center justify-center min-w-5 h-5 px-1 rounded-full bg-primary-600 text-white text-xs" style="display:none;">0</span>
        </button>
    </div>

    {{-- ===== LEFT: Product Panel ===== --}}
    <div id="pos-panel-produk" class="bg-neutral-50 dark:bg-canvas-dark"
         style="display:flex; flex-direction:column; flex:1; overflow:hidden;">

        {{-- Header: title + search --}}
        <div class="bg-white dark:bg-surface-dark border-b border-neutral-200 dark:border-line-dark"
             style="display:flex; align-items:center; gap:12px; padding:12px 20px; flex-shrink:0;">
            <h6 class="font-display font-semibold text-base text-ink dark:text-ink-d m-0" style="flex:1;">Pilih Produk</h6>
            <div style="position:relative;">
                <iconify-icon icon="lucide:search" class="text-neutral-400 text-sm" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;"></iconify-icon>
                <input type="text" id="product-search" placeholder="Cari produk..."
                    class="form-control rounded-lg text-sm" style="padding-top:6px;padding-bottom:6px;padding-left:32px;width:192px;">
            </div>
        </div>

        {{-- Category filter pills --}}
        <div class="bg-white dark:bg-surface-dark border-b border-neutral-200 dark:border-line-dark"
             style="padding:10px 20px; flex-shrink:0;">
            <div class="flex flex-wrap gap-2" id="category-pills">
                <button type="button"
                    class="category-pill active px-3 py-1 rounded-full text-xs font-semibold bg-primary-500 text-white transition-colors"
                    data-category="all">
                    Semua
                </button>
                @foreach($kategoris as $kat)
                <button type="button"
                    class="category-pill px-3 py-1 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-600 hover:bg-neutral-200 dark:bg-surface-dark-raised dark:text-ink-d2 transition-colors"
                    data-category="{{ $kat->id }}">
                    {{ $kat->name }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Product grid (scrollable) — kartu dirender via fetchProductGrid() --}}
        <div id="product-grid"
             style="flex:1; min-height:0; overflow-y:auto; padding:14px;
                    display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:14px;
                    align-content:start;">
            {{-- JS render via AJAX --}}
        </div>

        {{-- Pagination grid produk --}}
        <div id="product-grid-pagination"
             class="border-t border-neutral-200 dark:border-line-dark"
             style="flex-shrink:0; display:flex; justify-content:center; align-items:center;
                    gap:4px; padding:8px 14px; flex-wrap:wrap; min-height:44px;">
        </div>

    </div>

    {{-- ===== RIGHT: Cart Panel ===== --}}
    <div class="cart-produk flex flex-col justify-between wrapper-produk-detail bg-white dark:bg-surface-dark border-l border-neutral-200 dark:border-line-dark p-4 min-h-0">
        <div class="flex-1 min-h-0 overflow-y-auto">
            <div class="produk-header flex items-center justify-between">
                <h6 class="font-display font-semibold text-base text-ink dark:text-ink-d m-0">Detail Items</h6>
            </div>
            <div class="mt-3 mb-3">
                <label for="customer_name_cart" class="inline-block font-semibold text-neutral-600 dark:text-ink-d2 text-sm mb-2">
                    Nama Pelanggan
                </label>
                <input type="text" id="customer_name_cart" name="customer_name"
                    placeholder="Masukkan nama pelanggan"
                    class="form-control rounded-lg text-sm">
            </div>
            <!-- tempat item cart muncul -->
            <div class="produk-body-container mt-3"></div>

            <div class="flex py-2 border-t border-b border-neutral-200 dark:border-line-dark">
                <button type="button" onclick="HexaModal.show('diskon-modal')"
                 class="w-full py-2 text-xs bg-primary-600 text-white rounded-lg">
                 Tambahkan Diskon <i class="ri-money-dollar-box-fill"></i></button>
            </div>

            <div class="produk-footer mt-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-neutral-500 dark:text-ink-d2">Total Items</span>
                    <span class="font-medium text-sm total-items">0 Items</span>
                </div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-neutral-500 dark:text-ink-d2">Total Harga</span>
                    <span class="font-medium text-sm total-harga">Rp 0</span>
                </div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-neutral-500 dark:text-ink-d2">Diskon :</span>
                    <span></span>
                </div>
                <div class="inner-diskon">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-neutral-500 dark:text-ink-d2">- Diskon</span>
                        <span class="font-medium text-danger-600 text-xs diskon-input">-Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-neutral-500 dark:text-ink-d2">- Diskon Barang</span>
                        <span class="font-medium text-danger-600 text-xs diskon-barang">-Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-neutral-500 dark:text-ink-d2">- Total Diskon</span>
                        <span class="font-medium text-danger-600 text-xs total-diskon">-Rp 0</span>
                    </div>
                </div>
                <div class="flex items-center justify-between  py-2">
                    <span class="text-sm text-neutral-500 dark:text-ink-d2">Total Tagihan</span>
                    <span class="font-medium text-sm total-tagihan">Rp 0</span>
                </div>
            </div>
        </div>
        <div>
            <div class="flex mt-4 gap-2 relative">
                <button id="btn-empty-cart" class="w-full py-2 bg-danger-600 text-white rounded-lg">Empty <i class="ri-delete-bin-line"></i></button>

                <button id="moreToggle"
                    class="w-full flex justify-center items-center py-2 bg-warning-600 text-white rounded-lg gap-1 relative"
                    type="button">
                    More
                    <iconify-icon id="chevronMore" icon="mdi:chevron-down" class="text-lg transition-transform duration-200"></iconify-icon>
                </button>

                <div id="dropdownMore" class="hidden absolute mb-2 dropdown-more bg-white dark:bg-surface-dark border border-neutral-100 dark:border-line-dark text-black dark:text-ink-d rounded-lg shadow-lg w-48 z-50">
                    <ul class="p-2 text-sm">
                        <li><button type="button" class="block px-3 py-2 hover:bg-neutral-100 dark:hover:bg-surface-dark-raised rounded btn-hold">Hold</button></li>
                        <li><button type="button" class="block px-3 py-2 hover:bg-neutral-100 dark:hover:bg-surface-dark-raised rounded btn-hold-items">Hold Items</button></li>
                    </ul>
                </div>

            </div>
            <button type="button" id="btn-open-bayar" onclick="HexaModal.show('bayar-modal')" class="w-full py-2 bg-primary-600 text-white rounded-lg mt-2">Bayar <i class="ri-bank-card-fill"></i></button>
        </div>
    </div>

</div>

{{-- ===== Per-product modals ===== --}}
{{-- Generic product modal — populated via JS sebelum show --}}
<x-modal id="tambah-product-modal-generic" title="Tambah Product">
    <x-slot:body>
        <form class="form-tambah-cart">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <div class="col-span-12">
                    <label for="generic-qty" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                        Quantity
                    </label>
                    <input type="text" id="generic-qty" name="qty"
                        class="form-control rounded-lg" required>
                </div>

                <div class="col-span-12">
                    <label for="generic-keterangan" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                        Keterangan (Opsional)
                    </label>
                    <textarea id="generic-keterangan" name="keterangan"
                        class="form-control rounded-lg" rows="3"
                        placeholder="Catatan tambahan untuk item ini..."></textarea>
                </div>

                <div class="col-span-12 mt-4 flex items-center gap-3">
                    <button type="button" data-close-modal="tambah-product-modal-generic"
                        class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                        Cancel
                    </button>
                    <button type="submit"
                        class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                        Tambah
                    </button>
                </div>
            </div>
        </form>
    </x-slot:body>
</x-modal>

{{-- MODAL Hold Items --}}
<x-modal id="hold-items-modal" title="Hold Items" maxWidth="max-w-5xl">
    <x-slot:body>
        <x-data-table tableId="holdItems" :colspan="11" placeholder="Cari kode / nama pelanggan...">
            <x-slot:header>
                <tr>
                    <th scope="col">Aksi</th>
                    <th scope="col">No</th>
                    <th scope="col">Kode Transaksi</th>
                    <th scope="col">Nama Pelanggan</th>
                    <th scope="col">Qty</th>
                    <th scope="col">Total Sbl Diskon</th>
                    <th scope="col">Total Sesudah Diskon</th>
                    <th scope="col">Diskon</th>
                    <th scope="col">Diskon Barang</th>
                    <th scope="col">Keterangan</th>
                    <th scope="col">Tanggal Hold</th>
                </tr>
            </x-slot:header>
        </x-data-table>
    </x-slot:body>
</x-modal>

{{-- MODAL BAYAR --}}
<x-modal id="bayar-modal" title="Pembayaran">
    <x-slot:body>
        <form id="form-pembayaran">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <div class="col-span-12">
                    <label for="total_harus_bayar" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                        Total Yang Harus Dibayarkan
                    </label>
                    <input type="text" id="total_harus_bayar" name="total_harus_bayar"
                        class="form-control rounded-lg total-tagihan" readonly>
                </div>
                <div class="col-span-12 md:col-span-6">
                    <label for="bayar" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                        Jumlah Dibayarkan
                    </label>
                    <input type="text" id="bayar" name="bayar" placeholder="Masukkan jumlah bayar (Rp)"
                        class="form-control rounded-lg" required>
                </div>
                <div class="col-span-12 md:col-span-6">
                    <label for="kembalian" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                        Jumlah Kembalian
                    </label>
                    <input type="text" id="kembalian" name="kembalian"
                        class="form-control rounded-lg" readonly>
                </div>
                {{-- Metode Pembayaran --}}
                <div class="col-span-12">
                    <label class="form-label">Metode Pembayaran</label>
                    <select id="metode_pembayaran" name="metode_pembayaran" class="form-control" required>
                        <option value="">-- Pilih Metode --</option>
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                        <option value="ewallet">E-Wallet</option>
                    </select>
                </div>

                {{-- Checkbox Print Nota --}}
                <div class="col-span-12">
                    <div class="flex items-center gap-3 p-4 bg-neutral-50 dark:bg-surface-dark-raised rounded-lg border border-neutral-200 dark:border-line-dark">
                        <input type="checkbox" id="print_nota" name="print_nota"
                            class="w-5 h-5 text-primary-600 bg-white border-neutral-300 rounded focus:ring-primary-500 focus:ring-2"
                            checked>
                        <label for="print_nota" class="flex items-center gap-2 cursor-pointer">
                            <iconify-icon icon="solar:printer-bold" class="text-xl text-primary-600"></iconify-icon>
                            <span class="font-semibold text-neutral-700">Cetak Nota Otomatis</span>
                        </label>
                    </div>
                </div>

                <div class="col-span-12 mt-4 flex items-center gap-3">
                    <button type="button" data-close-modal="bayar-modal"
                        class="border w-1/2 border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                        Cancel
                    </button>
                    <button type="submit" id="btn-bayar"
                        class="w-1/2 bg-primary-500 hover:bg-primary-600 text-white border border-primary-600 text-base px-6 py-3 rounded-lg">
                        <iconify-icon icon="solar:card-bold" class="text-lg mr-2"></iconify-icon>
                        Proses Bayar
                    </button>
                </div>
            </div>
        </form>
    </x-slot:body>
</x-modal>

{{-- MODAL DISKON --}}
<x-modal id="diskon-modal" title="Tambahkan Diskon" maxWidth="max-w-md">
    <x-slot:body>
        <form>
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <div class="col-span-12">
                    <label for="diskon" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                        Jumlah Diskon
                    </label>
                    <input type="text" id="diskon" name="diskon" placeholder="Masukkan jumlah diskon (Rp)"
                        class="form-control rounded-lg" required>
                </div>

                <div class="col-span-12 mt-4 flex items-center gap-3">
                    <button type="button" data-close-modal="diskon-modal"
                        class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                        Cancel
                    </button>
                    <button type="submit"
                        class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                        Tambah
                    </button>
                </div>
            </div>
        </form>
    </x-slot:body>
</x-modal>

@endsection

@section('scripts')
<script src="{{ asset('assets/js/data-table.js') }}"></script>
<script src="{{ asset('assets/js/ajax-table.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let cart = [];
    let diskon = 0;
    let diskonBarang = 0;
    let currentTransactionId = null;

    function htmlEsc(str) {
        return String(str ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    const cartContainer = document.querySelector('.cart-produk .produk-body-container');
    const customerNameInput = document.getElementById('customer_name_cart'); // ← TAMBAH INI
    const totalItemEl = document.querySelector('.produk-footer .total-items');
    const totalHargaEl = document.querySelector('.produk-footer .total-harga');
    const totalDiskonEl = document.querySelector('.produk-footer .total-diskon');
    const totalTagihanEl = document.querySelector('.produk-footer .total-tagihan');
    const diskonBarangEl = document.querySelector('.produk-footer .diskon-barang');
    const diskonInputEl = document.querySelector('.produk-footer .diskon-input');
    const btnEmptyCart = document.getElementById('btn-empty-cart');

    function formatRp(n) {
        return Number(n || 0).toLocaleString();
    }

    // === Fungsi Update Cart UI ===
    function updateCartUI() {
        if (!cartContainer) return;
        cartContainer.innerHTML = '';

        let totalBeforeDiscount = 0;
        let totalItemDiscount = 0;
        let totalItems = 0;

        cart.forEach(item => {
            const itemTotal = (item.price || 0) * (item.qty || 0);
            let discountAmount = 0;

            if (item.discount && Number(item.discount) > 0) {
                if ((item.discount_type || '').toString() === 'percent') {
                    discountAmount = (item.price * item.discount / 100) * item.qty;
                } else {
                    discountAmount = (Number(item.discount) || 0) * item.qty;
                }
            }

            const itemSubtotal = itemTotal - discountAmount;

            totalBeforeDiscount += itemTotal;
            totalItemDiscount += discountAmount;
            totalItems += item.qty;

            const imgSrc = item.image
                ? `/storage/${item.image}`
                : '{{ asset("assets/images/kasir/product-placeholder.png") }}';

            const hargaHTML = discountAmount > 0
                ? `
                    <div class="flex flex-col items-end text-right">
                        <span class="text-danger-600 text-sm font-semibold">Rp ${formatRp(itemSubtotal)}</span>
                        <span class="text-neutral-400 dark:text-ink-d3 text-xs line-through">Rp ${formatRp(itemTotal)}</span>
                    </div>
                `
                : `
                    <div class="flex flex-col items-end text-right">
                        <span class="text-primary-600 text-sm font-semibold">Rp ${formatRp(itemSubtotal)}</span>
                    </div>
                `;

            const keteranganHTML = item.keterangan
                ? `<p class="text-xs text-neutral-400 dark:text-ink-d3 mt-1 italic">📝 ${item.keterangan}</p>`
                : '';

            cartContainer.innerHTML += `
                <div class="produk-body flex gap-3 py-2 border-b border-neutral-200 dark:border-line-dark">
                    <img src="${imgSrc}" alt="${item.name}" class="rounded w-12 h-12 object-cover">
                    <div class="w-full">
                        <h5 class="font-semibold text-sm">${item.name}</h5>
                        <p class="text-xs text-neutral-500 dark:text-ink-d2">${item.kategori?.name ?? ''}</p>
                        ${keteranganHTML}
                        <div class="flex items-center justify-between gap-3 mt-2 w-full">
                            <div class="flex items-center gap-2">
                                <button class="minus w-6 h-6 text-primary-600 border border-primary-600 rounded flex items-center justify-center" data-id="${item.id}">-</button>
                                <span>${item.qty}</span>
                                <button class="plus w-6 h-6 bg-primary-600 text-white rounded flex items-center justify-center" data-id="${item.id}">+</button>
                            </div>
                            ${hargaHTML}
                        </div>
                    </div>
                </div>
            `;
        });

        diskonBarang = totalItemDiscount;
        const totalDiskonAll = totalItemDiscount + (Number(diskon) || 0);
        const finalTotal = Math.max(totalBeforeDiscount - totalDiskonAll, 0);

        totalItemEl.innerText = `${totalItems} Items`;

        const cartBadge = document.getElementById('pos-tab-cart-badge');
        if (cartBadge) {
            cartBadge.textContent = totalItems;
            cartBadge.style.display = totalItems > 0 ? 'inline-flex' : 'none';
        }

        totalHargaEl.innerText = 'Rp ' + formatRp(totalBeforeDiscount);
        diskonBarangEl.innerText = '-Rp ' + formatRp(totalItemDiscount);
        diskonInputEl.innerText = '-Rp ' + formatRp(diskon);
        totalDiskonEl.innerText = '-Rp ' + formatRp(totalDiskonAll);

        const tagihanValueEl = totalTagihanEl.querySelector('span:last-child');
        if (tagihanValueEl) {
            tagihanValueEl.innerText = 'Rp ' + formatRp(finalTotal);
        } else {
            totalTagihanEl.innerText = 'Rp ' + formatRp(finalTotal);
        }
    }

    // Event delegation untuk tombol +/-
    cartContainer.addEventListener('click', function(e) {
        const plusBtn = e.target.closest('.plus');
        const minusBtn = e.target.closest('.minus');
        if (plusBtn) adjustQty(plusBtn.dataset.id, 1);
        if (minusBtn) adjustQty(minusBtn.dataset.id, -1);
    });

    // === Event delegation untuk form tambah cart ===
    document.addEventListener('submit', function(e) {
        const form = e.target.closest('.form-tambah-cart');
        if (!form) return;
        e.preventDefault();

        const productId = String(form.dataset.productId);
        const productName = form.dataset.name;
        const image = form.dataset.image;
        const categoryName = form.dataset.category;
        const price = parseFloat(form.dataset.price) || 0;
        const discount = parseFloat(form.dataset.discount) || 0;
        const discount_type = form.dataset.discount_type || '';
        const qty = parseInt(form.querySelector('input[name="qty"]').value) || 1;
        const keterangan = form.querySelector('textarea[name="keterangan"]').value || '';

        const existing = cart.find(p => p.id === productId);
        if (existing) {
            existing.qty += qty;
            existing.keterangan = keterangan;
        } else {
            cart.push({
                id: productId,
                name: productName,
                qty,
                price,
                discount,
                discount_type,
                kategori: { name: categoryName },
                image,
                keterangan,
            });
        }

        form.querySelector('input[name="qty"]').value = '';
        form.querySelector('textarea[name="keterangan"]').value = '';

        HexaModal.hide('tambah-product-modal-generic');

        updateCartUI();
    });

    function adjustQty(id, delta) {
        const item = cart.find(p => p.id == id);
        if (!item) return;
        item.qty += delta;
        if (item.qty <= 0) {
            cart = cart.filter(p => p.id != id);
        }
        updateCartUI();
    }

    btnEmptyCart.addEventListener('click', function () {
        if (cart.length === 0) {
            Swal.fire('Kosong', 'Cart sudah kosong!', 'info');
            return;
        }
        Swal.fire({
            title: 'Kosongkan Cart?',
            text: 'Semua item akan dihapus!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, kosongkan',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                cart = [];
                diskon = 0;
                if (customerNameInput) customerNameInput.value = '';
                updateCartUI();
                Swal.fire('Berhasil', 'Cart dikosongkan', 'success');
            }
        });
    });

    // === Tombol Hold Transaksi ===
    const btnHold = document.querySelector('.btn-hold');
    if (btnHold) {
        btnHold.addEventListener('click', function () {
            if (cart.length === 0) {
                Swal.fire('Kosong', 'Tidak ada item dalam cart!', 'warning');
                return;
            }

            const currentCustomerName = customerNameInput ? customerNameInput.value.trim() : '';

            fetch('{{ route("kasir.hold") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    cart,
                    diskon,
                    diskon_barang: diskonBarang,
                    customer_name: currentCustomerName,
                    old_transaction_id: currentTransactionId ?? null
                })
            })
            .then(res => {
                if (res.status === 419) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sesi Berakhir',
                        text: 'Sesi login sudah habis. Halaman akan dimuat ulang, lalu ulangi pembayaran.',
                        allowOutsideClick: false,
                        confirmButtonText: 'Muat Ulang'
                    }).then(() => location.reload());
                    return;
                }
                return res.json();
            })
            .then(data => {
                if (!data) return;
                if (data.success) {
                    Swal.fire({
                        title: 'Tersimpan',
                        text: 'Transaksi di-hold!',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal', data.message || 'Gagal menyimpan hold.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Gagal menyimpan transaksi!', 'error');
            });
        });
    }

    // === Form Diskon ===
    const formDiskon = document.querySelector('#diskon-modal form');
    if (formDiskon) {
        formDiskon.addEventListener('submit', function(e) {
            e.preventDefault();
            const inputDiskon = parseFloat(document.getElementById('diskon').value) || 0;
            diskon = inputDiskon;
            updateCartUI();
            Swal.fire('Berhasil', 'Diskon berhasil diterapkan!', 'success');
            HexaModal.hide('diskon-modal');
        });
    }

    // === Saat Modal Pembayaran Dibuka ===
    document.querySelectorAll('#btn-open-bayar').forEach(btn => {
        btn.addEventListener('click', function () {
            let totalSebelumDiskon = 0;
            let totalDiskonBarang = 0;

            cart.forEach(item => {
                const subtotal = item.price * item.qty;
                let diskonItem = 0;

                if (item.discount && Number(item.discount) > 0) {
                    if ((item.discount_type || '') === 'percent') {
                        diskonItem = (item.price * item.discount / 100) * item.qty;
                    } else {
                        diskonItem = (Number(item.discount) || 0) * item.qty;
                    }
                }

                totalSebelumDiskon += subtotal;
                totalDiskonBarang += diskonItem;
            });

            const totalDiskon = totalDiskonBarang + (Number(diskon) || 0);
            const totalTagihan = Math.max(totalSebelumDiskon - totalDiskon, 0);

            const totalInput = document.getElementById('total_harus_bayar');
            if (totalInput) {
                totalInput.value = totalTagihan.toLocaleString('id-ID');
            }

            document.getElementById('bayar').value = '';
            document.getElementById('kembalian').value = '';
        });
    });

    // === Buka modal produk generic — populate form dari data-* card ===
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-open-product-modal');
        if (!btn) return;
        if (btn.dataset.unavailable === '1') return;

        const form = document.querySelector('#tambah-product-modal-generic .form-tambah-cart');
        if (!form) return;

        form.dataset.productId     = btn.dataset.productId;
        form.dataset.name          = btn.dataset.name;
        form.dataset.price         = btn.dataset.price;
        form.dataset.discount      = btn.dataset.discount;
        form.dataset.discount_type = btn.dataset.discount_type;
        form.dataset.image         = btn.dataset.image;
        form.dataset.category      = btn.dataset.category;

        form.querySelector('input[name="qty"]').value        = '';
        form.querySelector('textarea[name="keterangan"]').value = '';

        HexaModal.show('tambah-product-modal-generic');
    });

    // === Auto hitung kembalian ===
    document.getElementById('bayar').addEventListener('input', function () {
        const totalBayar = parseInt(this.value.replace(/[^\d]/g, '')) || 0;
        const totalTagihan = parseInt(document.getElementById('total_harus_bayar').value.replace(/[^\d]/g, '')) || 0;
        const kembalian = totalBayar - totalTagihan;

        document.getElementById('kembalian').value = kembalian > 0
            ? kembalian.toLocaleString('id-ID')
            : '0';
    });

    // === FORM PEMBAYARAN ===
    document.getElementById('form-pembayaran').addEventListener('submit', async (e) => {
        e.preventDefault();

        if (cart.length === 0) {
            Swal.fire('Kosong', 'Tidak ada item di cart untuk dibayar!', 'warning');
            return;
        }

        const metodePembayaran = document.getElementById('metode_pembayaran').value;
        const dibayarkan = parseFloat(document.getElementById('bayar').value.replace(/[^\d]/g, '')) || 0;
        const kembalian = parseFloat(document.getElementById('kembalian').value.replace(/[^\d]/g, '')) || 0;
        const printNota = document.getElementById('print_nota').checked;
        const customerName = customerNameInput ? customerNameInput.value.trim() : '';

        if (!metodePembayaran) {
            Swal.fire('Oops!', 'Pilih metode pembayaran terlebih dahulu.', 'warning');
            return;
        }

        const totalTagihan = parseFloat(document.getElementById('total_harus_bayar').value.replace(/[^\d]/g, '')) || 0;
        if (dibayarkan < totalTagihan) {
            Swal.fire('Oops!', 'Jumlah yang dibayarkan kurang dari total tagihan!', 'warning');
            return;
        }

        const payload = {
            cart,
            diskon,
            diskon_barang: diskonBarang,
            metode_pembayaran: metodePembayaran,
            dibayarkan,
            kembalian,
            customer_name: customerName,
            transaction_id: currentTransactionId ?? null
        };

        try {
            Swal.fire({
                title: 'Memproses Pembayaran...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const res = await fetch('{{ url("/kasir/bayar") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            if (res.status === 419) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sesi Berakhir',
                    text: 'Sesi login sudah habis. Halaman akan dimuat ulang, lalu ulangi pembayaran.',
                    allowOutsideClick: false,
                    confirmButtonText: 'Muat Ulang'
                }).then(() => location.reload());
                return;
            }

            const data = await res.json();

            if (data.success) {
                Swal.close();

                if (printNota && data.transaction_id) {
                    const printUrl = `/kasir/print-nota/${data.transaction_id}`;
                    window.open(printUrl, '_blank');

                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Berhasil!',
                        html: `
                            <p>${data.message}</p>
                            <div class="mt-3 p-3 bg-green-50 rounded-lg">
                                <i class="ri-printer-fill text-green-600"></i>
                                <span class="text-sm text-gray-600">Nota sedang diunduh...</span>
                            </div>
                        `,
                        timer: 3000,
                        showConfirmButton: true
                    });
                } else {
                    Swal.fire('Berhasil!', data.message, 'success');
                }

                cart = [];
                diskon = 0;
                diskonBarang = 0;
                if (customerNameInput) customerNameInput.value = '';
                updateCartUI();
                currentTransactionId = null;

                HexaModal.hide('bayar-modal');

                document.getElementById('form-pembayaran').reset();
                document.getElementById('print_nota').checked = true;

            } else {
                Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat memproses pembayaran.', 'error');
            }
        } catch (err) {
            Swal.fire('Error!', 'Gagal menghubungi server.', 'error');
            console.error(err);
        }
    });

    // === Hold Items: cache untuk data aksi tombol ===
    let holdItemsCache = {};

    // === AjaxTable untuk Hold Items ===
    AjaxTable.init('holdItems', {
        url: '{{ route("kasir.hold.datatable") }}',
        colSpan: 11,
        renderRow: function(item) {
            holdItemsCache[item.id] = {
                items:         item.items_json,
                diskon:        item.diskon,
                diskon_barang: item.diskon_barang,
                customer_name: item.customer_name_raw,
            };
            return `<tr>
                <td class="whitespace-nowrap">
                    <button type="button" class="btn-view-detail btn-action" data-hold-id="${item.id}">
                        <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                    </button>
                    <button type="button" class="btn-load-cart btn-action" data-hold-id="${item.id}">
                        <i class="ri-shopping-bag-fill"></i>
                    </button>
                    <button type="button" class="btn-delete-hold btn-action btn-action-del" data-hold-id="${item.id}">
                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                    </button>
                </td>
                <td class="whitespace-nowrap">${item.no}</td>
                <td class="whitespace-nowrap">${item.kode_transaksi}</td>
                <td class="whitespace-nowrap">${item.customer_name}</td>
                <td class="whitespace-nowrap">${item.qty}</td>
                <td class="whitespace-nowrap">Rp ${Number(item.harga_sebelum_diskon).toLocaleString('id-ID')}</td>
                <td class="whitespace-nowrap">Rp ${Number(item.total_amount).toLocaleString('id-ID')}</td>
                <td class="whitespace-nowrap">Rp ${Number(item.diskon).toLocaleString('id-ID')}</td>
                <td class="whitespace-nowrap">Rp ${Number(item.diskon_barang).toLocaleString('id-ID')}</td>
                <td class="whitespace-nowrap text-xs italic text-neutral-500">${item.keterangan_flag ? '📝 Ada catatan' : '-'}</td>
                <td class="whitespace-nowrap">${item.created_at}</td>
            </tr>`;
        }
    });

    // === Buka Hold Items modal + refresh data ===
    document.querySelectorAll('.btn-hold-items').forEach(btn => {
        btn.addEventListener('click', function () {
            HexaModal.show('hold-items-modal');
            if (window._ajaxTables && window._ajaxTables['tbodyHoldItems']) {
                window._ajaxTables['tbodyHoldItems'].refresh();
            }
        });
    });

    // === Event delegation untuk aksi tabel hold ===
    document.addEventListener('click', function(e) {
        const viewBtn   = e.target.closest('#hold-items-modal .btn-view-detail');
        const loadBtn   = e.target.closest('#hold-items-modal .btn-load-cart');
        const deleteBtn = e.target.closest('#hold-items-modal .btn-delete-hold');

        if (viewBtn) {
            const cached = holdItemsCache[viewBtn.dataset.holdId];
            if (!cached) return;
            const items = cached.items;
            let html = `
                <div class="card border-0 overflow-hidden">
                    <div class="card-header">
                        <h5 class="card-title text-lg mb-0">Detail Transaksi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table basic-border-table mb-0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Produk</th>
                                        <th>Keterangan</th>
                                        <th>Qty</th>
                                        <th>Harga</th>
                                        <th>Diskon per Barang</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;
            if (items.length > 0) {
                items.forEach((it, i) => {
                    const subtotal = (it.qty * it.price) - (it.diskon * it.qty);
                    html += `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${it.product_name}</td>
                            <td><span class="cell-ellipsis" title="${htmlEsc(it.keterangan ?? '-')}">${it.keterangan ?? '-'}</span></td>
                            <td>${it.qty}</td>
                            <td>Rp ${it.price.toLocaleString('id-ID')}</td>
                            <td>Rp ${it.diskon.toLocaleString('id-ID')}</td>
                            <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });
            } else {
                html += `<tr><td colspan="7" class="text-center py-3">Tidak ada item.</td></tr>`;
            }
            html += `</tbody></table></div></div></div>`;
            Swal.fire({ html, showConfirmButton: true, confirmButtonText: 'Tutup', width: '800px' });
            return;
        }

        if (loadBtn) {
            const holdId = loadBtn.dataset.holdId;
            const cached = holdItemsCache[holdId];
            if (!cached) return;

            Swal.fire({
                title: 'Ganti Cart Sekarang?',
                text: 'Transaksi yang di-hold akan dimuat ke keranjang dan cart saat ini akan diganti.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Override',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    cart = cached.items.map(it => ({
                        id: String(it.product_id),
                        name: it.product_name,
                        qty: it.qty,
                        price: it.price,
                        discount: it.diskon ?? 0,
                        discount_type: 'nominal',
                        kategori: { name: it.kategori ?? '' },
                        image: it.image ?? null,
                        keterangan: it.keterangan ?? '',
                    }));

                    diskon = parseFloat(cached.diskon) || 0;
                    diskonBarang = parseFloat(cached.diskon_barang) || 0;
                    currentTransactionId = parseInt(holdId);

                    if (customerNameInput) customerNameInput.value = cached.customer_name;

                    updateCartUI();
                    HexaModal.hide('hold-items-modal');
                    Swal.fire('Berhasil', 'Transaksi berhasil dimuat ke cart!', 'success');
                }
            });
            return;
        }

        if (deleteBtn) {
            const holdId = deleteBtn.dataset.holdId;

            Swal.fire({
                title: 'Hapus Transaksi Hold?',
                text: 'Data hold ini akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/kasir/hold/${holdId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Terhapus!', 'Transaksi hold berhasil dihapus.', 'success');
                            if (currentTransactionId == holdId) {
                                currentTransactionId = null;
                            }
                            if (window._ajaxTables && window._ajaxTables['tbodyHoldItems']) {
                                window._ajaxTables['tbodyHoldItems'].refresh();
                            }
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Gagal menghubungi server.', 'error'));
                }
            });
        }
    });

    // === More dropdown ===
    const moreToggle = document.getElementById('moreToggle');
    const dropdownMore = document.getElementById('dropdownMore');
    const chevronMore = document.getElementById('chevronMore');

    if (moreToggle && dropdownMore && chevronMore) {
        moreToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdownMore.classList.toggle('hidden');
            chevronMore.classList.toggle('rotate-180');
        });

        document.addEventListener('click', function (e) {
            if (!dropdownMore.contains(e.target) && !moreToggle.contains(e.target)) {
                dropdownMore.classList.add('hidden');
                chevronMore.classList.remove('rotate-180');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                dropdownMore.classList.add('hidden');
                chevronMore.classList.remove('rotate-180');
            }
        });
    }

    updateCartUI();

    // Jaga sesi tetap hidup selama tab POS terbuka (ping tiap 15 menit)
    setInterval(() => {
        fetch('{{ route("session.ping") }}', { method: 'GET', credentials: 'same-origin' })
            .catch(() => {});
    }, 15 * 60 * 1000);
});

// === POS mobile tab toggle (Produk / Keranjang) ===
(function () {
    const shell = document.querySelector('.pos-shell');
    if (!shell) return;
    const tabs = document.querySelectorAll('.pos-tab');
    function setActive(name) {
        shell.setAttribute('data-active', name);
        tabs.forEach(t => {
            const on = t.dataset.tab === name;
            t.classList.toggle('text-primary-600', on);
            t.classList.toggle('border-primary-600', on);
            t.classList.toggle('text-neutral-500', !on);
            t.classList.toggle('border-transparent', !on);
        });
    }
    tabs.forEach(t => t.addEventListener('click', () => setActive(t.dataset.tab)));
    setActive('produk');
})();
</script>

{{-- Product grid: AJAX fetch + pagination --}}
<script>
(function () {
    var CAT_COLORS = ['#f97316','#3b82f6','#10b981','#8b5cf6','#ec4899',
                      '#f59e0b','#14b8a6','#f43f5e','#6366f1','#06b6d4'];
    var _page = 1, _search = '', _kat = '', _searchTimer = null;

    function fmtRp(n) {
        return Number(n || 0).toLocaleString('id-ID');
    }

    function renderCard(p) {
        var isUnavailable = !p.is_active || p.quantity <= 0;
        var bgColor = CAT_COLORS[(p.kategori_product_id || 0) % CAT_COLORS.length];

        var imageHtml = p.image
            ? '<img src="/storage/' + p.image + '" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">'
            : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background-color:' + bgColor + ';">'
              + '<iconify-icon icon="lucide:shopping-bag" class="text-white opacity-80" style="font-size:2rem;"></iconify-icon>'
              + '</div>';

        var overlay = '';
        if (!p.is_active) {
            overlay = '<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(23,23,23,.5);">'
                    + '<span style="color:#fff;font-size:11px;font-weight:700;padding:2px 10px;border-radius:999px;background:#404040;">Nonaktif</span>'
                    + '</div>';
        } else if (p.quantity <= 0) {
            overlay = '<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(23,23,23,.4);">'
                    + '<span style="color:#fff;font-size:11px;font-weight:700;padding:2px 10px;border-radius:999px;background:#dc2626;">Habis</span>'
                    + '</div>';
        }

        var stockBadge = !isUnavailable
            ? '<span style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,.5);color:#fff;font-size:10px;font-weight:600;padding:2px 6px;border-radius:999px;">' + p.quantity + '</span>'
            : '';

        var discountHtml = p.discount > 0
            ? '<p class="text-danger-500" style="font-size:10px;margin:4px 0 0;line-height:1.3;">Diskon: '
              + (p.discount_type === 'percent' ? p.discount + '%' : 'Rp ' + fmtRp(p.discount))
              + '</p>'
            : '';

        var outerStyle = 'display:flex;flex-direction:column;border-radius:16px;overflow:hidden;position:relative;min-height:196px;'
            + (isUnavailable ? 'opacity:0.6;' : '');
        var hoverCls = isUnavailable ? '' : 'hover:border-primary-400 hover:shadow-md';

        return '<div'
            + ' data-product-id="' + p.id + '"'
            + ' data-name="' + p.name.replace(/"/g, '&quot;') + '"'
            + ' data-price="' + p.price + '"'
            + ' data-discount="' + p.discount + '"'
            + ' data-discount_type="' + p.discount_type + '"'
            + ' data-image="' + (p.image || '') + '"'
            + ' data-category="' + (p.kategori_name || '').replace(/"/g, '&quot;') + '"'
            + ' data-category-id="' + (p.kategori_product_id || '') + '"'
            + ' data-unavailable="' + (isUnavailable ? '1' : '0') + '"'
            + ' class="btn-open-product-modal group border border-neutral-200 dark:border-line-dark bg-white dark:bg-surface-dark transition-all cursor-pointer ' + hoverCls + '"'
            + ' style="' + outerStyle + '">'

            + '<div style="position:relative;width:100%;height:96px;flex-shrink:0;">'
            + imageHtml + overlay + stockBadge
            + '</div>'

            + '<div style="display:flex;flex-direction:column;flex:1;padding:10px 10px 14px;">'
            + '<p class="text-neutral-400 dark:text-ink-d3" style="font-size:10px;margin:0 0 2px;line-height:1.3;">' + (p.kategori_name || '-') + '</p>'
            + '<h6 class="font-semibold text-sm text-ink dark:text-ink-d" style="margin:0 0 auto;line-height:1.3;">' + p.name + '</h6>'
            + '<div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;gap:4px;">'
            + '<span class="text-primary-600 font-bold text-sm tabular-nums">Rp ' + fmtRp(p.price) + '</span>'
            + '<span class="bg-success-500 text-white" style="width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.1rem;font-weight:700;line-height:1;padding:0;user-select:none;">+</span>'
            + '</div>'
            + discountHtml
            + '</div>'
            + '</div>';
    }

    function renderPagination(page, lastPage) {
        var el = document.getElementById('product-grid-pagination');
        if (!el) return;
        if (lastPage <= 1) { el.innerHTML = ''; return; }

        var base = 'px-3 py-1 rounded border text-xs transition-colors duration-150';
        var norm = base + ' border-neutral-200 dark:border-line-dark text-neutral-600 dark:text-ink-d2 hover:bg-neutral-100 dark:hover:bg-surface-dark-raised cursor-pointer';
        var act  = base + ' bg-primary-500 border-primary-500 text-white font-semibold';
        var dis  = base + ' border-neutral-200 dark:border-line-dark text-neutral-300 dark:text-ink-d3 opacity-40 cursor-not-allowed';

        var parts = [];
        parts.push('<button onclick="fetchProductGrid(' + (page - 1) + ')" '
            + (page <= 1 ? 'disabled ' : '') + 'class="' + (page <= 1 ? dis : norm) + '">&laquo;</button>');

        var start = Math.max(1, page - 2);
        var end   = Math.min(lastPage, start + 4);
        if (end - start < 4) start = Math.max(1, end - 4);

        for (var i = start; i <= end; i++) {
            parts.push('<button onclick="fetchProductGrid(' + i + ')" class="' + (i === page ? act : norm) + '">' + i + '</button>');
        }
        if (end < lastPage) {
            parts.push('<span class="px-2 text-xs text-neutral-400 dark:text-ink-d3">...</span>');
            parts.push('<button onclick="fetchProductGrid(' + lastPage + ')" class="' + norm + '">' + lastPage + '</button>');
        }
        parts.push('<button onclick="fetchProductGrid(' + (page + 1) + ')" '
            + (page >= lastPage ? 'disabled ' : '') + 'class="' + (page >= lastPage ? dis : norm) + '">&raquo;</button>');

        el.innerHTML = parts.join('');
    }

    window.fetchProductGrid = function (page) {
        _page = page || 1;
        var grid = document.getElementById('product-grid');
        if (!grid) return;

        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px 0;color:#9ca3af;font-size:14px;">Memuat produk...</div>';

        var params = new URLSearchParams({ page: _page, search: _search, kategori: _kat });
        fetch('{{ route("kasir.products.grid") }}?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function (data) {
            if (!data.data || data.data.length === 0) {
                grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px 0;color:#9ca3af;font-size:14px;">Tidak ada produk ditemukan.</div>';
                renderPagination(1, 1);
                return;
            }
            grid.innerHTML = data.data.map(renderCard).join('');
            renderPagination(data.page, data.lastPage);
        })
        .catch(function (err) {
            grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px 0;color:#dc2626;font-size:14px;">Gagal memuat produk.</div>';
            console.error(err);
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        // Category pills → re-fetch server
        document.querySelectorAll('.category-pill').forEach(function (pill) {
            pill.addEventListener('click', function () {
                document.querySelectorAll('.category-pill').forEach(function (p) {
                    p.classList.remove('active', 'bg-primary-500', 'text-white');
                    p.classList.add('bg-neutral-100', 'text-neutral-600', 'dark:bg-surface-dark-raised', 'dark:text-ink-d2');
                });
                this.classList.add('active', 'bg-primary-500', 'text-white');
                this.classList.remove('bg-neutral-100', 'text-neutral-600', 'dark:bg-surface-dark-raised', 'dark:text-ink-d2');
                _kat = this.dataset.category === 'all' ? '' : this.dataset.category;
                _search = '';
                var si = document.getElementById('product-search');
                if (si) si.value = '';
                fetchProductGrid(1);
            });
        });

        // Search input → debounce → re-fetch server
        var searchInput = document.getElementById('product-search');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(_searchTimer);
                var val = this.value;
                _searchTimer = setTimeout(function () {
                    _search = val;
                    fetchProductGrid(1);
                }, 400);
            });
        }

        // Initial load
        fetchProductGrid(1);
    });
}());
</script>
@endsection
