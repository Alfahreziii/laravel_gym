-- =============================================================================
-- File   : database/sql/2026_09_06_down_free_text_to_text.sql
-- Tujuan : ROLLBACK — kembalikan kolom TEXT ke tipe & panjang VARCHAR semula,
--          di database TENANT produksi. Kebalikan dari
--          database/sql/2026_09_06_up_free_text_to_text.sql.
--
-- Cara pakai (dialek MySQL/MariaDB, sama seperti koneksi 'tenant' di config/database.php):
--   mysql -u <user> -p <nama_database_tenant> < database/sql/2026_09_06_down_free_text_to_text.sql
--
--   Jalankan SATU KALI per database tenant yang ingin di-rollback. Aman dijalankan
--   ulang (idempoten) — tiap ALTER dibungkus pengecekan information_schema (skip
--   kalau kolom sudah bukan TEXT, atau kalau tabel/kolom tidak ada sama sekali).
--
--   Catatan kompatibilitas: sama seperti file up-nya, skrip ini pakai DELIMITER +
--   stored procedure SEMENTARA untuk guard idempoten (di-DROP di akhir file).
--
-- !! PERINGATAN SEBELUM ROLLBACK !!
--   Kalau ada isi kolom yang sudah lebih panjang dari batas VARCHAR semula (karena
--   sempat diisi teks panjang selagi bertipe TEXT), MySQL akan MEMOTONG nilai
--   tersebut saat MODIFY balik ke VARCHAR (atau gagal total kalau strict mode aktif).
--   Cek dulu sebelum menjalankan file ini:
--     SELECT MAX(CHAR_LENGTH(keterangan))   FROM paket_memberships;       -- batas: 100
--     SELECT MAX(CHAR_LENGTH(keterangan))   FROM trainers;                -- batas: 100
--     SELECT MAX(CHAR_LENGTH(deskripsi))    FROM transaksi_keuangans;     -- batas: 255
--     SELECT MAX(CHAR_LENGTH(description))  FROM product_quantity_logs;  -- batas: 255
--
-- Kolom yang dikembalikan (urutan eksekusi):
--   1. paket_memberships.keterangan      TEXT -> VARCHAR(100) NOT NULL
--   2. trainers.keterangan               TEXT -> VARCHAR(100) NOT NULL
--   3. transaksi_keuangans.deskripsi     TEXT -> VARCHAR(255) NOT NULL
--   4. product_quantity_logs.description TEXT -> VARCHAR(255) NULL
--
-- Catatan teknis:
--   - NOT NULL / NULL dikembalikan persis sesuai skema asli tiap kolom.
--   - Skrip ini hanya mengubah TIPE kolom, tidak menyentuh data/isi (selain efek
--     truncation otomatis MySQL yang disebut di peringatan atas, di luar kendali skrip ini).
-- =============================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS _tmp_rollback_free_text_to_text $$
CREATE PROCEDURE _tmp_rollback_free_text_to_text()
BEGIN
    -- paket_memberships.keterangan: kembali ke VARCHAR(100) NOT NULL
    IF (SELECT DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'paket_memberships'
          AND COLUMN_NAME = 'keterangan') = 'text' THEN
        ALTER TABLE paket_memberships MODIFY COLUMN keterangan VARCHAR(100) NOT NULL;
    END IF;

    -- trainers.keterangan: kembali ke VARCHAR(100) NOT NULL
    IF (SELECT DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'trainers'
          AND COLUMN_NAME = 'keterangan') = 'text' THEN
        ALTER TABLE trainers MODIFY COLUMN keterangan VARCHAR(100) NOT NULL;
    END IF;

    -- transaksi_keuangans.deskripsi: kembali ke VARCHAR(255) NOT NULL
    IF (SELECT DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'transaksi_keuangans'
          AND COLUMN_NAME = 'deskripsi') = 'text' THEN
        ALTER TABLE transaksi_keuangans MODIFY COLUMN deskripsi VARCHAR(255) NOT NULL;
    END IF;

    -- product_quantity_logs.description: kembali ke VARCHAR(255) NULL
    IF (SELECT DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'product_quantity_logs'
          AND COLUMN_NAME = 'description') = 'text' THEN
        ALTER TABLE product_quantity_logs MODIFY COLUMN description VARCHAR(255) NULL;
    END IF;
END $$

DELIMITER ;

CALL _tmp_rollback_free_text_to_text();

DROP PROCEDURE IF EXISTS _tmp_rollback_free_text_to_text;
