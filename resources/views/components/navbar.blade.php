<div class="navbar-header border-b border-neutral-200">
    <div class="flex items-center justify-between">
        <div class="col-auto">
            <div class="flex flex-wrap items-center gap-[16px]">
                <button type="button" class="sidebar-toggle">
                    <iconify-icon icon="heroicons:bars-3-solid" class="icon non-active"></iconify-icon>
                    <iconify-icon icon="iconoir:arrow-right" class="icon active"></iconify-icon>
                </button>
                <button type="button" class="sidebar-mobile-toggle d-flex !leading-[0]">
                    <iconify-icon icon="heroicons:bars-3-solid" class="icon !text-[30px]"></iconify-icon>
                </button>
                <form class="navbar-search">
                    <input type="text" name="search" placeholder="Search">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
        </div>
        <div class="col-auto">
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" id="theme-toggle" class="w-10 h-10 border border-neutral-200 rounded-full flex justify-center items-center">
                    <span id="theme-toggle-dark-icon" class="hidden">
                        <i class="ri-sun-line"></i>
                    </span>
                    <span id="theme-toggle-light-icon" class="hidden">
                        <i class="ri-moon-line"></i>
                    </span>
                </button>


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
