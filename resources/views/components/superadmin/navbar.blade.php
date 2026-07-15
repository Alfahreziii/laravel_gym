@props(['title' => 'Panel'])

@php
    $saUser     = auth('super_admin')->user();
    $saName     = $saUser?->name ?? 'Super Admin';
    $saInitials = collect(explode(' ', $saName))
        ->take(2)
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->join('');
@endphp

<header class="sa-topbar">

    {{-- Left: sidebar toggle + page title --}}
    <div class="sa-topbar-left">
        <button type="button" id="sa-sidebar-toggle"
                class="sa-navbar-ctrl-btn" title="Toggle menu">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
            </svg>
        </button>
        <div class="sa-topbar-title">{{ $title }}</div>
    </div>

    {{-- Right: dark mode toggle + profile pill + dropdown --}}
    <div class="sa-topbar-right">

        {{-- Dark mode toggle --}}
        <button type="button" id="sa-theme-toggle"
                class="sa-navbar-ctrl-btn" title="Ganti tema">
            {{-- Moon icon: shown in light mode --}}
            <svg id="sa-theme-dark-icon" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="1.8" style="width:19px;height:19px">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
            </svg>
            {{-- Sun icon: shown in dark mode, hidden by default --}}
            <svg id="sa-theme-light-icon" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="1.8"
                 style="width:19px;height:19px;display:none">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
            </svg>
        </button>

        {{-- Profile pill --}}
        <button type="button" id="sa-profile-toggle"
                class="sa-navbar-profile-pill" title="Akun">
            <span class="sa-navbar-avatar">{{ $saInitials }}</span>
            <div class="sa-navbar-profile-info">
                <div class="sa-navbar-profile-name">{{ $saName }}</div>
                <div class="sa-navbar-profile-role">Super Admin</div>
            </div>
        </button>

        {{-- Profile dropdown --}}
        <div id="sa-profile-dropdown" class="sa-profile-dropdown hidden">
            <div class="sa-profile-dropdown-header">
                <div class="sa-profile-dropdown-name">{{ $saName }}</div>
                <div class="sa-profile-dropdown-role">Super Admin</div>
            </div>
            <div class="sa-profile-dropdown-divider"></div>
            <form method="POST" action="{{ route('super_admin.logout') }}">
                @csrf
                <button type="submit" class="sa-profile-dropdown-item sa-profile-dropdown-item-danger">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>

    </div>
</header>
