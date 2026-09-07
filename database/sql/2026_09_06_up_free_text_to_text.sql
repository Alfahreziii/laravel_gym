-- =============================================================================
-- File   : database/sql/2026_09_06_up_free_text_to_text.sql
-- Tujuan : Ubah kolom VARCHAR yang menampung teks bebas (bukan enum/identifier/
--          status pendek) menjadi TEXT, di database TENANT produksi.
--
--          Ini adalah versi SQL manual dari migrasi Laravel
--          `database/migrations/2026_09_06_000000_change_free_text_columns_to_text.php`.
--          Migrasi Laravel tetap dipakai untuk lokal (`php artisan migrate`);
--          file ini dipakai untuk tenant DB produksi yang di-apply manual per DB
--          (bukan lewat artisan, sesuai keputusan: tidak ada command tenants:migrate).
--
-- Cara pakai (dialek MySQL/MariaDB, sama seperti koneksi 'tenant' di config/database.php):
--   mysql -u <user> -p <nama_database_tenant> < database/sql/2026_09_06_up_free_text_to_text.sql
--
--   Jalankan SATU KALI per database tenant. Aman dijalankan ulang (idempoten) —
--   tiap ALTER dibungkus pengecekan information_schema (skip kalau kolom sudah TEXT,
--   atau kalau tabel/kolom tidak ada sama sekali).
--
--   Catatan kompatibilitas: skrip ini pakai DELIMITER + stored procedure SEMENTARA
--   untuk guard idempoten (di-DROP di akhir file, tidak meninggalkan objek permanen).
--   DELIMITER dikenali oleh client `mysql` CLI, phpMyAdmin, dan sebagian besar GUI
--   (HeidiSQL, DBeaver, TablePlus). Kalau menjalankan lewat driver yang mengeksekusi
--   file sebagai satu query mentah (tanpa memahami DELIMITER), pecah manual per blok.
--
-- Kolom yang diubah (urutan eksekusi, hasil audit):
--   1. paket_memberships.keterangan      VARCHAR(100) NOT NULL -> TEXT NOT NULL
--   2. trainers.keterangan               VARCHAR(100) NOT NULL -> TEXT NOT NULL
--   3. transaksi_keuangans.deskripsi     VARCHAR(255) NOT NULL -> TEXT NOT NULL
--   4. product_quantity_logs.description VARCHAR(255) NULL     -> TEXT NULL
--
-- Catatan teknis:
--   - TEXT di MySQL/MariaDB tidak boleh punya DEFAULT -> tidak ditambahkan di sini.
--   - NOT NULL / NULL dipertahankan persis sesuai skema asli tiap kolom (lihat
--     migrasi create table masing-masing tabel).
--   - Tidak ada kolom di sini yang ter-index/unique/FK, jadi ALTER aman tanpa
--     perlu drop index dulu.
--   - Skrip ini hanya mengubah TIPE kolom, tidak menyentuh data/isi.
-- =============================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS _tmp_migrate_free_text_to_text $$
CREATE PROCEDURE _tmp_migrate_free_text_to_text()
BEGIN
    -- paket_memberships.keterangan: keterangan paket membership, sudah dirender
    -- sebagai <textarea> di form create/edit -> memang free text, bukan label pendek.
    IF (SELECT DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'paket_memberships'
          AND COLUMN_NAME = 'keterangan') <> 'text' THEN
        ALTER TABLE paket_memberships MODIFY COLUMN keterangan TEXT NOT NULL;
    END IF;

    -- trainers.keterangan: catatan bebas tentang trainer, sebelumnya terpotong
    -- diam-diam di batas 100 karakter (VARCHAR) walau divalidasi max:100 di controller.
    IF (SELECT DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'trainers'
          AND COLUMN_NAME = 'keterangan') <> 'text' THEN
        ALTER TABLE trainers MODIFY COLUMN keterangan TEXT NOT NULL;
    END IF;

    -- transaksi_keuangans.deskripsi: deskripsi transaksi keuangan, dirender sebagai
    -- <textarea> di modal input Neraca -> free text, dibatasi cuma karena default VARCHAR.
    IF (SELECT DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'transaksi_keuangans'
          AND COLUMN_NAME = 'deskripsi') <> 'text' THEN
        ALTER TABLE transaksi_keuangans MODIFY COLUMN deskripsi TEXT NOT NULL;
    END IF;

    -- product_quantity_logs.description: alasan perubahan stok, dirender sebagai
    -- <textarea> di form restock/adjustment produk -> free text, nullable (opsional diisi).
    IF (SELECT DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'product_quantity_logs'
          AND COLUMN_NAME = 'description') <> 'text' THEN
        ALTER TABLE product_quantity_logs MODIFY COLUMN description TEXT NULL;
    END IF;
END $$

DELIMITER ;

CALL _tmp_migrate_free_text_to_text();

DROP PROCEDURE IF EXISTS _tmp_migrate_free_text_to_text;
