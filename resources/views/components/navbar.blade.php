<div class="navbar-header">
    <div class="flex items-center justify-between w-full">

        {{-- Left: sidebar toggles --}}
        <div class="flex items-center gap-4">
            <button type="button" class="sidebar-toggle">
                <iconify-icon icon="heroicons:bars-3-solid" class="icon non-active"></iconify-icon>
                <iconify-icon icon="iconoir:arrow-right" class="icon active"></iconify-icon>
            </button>
            <button type="button" class="sidebar-mobile-toggle d-flex !leading-[0]">
                <iconify-icon icon="heroicons:bars-3-solid" class="icon !text-[30px]"></iconify-icon>
            </button>
        </div>

        {{-- Right: notifications, dark mode, profile --}}
        <div class="flex items-center gap-2">

            {{-- Notification button + dropdown --}}
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

            <button data-dropdown-toggle="dropdownNotification"
                class="navbar-ctrl-btn relative" type="button" title="Notifikasi">
                <x-icon.bell class="text-[19px]" />
                @if ($totalNotifications > 0)
                    <span class="navbar-notif-dot"></span>
                @endif
            </button>

            <div id="dropdownNotification"
                class="z-10 hidden bg-white dark:bg-surface-dark rounded-2xl overflow-hidden shadow-lg max-w-[394px] w-full border border-neutral-100 dark:border-line-dark">

                <div class="px-4 py-3 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                    <span class="font-semibold text-sm">Notifikasi</span>
                    <span class="text-xs text-neutral-400">{{ $totalNotifications }} notifikasi</span>
                </div>

                <div class="overflow-y-auto" style="max-height: 480px;">

                    @if (Auth::user()->hasRole(['admin', 'spv']))

                        {{-- SECTION: Stok Menipis --}}
                        <div class="notif-section">
                            <button type="button" onclick="toggleNotifSection(this)"
                                class="notif-section-header w-full flex items-center justify-between px-4 py-2 bg-neutral-50 dark:bg-neutral-900/50 border-b border-neutral-100 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                                <div class="flex items-center gap-2 text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    <iconify-icon icon="mdi:alert-outline" class="text-warning-500 text-base"></iconify-icon>
                                    Stok Menipis
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 rounded-full px-2 py-0.5">{{ $lowStockProducts->count() }}</span>
                                    <iconify-icon icon="mdi:chevron-down" class="notif-chevron text-neutral-400 transition-transform duration-200"></iconify-icon>
                                </div>
                            </button>
                            <div class="notif-section-body overflow-y-auto" style="max-height: 200px;">
                                @forelse($lowStockProducts as $product)
                                    <a href="{{ route('products.index') }}"
                                        class="flex px-4 py-3 hover:bg-gray-100 dark:hover:bg-neutral-700 justify-between gap-1 border-b border-neutral-100 dark:border-neutral-700">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0 w-11 h-11 bg-warning-100 text-warning-600 flex justify-center items-center rounded-full">
                                                <iconify-icon icon="mdi:alert-outline" class="text-2xl"></iconify-icon>
                                            </div>
                                            <div>
                                                <h6 class="text-sm font-semibold mb-1 dark:text-neutral-100">{{ $product->name }}</h6>
                                                <p class="mb-0 text-sm line-clamp-1 dark:text-neutral-400">Stok: {{ $product->quantity }}
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
                                class="notif-section-header w-full flex items-center justify-between px-4 py-2 bg-neutral-50 dark:bg-neutral-900/50 border-b border-neutral-100 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                                <div class="flex items-center gap-2 text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    <iconify-icon icon="mdi:calendar-clock" class="text-danger-500 text-base"></iconify-icon>
                                    Membership Hampir Habis
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 rounded-full px-2 py-0.5">{{ $expiringMemberships->count() }}</span>
                                    <iconify-icon icon="mdi:chevron-down" class="notif-chevron text-neutral-400 transition-transform duration-200"></iconify-icon>
                                </div>
                            </button>
                            <div class="notif-section-body overflow-y-auto" style="max-height: 200px;">
                                @forelse($expiringMemberships as $membership)
                                    @php $sisaHari = \Carbon\Carbon::today()->diffInDays($membership->tgl_selesai); @endphp
                                    <a href="{{ route('anggota_membership.edit', $membership->id) }}"
                                        class="flex px-4 py-3 hover:bg-gray-100 dark:hover:bg-neutral-700 justify-between gap-1 border-b border-neutral-100 dark:border-neutral-700">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0 w-11 h-11 bg-danger-100 text-danger-600 flex justify-center items-center rounded-full">
                                                <iconify-icon icon="mdi:calendar-clock" class="text-2xl"></iconify-icon>
                                            </div>
                                            <div>
                                                <h6 class="text-sm font-semibold mb-1 dark:text-neutral-100">{{ $membership->anggota->name }}</h6>
                                                <p class="mb-0 text-sm line-clamp-1 dark:text-neutral-400">Berakhir:
                                                    {{ $membership->tgl_selesai->format('d M Y') }}</p>
                                            </div>
                                        </div>
                                        <div class="shrink-0">
                                            <span class="text-sm {{ $sisaHari <= 2 ? 'text-danger-600 font-semibold' : 'text-warning-500' }}">
                                                {{ $sisaHari == 0 ? 'Hari ini' : $sisaHari . ' hari lagi' }}
                                            </span>
                                        </div>
                                    </a>
                                @empty
                                    <p class="text-center py-3 text-sm text-neutral-400">Tidak ada yang hampir habis 🎉</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- SECTION: Membership Tidak Aktif --}}
                        <div class="notif-section">
                            <button type="button" onclick="toggleNotifSection(this)"
                                class="notif-section-header w-full flex items-center justify-between px-4 py-2 bg-neutral-50 dark:bg-neutral-900/50 border-b border-neutral-100 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                                <div class="flex items-center gap-2 text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    <iconify-icon icon="mdi:account-off-outline" class="text-purple-500 text-base"></iconify-icon>
                                    Membership Tidak Aktif
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 rounded-full px-2 py-0.5">{{ $expiredMemberships->count() }}</span>
                                    <iconify-icon icon="mdi:chevron-down" class="notif-chevron text-neutral-400 transition-transform duration-200"></iconify-icon>
                                </div>
                            </button>
                            <div class="notif-section-body overflow-y-auto" style="max-height: 200px;">
                                @forelse($expiredMemberships as $membership)
                                    @php $sudahHari = \Carbon\Carbon::today()->diffInDays($membership->tgl_selesai); @endphp
                                    <a href="{{ route('anggota_membership.edit', $membership->id) }}"
                                        class="flex px-4 py-3 hover:bg-gray-100 dark:hover:bg-neutral-700 justify-between gap-1 border-b border-neutral-100 dark:border-neutral-700">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0 w-11 h-11 bg-purple-100 text-purple-600 flex justify-center items-center rounded-full">
                                                <iconify-icon icon="mdi:account-off-outline" class="text-2xl"></iconify-icon>
                                            </div>
                                            <div>
                                                <h6 class="text-sm font-semibold mb-1 dark:text-neutral-100">{{ $membership->anggota->name }}</h6>
                                                <p class="mb-0 text-sm line-clamp-1 dark:text-neutral-400">Berakhir:
                                                    {{ $membership->tgl_selesai->format('d M Y') }}</p>
                                            </div>
                                        </div>
                                        <div class="shrink-0">
                                            <span class="text-sm text-neutral-500">{{ $sudahHari }} hari lalu</span>
                                        </div>
                                    </a>
                                @empty
                                    <p class="text-center py-3 text-sm text-neutral-400">Tidak ada member tidak aktif</p>
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
                                    <div class="flex-shrink-0 w-11 h-11 bg-{{ $notif['color'] }}-100 text-{{ $notif['color'] }}-600 flex justify-center items-center rounded-full">
                                        <iconify-icon icon="{{ $notif['icon'] }}" class="text-2xl"></iconify-icon>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-semibold mb-1 dark:text-neutral-100">{{ $notif['title'] }}</h6>
                                        <p class="mb-0 text-sm line-clamp-1 dark:text-neutral-400">{{ $notif['message'] }}</p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-3 text-sm text-neutral-400">Tidak ada notifikasi 🎉</div>
                        @endforelse
                    @endif

                </div>
            </div>

            {{-- Zona waktu — label tampil untuk semua role, dropdown ganti khusus admin|spv --}}
            @php
                $canChangeTimezone = Auth::user()->hasRole(['admin', 'spv']);
            @endphp
            <button @if ($canChangeTimezone) data-dropdown-toggle="dropdownTimezone" @endif id="timezoneToggle"
                class="navbar-ctrl-btn flex items-center gap-1 !w-auto px-2" type="button" title="Zona waktu">
                <iconify-icon icon="mdi:clock-outline" class="text-[19px]"></iconify-icon>
                <span class="text-xs font-semibold">{{ tz_label() }}</span>
            </button>

            @if ($canChangeTimezone)
                @php
                    $tzOptions = [
                        'Asia/Jakarta'  => 'WIB — Jakarta',
                        'Asia/Makassar' => 'WITA — Makassar',
                        'Asia/Jayapura' => 'WIT — Jayapura',
                    ];
                    $currentTz = tenant_timezone();
                @endphp
                <div id="dropdownTimezone"
                    class="z-10 hidden bg-white dark:bg-surface-dark rounded-2xl overflow-hidden shadow-lg w-56 border border-neutral-100 dark:border-line-dark">
                    <div class="px-4 py-3 border-b border-neutral-100 dark:border-neutral-700">
                        <span class="font-semibold text-sm">Zona Waktu</span>
                    </div>
                    <ul class="py-2">
                        @foreach ($tzOptions as $tzValue => $tzLabel)
                            <li>
                                <form method="POST" action="{{ route('gym_profile.update_timezone') }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="timezone" value="{{ $tzValue }}">
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-neutral-700 flex items-center justify-between {{ $currentTz === $tzValue ? 'text-primary-600 font-semibold' : '' }}">
                                        {{ $tzLabel }}
                                        @if ($currentTz === $tzValue)
                                            <iconify-icon icon="mdi:check"></iconify-icon>
                                        @endif
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Dark mode toggle --}}
            <button type="button" id="theme-toggle" class="navbar-ctrl-btn" title="Ganti tema">
                <x-icon.moon id="theme-toggle-dark-icon" class="text-[19px]" />
                <iconify-icon id="theme-toggle-light-icon" icon="ph:sun-bold" class="text-[19px] hidden"></iconify-icon>
            </button>

            {{-- Profile pill --}}
            @php
                $userName     = Auth::user()->name;
                $userInitials = collect(explode(' ', $userName))
                    ->take(2)
                    ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                    ->join('');
                $userRole = Auth::user()->getRoleNames()->first();
            @endphp

            <button data-dropdown-toggle="dropdownProfile" id="profileToggle"
                class="navbar-profile-pill" type="button">
                <span class="navbar-avatar">{{ $userInitials }}</span>
                <div class="navbar-profile-info">
                    <div class="navbar-profile-name">{{ $userName }}</div>
                    <div class="navbar-profile-role">{{ $userRole }}</div>
                </div>
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
                                <iconify-icon icon="solar:user-linear" class="icon text-xl"></iconify-icon> My Profile
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
                        </li>
                    </ul>
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
        const profileToggle = document.getElementById('profileToggle');
        const profileDropdown = document.getElementById('dropdownProfile');

        const notifToggle = document.querySelector('[data-dropdown-toggle="dropdownNotification"]');
        const notifDropdown = document.getElementById('dropdownNotification');

        const timezoneToggle = document.getElementById('timezoneToggle');
        const timezoneDropdown = document.getElementById('dropdownTimezone');

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
            if (notifDropdown) notifDropdown.classList.add('hidden');
            if (timezoneDropdown) timezoneDropdown.classList.add('hidden');
        }

        document.addEventListener('click', function(e) {
            const clickedOutsideProfile = profileDropdown && !profileDropdown.contains(e.target) &&
                profileToggle && !profileToggle.contains(e.target);
            const clickedOutsideNotif = notifDropdown && !notifDropdown.contains(e.target) &&
                notifToggle && !notifToggle.contains(e.target);
            const clickedOutsideTimezone = timezoneDropdown && !timezoneDropdown.contains(e.target) &&
                timezoneToggle && !timezoneToggle.contains(e.target);

            if (clickedOutsideProfile && clickedOutsideNotif && clickedOutsideTimezone) {
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
        toggleDropdown(timezoneToggle, timezoneDropdown);
    });
</script>
