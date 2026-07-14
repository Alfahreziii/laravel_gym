<?php

if (! function_exists('tenant_storage_path')) {
    /**
     * Kembalikan folder path untuk upload, dengan prefix tenants/{subdomain}/ jika ada tenant aktif.
     *
     * Hasil disimpan langsung di DB (mis. "tenants/fithub/anggotas").
     * Ditampilkan via asset('storage/' . $path) — symlink public/storage → storage/app/public sudah cover subfolder ini.
     *
     * Fallback ke folder asli (tanpa prefix) di konteks non-tenant (super admin, CLI).
     */
    function tenant_storage_path(string $folder): string
    {
        if (! app()->bound('tenant')) {
            return $folder;
        }

        return 'tenants/' . app('tenant')->subdomain . '/' . $folder;
    }
}

if (! function_exists('tenant_module')) {
    /**
     * Cek apakah modul tertentu aktif untuk tenant yang sedang aktif di request ini.
     *
     * Key yang valid: 'trainer', 'pos', 'keuangan'
     *
     * Mengembalikan false jika:
     *  - Tidak ada tenant yang di-resolve (localhost / super admin / non-tenant request)
     *  - Tenant tidak punya record tenant_modules
     *  - Key tidak dikenal
     */
    function tenant_module(string $key): bool
    {
        if (! app()->bound('tenant')) {
            return false;
        }

        $module = app('tenant')->module;

        if (! $module) {
            return false;
        }

        return (bool) ($module->{$key} ?? false);
    }
}
