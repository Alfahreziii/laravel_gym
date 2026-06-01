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
                <!-- Notification Start -->
                <button data-dropdown-toggle="dropdownNotification"
                    class="has-indicator relative w-10 h-10 flex justify-center items-center" type="button">
                    <iconify-icon icon="iconoir:bell" class="text-neutral-900 text-xl"></iconify-icon>

                    @php
                        $totalNotifications = 0;
                        if (Auth::user()->hasRole(['admin', 'spv'])) {
                            $totalNotifications =
                                $lowStockProducts->count() +
                                $expiringMemberships->count() +
                                $expiredMemberships->count();
                        } elseif (Auth::user()->hasRole('trainer')) {
                            $totalNotifications = $trainerNotifications->count();
                        }
                    @endphp

                    @if ($totalNotifications > 0)
                        <span
                            class="indicator bg-danger-600 border-2 border-white dark:border-neutral-700 absolute rounded-full w-4 h-4 top-0 right-0 text-white text-xs">
                            {{ $totalNotifications }}
                        </span>
                    @endif
                </button>

                <div id="dropdownNotification"
                    class="z-10 hidden bg-white dark:bg-neutral-700 rounded-2xl overflow-hidden shadow-lg max-w-[394px] w-full">

                    <div class="px-4 py-3 border-b border-neutral-100 flex items-center justify-between">
                        <span class="font-semibold text-sm">Notifikasi</span>
                        <span class="text-xs text-neutral-400">{{ $totalNotifications }} notifikasi</span>
                    </div>

                    <div class="overflow-y-auto" style="max-height: 480px;">

                        @if (Auth::user()->hasRole(['admin', 'spv']))

                            {{-- SECTION: Stok Menipis --}}
                            <div class="notif-section">
                                <button type="button" onclick="toggleNotifSection(this)"
                                    class="notif-section-header w-full flex items-center justify-between px-4 py-2 bg-neutral-50 border-b border-neutral-100 hover:bg-neutral-100 transition-colors">
                                    <div
                                        class="flex items-center gap-2 text-xs font-semibold text-neutral-500 uppercase tracking-wider">
                                        <iconify-icon icon="mdi:alert-outline"
                                            class="text-warning-500 text-base"></iconify-icon>
                                        Stok Menipis
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs bg-neutral-200 text-neutral-600 rounded-full px-2 py-0.5">{{ $lowStockProducts->count() }}</span>
                                        <iconify-icon icon="mdi:chevron-down"
                                            class="notif-chevron text-neutral-400 transition-transform duration-200"></iconify-icon>
                                    </div>
                                </button>
                                <div class="notif-section-body overflow-y-auto" style="max-height: 200px;">
                                    @forelse($lowStockProducts as $product)
                                        <a href="{{ route('products.index') }}"
                                            class="flex px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-600 justify-between gap-1 border-b border-neutral-50">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="flex-shrink-0 w-11 h-11 bg-warning-100 text-warning-600 flex justify-center items-center rounded-full">
                                                    <iconify-icon icon="mdi:alert-outline"
                                                        class="text-2xl"></iconify-icon>
                                                </div>
                                                <div>
                                                    <h6 class="text-sm font-semibold mb-1">{{ $product->name }}</h6>
                                                    <p class="mb-0 text-sm line-clamp-1">Stok: {{ $product->quantity }}
                                                        &nbsp;|&nbsp; Reorder: {{ $product->reorder }}</p>
                                                </div>
                                            </div>
                                            <div class="shrink-0">
                                                <span class="text-sm text-neutral-500">Stok menipis</span>
                                            </div>
                                        </a>
                                    @empty
                                        <p class="text-center py-3 text-sm text-neutral-400">Stok aman 🎉</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- SECTION: Membership Hampir Habis --}}
                            <div class="notif-section">
                                <button type="button" onclick="toggleNotifSection(this)"
                                    class="notif-section-header w-full flex items-center justify-between px-4 py-2 bg-neutral-50 border-b border-neutral-100 hover:bg-neutral-100 transition-colors">
                                    <div
                                        class="flex items-center gap-2 text-xs font-semibold text-neutral-500 uppercase tracking-wider">
                                        <iconify-icon icon="mdi:calendar-clock"
                                            class="text-danger-500 text-base"></iconify-icon>
                                        Membership Hampir Habis
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs bg-neutral-200 text-neutral-600 rounded-full px-2 py-0.5">{{ $expiringMemberships->count() }}</span>
                                        <iconify-icon icon="mdi:chevron-down"
                                            class="notif-chevron text-neutral-400 transition-transform duration-200"></iconify-icon>
                                    </div>
                                </button>
                                <div class="notif-section-body overflow-y-auto" style="max-height: 200px;">
                                    @forelse($expiringMemberships as $membership)
                                        @php $sisaHari = \Carbon\Carbon::today()->diffInDays($membership->tgl_selesai); @endphp
                                        <a href="{{ route('anggota_membership.edit', $membership->id) }}"
                                            class="flex px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-600 justify-between gap-1 border-b border-neutral-50">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="flex-shrink-0 w-11 h-11 bg-danger-100 text-danger-600 flex justify-center items-center rounded-full">
                                                    <iconify-icon icon="mdi:calendar-clock"
                                                        class="text-2xl"></iconify-icon>
                                                </div>
                                                <div>
                                                    <h6 class="text-sm font-semibold mb-1">
                                                        {{ $membership->anggota->name }}</h6>
                                                    <p class="mb-0 text-sm line-clamp-1">Berakhir:
                                                        {{ $membership->tgl_selesai->format('d M Y') }}</p>
                                                </div>
                                            </div>
                                            <div class="shrink-0">
                                                <span
                                                    class="text-sm {{ $sisaHari <= 2 ? 'text-danger-600 font-semibold' : 'text-warning-500' }}">
                                                    {{ $sisaHari == 0 ? 'Hari ini' : $sisaHari . ' hari lagi' }}
                                                </span>
                                            </div>
                                        </a>
                                    @empty
                                        <p class="text-center py-3 text-sm text-neutral-400">Tidak ada yang hampir habis
                                            🎉</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- SECTION: Membership Tidak Aktif --}}
                            <div class="notif-section">
                                <button type="button" onclick="toggleNotifSection(this)"
                                    class="notif-section-header w-full flex items-center justify-between px-4 py-2 bg-neutral-50 border-b border-neutral-100 hover:bg-neutral-100 transition-colors">
                                    <div
                                        class="flex items-center gap-2 text-xs font-semibold text-neutral-500 uppercase tracking-wider">
                                        <iconify-icon icon="mdi:account-off-outline"
                                            class="text-purple-500 text-base"></iconify-icon>
                                        Membership Tidak Aktif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs bg-neutral-200 text-neutral-600 rounded-full px-2 py-0.5">{{ $expiredMemberships->count() }}</span>
                                        <iconify-icon icon="mdi:chevron-down"
                                            class="notif-chevron text-neutral-400 transition-transform duration-200"></iconify-icon>
                                    </div>
                                </button>
                                <div class="notif-section-body overflow-y-auto" style="max-height: 200px;">
                                    @forelse($expiredMemberships as $membership)
                                        @php $sudahHari = \Carbon\Carbon::today()->diffInDays($membership->tgl_selesai); @endphp
                                        <a href="{{ route('anggota_membership.edit', $membership->id) }}"
                                            class="flex px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-600 justify-between gap-1 border-b border-neutral-50">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="flex-shrink-0 w-11 h-11 bg-purple-100 text-purple-600 flex justify-center items-center rounded-full">
                                                    <iconify-icon icon="mdi:account-off-outline"
                                                        class="text-2xl"></iconify-icon>
                                                </div>
                                                <div>
                                                    <h6 class="text-sm font-semibold mb-1">
                                                        {{ $membership->anggota->name }}</h6>
                                                    <p class="mb-0 text-sm line-clamp-1">Berakhir:
                                                        {{ $membership->tgl_selesai->format('d M Y') }}</p>
                                                </div>
                                            </div>
                                            <div class="shrink-0">
                                                <span class="text-sm text-neutral-500">{{ $sudahHari }} hari
                                                    lalu</span>
                                            </div>
                                        </a>
                                    @empty
                                        <p class="text-center py-3 text-sm text-neutral-400">Tidak ada member tidak
                                            aktif</p>
                                    @endforelse
                                </div>
                            </div>

                        @endif

                        {{-- TRAINER --}}
                        @if (Auth::user()->hasRole('trainer'))
                            @forelse($trainerNotifications as $notif)
                                <a href="{{ $notif['url'] }}"
                                    class="flex px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-600 justify-between gap-1">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex-shrink-0 w-11 h-11 bg-{{ $notif['color'] }}-100 text-{{ $notif['color'] }}-600 flex justify-center items-center rounded-full">
                                            <iconify-icon icon="{{ $notif['icon'] }}"
                                                class="text-2xl"></iconify-icon>
                                        </div>
                                        <div>
                                            <h6 class="text-sm font-semibold mb-1">{{ $notif['title'] }}</h6>
                                            <p class="mb-0 text-sm line-clamp-1">{{ $notif['message'] }}</p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="text-center py-3 text-sm text-neutral-400">Tidak ada notifikasi 🎉</div>
                            @endforelse
                        @endif

                    </div>
                </div>
                <!-- Notification End -->

                <button data-dropdown-toggle="dropdownProfile" id="profileToggle"
                    class="flex justify-center items-center rounded-full gap-1" type="button">
                    {{ Auth::user()->name }}
                    <iconify-icon id="chevronIcon" icon="mdi:chevron-down"
                        class="text-lg transition-transform duration-200"></iconify-icon>
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
                                <a class="text-black px-0 py-2 hover:text-primary-600 flex items-center gap-4"
                                    href="{{ route('viewProfile') }}">
                                    <iconify-icon icon="solar:user-linear" class="icon text-xl"></iconify-icon> My
                                    Profile
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
    function toggleNotifSection(btn) {
        const body = btn.closest('.notif-section').querySelector('.notif-section-body');
        const chevron = btn.querySelector('.notif-chevron');
        const isOpen = !body.classList.contains('hidden');
        if (isOpen) {
            body.classList.add('hidden');
            chevron.style.transform = 'rotate(-90deg)';
        } else {
            body.classList.remove('hidden');
            chevron.style.transform = 'rotate(0deg)';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // ====== PROFILE DROPDOWN ======
        const profileToggle = document.getElementById('profileToggle');
        const profileDropdown = document.getElementById('dropdownProfile');
        const profileChevron = profileToggle?.querySelector('#chevronIcon');

        // ====== NOTIF DROPDOWN ======
        const notifToggle = document.querySelector('[data-dropdown-toggle="dropdownNotification"]');
        const notifDropdown = document.getElementById('dropdownNotification');

        function toggleDropdown(toggleBtn, dropdown) {
            if (!toggleBtn || !dropdown) return;

            toggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isHidden = dropdown.classList.contains('hidden');
                closeAllDropdowns();
                if (isHidden) {
                    dropdown.classList.remove('hidden');
                }
            });
        }

        function closeAllDropdowns() {
            if (profileDropdown) profileDropdown.classList.add('hidden');
            if (profileChevron) profileChevron.classList.remove('rotate-180');
            if (notifDropdown) notifDropdown.classList.add('hidden');
        }

        document.addEventListener('click', function(e) {
            const clickedOutsideProfile = profileDropdown && !profileDropdown.contains(e.target) &&
                profileToggle && !profileToggle.contains(e.target);
            const clickedOutsideNotif = notifDropdown && !notifDropdown.contains(e.target) &&
                notifToggle && !notifToggle.contains(e.target);

            if (clickedOutsideProfile && clickedOutsideNotif) {
                closeAllDropdowns();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                closeAllDropdowns();
            }
        });

        toggleDropdown(profileToggle, profileDropdown);
        toggleDropdown(notifToggle, notifDropdown);
    });
</script>
