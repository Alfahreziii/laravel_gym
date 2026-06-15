<?php

namespace App\Http\Controllers\Concerns;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Trait untuk export laporan ke Excel (.xls).
 *
 * Menggunakan format HTML table dengan namespace Office (sama seperti
 * yang sudah dipakai di KasirController::exportCsv), sehingga Excel
 * membuka file ini secara native lengkap dengan styling, tanpa perlu
 * dependency tambahan (Maatwebsite/Excel dll).
 */
trait ExportsExcel
{
    /**
     * Bungkus $bodyHtml (isi <table> dst) menjadi response download .xls
     */
    protected function excelDownload(string $bodyHtml, string $title, string $filename): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $styles = $this->excelStyles();

        $callback = function () use ($bodyHtml, $title, $styles) {
            echo chr(0xEF) . chr(0xBB) . chr(0xBF); // BOM UTF-8 agar karakter Indonesia tampil benar
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="UTF-8"><title>' . $this->exEsc($title) . '</title>';
            echo '<style>' . $styles . '</style></head><body>';
            echo $bodyHtml;
            echo '</body></html>';
        };

        return response()->stream($callback, 200, $headers);
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
    protected function exNum($n): string
    {
        return number_format((float) $n, 0, ',', '.');
    }

    /**
     * HTML-escape singkat, fallback ke '-' kalau null/empty.
     */
    protected function exEsc($val): string
    {
        $val = $val === null || $val === '' ? '-' : $val;
        return htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
    }
}
