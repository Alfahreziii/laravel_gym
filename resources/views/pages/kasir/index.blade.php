<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wowdash - Tailwind CSS Admin Dashboard Laravel-11 Template</title>
    <link rel="icon" type="image/png') }}" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">
    <!-- google fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <!-- remix icon font css  -->
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">

    <!-- Apex Chart css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/apexcharts.css') }}">
    <!-- Data Table css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/dataTables.min.css') }}">
    <!-- Text Editor css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/editor-katex.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/editor.atom-one-dark.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/editor.quill.snow.css') }}">
    <!-- Date picker css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/flatpickr.min.css') }}">
    <!-- Calendar css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/full-calendar.css') }}">
    <!-- Vector Map css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/jquery-jvectormap-2.0.5.css') }}">
    <!-- Popup css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/magnific-popup.css') }}">
    <!-- Slick Slider css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/slick.css') }}">
    <!-- prism css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/prism.css') }}">
    <!-- file upload css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/file-upload.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/audioplayer.css') }}">
    <!-- main css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
        
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const profileToggle = document.getElementById('profileToggle');
            const dropdown = document.getElementById('dropdownProfile');
            const chevron = document.getElementById('chevronIcon');

            if (!profileToggle || !dropdown || !chevron) return;

            // pastikan icon punya animasi
            chevron.classList.add('transition-rotate');

            // Ketika tombol profile diklik: tunggu sebentar lalu sinkronkan icon
            profileToggle.addEventListener('click', function (e) {
                // beri waktu ke script dropdown (jika ada) untuk toggle class hidden
                setTimeout(() => {
                    if (dropdown.classList.contains('hidden')) {
                        chevron.classList.remove('rotate-180');
                    } else {
                        chevron.classList.add('rotate-180');
                    }
                }, 0);
            });

            // Klik di luar -> pastikan dropdown ditutup dan chevron kembali
            document.addEventListener('click', function (e) {
                if (!dropdown.contains(e.target) && !profileToggle.contains(e.target)) {
                    // jika dropdown masih terbuka, tutup & reset icon
                    if (!dropdown.classList.contains('hidden')) {
                        dropdown.classList.add('hidden'); // aman jika library sudah menutupnya
                    }
                    chevron.classList.remove('rotate-180');
                }
            });

            // Tombol ESC juga menutup dropdown + reset icon
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' || e.key === 'Esc') {
                    if (!dropdown.classList.contains('hidden')) {
                        dropdown.classList.add('hidden');
                    }
                    chevron.classList.remove('rotate-180');
                }
            });
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
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

        // tombol Hold Items buka modal
        document.querySelectorAll('.btn-hold-items').forEach(btn => {
            btn.addEventListener('click', function () {
                const targetModal = document.getElementById(this.dataset.modalTarget);
                if (targetModal) {
                    targetModal.classList.remove('hidden');
                }
            });
        });

        // tombol close modal
        document.querySelectorAll('.btn-close-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                this.closest('.hidden')?.classList.add('hidden');
                this.closest('[id$="-modal"]')?.classList.add('hidden'); // fallback
            });
        });
    });
</script>

</head>
<body class="dark:bg-neutral-800 bg-neutral-100">
    <div class="flex">
        <div class="bg-white">
            <a href="{{ route('index') }}" class="sidebar-logo">
                <img src="{{ asset('assets/images/logo.png') }}" alt="site logo" class="light-logo">
                <img src="{{ asset('assets/images/logo-light.png') }}" alt="site logo" class="dark-logo">
                <img src="{{ asset('assets/images/logo-icon.png') }}" alt="site logo" class="logo-icon">
            </a>
        </div>
        <div class="navbar-header border-b border-neutral-200 w-full">
            <div class="flex items-center justify-end py-2">
                <div class="col-auto">
                    <div class="flex flex-wrap items-center gap-3">
                        <button data-dropdown-toggle="dropdownProfile" id="profileToggle"
                            class="flex justify-center items-center rounded-full gap-1" type="button">
                            {{ Auth::user()->name }}
                            <iconify-icon id="chevronIcon" icon="mdi:chevron-down" class="text-lg transition-transform duration-200"></iconify-icon>
                        </button>
                        <div id="dropdownProfile" class="z-10 hidden bg-white rounded-lg shadow-lg dropdown-menu-sm p-3">
                            <div class="py-3 px-4 rounded-lg bg-primary-50 mb-4 flex items-center justify-between gap-2">
                                <div>
                                    <h6 class="text-lg text-neutral-900 font-semibold mb-0">{{ Auth::user()->name }}</h6>
                                    <span class="text-neutral-500">Admin</span>
                                </div>
                            </div>
    
                            <div class="max-h-[400px] overflow-y-auto scroll-sm pe-2">
                                <ul class="flex flex-col">
                                    <li>
                                        <a class="text-black px-0 py-2 hover:text-primary-600 flex items-center gap-4" href="{{ route('viewProfile') }}">
                                            <iconify-icon icon="solar:user-linear" class="icon text-xl"></iconify-icon>  My Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a class="text-black px-0 py-2 hover:text-primary-600 flex items-center gap-4" href="{{ route('email') }}">
                                            <iconify-icon icon="tabler:message-check" class="icon text-xl"></iconify-icon>  Inbox
                                        </a>
                                    </li>
                                    <li>
                                        <a class="text-black px-0 py-2 hover:text-primary-600 flex items-center gap-4" href="{{ route('company') }}">
                                            <iconify-icon icon="icon-park-outline:setting-two" class="icon text-xl"></iconify-icon>  Setting
                                        </a>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit"
                                                class="text-black px-0 py-2 hover:text-danger-600 flex items-center gap-4">
                                                <iconify-icon icon="lucide:power" class="icon text-xl"></iconify-icon> Log Out
                                            </button>
                                        </form>
                                        <!-- <a class="text-black px-0 py-2 hover:text-danger-600 flex items-center gap-4" href="javascript:void(0)">
                                            <iconify-icon icon="lucide:power" class="icon text-xl"></iconify-icon>  Log Out
                                        </a> -->
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="wrapper w-full">
        <div class="flex">
            <div class="content-produk">
                <div class="grid grid-cols-12">
                    <div class="col-span-12">
                        <div class="card border-0 overflow-hidden">
                            <div class="card-header flex items-center justify-between">
                                <h6 class="card-title mb-0 text-lg">Produk</h6>
                            </div>
                            <div class="card-body">
                                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate w-full">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Foto Produk</th>
                                            <th>Nama Produk</th>
                                            <th>Harga</th>
                                            <th>Diskon</th>
                                            <th>Stok</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($products as $index => $product)
                                            <tr>
                                                <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                                                <td class="whitespace-nowrap">
                                                    @if($product->image)
                                                        <img src="{{ asset('storage/' . $product->image) }}" 
                                                            alt="image {{ $product->name }}" 
                                                            class="w-10 h-10 rounded-full object-cover">
                                                    @else
                                                        <img src="{{ asset('assets/images/kasir/product-placeholder.png') }}" 
                                                            alt="image {{ $product->name }}" 
                                                            class="w-10 h-10 rounded-full object-cover">
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap">
                                                    {{ $product->name }}
                                                </td>
                                                <td class="whitespace-nowrap">
                                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                                </td>
                                                <td class="whitespace-nowrap">
                                                    @if($product->discount > 0)
                                                        {{ $product->discount }}
                                                        {{ $product->discount_type == 'percent' ? '%' : 'Rp' }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap">
                                                    {{ $product->quantity }}
                                                </td>
                                                <td class="whitespace-nowrap ">
                                                    @if($product->is_active)
                                                        <span class="bg-success-100 text-success-600 px-4 py-1.5 rounded-full font-medium text-sm">Aktif</span>
                                                    @else
                                                        <span class="bg-danger-100 text-danger-600 px-4 py-1.5 rounded-full font-medium text-sm">Nonaktif</span>
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap flex gap-2 items-center justify-center">
                                                    <button 
                                                        type="button" 
                                                        data-modal-target="tambah-product-modal-{{ $product->id }}" 
                                                        data-modal-toggle="tambah-product-modal-{{ $product->id }}"
                                                        data-name="{{ $product->name }}"
                                                        data-price="{{ $product->price }}"
                                                        data-image="{{ $product->image }}"
                                                        data-category="{{ $product->kategori->name }}"
                                                        class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                                                        <i class="ri-shopping-bag-fill"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @foreach($products as $product)
                                {{-- MODAL TAMBAH PRODUCT --}}
                                <div id="tambah-product-modal-{{ $product->id }}" tabindex="-1" 
                                    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 
                                    justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                                    <div class="rounded-2xl bg-white max-w-[800px] w-full">
                                        <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
                                            <h1 class="text-xl">Tambah Product</h1>
                                            <button data-modal-hide="tambah-product-modal-{{ $product->id }}" type="button"
                                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                                </svg>
                                                <span class="sr-only">Close modal</span>
                                            </button>
                                        </div>

                                        <div class="p-6">
                                            <form 
                                                data-product-id="{{ $product->id }}"  
                                                data-name="{{ $product->name }}"
                                                data-price="{{ $product->price }}"
                                                data-image="{{ $product->image }}"
                                                data-category="{{ $product->kategori->name ?? 'ada nih' }}"
                                                class="form-tambah-cart">

                                                @csrf
                                                @method('PUT')

                                                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                                    <div class="col-span-12">
                                                        <label for="quantity_{{ $product->id }}" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                            Quantity
                                                        </label>
                                                        <input type="text" id="quantity_{{ $product->id }}" name="qty"
                                                            class="form-control rounded-lg" required>
                                                    </div>

                                                    <div class="col-span-12 mt-4 flex items-center gap-3">
                                                        <button type="button" data-modal-hide="tambah-product-modal-{{ $product->id }}"
                                                            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                                            Cancel
                                                        </button>
                                                        <button type="submit" data-modal-hide="tambah-product-modal-{{ $product->id }}"
                                                            class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                                                            Tambah
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cart-produk flex flex-col justify-between wrapper-produk-detail">
                <div class="">
                    <div class="produk-header flex items-center justify-between">
                        <h6 class="card-title mb-0 text-lg">Detail Items</h6>
                    </div>
                    
                    <!-- tempat item cart muncul -->
                    <div class="produk-body-container mt-3"></div>

                    <div class="produk-footer mt-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-neutral-500">Total Items</span>
                            <span class="font-medium text-sm total-items">0 Items</span>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-neutral-500">Total Harga</span>
                            <span class="font-medium text-sm total-harga">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-neutral-500">Diskon</span>
                            <span class="font-medium text-danger-600 text-sm">-Rp. 50,000</span>
                        </div>
                        <div class="flex items-center justify-between total-tagihan py-2">
                            <span class="text-sm text-neutral-500">Total Tagihan</span>
                            <span class="font-medium text-sm">Rp 0</span>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="flex mt-4 gap-2 relative">
                        <button id="btn-empty-cart" class="w-full py-2 bg-danger-600 text-white rounded-lg">Empty <i class="ri-delete-bin-line"></i></button>

                        <button id="moreToggle"
                            class="w-full flex justify-center items-center py-2 bg-warning-600 text-white rounded-lg gap-1 relative btn-hold-items"
                            type="button">
                            More
                            <iconify-icon id="chevronMore" icon="mdi:chevron-down" class="text-lg transition-transform duration-200"></iconify-icon>
                        </button>

                        <div id="dropdownMore" class="hidden absolute mb-2 dropdown-more bg-white text-black rounded-lg shadow-lg w-48 z-50">
                            <ul class="p-2 text-sm">
                                <li><button type="button" class="block px-3 py-2 hover:bg-neutral-100 rounded btn-hold">Hold</button></li>
                                <li><button type="button" data-modal-target="hold-items-modal" data-modal-toggle="hold-items-modal" class="block px-3 py-2 hover:bg-neutral-100 rounded btn-hold-items">Hold Items</button></li>
                            </ul>
                        </div>

                    </div>
                    <button class="w-full py-2 bg-primary-600 text-white rounded-lg mt-2">Bayar <i class="ri-bank-card-fill"></i></button>
                </div>
            </div>
        </div>
    </div>
    
    {{-- MODAL Hold Items --}}
    <div id="hold-items-modal" tabindex="-1"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center 
        w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="rounded-2xl bg-white max-w-[800px] w-full">
            <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
                <h1 class="text-xl">Hold Items</h1>
                <button data-modal-hide="hold-items-modal" type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <div class="card-body">
                <table id="selection-table-2" class="border border-neutral-200 rounded-lg border-separate w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Tanggal Hold</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        let cart = [];
        const cartContainer = document.querySelector('.cart-produk .produk-body-container');
        const totalItemEl = document.querySelector('.produk-footer .total-items');
        const totalHargaEl = document.querySelector('.produk-footer .total-harga');
        const totalTagihanEl = document.querySelector('.produk-footer .total-tagihan');
        const btnEmptyCart = document.getElementById('btn-empty-cart');

        // === Fungsi Update Cart UI ===
        function updateCartUI() {
            if (!cartContainer) return;
            cartContainer.innerHTML = '';

            let total = 0;
            let totalItems = 0;

            cart.forEach(item => {
                const subtotal = item.qty * item.price;
                total += subtotal;
                totalItems += item.qty;

                const imgSrc = item.image 
                    ? `/storage/${item.image}` 
                    : '{{ asset("assets/images/kasir/product-placeholder.png") }}';

                cartContainer.innerHTML += `
                    <div class="produk-body flex gap-3 py-2 border-b border-neutral-200">
                        <img src="${imgSrc}" alt="${item.name}" class="rounded w-12 h-12 object-cover">
                        <div class="w-full">
                            <h5 class="font-semibold text-sm">${item.name}</h5>
                            <p class="text-xs text-neutral-500">${item.kategori.name}</p>
                            <div class="flex items-center justify-between gap-3 mt-2 w-full">
                                <div class="flex items-center gap-2">
                                    <button class="minus w-6 h-6 text-primary-600 border border-primary-600 rounded flex items-center justify-center" data-id="${item.id}">-</button>
                                    <span>${item.qty}</span>
                                    <button class="plus w-6 h-6 bg-primary-600 text-white rounded flex items-center justify-center" data-id="${item.id}">+</button>
                                </div>
                                <span class="text-primary-600 text-sm font-semibold">Rp ${subtotal.toLocaleString()}</span>
                            </div>
                        </div>
                    </div>
                `;
            });

            totalItemEl.innerText = `${totalItems} Items`;
            totalHargaEl.innerText = 'Rp ' + total.toLocaleString();
            totalTagihanEl.querySelector('span:last-child').innerText = 'Rp ' + total.toLocaleString();

            // event plus/minus
            document.querySelectorAll('.plus').forEach(btn => {
                btn.addEventListener('click', () => adjustQty(btn.dataset.id, 1));
            });
            document.querySelectorAll('.minus').forEach(btn => {
                btn.addEventListener('click', () => adjustQty(btn.dataset.id, -1));
            });
        }

        // === Tambah ke Cart dari Modal ===
        document.querySelectorAll('.form-tambah-cart').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const productId = this.dataset.productId;
                const productName = this.dataset.name;
                const image = this.dataset.image;
                const categoryName = this.dataset.category;
                const price = parseFloat(this.dataset.price);
                const qty = parseInt(this.querySelector('input[name="qty"]').value) || 1;

                const existing = cart.find(p => p.id === productId);
                if (existing) {
                    existing.qty += qty;
                } else {
                    cart.push({ id: productId, name: productName, qty, price, kategori: { name: categoryName }, image });
                }

                updateCartUI();
                this.closest('[id^="tambah-product-modal"]').classList.add('hidden');
            });
        });

        // === Ubah Quantity ===
        function adjustQty(id, delta) {
            const item = cart.find(p => p.id === id);
            if (!item) return;
            item.qty += delta;
            if (item.qty <= 0) {
                cart = cart.filter(p => p.id !== id);
            }
            updateCartUI();
        }

        // === Tombol Kosongkan Cart ===
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
                    updateCartUI();
                    Swal.fire('Berhasil', 'Cart dikosongkan', 'success');
                }
            });
        });

        // === Tombol Hold Transaksi ===
        document.querySelector('.btn-hold').addEventListener('click', function () {
            if (cart.length === 0) {
                Swal.fire('Kosong', 'Tidak ada item dalam cart!', 'warning');
                return;
            }

            fetch('{{ route("kasir.hold") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ cart })
            })
            .then(res => res.json())
            .then(data => {
                Swal.fire('Tersimpan', 'Transaksi di-hold!', 'success');
                cart = [];
                updateCartUI();
            });
        });
        
        // === Tombol Hold Items (buka modal + tampilkan data) ===
        document.querySelectorAll('.btn-hold-items').forEach(btn => {
            btn.addEventListener('click', function () {
                const targetModal = document.getElementById(this.dataset.modalTarget);
                if (!targetModal) return;

                targetModal.classList.remove('hidden');
                const tableEl = targetModal.querySelector('#selection-table-2');

                // Bersihkan DataTable lama
                if (tableEl.classList.contains('datatable-initialized')) {
                    const instance = simpleDatatables.DataTable.instances.find(dt => dt.table === tableEl);
                    if (instance) instance.destroy();
                    tableEl.classList.remove('datatable-initialized');
                }

                // Fetch data transaksi hold
                fetch('{{ route("getHeldTransactions") }}')
                    .then(res => res.json())
                    .then(data => {
                        const tbody = tableEl.querySelector('tbody');
                        tbody.innerHTML = '';

                        data.forEach((transaction, index) => {
                            const totalItems = transaction.items.reduce((sum, item) => sum + parseInt(item.qty), 0);
                            const totalHarga = transaction.items.reduce((sum, item) => sum + (item.price * item.qty), 0);

                            tbody.innerHTML += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${transaction.transaction_code}</td>
                                    <td>${totalItems}</td>
                                    <td>Rp ${totalHarga.toLocaleString()}</td>
                                    <td>${new Date(transaction.created_at).toLocaleString()}</td>
                                    <td>
                                        <button 
                                            class="btn-view-detail bg-primary-100 text-primary-600 rounded px-2 py-1"
                                            data-items='${JSON.stringify(transaction.items)}'>
                                            View
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });

                        // ✅ Inisialisasi DataTable hanya sekali
                        const datatable = new simpleDatatables.DataTable(tableEl, {
                            searchable: true,
                            fixedHeight: true,
                            perPageSelect: [5, 10, 15],
                            labels: {
                                placeholder: "Cari...",
                                perPage: "Data per halaman",
                                noRows: "Tidak ada data hold",
                                info: "Menampilkan {start}–{end} dari {rows} data"
                            }
                        });

                        tableEl.classList.add('datatable-initialized');

                        // Tombol view detail
                        tableEl.querySelectorAll('.btn-view-detail').forEach(btn => {
                            btn.addEventListener('click', () => {
                                const items = JSON.parse(btn.dataset.items);
                                let html = '<ul class="text-left">';
                                items.forEach(it => {
                                    html += `<li>${it.name} — ${it.qty} × Rp ${it.price.toLocaleString()}</li>`;
                                });
                                html += '</ul>';

                                Swal.fire({
                                    title: 'Detail Transaksi',
                                    html,
                                    confirmButtonText: 'Tutup'
                                });
                            });
                        });
                    })
                    .catch(err => console.error('Error fetching held transactions:', err));
            });
        });
        
        // === Inisialisasi Awal ===
        updateCartUI();
    });
    </script>
    <x-script  script='{!! isset($script) ? $script : "" !!}' />
    <script src="{{ asset('assets/js/data-table.js') }}"></script>
</body>
</html>