# CLAUDE.md

## Konteks Penting

**Project ini sudah berjalan di produksi (gym management system).** Semua perubahan harus
dilakukan dengan hati-hati — jangan melakukan refactor besar, perubahan skema database, atau
penghapusan kode tanpa memastikan tidak ada yang bergantung padanya. Jika ragu, tanyakan ke user
sebelum mengubah struktur yang sudah ada.

## Framework & Stack

- **Laravel 11** (PHP ^8.2), arsitektur monolith klasik (Blade + Controller, tanpa API
  Resource/Service layer terpisah).
- `spatie/laravel-permission` — role & permission (`admin`, `spv`, `trainer`, `member`, `guest`).
- `barryvdh/laravel-dompdf` — export laporan ke PDF.
- `milon/barcode` — barcode kartu member.
- `laravel/sanctum`, `laravel/breeze` — scaffolding auth.
- Testing pakai **Pest**, bukan PHPUnit langsung.

## Organisasi Controller

`app/Http/Controllers/` dipecah per **domain bisnis**, bukan per resource generik:

```
Auth/            → login, register, verifikasi email (bawaan Breeze)
Membership/      → Anggota, AnggotaMembership, PaketMembership, KategoriPaket, PembayaranMembership
PersonalTrainer/ → PaketPersonalTrainer, MemberTrainer, PembayaranTrainer, Specialisasi
Trainer/         → TrainerDashboard, TrainerListMember, TrainerPlaylist, GajiTrainer, LevelTrainer, RiwayatGajiTrainer
Kasir/           → Kasir (POS), Product, KategoriProduct
Kehadiran/       → KehadiranMember, KehadiranTrainer, AbsenNotif, NoRole (absensi tanpa role)
Keuangan/        → TransaksiKeuangan, Neraca
Api/             → GateController
Concerns/        → trait yang dipakai bareng controller (lihat ExportsExcel di bawah)
```

Controller top-level (`TrainerController`, `AlatGymController`, `UsersController`, dst.) dipakai
untuk entity yang tidak butuh sub-namespace.

Routing di `routes/web.php` mengikuti pola yang sama: dikelompokkan per role lewat
`RoleMiddleware::class . ':admin|spv'` dst., lalu `Route::controller(X::class)->group()` untuk
daftar action per controller — **bukan** `Route::resource`.

## Pola CRUD + Export yang Berulang

Hampir semua controller domain mengikuti method set yang sama:
`index`, `datatable` (AJAX untuk DataTables), `exportPdf`, `exportExcel`, `store`, `update`,
`destroy`. Saat menambah fitur baru di domain yang sudah ada, ikuti pola ini agar konsisten
dengan controller lain (lihat penamaan route seperti `anggota.export_pdf`,
`trainer.export_excel`, dll.).

### Trait `ExportsExcel`

`app/Http/Controllers/Concerns/ExportsExcel.php` — dipakai di 11+ controller (Kehadiran,
Keuangan, Trainer, Membership, PersonalTrainer, Kasir). Menyediakan:
- `excelDownload()` — bungkus HTML table jadi response download `.xls`
- `excelStyles()` — CSS umum seragam untuk semua laporan Excel
- `exNum()` — format angka ala Indonesia (1.000.000)
- `exEsc()` — HTML-escape dengan fallback `-`

Ini sengaja **tidak** memakai Maatwebsite/Excel — formatnya HTML table dengan namespace Office
agar Excel membukanya secara native tanpa dependency tambahan. Kalau menambah export baru,
reuse trait ini daripada menulis ulang logic-nya.

## RoleMiddleware Custom

`app/Http/Middleware/RoleMiddleware.php` — bukan middleware Spatie langsung. Menerima beberapa
role dipisah `|` (contoh: `:admin|spv`). Ada logic khusus untuk role `trainer`: cek status
`Trainer::STATUS_AKTIF` dan redirect ke halaman `trainer.waiting.approval` jika trainer belum
aktif. Kalau menambah role atau mengubah flow approval trainer, perubahan harus dilakukan di
sini, bukan dengan menambah middleware terpisah.

## Helper Terkait

- `RoleRedirectHelper` (`app/Helpers/`) — redirect setelah login berdasarkan role.
- View Composers (`NavbarComposer`, `TrainerNavbarComposer`) — inject data navbar tanpa
  duplikasi di tiap controller.
- Observer (`TrainerObserver`, `KehadiranMemberObserver`) — side-effect otomatis pada model
  (misal update status).
