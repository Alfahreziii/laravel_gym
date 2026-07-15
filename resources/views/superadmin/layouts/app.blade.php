<!DOCTYPE html>
<html lang="id">

@php $pageTitle = $__env->yieldContent('title', 'Panel'); @endphp

<x-superadmin.head :page-title="$pageTitle" />

<body>

    {{-- Mobile sidebar overlay (di luar aside supaya stacking context benar) --}}
    <div class="sa-sidebar-overlay" id="sa-sidebar-overlay"></div>

    <x-superadmin.sidebar />

    <div class="sa-main">

        <x-superadmin.navbar :title="$pageTitle" />

        <main class="sa-page-content">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="sa-flash sa-flash-success">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('info'))
                <div class="sa-flash sa-flash-info">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                    </svg>
                    {{ session('info') }}
                </div>
            @endif

            @if ($errors->any() && !session('open_modal'))
                <div class="sa-flash sa-flash-error">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                    @foreach ($errors->all() as $e) {{ $e }} @endforeach
                </div>
            @endif

            @yield('content')

        </main>

        <footer class="sa-footer">
            <p>© 2025 HEXA DEVELOPMENT. All Rights Reserved.</p>
            <p>Made by <span class="sa-footer-credit">Al Fahrezi</span></p>
        </footer>

    </div>

    @yield('modals')

    <x-superadmin.scripts />

    @yield('scripts')

</body>
</html>
