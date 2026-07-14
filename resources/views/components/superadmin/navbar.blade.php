@props(['title' => 'Panel'])

<header class="sa-topbar">
    <div class="sa-topbar-title">{{ $title }}</div>
    <div class="sa-topbar-meta">
        admin.{{ env('TENANT_BASE_DOMAIN', 'sistemgate.com') }}
    </div>
</header>
