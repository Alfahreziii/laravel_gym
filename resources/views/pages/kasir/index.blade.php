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
                                            <th>S.L</th>
                                            <th>Barcode</th>
                                            <th>Nama Alat Gym</th>
                                            <th>Jumlah</th>
                                            <th>Harga</th>
                                            <th>Tanggal Pembelian</th>
                                            <th>Lokasi Alat</th>
                                            <th>Kondisi Alat</th>
                                            <th>Vendor</th>
                                            <th>Kontak</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($alatGyms as $index => $item)
                                        <tr>
                                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                                            <td class="whitespace-nowrap"><a class="text-primary-600" href="{{ route('alat_gym.edit', $item->id) }}">{{ $item->barcode }}</a></td>
                                            <td class="whitespace-nowrap">{{ $item->nama_alat_gym ?? '-' }}</td>
                                            <td class="whitespace-nowrap">{{ $item->jumlah ?? '-' }}</td>
                                            <td class="whitespace-nowrap">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                            <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($item->tgl_pembelian)->format('d M Y') }}</td>
                                            <td class="whitespace-nowrap">{{ $item->lokasi_alat ?? '-' }}</td>
                                            <td class="whitespace-nowrap">{{ $item->kondisi_alat ?? '-' }}</td>
                                            <td class="whitespace-nowrap">{{ $item->vendor ?? '-' }}</td>
                                            <td class="whitespace-nowrap">{{ $item->kontak ?? '-' }}</td>
                                            <td class="whitespace-nowrap flex gap-2">
                                                <a href="{{ route('alat_gym.edit', $item->id) }}" 
                                                class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                                </a>
                                                <a href="{{ route('alat_gym.edit', $item->id) }}" class="w-8 h-8 bg-primary-50 text-primary-600 rounded-full inline-flex items-center justify-center">
                                                <form action="{{ route('alat_gym.destroy', $item->id) }}" method="POST" class="inline-block delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" 
                                                            class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center delete-btn">
                                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cart-produk">

            </div>
        </div>
    </div>
    <x-script  script='{!! isset($script) ? $script : "" !!}' />
    <script src="{{ asset('assets/js/data-table.js') }}"></script>
</body>
</html>