<?php

namespace App\Libraries;

use Dompdf\CanvasFactory;
use Dompdf\Dompdf;
use Dompdf\FontMetrics;

/**
 * Draws the Report Center record list straight onto a PDF page.
 *
 * The old export built an HTML table and let Dompdf lay it out, which cost about 40 ms per row and
 * grew worse the longer the list (982 records took 40-60 seconds). Drawing the rows directly is
 * the same layout without the HTML/CSS engine: the same list now takes a second or two.
 */
class RecordsPdf
{
    private const MAROON = [0.5, 0, 0];

    /**
     * @param array<int, array<string, mixed>> $activities rows from RecordsController::buildActivities()
     * @return string raw PDF bytes
     */
    public static function render(array $activities, string $moduleLabel, string $logoPath = ''): string
    {
        $dompdf  = new Dompdf();
        $canvas  = CanvasFactory::get_instance($dompdf, 'letter', 'portrait');
        $metrics = new FontMetrics($canvas, $dompdf->getOptions());
        $font    = $metrics->getFont('helvetica');
        $bold    = $metrics->getFont('helvetica', 'bold');

        $pageW = $canvas->get_width();
        $pageH = $canvas->get_height();
        $mx = 32; $top = 36; $bottom = 40;
        $cols = [ // label, width
            ['Date', 62], ['Category', 128], ['Record', 190], ['Performed by', 100], ['Status', 0],
        ];
        $cols[4][1] = $pageW - 2 * $mx - ($cols[0][1] + $cols[1][1] + $cols[2][1] + $cols[3][1]);
        $text = fn(float $x, float $y, string $s, $f, float $size, array $c = [0.13, 0.13, 0.13]) => $canvas->text($x, $y, $s, $f, $size, $c);
        $width = fn(string $s, $f, float $size): float => $metrics->getTextWidth($s, $f, $size);

        // Wrap text to a column width, at most $maxLines lines (last one gets an ellipsis).
        $wrap = function (string $s, $f, float $size, float $w, int $maxLines) use ($width): array {
            $words = preg_split('/\s+/u', trim($s)) ?: [];
            $lines = []; $cur = '';
            foreach ($words as $word) {
                $try = $cur === '' ? $word : $cur . ' ' . $word;
                if ($width($try, $f, $size) <= $w) { $cur = $try; continue; }
                if ($cur !== '') { $lines[] = $cur; }
                $cur = $word;
                while ($width($cur, $f, $size) > $w && mb_strlen($cur) > 1) { // one very long word
                    $cut = mb_strlen($cur);
                    while ($cut > 1 && $width(mb_substr($cur, 0, $cut), $f, $size) > $w) { $cut--; }
                    $lines[] = mb_substr($cur, 0, $cut);
                    $cur = mb_substr($cur, $cut);
                }
            }
            if ($cur !== '') { $lines[] = $cur; }
            if (count($lines) > $maxLines) {
                $lines = array_slice($lines, 0, $maxLines);
                $last = rtrim($lines[$maxLines - 1], '. ');
                while ($last !== '' && $width($last . '...', $f, $size) > $w) { $last = mb_substr($last, 0, -1); }
                $lines[$maxLines - 1] = $last . '...';
            }
            return $lines ?: [''];
        };

        $y = $top;
        $header = function () use ($canvas, $text, $width, $font, $bold, $pageW, $mx, $moduleLabel, $activities, $logoPath, &$y, $top) {
            $canvas->filled_rectangle(0, 0, $pageW, 10, self::MAROON);          // top band
            $y = $top;
            $tx = $mx;
            if ($logoPath !== '' && is_file($logoPath)) {
                $canvas->image($logoPath, $mx, $y, 50, 50);
                $tx = $mx + 62;
            }
            $text($tx, $y + 2, 'FOUNDATION UNIVERSITY', $bold, 9, [0.4, 0.4, 0.4]);
            $text($tx, $y + 14, 'UBRA Report Center', $bold, 18, self::MAROON);
            $text($tx, $y + 38, $moduleLabel, $font, 10.5);
            $right = 'Generated ' . date('M j, Y g:i A');
            $text($pageW - $mx - $width($right, $font, 8.5), $y + 4, $right, $font, 8.5, [0.4, 0.4, 0.4]);
            $count = count($activities) . ' record' . (count($activities) === 1 ? '' : 's');
            $text($pageW - $mx - $width($count, $bold, 11), $y + 22, $count, $bold, 11, self::MAROON);
            $y += 62;
            $canvas->line($mx, $y, $pageW - $mx, $y, self::MAROON, 1.2);
            $y += 12;
        };
        $tableHead = function () use ($canvas, $text, $bold, $cols, $mx, $pageW, &$y) {
            $canvas->filled_rectangle($mx, $y, $pageW - 2 * $mx, 20, self::MAROON);
            $x = $mx;
            foreach ($cols as [$label, $w]) { $text($x + 5, $y + 5, $label, $bold, 9, [1, 1, 1]); $x += $w; }
            $y += 20;
        };

        $header();
        $tableHead();

        if (!$activities) {
            $text($mx + 5, $y + 12, 'No matching records.', $font, 10, [0.4, 0.4, 0.4]);
        }

        $n = 0;
        foreach ($activities as $a) {
            $date = !empty($a['date']) ? date('M j, Y', strtotime((string) $a['date'])) : '-';
            $cat  = trim((string) ($a['category'] ?? $a['module'] ?? '')) . (!empty($a['type_label']) ? ' - ' . $a['type_label'] : '');
            $catL = $wrap($cat, $font, 9, $cols[1][1] - 10, 2);
            $recL = $wrap((string) ($a['record'] ?? ''), $font, 9, $cols[2][1] - 10, 2);
            $subL = $wrap((string) ($a['record_sub'] ?? ''), $font, 8, $cols[2][1] - 10, 1);
            $byL  = $wrap((string) ($a['performed_by'] ?? ''), $font, 9, $cols[3][1] - 10, 2);
            $stL  = $wrap((string) ($a['status'] ?? ''), $font, 9, $cols[4][1] - 10, 2);
            $subShown = trim((string) ($a['record_sub'] ?? '')) !== '';
            $lines = max(count($catL), count($recL) + ($subShown ? 1 : 0), count($byL), count($stL), 1);
            $h = $lines * 11 + 8;

            if ($y + $h > $pageH - $bottom) {
                $canvas->new_page();
                $y = $top;
                $tableHead();
            }
            if ($zebra = (++$n % 2 === 0)) { $canvas->filled_rectangle($mx, $y, $pageW - 2 * $mx, $h, [0.973, 0.953, 0.953]); }
            $x = $mx;
            $text($x + 5, $y + 4, $date, $font, 9); $x += $cols[0][1];
            foreach ($catL as $i => $l) { $text($x + 5, $y + 4 + $i * 11, $l, $font, 9); } $x += $cols[1][1];
            foreach ($recL as $i => $l) { $text($x + 5, $y + 4 + $i * 11, $l, $font, 9); }
            if ($subShown) { $text($x + 5, $y + 4 + count($recL) * 11, $subL[0], $font, 8, [0.5, 0.5, 0.5]); }
            $x += $cols[2][1];
            foreach ($byL as $i => $l) { $text($x + 5, $y + 4 + $i * 11, $l, $font, 9); } $x += $cols[3][1];
            $st = strtolower((string) ($a['status'] ?? ''));
            $stColor = match (true) {
                in_array($st, ['completed', 'done', 'resolved', 'returned', 'approved', 'active'], true) => [0.18, 0.49, 0.2],
                in_array($st, ['pending', 'in progress', 'borrowed', 'reviewed', 'in transit', 'overdue'], true) => [0.70, 0.42, 0.0],
                default => [0.13, 0.13, 0.13],
            };
            foreach ($stL as $i => $l) { $text($x + 5, $y + 4 + $i * 11, $l, $bold, 9, $stColor); }
            $y += $h;
            $canvas->line($mx, $y, $pageW - $mx, $y, [0.82, 0.82, 0.82], 0.5);
        }

        // Page numbers, drawn once the page count is known.
        $canvas->page_text($mx, $pageH - 26, 'FU UBRA - Unified Buildings Resources and Administration', $font, 8, [0.4, 0.4, 0.4]);
        $canvas->page_text($pageW - $mx - 60, $pageH - 26, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 8, [0.4, 0.4, 0.4]);

        return $canvas->output();
    }
}
