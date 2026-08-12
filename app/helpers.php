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

if (! function_exists('tenant_cache_key')) {
    /**
     * Scope cache key ke tenant aktif (pakai id tenant), supaya data cache
     * satu tenant tidak "bocor" terbaca/tertimpa oleh tenant lain — penting
     * untuk cache store yang tidak ikut ter-isolasi oleh switch koneksi DB
     * tenant (mis. CACHE_STORE=redis/file, beda dengan CACHE_STORE=database
     * yang kebetulan ikut per-DB tenant).
     *
     * Fallback ke key asli (tanpa prefix) di konteks non-tenant (super admin, CLI).
     */
    function tenant_cache_key(string $key): string
    {
        if (! app()->bound('tenant')) {
            return $key;
        }

        return $key . ':tenant_' . app('tenant')->id;
    }
}

if (! function_exists('tenant_timezone')) {
    /**
     * Zona waktu tampilan yang dipilih tenant (WIB/WITA/WIT), dari kolom
     * tenants.timezone. Fallback ke config('app.timezone') kalau tidak ada
     * tenant aktif (super admin, CLI, dsb).
     *
     * Ini HANYA memengaruhi lapisan tampilan — jangan dipakai untuk mengganti
     * config('app.timezone') / koneksi DB, karena data lama (kolom TIMESTAMP)
     * disimpan mengasumsikan offset yang konsisten.
     */
    function tenant_timezone(): string
    {
        if (app()->bound('tenant') && app('tenant')->timezone) {
            return app('tenant')->timezone;
        }

        return config('app.timezone');
    }
}

if (! function_exists('tenant_now')) {
    /**
     * now() yang sudah dikonversi ke zona waktu pilihan tenant.
     */
    function tenant_now(): \Carbon\Carbon
    {
        return now()->setTimezone(tenant_timezone());
    }
}

if (! function_exists('to_tenant_tz')) {
    /**
     * Konversi timestamp (created_at, dll) ke zona waktu pilihan tenant untuk
     * ditampilkan. Aman dipakai ke data lama — kolom TIMESTAMP MySQL disimpan
     * dalam UTC internal, jadi cuma dikonversi ulang, bukan digeser nilainya.
     */
    function to_tenant_tz(mixed $date): \Carbon\Carbon
    {
        return \Carbon\Carbon::parse($date)->setTimezone(tenant_timezone());
    }
}

if (! function_exists('tz_label')) {
    /**
     * Label singkatan zona waktu tampilan tenant (WIB/WITA/WIT) — jangan
     * hardcode "WIB" di controller/view.
     */
    function tz_label(): string
    {
        return tenant_now()->format('T');
    }
}

if (! function_exists('tenant_today_date')) {
    /**
     * Tanggal "hari ini" menurut kalender zona tenant (Y-m-d). Pakai ini
     * untuk bandingkan/isi kolom bertipe DATE (tgl_mulai, tgl_selesai,
     * tanggal jurnal, dll) — kolom DATE tidak kena konversi timezone MySQL,
     * jadi cukup pakai tanggal kalender tenant apa adanya.
     */
    function tenant_today_date(): string
    {
        return \Carbon\Carbon::now(tenant_timezone())->toDateString();
    }
}

if (! function_exists('tenant_today')) {
    /**
     * Pengganti Carbon::today() — tanggal kalendernya ikut zona tenant, tapi
     * Carbon-nya sengaja di-anchor di timezone penyimpanan (config('app.timezone'))
     * supaya tetap "sezona" dengan kolom DATE hasil cast Eloquent (tgl_mulai,
     * tgl_selesai, dll — yang juga di-parse pakai app.timezone). Ini penting:
     * kalau tenant_today() dianchor di zona tenant sementara sisi lain masih
     * di zona penyimpanan, perbandingan instant (<=, >=, lt, gt, diffInDays)
     * jadi geser sejam/dua jam meski tanggalnya sama — DATE tidak punya makna
     * timezone sendiri, jadi harus dibandingkan dalam satu zona referensi yang
     * konsisten.
     */
    function tenant_today(): \Carbon\Carbon
    {
        return \Carbon\Carbon::parse(tenant_today_date(), config('app.timezone'));
    }
}

if (! function_exists('tenant_today_range')) {
    /**
     * Rentang [awal, akhir] "hari ini" menurut kalender zona tenant,
     * dikonversi balik ke timezone penyimpanan (config('app.timezone')).
     *
     * WAJIB dipakai (bukan whereDate('created_at', ...)) untuk query hari-ini
     * di kolom TIMESTAMP (created_at dll) — karena MySQL menafsirkan TIMESTAMP
     * pakai offset tetap dari config/database.php, jadi batas hari yang benar
     * harus dihitung dulu di zona tenant baru dikonversi ke zona penyimpanan.
     */
    function tenant_today_range(): array
    {
        $tz = tenant_timezone();

        return [
            \Carbon\Carbon::now($tz)->startOfDay()->setTimezone(config('app.timezone')),
            \Carbon\Carbon::now($tz)->endOfDay()->setTimezone(config('app.timezone')),
        ];
    }
}

if (! function_exists('tenant_month_range')) {
    /**
     * Rentang [awal, akhir] bulan berjalan menurut kalender zona tenant,
     * dikonversi ke timezone penyimpanan — dipakai bareng whereBetween()
     * sebagai pengganti whereMonth()/whereYear() di kolom TIMESTAMP, dengan
     * alasan yang sama seperti tenant_today_range().
     */
    function tenant_month_range(): array
    {
        $tz = tenant_timezone();

        return [
            \Carbon\Carbon::now($tz)->startOfMonth()->setTimezone(config('app.timezone')),
            \Carbon\Carbon::now($tz)->endOfMonth()->setTimezone(config('app.timezone')),
        ];
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
