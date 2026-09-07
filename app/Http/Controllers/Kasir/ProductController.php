<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;

use App\Models\Product;
use App\Models\KategoriProduct;
use App\Models\ProductQuantityLog;
use App\Models\AkunKeuangan;
use App\Models\TransaksiKeuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Concerns\ExportsExcel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProductController extends Controller
{
    use ExportsExcel;

    public function exportPdf()
    {
        try {
            $products = Product::with('kategori')->get();

            $totalProduk    = $products->count();
            $totalNilaiStok = $products->sum(fn($p) => $p->hpp * $p->quantity);
            $totalNilaiJual = $products->sum(function ($p) {
                $harga = $p->price;
                if ($p->discount > 0) {
                    $harga = $p->discount_type === 'percent'
                        ? $p->price - ($p->price * $p->discount / 100)
                        : $p->price - $p->discount;
                }
                return $harga * $p->quantity;
            });
            $produkAktif    = $products->where('is_active', 1)->count();
            $produkNonaktif = $products->where('is_active', 0)->count();
            $totalStok      = $products->sum('quantity');
            $title          = 'Laporan Data Produk';
            $tenant         = app('tenant');

            $pdf = Pdf::loadView('pages.admin.kasir.products.pdf', compact(
                'products',
                'totalProduk',
                'totalNilaiStok',
                'totalNilaiJual',
                'produkAktif',
                'produkNonaktif',
                'totalStok',
                'title',
                'tenant'
            ));
            $pdf->setPaper('a4', 'landscape');

            return $pdf->download('Laporan_Produk_' . date('Y-m-d_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal export PDF produk', ['error' => $e->getMessage()]);
            return redirect()->back()->with('danger', 'Gagal export PDF. Silakan coba lagi atau hubungi admin.');
        }
    }

    /**
     * Export data produk ke Excel (.xls) — kolom sama seperti Laporan PDF.
     */
    public function exportExcel()
    {
        try {
            $products = Product::with('kategori')->get();

            $totalProduk    = $products->count();
            $totalNilaiStok = $products->sum(fn($p) => $p->hpp * $p->quantity);
            $totalNilaiJual = $products->sum(function ($p) {
                $harga = $p->price;
                if ($p->discount > 0) {
                    $harga = $p->discount_type === 'percent'
                        ? $p->price - ($p->price * $p->discount / 100)
                        : $p->price - $p->discount;
                }
                return $harga * $p->quantity;
            });
            $produkAktif    = $products->where('is_active', 1)->count();
            $produkNonaktif = $products->where('is_active', 0)->count();
            $totalStok      = $products->sum('quantity');

            $rows = '';
            foreach ($products as $i => $p) {
                $hargaSetelahDiskon = $p->price;
                if ($p->discount > 0) {
                    $hargaSetelahDiskon = $p->discount_type === 'percent'
                        ? $p->price - ($p->price * $p->discount / 100)
                        : $p->price - $p->discount;
                }
                $nilaiJual    = $hargaSetelahDiskon * $p->quantity;
                $nilaiStokHpp = $p->hpp * $p->quantity;
                $diskon = $p->discount > 0
                    ? ($p->discount_type === 'percent' ? $p->discount . '%' : 'Rp ' . $this->exNum($p->discount))
                    : '-';

                $rows .= '<tr>'
                    . '<td class="center">' . ($i + 1) . '</td>'
                    . '<td>' . $this->exEsc($p->name) . '</td>'
                    . '<td>' . $this->exEsc($p->kategori->name ?? '-') . '</td>'
                    . '<td class="num">Rp ' . $this->exNum($p->hpp) . '</td>'
                    . '<td class="num">Rp ' . $this->exNum($p->price) . '</td>'
                    . '<td class="center">' . $diskon . '</td>'
                    . '<td class="num">Rp ' . $this->exNum($hargaSetelahDiskon) . '</td>'
                    . '<td class="center">' . $p->quantity . '</td>'
                    . '<td class="center">' . $p->reorder . '</td>'
                    . '<td class="center">' . ($p->is_active ? 'Aktif' : 'Nonaktif') . '</td>'
                    . '<td class="num">Rp ' . $this->exNum($nilaiStokHpp) . '</td>'
                    . '<td class="num">Rp ' . $this->exNum($nilaiJual) . '</td>'
                    . '</tr>';
            }

            $html = '<table>';
            $html .= '<tr><td colspan="12" class="title">Laporan Data Produk</td></tr>';
            $html .= '<tr><td colspan="12" class="subtitle">Dicetak: ' . tenant_now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') . ' ' . tz_label() . '</td></tr>';
            $html .= '<tr><td colspan="12"></td></tr>';
            $html .= '<tr>'
                . '<td colspan="3" class="summary-label">Total Produk</td><td colspan="3" class="summary-val">' . $totalProduk . '</td>'
                . '<td colspan="3" class="summary-label">Aktif / Nonaktif</td><td colspan="3" class="summary-val">' . $produkAktif . ' / ' . $produkNonaktif . '</td>'
                . '</tr>';
            $html .= '<tr>'
                . '<td colspan="3" class="summary-label">Total Stok</td><td colspan="3" class="summary-val">' . $this->exNum($totalStok) . '</td>'
                . '<td colspan="3" class="summary-label">Nilai Stok (HPP) / Nilai Jual</td><td colspan="3" class="summary-val">Rp ' . $this->exNum($totalNilaiStok) . ' / Rp ' . $this->exNum($totalNilaiJual) . '</td>'
                . '</tr>';
            $html .= '<tr><td colspan="12"></td></tr>';
            $html .= '<tr>'
                . '<th>No</th><th>Nama Produk</th><th>Kategori</th><th>HPP</th><th>Harga Jual</th><th>Diskon</th>'
                . '<th>Harga Setelah Diskon</th><th>Stok</th><th>Reorder</th><th>Status</th>'
                . '<th>Nilai Stok (HPP)</th><th>Nilai Jual</th>'
                . '</tr>';
            $html .= $rows;
            $html .= '<tr class="grand-row">'
                . '<td colspan="10" class="center">TOTAL</td>'
                . '<td class="num">Rp ' . $this->exNum($totalNilaiStok) . '</td>'
                . '<td class="num">Rp ' . $this->exNum($totalNilaiJual) . '</td>'
                . '</tr>';
            $html .= '</table>';

            $filename = 'Laporan_Produk_' . date('Y-m-d_His') . '.xls';

            return $this->excelDownload($html, 'Laporan Data Produk', $filename);
        } catch (\Exception $e) {
            Log::error('Gagal export Excel produk', ['error' => $e->getMessage()]);
            return redirect()->back()->with('danger', 'Gagal export Excel. Silakan coba lagi atau hubungi admin.');
        }
    }

    public function index()
    {
        $products = Product::with('kategori')->latest()->get();
        return view('pages.admin.kasir.products.index', compact('products'));
    }

    public function datatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = Product::with('kategori');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('kategori', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $total   = (clone $query)->count();
        $data    = (clone $query)->latest()->skip(($page - 1) * $perPage)->take($perPage)->get();
        $isAdmin = (bool) auth()->user()?->hasRole('admin');

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage, $isAdmin) {
                $diskon = '-';
                if ($item->discount > 0) {
                    $diskon = $item->discount_type === 'percent'
                        ? $item->discount . '%'
                        : 'Rp ' . number_format($item->discount, 0, ',', '.');
                }
                return [
                    'no'            => (($page - 1) * $perPage) + $index + 1,
                    'id'            => $item->id,
                    'name'          => $item->name,
                    'barcode'       => $item->barcode,
                    'image_url'     => $item->image
                        ? asset('storage/' . $item->image)
                        : asset('assets/images/kasir/product-placeholder.png'),
                    'quantity'      => $item->quantity,
                    'is_active'     => (bool) $item->is_active,
                    'kategori_name' => $item->kategori->name ?? '-',
                    'hpp'           => $item->hpp,
                    'price'         => $item->price,
                    'diskon'        => $diskon,
                    'reorder'       => $item->reorder,
                    'edit_url'      => $isAdmin ? route('products.edit', $item->id) : null,
                    'logs_url'      => route('products.logs', $item->id),
                    'adjust_url'    => $isAdmin ? route('products.adjust', $item->id) : null,
                    'delete_url'    => $isAdmin ? route('products.destroy', $item->id) : null,
                    'is_admin'      => $isAdmin,
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    public function create()
    {
        $categories = KategoriProduct::all();
        return view('pages.admin.kasir.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'name'                => 'required|string|max:255',
                'barcode'             => 'required|string|max:100|unique:products',
                'description'         => 'nullable|string',
                'image'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'price'               => 'required|numeric|min:0',
                'hpp'                 => 'required|numeric|min:0',
                'discount'            => 'nullable|numeric|min:0',
                'discount_type'       => 'nullable|in:percent,nominal',
                'quantity'            => 'integer|min:0',
                'reorder'             => 'integer|min:0',
                'is_active'           => 'boolean',
                'kategori_product_id' => 'required|exists:kategori_products,id',
            ]);

            $data = $validated;

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store(tenant_storage_path('products'), 'public');
            }

            $product = Product::create($data);

            if ($product->quantity > 0) {
                ProductQuantityLog::create([
                    'product_id'       => $product->id,
                    'type'             => 'in',
                    'quantity'         => $product->quantity,
                    'current_quantity' => $product->quantity,
                    'description'      => 'Stok awal produk',
                ]);

                /*
                 * JURNAL: Pembelian stok awal
                 * Debit  AST004 (Persediaan Barang Dagang) → aset bertambah
                 * Kredit AST001 (Kas)                      → kas berkurang
                 */
                $this->jurnalPembelianStok(
                    $product,
                    $product->quantity,
                    'Pembelian stok awal: ' . $product->name
                );
            }

            DB::commit();
            return back()->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambahkan produk', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('danger', 'Gagal menambahkan produk. Silakan coba lagi atau hubungi admin.')->withInput();
        }
    }

    public function edit(Product $product)
    {
        $categories = KategoriProduct::all();
        return view('pages.admin.kasir.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'name'                => 'required|string|max:255',
                'barcode'             => 'required|string|max:100|unique:products,barcode,' . $product->id,
                'description'         => 'nullable|string',
                'image'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'price'               => 'required|numeric|min:0',
                'hpp'                 => 'required|numeric|min:0',
                'discount'            => 'nullable|numeric|min:0',
                'discount_type'       => 'nullable|in:percent,nominal',
                'reorder'             => 'integer|min:0',
                'is_active'           => 'boolean',
                'kategori_product_id' => 'required|exists:kategori_products,id',
            ]);

            $data = $validated;

            if ($request->hasFile('image')) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $request->file('image')->store(tenant_storage_path('products'), 'public');
            }

            $product->update($data);

            return back()->with('success', 'Produk berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui produk', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('danger', 'Gagal memperbarui produk. Silakan coba lagi atau hubungi admin.')->withInput();
        }
    }

    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            /*
             * JURNAL: Produk dihapus — balik nilai persediaan yang masih ada
             * Debit  AST001 (Kas)                      → kas bertambah kembali
             * Kredit AST004 (Persediaan Barang Dagang) → aset berkurang
             *
             * Logika: produk dihapus = kita asumsikan persediaan dikembalikan / dibalik
             */
            if ($product->quantity > 0) {
                $this->jurnalHapusProduk(
                    $product,
                    $product->quantity,
                    'Produk dihapus (sisa stok ' . $product->quantity . '): ' . $product->name
                );
            }

            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus produk', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('danger', 'Gagal menghapus produk. Silakan coba lagi atau hubungi admin.');
        }
    }

    public function adjustQuantity(Request $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'type'        => 'required|in:in,out',
                'quantity'    => 'required|integer|min:1',
                'description' => 'nullable|string|max:255',
            ]);

            $change      = $validated['type'] === 'in' ? $validated['quantity'] : -$validated['quantity'];
            $newQuantity = $product->quantity + $change;

            if ($newQuantity < 0) {
                return back()->with('danger', 'Stok tidak boleh kurang dari 0.');
            }

            ProductQuantityLog::create([
                'product_id'       => $product->id,
                'type'             => $validated['type'],
                'quantity'         => $validated['quantity'],
                'current_quantity' => $newQuantity,
                'description'      => $validated['description'] ??
                    ($validated['type'] === 'in' ? 'Barang masuk' : 'Barang keluar'),
            ]);

            $product->update(['quantity' => $newQuantity]);

            if ($validated['type'] === 'in') {
                /*
                 * JURNAL: Tambah stok (barang masuk / restock)
                 * Debit  AST004 (Persediaan Barang Dagang) → aset bertambah
                 * Kredit AST001 (Kas)                      → kas berkurang
                 */
                $this->jurnalPembelianStok(
                    $product,
                    $validated['quantity'],
                    $validated['description'] ?? 'Restock barang masuk: ' . $product->name
                );
            } else {
                /*
                 * JURNAL: Kurangi stok manual (bukan dari penjualan — rusak/hilang/susut)
                 * Debit  BEB002 (Beban Kerugian Persediaan) → beban bertambah
                 * Kredit AST004 (Persediaan Barang Dagang)  → aset berkurang
                 *
                 * BUKAN BEB001 (HPP) karena HPP hanya diakui saat terjadi penjualan ke customer
                 */
                $this->jurnalKerugianPersediaan(
                    $product,
                    $validated['quantity'],
                    $validated['description'] ?? 'Penyesuaian stok keluar: ' . $product->name
                );
            }

            DB::commit();
            return back()->with('success', 'Stok produk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui stok produk', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('danger', 'Gagal memperbarui stok. Silakan coba lagi atau hubungi admin.');
        }
    }

    /**
     * Download template Excel untuk import produk massal. Foto produk
     * sengaja tidak termasuk kolom — tetap diupload manual per-produk lewat form edit.
     */
    public function downloadImportTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Produk');

        $headers = [
            'A1' => 'Nama Produk*',
            'B1' => 'Barcode*',
            'C1' => 'Kategori* (pilih dari dropdown)',
            'D1' => 'Deskripsi',
            'E1' => 'Harga Jual*',
            'F1' => 'HPP*',
            'G1' => 'Diskon',
            'H1' => 'Tipe Diskon (percent/nominal)',
            'I1' => 'Stok Awal',
            'J1' => 'Reorder Point',
            'K1' => 'Aktif (Ya/Tidak)',
        ];
        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('A1:K1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E1F2');

        // Catatan di header Kategori — muncul saat kursor diarahkan ke sel ini di Excel.
        $kategoriNote = $sheet->getComment('C1');
        $kategoriNote->getText()->createTextRun(
            'Wajib sama persis dengan salah satu nama di sheet "Daftar Kategori". '
            . 'Klik sel di kolom ini lalu pilih dari dropdown yang muncul — '
            . 'kalau ketik manual nama yang tidak ada di daftar, Excel akan menolaknya.'
        );
        $kategoriNote->setWidth('220pt');
        $kategoriNote->setHeight('110pt');

        // Kolom Barcode DIPAKSA format Text — kalau dibiarkan format Number/General,
        // Excel akan menampilkan barcode yang panjang jadi notasi ilmiah (mis. 8.99E+12)
        // dan angka nol di depan bisa hilang. Diterapkan ke banyak baris ke bawah
        // supaya baris baru yang diisi user juga ikut format Text ini.
        $sheet->getStyle('B2:B1000')->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        $categories = KategoriProduct::orderBy('name')->pluck('name')->values();

        $sheet->fromArray([
            'Contoh Protein Bar', null, $categories->first() ?? 'Minuman',
            'Contoh deskripsi produk (opsional)', 15000, 10000, 0, 'nominal', 10, 5, 'Ya',
        ], null, 'A2');
        $sheet->setCellValueExplicit('B2', '8991234567890', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Sheet kedua: daftar kategori yang valid, biar admin tidak salah ketik nama kategori
        $catSheet = $spreadsheet->createSheet();
        $catSheet->setTitle('Daftar Kategori');
        $catSheet->setCellValue('A1', 'Kategori yang tersedia di sistem');
        $catSheet->getStyle('A1')->getFont()->setBold(true);
        foreach ($categories as $i => $name) {
            $catSheet->setCellValue('A' . ($i + 2), $name);
        }
        $catSheet->getColumnDimension('A')->setAutoSize(true);

        // Dropdown di kolom Kategori (Template Produk) — daftarnya diambil langsung
        // dari sheet "Daftar Kategori", jadi user tinggal pilih, tidak perlu ketik manual.
        if ($categories->isNotEmpty()) {
            $lastRow = $categories->count() + 1;
            $validation = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setPromptTitle('Pilih Kategori');
            $validation->setPrompt('Pilih salah satu kategori dari daftar. Lihat sheet "Daftar Kategori" untuk daftar lengkapnya.');
            $validation->setErrorTitle('Kategori tidak valid');
            $validation->setError('Kategori harus dipilih dari daftar yang tersedia, tidak boleh ketik manual.');
            $validation->setFormula1("'Daftar Kategori'!\$A\$2:\$A\${$lastRow}");

            $sheet->setDataValidation('C2:C1000', $validation);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'Template_Import_Produk.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import produk massal dari file Excel (.xlsx) hasil template di atas.
     *
     * - Barcode yang sudah ada di-update (termasuk penyesuaian stok + jurnal keuangan).
     * - Barcode baru dibuat sebagai produk baru (stok awal + jurnal keuangan, sama seperti store()).
     * - Baris dengan data wajib kosong atau kategori tidak ditemukan di-skip & dilaporkan sebagai error.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $reader = new XlsxReader();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($request->file('file')->getRealPath());
            $rows = $spreadsheet->getSheet(0)->toArray(null, true, true, false);
        } catch (\Exception $e) {
            Log::error('Gagal membaca file import produk', ['error' => $e->getMessage()]);
            return back()->with('danger', 'Gagal membaca file. Pastikan formatnya .xlsx sesuai template yang disediakan.');
        }

        array_shift($rows); // buang baris header

        $kategoriMap = KategoriProduct::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [mb_strtolower(trim($name)) => $id]);

        $created = 0;
        $updated = 0;
        $errors  = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +1 krn array_shift, +1 krn baris excel mulai dari 1

            [$name, $barcode, $kategoriNama, $description, $price, $hpp, $discount, $discountType, $quantity, $reorder, $aktif] =
                array_pad(array_values($row), 11, null);

            $name         = trim((string) $name);
            $kategoriNama = trim((string) $kategoriNama);

            // Kalau barcode kebaca sebagai angka (kolomnya sempat berformat Number di
            // file user), hindari notasi ilmiah (mis. 8.99123E+12) dengan format
            // fixed-point dulu sebelum di-cast ke string.
            $barcode = is_numeric($barcode) ? sprintf('%.0f', $barcode) : trim((string) $barcode);

            if ($name === '' && $barcode === '') {
                continue; // baris kosong
            }

            if ($name === '' || $barcode === '' || $kategoriNama === '' || $price === null || $price === '' || $hpp === null || $hpp === '') {
                $errors[] = "Baris {$rowNum}: Nama, Barcode, Kategori, Harga Jual, dan HPP wajib diisi.";
                continue;
            }

            $kategoriId = $kategoriMap[mb_strtolower($kategoriNama)] ?? null;
            if (!$kategoriId) {
                $errors[] = "Baris {$rowNum}: Kategori '{$kategoriNama}' tidak ditemukan di sistem.";
                continue;
            }

            $discountType = $discountType !== null && trim((string) $discountType) !== ''
                ? strtolower(trim((string) $discountType))
                : null;
            if ($discountType && !in_array($discountType, ['percent', 'nominal'], true)) {
                $errors[] = "Baris {$rowNum}: Tipe Diskon harus 'percent' atau 'nominal'.";
                continue;
            }

            $quantity = ($quantity !== null && $quantity !== '') ? max(0, (int) $quantity) : 0;
            $reorder  = ($reorder !== null && $reorder !== '') ? max(0, (int) $reorder) : 0;
            $isActive = $this->parseAktifCell($aktif);

            DB::beginTransaction();
            try {
                $product = Product::where('barcode', $barcode)->first();

                $data = [
                    'name'                => $name,
                    'barcode'             => $barcode,
                    'description'         => $description !== null && trim((string) $description) !== '' ? trim((string) $description) : null,
                    'price'               => (float) $price,
                    'hpp'                 => (float) $hpp,
                    'discount'            => $discount !== null && $discount !== '' ? (float) $discount : null,
                    'discount_type'       => $discountType,
                    'reorder'             => $reorder,
                    'is_active'           => $isActive,
                    'kategori_product_id' => $kategoriId,
                ];

                if ($product) {
                    $product->update($data);

                    $delta = $quantity - $product->quantity;
                    if ($delta !== 0) {
                        $type   = $delta > 0 ? 'in' : 'out';
                        $qtyAbs = abs($delta);

                        ProductQuantityLog::create([
                            'product_id'       => $product->id,
                            'type'             => $type,
                            'quantity'         => $qtyAbs,
                            'current_quantity' => $quantity,
                            'description'      => 'Penyesuaian stok dari import Excel',
                        ]);

                        $product->update(['quantity' => $quantity]);

                        if ($type === 'in') {
                            $this->jurnalPembelianStok($product, $qtyAbs, 'Penyesuaian stok (import): ' . $product->name);
                        } else {
                            $this->jurnalKerugianPersediaan($product, $qtyAbs, 'Penyesuaian stok (import): ' . $product->name);
                        }
                    }

                    $updated++;
                } else {
                    $data['quantity'] = $quantity;
                    $product = Product::create($data);

                    if ($product->quantity > 0) {
                        ProductQuantityLog::create([
                            'product_id'       => $product->id,
                            'type'             => 'in',
                            'quantity'         => $product->quantity,
                            'current_quantity' => $product->quantity,
                            'description'      => 'Stok awal produk (import Excel)',
                        ]);

                        $this->jurnalPembelianStok($product, $product->quantity, 'Pembelian stok awal (import): ' . $product->name);
                    }

                    $created++;
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Gagal menyimpan baris import produk', [
                    'row'   => $rowNum,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $errors[] = "Baris {$rowNum}: Gagal disimpan, periksa kembali datanya.";
            }
        }

        $message = "Import selesai: {$created} produk baru, {$updated} produk diperbarui.";
        if ($errors) {
            $message .= ' ' . count($errors) . ' baris gagal diproses.';
        }

        return back()
            ->with($errors ? 'warning' : 'success', $message)
            ->with('import_errors', $errors);
    }

    private function parseAktifCell($value): bool
    {
        if ($value === null || trim((string) $value) === '') {
            return true; // default aktif kalau kosong
        }

        return in_array(strtolower(trim((string) $value)), ['ya', 'yes', '1', 'true', 'aktif'], true);
    }

    public function logs($product)
    {
        $products = Product::findOrFail($product);
        $logs     = $products->quantityLogs()->latest()->get();
        return view('pages.admin.kasir.products.logs', compact('products', 'logs'));
    }

    public function datatableLogs(Request $request, $product)
    {
        $productModel = Product::findOrFail($product);
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = ProductQuantityLog::where('product_id', $productModel->id)->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage, $productModel) {
                return [
                    'no'               => (($page - 1) * $perPage) + $index + 1,
                    'product_name'     => $productModel->name,
                    'type'             => $item->type,
                    'quantity'         => $item->quantity,
                    'current_quantity' => $item->current_quantity,
                    'description'      => $item->description ?? '-',
                    'created_at'       => to_tenant_tz($item->created_at)->format('d M Y, H:i'),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    // =========================================================
    // JURNAL HELPERS
    // =========================================================

    /**
     * Pembelian stok / barang masuk / stok awal
     *
     * Debit  : AST004 (Persediaan Barang Dagang) → aset naik
     * Kredit : AST001 (Kas)                      → kas turun
     */
    protected function jurnalPembelianStok(Product $product, int $qty, string $deskripsi): void
    {
        $persediaan = AkunKeuangan::where('kode', 'AST004')->first();
        $kas        = AkunKeuangan::where('kode', 'AST001')->first();

        if (!$persediaan || !$kas) {
            Log::warning('jurnalPembelianStok: AST004 atau AST001 tidak ditemukan');
            return;
        }

        $nilai   = $product->hpp * $qty;
        $tanggal = tenant_today_date();
        $ref     = ['referensi_id' => $product->id, 'referensi_tabel' => 'products'];

        TransaksiKeuangan::create(array_merge($ref, [
            'akun_id'   => $persediaan->id,
            'deskripsi' => $deskripsi,
            'debit'     => $nilai,
            'kredit'    => 0,
            'tanggal'   => $tanggal,
        ]));

        TransaksiKeuangan::create(array_merge($ref, [
            'akun_id'   => $kas->id,
            'deskripsi' => $deskripsi,
            'debit'     => 0,
            'kredit'    => $nilai,
            'tanggal'   => $tanggal,
        ]));
    }

    /**
     * Pengurangan stok manual (rusak / hilang / susut) — BUKAN dari penjualan
     *
     * Debit  : BEB002 (Beban Kerugian Persediaan) → beban naik
     * Kredit : AST004 (Persediaan Barang Dagang)  → aset turun
     */
    protected function jurnalKerugianPersediaan(Product $product, int $qty, string $deskripsi): void
    {
        $persediaan = AkunKeuangan::where('kode', 'AST004')->first();
        $beban      = AkunKeuangan::where('kode', 'BEB002')->first();

        if (!$persediaan || !$beban) {
            Log::warning('jurnalKerugianPersediaan: AST004 atau BEB002 tidak ditemukan');
            return;
        }

        $nilai   = $product->hpp * $qty;
        $tanggal = tenant_today_date();
        $ref     = ['referensi_id' => $product->id, 'referensi_tabel' => 'products'];

        TransaksiKeuangan::create(array_merge($ref, [
            'akun_id'   => $beban->id,
            'deskripsi' => $deskripsi,
            'debit'     => $nilai,
            'kredit'    => 0,
            'tanggal'   => $tanggal,
        ]));

        TransaksiKeuangan::create(array_merge($ref, [
            'akun_id'   => $persediaan->id,
            'deskripsi' => $deskripsi,
            'debit'     => 0,
            'kredit'    => $nilai,
            'tanggal'   => $tanggal,
        ]));
    }

    /**
     * Produk dihapus dari sistem — balik nilai persediaan
     *
     * Debit  : AST001 (Kas)                      → kas naik (balik)
     * Kredit : AST004 (Persediaan Barang Dagang) → aset turun
     */
    protected function jurnalHapusProduk(Product $product, int $qty, string $deskripsi): void
    {
        $persediaan = AkunKeuangan::where('kode', 'AST004')->first();
        $kas        = AkunKeuangan::where('kode', 'AST001')->first();

        if (!$persediaan || !$kas) {
            Log::warning('jurnalHapusProduk: AST004 atau AST001 tidak ditemukan');
            return;
        }

        $nilai   = $product->hpp * $qty;
        $tanggal = tenant_today_date();
        $ref     = ['referensi_id' => $product->id, 'referensi_tabel' => 'products'];

        TransaksiKeuangan::create(array_merge($ref, [
            'akun_id'   => $kas->id,
            'deskripsi' => $deskripsi,
            'debit'     => $nilai,
            'kredit'    => 0,
            'tanggal'   => $tanggal,
        ]));

        TransaksiKeuangan::create(array_merge($ref, [
            'akun_id'   => $persediaan->id,
            'deskripsi' => $deskripsi,
            'debit'     => 0,
            'kredit'    => $nilai,
            'tanggal'   => $tanggal,
        ]));
    }
}
