<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriAkun;
use App\Models\AkunKeuangan;

class NeracaController extends Controller
{
    public function index()
    {
        // Ambil semua kategori (Aset, Kewajiban, Modal)
        $kategori = KategoriAkun::with(['akun.transaksi'])->get();

        // Hitung saldo untuk tiap akun DENGAN RUMUS YANG BENAR
        $kategori->each(function ($kat) {
            $kat->akun->each(function ($akun) use ($kat) {
                $debit = $akun->transaksi->sum('debit');
                $kredit = $akun->transaksi->sum('kredit');
                
                // PERBAIKAN: Rumus berbeda per kategori
                if ($kat->kode === 'AST') {
                    // Aset: bertambah di debit
                    $akun->saldo = $debit - $kredit;
                } else {
                    // Kewajiban & Modal: bertambah di kredit
                    $akun->saldo = $kredit - $debit;
                }
            });
        });

        // Hitung total
        $total_aset = $kategori->where('kode', 'AST')->first()?->akun->sum('saldo') ?? 0;
        $total_kewajiban = $kategori->where('kode', 'KEW')->first()?->akun->sum('saldo') ?? 0;
        $total_modal = $kategori->where('kode', 'MOD')->first()?->akun->sum('saldo') ?? 0;

        $total_kewajiban_modal = $total_kewajiban + $total_modal;

        return view('pages.neraca.index', compact(
            'kategori',
            'total_aset',
            'total_kewajiban',
            'total_modal',
            'total_kewajiban_modal'
        ));
    }
}