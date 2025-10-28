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
                <!-- Notification Start  -->
                <button data-dropdown-toggle="dropdownNotification" class="has-indicator relative w-10 h-10 flex justify-center items-center" type="button">
                    <iconify-icon icon="iconoir:bell" class="text-neutral-900 text-xl"></iconify-icon>
                    @if($lowStockProducts->count() > 0)
                        <span class="indicator bg-danger-600 border-2 border-white dark:border-neutral-700 absolute rounded-full w-4 h-4 top-0 right-0 text-white text-xs">{{ $lowStockProducts->count() }}</span>
                    @else
                        <span class="indicator hidden bg-danger-600 border-2 border-white dark:border-neutral-700 absolute rounded-full w-4 h-4 top-0 right-0 text-white text-xs">{{ $lowStockProducts->count() }}</span>
                    @endif    
                </button>
                <div id="dropdownNotification" class="z-10 hidden bg-white dark:bg-neutral-700 rounded-2xl overflow-hidden shadow-lg max-w-[394px] w-full">
                    <div class="scroll-sm !border-t-0">
                        <div class="max-h-[400px] overflow-y-auto">
                            @forelse($lowStockProducts as $product)
                                <a href="javascript:void(0)" class="flex px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-600 justify-between gap-1">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0 relative w-11 h-11 bg-warning-100 text-warning-600 flex justify-center items-center rounded-full">
                                            <iconify-icon icon="mdi:alert-outline" class="text-2xl"></iconify-icon>
                                        </div>
                                        <div>
                                            <h6 class="text-sm fw-semibold mb-1">{{ $product->name }}</h6>
                                            <p class="mb-0 text-sm line-clamp-1">
                                                Stok: {{ $product->quantity }} &nbsp;|&nbsp; Reorder: {{ $product->reorder }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="shrink-0">
                                        <span class="text-sm text-neutral-500">Stok menipis</span>
                                    </div>
                                </a>
                            @empty
                                <div class="text-center py-3 text-sm text-neutral-500">
                                    Semua stok aman ✅
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <!-- Notification End  -->

                <button data-dropdown-toggle="dropdownProfile" id="profileToggle"
                    class="flex justify-center items-center rounded-full gap-1" type="button">
                    {{ Auth::user()->name }}
                    <iconify-icon id="chevronIcon" icon="mdi:chevron-down" class="text-lg transition-transform duration-200"></iconify-icon>
                </button>
                <div id="dropdownProfile" class="z-10 hidden bg-white rounded-lg shadow-lg dropdown-menu-sm p-3">
                    <div class="py-3 px-4 rounded-lg bg-primary-50 mb-4 flex items-center justify-between gap-2">
                        <div>
                            <h6 class="text-lg text-neutral-900 font-semibold mb-0">{{ Auth::user()->name }}</h6>
                            <span class="text-neutral-500">{{ Auth::user()->getRoleNames()->first() }}</span>
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
    // ====== PROFILE DROPDOWN ======
    const profileToggle = document.getElementById('profileToggle');
    const profileDropdown = document.getElementById('dropdownProfile');
    const profileChevron = profileToggle?.querySelector('#chevronIcon');

    // ====== NOTIF DROPDOWN ======
    const notifToggle = document.getElementById('notifToggle');
    const notifDropdown = document.getElementById('dropdownNotif');
    const notifChevron = notifToggle?.querySelector('#chevronIcon');

    function toggleDropdown(toggleBtn, dropdown, chevron) {
        if (!toggleBtn || !dropdown || !chevron) return;

        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation(); // cegah klik bubble ke document
            const isHidden = dropdown.classList.contains('hidden');

            // Tutup semua dropdown lain dulu
            closeAllDropdowns();

            // Toggle dropdown ini
            if (isHidden) {
                dropdown.classList.remove('hidden');
                chevron.classList.add('rotate-180');
            } else {
                dropdown.classList.add('hidden');
                chevron.classList.remove('rotate-180');
            }
        });
    }

    function closeAllDropdowns() {
        [profileDropdown, notifDropdown].forEach((d, i) => {
            if (d && !d.classList.contains('hidden')) {
                d.classList.add('hidden');
                const chevron = (i === 0) ? profileChevron : notifChevron;
                chevron?.classList.remove('rotate-180');
            }
        });
    }

    // Klik di luar -> tutup semua dropdown
    document.addEventListener('click', function (e) {
        if (
            !profileDropdown.contains(e.target) && !profileToggle.contains(e.target) &&
            !notifDropdown.contains(e.target) && !notifToggle.contains(e.target)
        ) {
            closeAllDropdowns();
        }
    });

    // Tekan ESC -> tutup semua dropdown
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            closeAllDropdowns();
        }
    });

    // Inisialisasi
    toggleDropdown(profileToggle, profileDropdown, profileChevron);
    toggleDropdown(notifToggle, notifDropdown, notifChevron);
});
</script>

