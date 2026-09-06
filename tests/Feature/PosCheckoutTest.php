<?php

use App\Models\AkunKeuangan;
use App\Models\KategoriProduct;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\ProductQuantityLog;
use App\Models\TransaksiKeuangan;
use Tests\Concerns\InteractsWithTenant;

uses(InteractsWithTenant::class);

beforeEach(function () {
    $this->setUpTenant();
    $this->actingAsRole('admin');

    $this->kategori = KategoriProduct::create(['name' => 'Minuman']);

    $this->product = Product::create([
        'name'                => 'Air Mineral 600ml',
        'barcode'             => 'BC-' . uniqid(),
        'price'               => 50000,
        'hpp'                 => 30000,
        'quantity'            => 10,
        'is_active'           => true,
        'kategori_product_id' => $this->kategori->id,
    ]);
});

it('processes POS checkout: transaction, stock, and financial journal (KasirController@bayar)', function () {
    $payload = [
        'cart' => [
            [
                'id'       => $this->product->id,
                'name'     => $this->product->name,
                'qty'      => 2,
                'price'    => 50000,
                'kategori' => ['name' => $this->kategori->name],
            ],
        ],
        'diskon'             => 0,
        'diskon_barang'      => 0,
        'metode_pembayaran'  => 'cash',
        'dibayarkan'         => 100000,
        'kembalian'          => 0,
        'customer_name'      => 'Budi',
        'transaction_id'     => null,
    ];

    $response = $this->postJson(route('kasirbayar'), $payload);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    $transactionId = $response->json('transaction_id');

    // Transaction + item
    $transaction = Transaction::find($transactionId);
    expect($transaction)->not->toBeNull();
    expect((float) $transaction->total_amount)->toBe(100000.0);
    expect($transaction->status)->toBe('completed');
    expect($transaction->customer_name)->toBe('Budi');

    $item = TransactionItem::where('transaction_id', $transactionId)->first();
    expect($item)->not->toBeNull();
    expect($item->product_id)->toBe($this->product->id);
    expect((int) $item->qty)->toBe(2);
    expect((float) $item->price)->toBe(50000.0);

    // Stok berkurang
    $this->product->refresh();
    expect((int) $this->product->quantity)->toBe(8);

    $log = ProductQuantityLog::where('product_id', $this->product->id)->first();
    expect($log)->not->toBeNull();
    expect($log->type)->toBe('out');
    expect((int) $log->quantity)->toBe(2);
    expect((int) $log->current_quantity)->toBe(8);

    // Jurnal keuangan: netBayar = 100000, totalHPP = 30000 x 2 = 60000
    $akunKas        = AkunKeuangan::where('kode', 'AST001')->first();
    $akunPendapatan = AkunKeuangan::where('kode', 'MOD005')->first();
    $akunHpp        = AkunKeuangan::where('kode', 'BEB001')->first();
    $akunPersediaan = AkunKeuangan::where('kode', 'AST004')->first();

    $jurnal = TransaksiKeuangan::where('referensi_tabel', 'transactions')
        ->where('referensi_id', $transactionId)
        ->get();

    expect($jurnal)->toHaveCount(4);

    $debitKas = $jurnal->firstWhere('akun_id', $akunKas->id);
    expect((float) $debitKas->debit)->toBe(100000.0);
    expect((float) $debitKas->kredit)->toBe(0.0);

    $kreditPendapatan = $jurnal->firstWhere('akun_id', $akunPendapatan->id);
    expect((float) $kreditPendapatan->kredit)->toBe(100000.0);
    expect((float) $kreditPendapatan->debit)->toBe(0.0);

    $debitHpp = $jurnal->firstWhere('akun_id', $akunHpp->id);
    expect((float) $debitHpp->debit)->toBe(60000.0);
    expect((float) $debitHpp->kredit)->toBe(0.0);

    $kreditPersediaan = $jurnal->firstWhere('akun_id', $akunPersediaan->id);
    expect((float) $kreditPersediaan->kredit)->toBe(60000.0);
    expect((float) $kreditPersediaan->debit)->toBe(0.0);
});
