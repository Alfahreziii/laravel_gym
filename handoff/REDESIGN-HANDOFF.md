# HexaGym Redesign — Handoff ke Claude Code

> Sumber visual: `HexaGym Dashboard.dc.html` + `HexaGym Design System.dc.html` (Claude Design).
> Target repo: `Alfahreziii/laravel_gym` @ branch `redesign-frontend`.
> **Aturan:** hanya frontend (Blade + Tailwind + CSS). JANGAN ubah controller, route, model, migration, atau logic Laravel.

---

## 0. Cara pakai file ini
Di repo, ke Claude Code:
> "Implement redesign sesuai `docs/redesign/REDESIGN-HANDOFF.md` dan file HTML acuan di `docs/redesign/`. Frontend saja — jangan sentuh controller/route/model."

Acuan tampilan (standalone HTML) taruh di `docs/redesign/`:
- `dashboard.html` — target Admin Dashboard
- `members.html` — target Member list + add/edit form + delete confirm
- `member-detail.html` — target detail/profil member (header + tab pembayaran/kehadiran/riwayat)
- `pos.html` — target Mesin Kasir / POS (product grid + cart)
- `neraca.html` — target Laporan Keuangan / Neraca (summary + buku besar)
- `login.html` — target Login / Auth (split brand panel + form)
- `design-system.html` — referensi semua komponen (token, button, table, form, badge, card, modal, alert)

---

## 1. Tokens — patch `tailwind.config.js`

Skala `primary/success/danger/warning/info` **sudah benar**, jangan diubah. Yang kurang: **warm surface** (preview yang disetujui pakai off-white hangat, bukan `neutral` dingin bawaan). Tambahkan ke `theme.extend.colors`:

```js
// theme.extend.colors — TAMBAHAN, bukan pengganti
surface: {
  light:  '#FAFAF9', // kartu / panel (light)
  raised: '#F1F0ED', // header tabel, chip (light)
  dark:   '#1F1B17', // kartu / panel (dark)
  'dark-raised': '#28231D',
},
canvas: {
  light: '#EBEAE7', // app background (light)
  dark:  '#15120F', // app background (dark)
},
line: {
  light: '#E2E0DB', // border (light)
  dark:  '#332D26',
},
ink: {
  DEFAULT: '#1A1A18', // text primary (light)
  2: '#6E6A63',       // secondary
  3: '#9C978E',       // muted
  'd':  '#F4F1EC',    // text primary (dark)
  'd2': '#A8A29A',
  'd3': '#6E685F',
},
```

Radius standar (opsional, kalau mau token): card `16px` (`rounded-2xl`), control `10px` (`rounded-[10px]`), chip `8px` (`rounded-lg`), pill `full`.

Font sudah benar: `font-display` = Barlow Condensed, `font-sans` = Plus Jakarta Sans.

---

## 2. `resources/css/app.css` (saat ini KOSONG — perlu diisi)

Komponen Blade sudah memanggil `.card`, `.card-header`, `.card-body`, `.form-control`. Definisikan pakai `@layer components`:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  body {
    @apply bg-canvas-light text-ink font-sans antialiased;
  }
  .dark body {
    @apply bg-canvas-dark text-ink-d;
  }
}

@layer components {
  /* ---- Card ---- */
  .card {
    @apply bg-surface-light dark:bg-surface-dark border border-line-light dark:border-line-dark
           rounded-2xl shadow-sm dark:shadow-none;
  }
  .card-header {
    @apply px-5 py-4 border-b border-line-light dark:border-line-dark;
  }
  .card-body { @apply p-5; }

  /* ---- Form controls ---- */
  .form-control {
    @apply w-full bg-canvas-light dark:bg-canvas-dark
           border border-line-light dark:border-line-dark rounded-[10px]
           px-3 py-2.5 text-ink dark:text-ink-d
           placeholder:text-ink-3
           focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/15;
  }
  .form-control-sm { @apply px-2.5 py-1.5 text-sm; }
  .form-label { @apply block text-xs font-semibold text-ink-2 dark:text-ink-d2 mb-1.5; }
  .form-error { @apply mt-1 text-xs text-danger-700 dark:text-danger-400 flex items-center gap-1; }
  .form-control.is-invalid {
    @apply border-danger-500 ring-4 ring-danger-500/15;
  }

  /* ---- Buttons ---- */
  .btn { @apply inline-flex items-center gap-2 font-semibold rounded-[10px] px-4 py-2.5
                transition-colors focus:outline-none focus:ring-4; }
  .btn-primary   { @apply bg-primary-500 text-white hover:bg-primary-600 focus:ring-primary-500/30; }
  .btn-secondary { @apply bg-surface-light dark:bg-surface-dark text-ink dark:text-ink-d
                          border border-line-light dark:border-line-dark
                          hover:bg-canvas-light dark:hover:bg-canvas-dark focus:ring-primary-500/30; }
  .btn-danger    { @apply bg-danger-500 text-white hover:bg-danger-600 focus:ring-danger-500/30; }
  .btn-ghost     { @apply text-ink-2 dark:text-ink-d2 hover:bg-canvas-light dark:hover:bg-canvas-dark
                          hover:text-ink dark:hover:text-ink-d; }
  .btn-icon      { @apply w-10 h-10 grid place-items-center p-0 border border-line-light
                          dark:border-line-dark bg-surface-light dark:bg-surface-dark
                          text-ink-2 hover:bg-canvas-light dark:hover:bg-canvas-dark; }
  .btn:disabled  { @apply opacity-40 cursor-not-allowed; }
}
```

Pastikan `resources/js/app.js` atau layout meng-import `app.css` (via Vite) — cek `@vite(['resources/css/app.css', ...])` di `<x-head>`.

---

## 3. Pemetaan section reference → komponen Blade asli

| Reference (design-system.html) | Komponen Blade repo | Catatan implementasi |
|---|---|---|
| **01 Tokens** | `tailwind.config.js` + `app.css` | lihat §1 & §2 |
| **02 Buttons** | (buat) `.btn-*` di `app.css` | atau `<x-primary-button>` / `<x-danger-button>` yang sudah ada — samakan style ke `.btn-*` |
| **03 Data Table** | `<x-data-table tableId>` | AJAX DataTables sudah ada. Redesign = ganti style thead (`bg-surface-raised`), row hover (`hover:bg-primary-50 dark:hover:bg-primary-600/10`), zebra, kolom aksi tombol `.btn-icon`. Pagination di-render JS — samakan style tombolnya. |
| **04 Forms** | `.form-control`, `.form-label`, `.form-error` | input + `@tailwindcss/forms` sudah dipasang. Input ber-ikon: bungkus `relative`, ikon `absolute left-3`, input `pl-9`. |
| **05 Badges** | `<x-badge type dot>` | sudah ada & cocok. Map status app → type: Aktif/Lunas→`success`, Belum Bayar/Expired→`danger`, Pending/Akan berakhir→`warning`, Nonaktif→`neutral`. |
| **06 Cards** | `<x-card>` (`.card`) + stat card gradient | Content card = `<x-card>`. Stat card gradient = section terpisah (lihat §4). |
| **07 Modal** | `<x-modal id title maxWidth>` | 2 varian sudah cocok: konfirmasi (`maxWidth="max-w-md"`, footer `.btn-secondary` + `.btn-danger`) & form (`max-w-2xl`, body `$body` scroll bawaan, footer `.btn-secondary` + `.btn-primary`). Kontrol via `HexaModal.show(id)`. |
| **08 Alerts** | `<x-alert type dismissible>` | sudah ada (iconify). Cocok apa adanya. |

---

## 4. Stat card gradient (khusus Dashboard)

4 KPI card pakai glass/gradient-tint. Ini treatment hero — buat sebagai partial `resources/views/components/stat-card.blade.php` supaya reusable:

```blade
@props(['tone' => 'primary', 'label', 'value', 'sub' => null, 'delta' => null])
{{-- tone: primary(biru→ungu) | money(emerald→teal) | attend(cyan→teal) | warn(amber→merah) --}}
```

Gradient per tone (inline style, karena arbitrary rgba):
- **primary / Member Aktif** → `linear-gradient(135deg,rgba(59,130,246,.13),rgba(139,92,246,.11))`, border `rgba(124,108,246,.24)`, glow `rgba(99,102,241,.32)`, ikon-box `linear-gradient(135deg,#3B82F6,#8B5CF6)`
- **money / Pendapatan** → tint `rgba(16,185,129,.13)→rgba(20,184,166,.10)`, border `rgba(16,185,129,.24)`, glow `rgba(16,185,129,.30)`, ikon `#10B981→#14B8A6`
- **attend / Kehadiran** → tint `rgba(6,182,212,.13)→rgba(13,148,136,.10)`, border `rgba(6,182,212,.24)`, glow `rgba(6,182,212,.30)`, ikon `#06B6D4→#0D9488`
- **warn / Akan berakhir** → tint `rgba(245,158,11,.14)→rgba(239,68,68,.11)`, border `rgba(245,158,11,.26)`, glow `rgba(239,68,68,.30)`, ikon `#F59E0B→#EF4444`

Semua: `border-radius:16px; backdrop-filter:blur(7px); box-shadow:0 14px 34px -12px <glow>`. Hover: `translateY(-4px)` + glow diperkuat (`-12px .44–.46`). Cek `dashboard.html` untuk markup persisnya.

---

## 5. Layout & IA
- Dark mode: `class` strategy (sudah). Toggle set/remove `.dark` di `<html>`, persist ke `localStorage`.
- Sidebar (role-gated), topbar, nav-item active state (bar oranye kiri + `bg-primary-50`) — lihat `dashboard.html`.
- Sidebar IA per DESIGN.md §8: Dashboard · Membership/GYM · POS · Users · Payroll · Keuangan · Trainer · Profile. admin=full, trainer=grup Trainer, kasir=POS.

---

## 6. Urutan build
**Tier-1 (inti):**
1. ✅ Admin Dashboard (showpiece) — `dashboard.html`
2. ✅ Member list + add/edit form — `members.html`
3. ✅ Mesin Kasir / POS — `pos.html`

**Tambahan (layout khas, bukan turunan tabel):**
4. ✅ Login / Auth (showpiece) — `login.html`
5. ✅ Detail / Profil Member — `member-detail.html`
6. ✅ Laporan Keuangan / Neraca — `neraca.html`

Sisa layar app (Paket Member, Users, Alat Gym, Product, Riwayat Transaksi, Gaji Trainer, dll.) = **turunan pola Members** (list + `<x-data-table>` + modal) — pakai `members.html` sebagai template, ganti kolom & field. Tidak perlu mock baru.

Light dulu, lalu pastikan varian dark. Screen dense tetap fungsional; hero treatment hanya Dashboard + Login.

---

## 7. Member list (`members.html`) → komponen Blade

Layar ini menempel ke controller/route member yang **sudah ada** — frontend saja.

| Bagian di `members.html` | Komponen / class Blade | Catatan |
|---|---|---|
| Page header + tombol "Tambah member" | markup biasa + `.btn-primary` | tombol `onClick` → `HexaModal.show('member-form')` |
| Summary strip (4 kartu ringkas) | `<x-card>` versi padat, atau partial `stat-mini` | angka dari controller (total/aktif/expiring/expired) |
| Filter bar (search + tab status + export) | form GET biasa | tab status = link `?status=active` dsb (server-side), ATAU biarkan DataTables handle search/filter client-side |
| **Tabel** | `<x-data-table tableId="members">` | kolom: checkbox, member(avatar+kode), kontak(email/HP), paket, status, bergabung, berakhir, aksi. Status → `<x-badge :type>` (map: active→success, expiring→warning, expired→danger, nonaktif→neutral). Aksi = `.btn-icon` edit + hapus. Pagination di-render DataTables — samakan style tombol ke acuan. |
| **Modal tambah/edit** | `<x-modal id="member-form" title maxWidth="max-w-xl">` | body = field form (`.form-control`, `.form-label`), grid 2 kolom. Satu modal dipakai 2 mode: kosong utk tambah, prefilled utk edit (isi via JS saat tombol edit diklik, atau route edit). Footer `.btn-secondary` Batal + `.btn-primary` Simpan. |
| **Modal hapus** | `<x-modal id="member-delete" maxWidth="max-w-md">` | ikon danger, nama member dinamis, footer `.btn-secondary` + `.btn-danger`. Submit = `<form method="POST">` + `@method('DELETE')`. |

Catatan: di acuan HTML, filter & hapus jalan client-side hanya untuk demo interaksi. Di Blade, hormati flow asli (route resource + DataTables server-side kalau memang begitu implementasinya).

---

## 8. POS / Mesin Kasir (`pos.html`) → komponen Blade

Layar full-height 2 kolom: **grid produk** (kiri) + **panel keranjang** (kanan, `sticky`). Sidebar tetap, nav aktif "Mesin Kasir".

| Bagian di `pos.html` | Implementasi Blade | Catatan |
|---|---|---|
| Kolom kiri: header + search produk + tab kategori | markup biasa | tab kategori filter grid; di acuan client-side |
| **Product grid** | loop `@foreach($products as $p)` kartu `.card` tappable | tinting per kategori (inline style, lihat acuan). Badge "Stok N" muncul saat `stock <= 5`. Ketuk kartu = tambah ke cart. |
| Panel keranjang: pilih member | `<select class="form-control">` | Umum (non-member) atau member terdaftar → memicu diskon |
| Line item + stepper qty | partial JS (Alpine/vanilla) | tombol `+`/`−` ubah qty, `× line total` |
| Ringkasan (subtotal/diskon/PPN/total) | dihitung JS/Livewire | diskon member 10%, PPN 11% (samakan ke aturan pajak asli app) |
| Metode bayar (Tunai/Kartu/QRIS) | segmented buttons | state aktif = `.btn` accent |
| Tombol **Bayar** | submit transaksi | disabled saat keranjang kosong; sukses → modal "Transaksi berhasil" (`<x-modal>`) + cetak struk |

Catatan: cart & totals di acuan murni client-side demo. Di app, sambungkan ke controller transaksi / Livewire yang sudah ada — jangan ubah logic harga/pajak, hanya tampilannya. Angka diskon/PPN di acuan placeholder.

> **Interaksi ringan** (toggle tema, cart qty, tab filter, modal): boleh pakai Alpine.js atau vanilla JS kecil — hindari nambah framework berat. `HexaModal` yang sudah ada dipakai untuk semua modal.

---

## 9. Login / Auth (`login.html`) → Blade

Layar publik (di luar layout admin — pakai `layouts/guest` atau standalone). Split 2 kolom: **brand panel** (kiri, gradient oranye, showpiece) + **form** (kanan).

| Bagian | Implementasi | Catatan |
|---|---|---|
| Brand panel kiri | markup statis | gradient `150deg #7A2C10→#BC3E14→#F2622E`, hex float, stat strip. Sembunyikan `@media (max-width:900px)`. |
| Form login | `<form method="POST" action="{{ route('login') }}">` + `@csrf` | field email + password (`.form-control` + ikon), toggle lihat sandi, checkbox "Ingat saya" (`name="remember"`), tombol `.btn-primary` full-width. |
| Error state | `@error` / `$errors` | banner danger di atas form (lihat acuan). |
| Google / SSO | opsional | hapus kalau app tidak pakai OAuth. |

Jangan ubah guard/route auth Laravel — hanya tampilan Blade-nya.

---

## 10. Detail / Profil Member (`member-detail.html`) → Blade

Route `members/{member}` (show). Bukan tabel — layout profil.

| Bagian | Implementasi | Catatan |
|---|---|---|
| Cover + avatar + nama + stat | markup dari `$member` | cover gradient oranye, avatar inisial, badge status (`<x-badge>`), 3 stat (kunjungan/bulan aktif/total belanja). |
| Kolom kiri: Langganan aktif + Data pribadi | `<x-card>` | progress bar sisa masa aktif = `%` dihitung dari tanggal. |
| Kolom kanan: **tabs** | tab JS (Alpine) | `Pembayaran` → tabel riwayat (`<x-data-table>` atau tabel biasa); `Kehadiran` → heatmap + daftar kunjungan; `Riwayat` → timeline vertikal. Data dari relasi model (`$member->payments`, `->attendances`). |

Heatmap & timeline murni tampilan — sambungkan ke data absensi/transaksi asli.

---

## 11. Laporan Keuangan / Neraca (`neraca.html`) → Blade

Route laporan/neraca. Layar padat angka + export.

| Bagian | Implementasi | Catatan |
|---|---|---|
| Toolbar: date-range + Cetak + Export | form GET (periode) | Export → route yang sudah ada (Excel/PDF). Cetak → `window.print()` + print CSS. |
| 4 summary card | `<x-card>` padat | pemasukan/pengeluaran/laba/saldo. Angka dari controller. |
| **Buku besar** (tabel debit/kredit + `<tfoot>` total) | tabel biasa / `<x-data-table>` | filter Semua/Masuk/Keluar. Kategori = chip berwarna. Debit hijau, kredit merah, total di footer tebal. |
| Rincian pemasukan (bar) + kartu Laba bersih (gradient) | `<x-card>` + stat gradient | kartu laba pakai gradient emerald (acuan §4 tone `money`). |

Angka di acuan placeholder — pakai hitungan controller/akuntansi asli. Untuk cetak/PDF, sembunyikan sidebar & toolbar via `@media print`.
