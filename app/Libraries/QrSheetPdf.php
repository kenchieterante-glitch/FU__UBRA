<?php

namespace App\Libraries;

/**
 * Lays QR code pictures out on bond paper, N to a sheet, under the university
 * header - the same look as the key QR sheet (SecurityDeptController::keysPdf).
 * The browser draws the QR images; this only places them and builds the PDF.
 */
class QrSheetPdf
{
    public const PAPERS = [
        'letter' => ['size' => 'letter', 'w' => 215.9, 'h' => 279.4],
        'a4'     => ['size' => 'a4', 'w' => 210.0, 'h' => 297.0],
        'long'   => ['size' => [0, 0, 612, 936], 'w' => 215.9, 'h' => 330.2], // 8.5 x 13 in
    ];

    public const GRIDS = [6 => [2, 3], 8 => [2, 4], 12 => [3, 4], 15 => [3, 5], 20 => [4, 5], 24 => [4, 6], 30 => [5, 6], 35 => [5, 7], 48 => [6, 8]];

    /**
     * @param array<int, array{name:string, place:string, img:string}> $items
     * @return string raw PDF bytes
     */
    public static function render(array $items, string $paperKey, int $per, string $moduleName, string $countLabel): string
    {
        $paper = self::PAPERS[$paperKey];
        [$cols, $rows] = self::GRIDS[$per];

        $margin  = 10; // mm
        $headerH = 30; // mm reserved for the header on every sheet
        $cw = ($paper['w'] - $margin * 2) / $cols;
        $ch = ($paper['h'] - $margin * 2 - $headerH - 12) / $rows; // 12 mm safety so a sheet never spills onto a second page
        $qr = min($cw * 0.72, $ch * 0.58);
        $nameFont = max(7, min(11, $qr / 2.6));

        $logoPath = FCPATH . 'images/UBRA LOGO (no background).png';
        $logo = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
        $header = '<div class="doc-header">' . ($logo ? '<img src="' . $logo . '" class="doc-logo">' : '')
            . '<div class="doc-system-name">FOUNDATION UNIVERSITY — UBRA</div>'
            . '<div class="doc-module-name">' . esc($moduleName) . '</div>'
            . '<div class="meta">Generated: ' . esc(date('M j, Y g:i A')) . ' &middot; ' . count($items) . ' ' . esc($countLabel) . '</div></div>';

        $pages  = '';
        $chunks = array_chunk($items, $per);
        foreach ($chunks as $pi => $chunk) {
            $table = '<table class="grid">';
            for ($r = 0; $r < $rows; $r++) {
                $table .= '<tr>';
                for ($c = 0; $c < $cols; $c++) {
                    $it = $chunk[$r * $cols + $c] ?? null;
                    $table .= '<td class="cell">' . ($it
                        ? '<img src="' . $it['img'] . '" class="qr"><div class="n">' . esc($it['name']) . '</div>' . ($it['place'] !== '' ? '<div class="p">' . esc($it['place']) . '</div>' : '')
                        : '') . '</td>';
                }
                $table .= '</tr>';
            }
            $pages .= '<div class="sheet"' . ($pi < count($chunks) - 1 ? ' style="page-break-after:always"' : '') . '>' . $header . $table . '</div>';
        }

        $html = '<html><head><style>
            @page { margin: ' . $margin . 'mm; }
            body { font-family: Helvetica, Arial, sans-serif; color: #222; margin: 0; }
            .doc-header { text-align: center; height: ' . $headerH . 'mm; }
            .doc-logo { display: block; margin: 0 auto 4px; height: 38px; }
            .doc-system-name { font-size: 15px; font-weight: bold; letter-spacing: .04em; color: #800000; }
            .doc-module-name { font-size: 13px; font-weight: bold; margin-top: 2px; }
            .meta { color: #666; font-size: 11px; margin-top: 4px; }
            table.grid { border-collapse: collapse; width: 100%; table-layout: fixed; }
            td.cell { width: ' . $cw . 'mm; height: ' . ($ch - 2) . 'mm; border: 0.2mm dashed #999; text-align: center; vertical-align: middle; padding: 1mm; overflow: hidden; }
            img.qr { width: ' . $qr . 'mm; height: ' . $qr . 'mm; }
            .n { font-weight: bold; font-size: ' . $nameFont . 'px; margin-top: 1mm; }
            .p { font-size: ' . max(5, $nameFont * 0.75) . 'px; color: #555; margin-top: 0.5mm; }
        </style></head><body>' . $pages . '</body></html>';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper['size'], 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
