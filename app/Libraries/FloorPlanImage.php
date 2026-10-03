<?php

namespace App\Libraries;

/**
 * Prepares an uploaded floor-plan picture for the Safety & Security page:
 * paints over the legend printed on it, then crops to the drawing so it sits centred.
 * The uploaded file is never changed; the result is cached under writable/cache/floorplans.
 */
class FloorPlanImage
{
    private const TOP_BAND = 0.055; // white strip (share of final width) where the page legend sits
    private const PAD      = 0.03;

    // Legend areas to paint over, as [x1, y1, x2, y2] in % of the picture. Matched by file-name prefix.
    private const LEGENDS = [
        'ADMINandARTS'                => [[0, 71.5, 31, 100]],
        'Agriculture Building'        => [[0, 74, 31, 100]],
        'Arts and Sciences'           => [[0, 28.5, 16, 71]],
        'Business and Administration' => [[0, 70.5, 31, 100]],
        'EDUC'                        => [[0, 71.5, 31, 100]],
        'IT Building'                 => [[0, 72, 15.5, 100], [15, 85, 30, 100]],
        'Kennel Caf'                  => [[0, 72, 31, 100]],
        'Law Building'                => [[0, 29, 15, 70]],
        'Main Building'               => [[0, 72.5, 31, 100]],
        'Main Library'                => [[0, 72, 31, 100]],
        'Museum'                      => [[0, 36.5, 18, 79.5]],
        'SSS'                         => [[0, 28, 16, 70]],
    ];

    // Logo + building-name stamp in the bottom-right corner (not part of the drawing).
    private const STAMPS = [[88, 85, 100, 100], [70, 87.5, 100, 100]];

    public static function path(string $file): string
    {
        $dir = WRITEPATH . 'cache/floorplans/';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $src = FCPATH . 'Files/Floor Plans/' . $file;
        $out = $dir . md5($file . '|v4|' . filemtime($src)) . '.png';
        if (!is_file($out)) self::build($src, $file, $out);
        return $out;
    }

    /** Where the cropped picture sits in the original (fractions), for converting stored marker positions. */
    public static function frame(string $file): array
    {
        $src = FCPATH . 'Files/Floor Plans/' . $file;
        [$im, $w, $h] = self::prepared($src, $file);
        $box = self::box($im, $w, $h);
        imagedestroy($im);
        return ['x' => $box[0] / $w, 'y' => $box[1] / $h, 'w' => ($box[2] - $box[0]) / $w, 'h' => ($box[3] - $box[1]) / $h, 'W' => $w, 'H' => $h, 'band' => self::TOP_BAND, 'pad' => self::PAD];
    }

    private static function prepared(string $src, string $file): array
    {
        $im = imagecreatefrompng($src);
        $w = imagesx($im);
        $h = imagesy($im);
        $white = imagecolorallocate($im, 255, 255, 255);
        $rects = [];
        foreach (self::LEGENDS as $prefix => $r) {
            if (str_starts_with($file, $prefix)) { $rects = $r; break; }
        }
        $stamps = self::STAMPS;
        if (str_starts_with($file, 'Business')) $stamps[] = [63, 87.5, 100, 100]; // longer building name
        foreach (array_merge($rects, $stamps) as [$x1, $y1, $x2, $y2]) {
            imagefilledrectangle($im, (int) ($w * $x1 / 100), (int) ($h * $y1 / 100), (int) ceil($w * $x2 / 100), (int) ceil($h * $y2 / 100), $white);
        }
        return [$im, $w, $h];
    }

    private static function box($im, int $w, int $h): array
    {
        $x1 = $w; $y1 = $h; $x2 = 0; $y2 = 0;
        for ($y = 0; $y < $h; $y += 2) {
            for ($x = 0; $x < $w; $x += 2) {
                $c = imagecolorat($im, $x, $y);
                if ((($c >> 16) & 255) < 235 || (($c >> 8) & 255) < 235 || ($c & 255) < 235) {
                    if ($x < $x1) $x1 = $x;
                    if ($x > $x2) $x2 = $x;
                    if ($y < $y1) $y1 = $y;
                    if ($y > $y2) $y2 = $y;
                }
            }
        }
        if ($x2 <= $x1 || $y2 <= $y1) return [0, 0, $w, $h];
        return [$x1, $y1, min($w, $x2 + 2), min($h, $y2 + 2)];
    }

    private static function build(string $src, string $file, string $out): void
    {
        [$im, $w, $h] = self::prepared($src, $file);
        [$x1, $y1, $x2, $y2] = self::box($im, $w, $h);
        $cw = $x2 - $x1;
        $ch = $y2 - $y1;
        $pad = (int) round($cw * self::PAD);
        $band = (int) round(($cw + 2 * $pad) * self::TOP_BAND);
        $dst = imagecreatetruecolor($cw + 2 * $pad, $ch + 2 * $pad + $band);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopy($dst, $im, $pad, $pad + $band, $x1, $y1, $cw, $ch);
        imagepng($dst, $out, 6);
        imagedestroy($im);
        imagedestroy($dst);
    }
}
