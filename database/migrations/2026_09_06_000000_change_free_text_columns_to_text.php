<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ubah kolom string yang sebenarnya menampung teks bebas (bukan enum/identifier/status
 * pendek) menjadi TEXT, supaya tidak terpotong di batas VARCHAR lama.
 *
 * Kandidat yang diubah di sini (hasil audit — lihat percakapan terkait):
 * - paket_memberships.keterangan  : VARCHAR(100) → TEXT (dirender sebagai <textarea>)
 * - trainers.keterangan           : VARCHAR(100) → TEXT (catatan bebas trainer)
 * - transaksi_keuangans.deskripsi : VARCHAR(255) → TEXT (dirender sebagai <textarea>)
 *
 * Ketiganya sudah NOT NULL & tanpa default di migrasi aslinya — dipertahankan apa adanya.
 * TEXT di MySQL/MariaDB tidak boleh punya DEFAULT, jadi kolom ini memang harus tanpa default.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('paket_memberships') && Schema::hasColumn('paket_memberships', 'keterangan')) {
            Schema::table('paket_memberships', function (Blueprint $table) {
                $table->text('keterangan')->change();
            });
        }

        if (Schema::hasTable('trainers') && Schema::hasColumn('trainers', 'keterangan')) {
            Schema::table('trainers', function (Blueprint $table) {
                $table->text('keterangan')->change();
            });
        }

        if (Schema::hasTable('transaksi_keuangans') && Schema::hasColumn('transaksi_keuangans', 'deskripsi')) {
            Schema::table('transaksi_keuangans', function (Blueprint $table) {
                $table->text('deskripsi')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('paket_memberships') && Schema::hasColumn('paket_memberships', 'keterangan')) {
            Schema::table('paket_memberships', function (Blueprint $table) {
                $table->string('keterangan', 100)->change();
            });
        }

        if (Schema::hasTable('trainers') && Schema::hasColumn('trainers', 'keterangan')) {
            Schema::table('trainers', function (Blueprint $table) {
                $table->string('keterangan', 100)->change();
            });
        }

        if (Schema::hasTable('transaksi_keuangans') && Schema::hasColumn('transaksi_keuangans', 'deskripsi')) {
            Schema::table('transaksi_keuangans', function (Blueprint $table) {
                $table->string('deskripsi')->change();
            });
        }
    }
};
