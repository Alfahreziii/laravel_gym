<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Html as HtmlReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Trait untuk export laporan ke Excel (.xlsx asli / OpenXML).
 *
 * Setiap controller membangun satu blok HTML (tepat satu <table>, judul/
 * subjudul/summary sebagai baris ber-colspan di dalamnya). HTML itu diparse
 * oleh PhpSpreadsheet\Reader\Html lalu ditulis ulang sebagai .xlsx asli via
 * PhpSpreadsheet\Writer\Xlsx — jadi klien Excel tidak lagi mendapat warning
 * "file format and extension don't match" dan copy-paste berjalan normal.
 *
 * Keterbatasan yang diterima: reader HTML hanya menerapkan sebagian styling,
 * mso-number-format tidak ikut terbawa (angka polos jadi number, string
 * seperti "Rp 200.000" tetap teks apa adanya). colspan pada baris judul/
 * summary tetap menjadi merged cell.
 */
trait ExportsExcel
{
    /**
     * Bungkus $bodyHtml (isi <table> dst) menjadi response download .xlsx asli.
     */
    protected function excelDownload(string $bodyHtml, string $title, string $filename): StreamedResponse|RedirectResponse
    {
        // Normalisasi ekstensi: apa pun yang masuk (.xls/.xlsx/tanpa ekstensi), paksa jadi .xlsx
        $filename = preg_replace('/\.xlsx?$/i', '', $filename) . '.xlsx';

        $html = '<html><head><meta charset="UTF-8"><title>' . $this->exEsc($title) . '</title>'
            . '<style>' . $this->excelStyles() . '</style></head><body>' . $bodyHtml . '</body></html>';

        try {
            $reader = new HtmlReader();

            if (method_exists($reader, 'loadFromString')) {
                $spreadsheet = $reader->loadFromString($html);
            } else {
                $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_html_');
                file_put_contents($tmpFile, $html);
                $spreadsheet = $reader->load($tmpFile);
                unlink($tmpFile);
            }

            $this->applyReportStyling($spreadsheet);

            $writer = new Xlsx($spreadsheet);
        } catch (\Throwable $e) {
            Log::error('Gagal membuat file Excel (xlsx)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('danger', 'Gagal membuat file Excel. Silakan coba lagi atau hubungi admin.');
        }

        return response()->streamDownload(function () use ($writer, $spreadsheet) {
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Terapkan styling langsung ke sel via kode PhpSpreadsheet, karena
     * Reader\Html membuang CSS dari excelStyles() saat parsing. Meniru
     * tampilan HTML lama: header gelap #2C3E50/putih bold, border tipis
     * #CCCCCC, zebra #F2F2F2 pada baris data, dan semua kolom auto-lebar.
     *
     * Batasan yang diterima: styling per-kelas lama (.summary-val hijau,
     * .title 14pt, .grand-row gelap, dst.) tidak direproduksi persis karena
     * info class CSS hilang saat parsing HTML — kalau butuh fidelity penuh,
     * itu perlu penulisan sel per-controller (di luar scope perbaikan ini).
     */
    protected function applyReportStyling(Spreadsheet $spreadsheet): void
    {
        $sheet           = $spreadsheet->getActiveSheet();
        $highestRow      = $sheet->getHighestRow();
        $highestCol      = $sheet->getHighestColumn();
        $highestColIndex = Coordinate::columnIndexFromString($highestCol);

        // 1) Auto-lebar semua kolom — perbaikan paling terlihat, cegah teks kepotong
        for ($colIndex = 1; $colIndex <= $highestColIndex; $colIndex++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colIndex))->setAutoSize(true);
        }

        // 2) Deteksi baris header: baris pertama yang SEMUA selnya (kolom 1..N) terisi.
        // Baris judul/subjudul/summary tidak akan "penuh" (banyak sel kosong / ter-merge).
        $headerRow = null;
        for ($row = 1; $row <= $highestRow; $row++) {
            $isFullRow = true;
            for ($colIndex = 1; $colIndex <= $highestColIndex; $colIndex++) {
                $value = $sheet->getCell([$colIndex, $row])->getValue();
                if ($value === null || $value === '') {
                    $isFullRow = false;
                    break;
                }
            }
            if ($isFullRow) {
                $headerRow = $row;
                break;
            }
        }

        $thinBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'CCCCCC'],
                ],
            ],
        ];

        if ($headerRow === null) {
            // Pengaman: baris header tak terdeteksi (report tak biasa). Jangan error,
            // cukup auto-size (sudah dilakukan di atas) + border seluruh range terpakai.
            $sheet->getStyle("A1:{$highestCol}{$highestRow}")->applyFromArray($thinBorder);
            return;
        }

        // 2a) Style baris header
        $sheet->getStyle("A{$headerRow}:{$highestCol}{$headerRow}")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 2b) Border tipis untuk seluruh range data (header s.d. baris terakhir)
        $sheet->getStyle("A{$headerRow}:{$highestCol}{$highestRow}")->applyFromArray($thinBorder);

        // 2c) Zebra pada baris data + vertical align middle
        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $rowRange = "A{$row}:{$highestCol}{$row}";
            $sheet->getStyle($rowRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $dataIndex = $row - $headerRow;
            if ($dataIndex % 2 === 0) {
                $sheet->getStyle($rowRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F2F2F2');
            }
        }
    }

    /**
     * CSS umum untuk semua laporan Excel (selaras dengan style kasir exportCsv).
     */
    protected function excelStyles(): string
    {
        return <<<CSS
            body { font-family: Arial, sans-serif; font-size: 10pt; }
            table { border-collapse: collapse; width: 100%; margin-bottom: 14px; }
            th {
                background-color: #2c3e50; color: #ffffff;
                border: 1px solid #999; padding: 6px 8px;
                text-align: center; font-size: 10pt; white-space: nowrap;
            }
            td { border: 1px solid #ccc; padding: 5px 8px; font-size: 10pt; vertical-align: middle; }
            tr:nth-child(even) td { background-color: #f2f2f2; }
            .num { text-align: right; mso-number-format:'\\#\\,\\#\\#0'; }
            .center { text-align: center; }
            .title { font-size: 14pt; font-weight: bold; }
            .subtitle { font-size: 9pt; color: #555; }
            .summary-label { font-weight: bold; background-color: #ecf0f1; }
            .summary-val { text-align: right; font-weight: bold; color: #27ae60; }
            .grand-row td { background-color: #2c3e50; color: #ffffff; font-weight: bold; }
            .grand-row .num { text-align: right; color: #ffffff; }
            .badge-ok { color: #155724; font-weight: bold; }
            .badge-warn { color: #856404; font-weight: bold; }
            .badge-danger { color: #721c24; font-weight: bold; }
            CSS;
    }

    /**
     * Format angka ala Indonesia (1.000.000), tanpa desimal.
     */
    protected function exNum(mixed $n): string
    {
        return number_format((float) $n, 0, ',', '.');
    }

    /**
     * HTML-escape singkat, fallback ke '-' kalau null/empty.
     */
    protected function exEsc(mixed $val): string
    {
        $val = $val === null || $val === '' ? '-' : $val;
        return htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
    }
}
