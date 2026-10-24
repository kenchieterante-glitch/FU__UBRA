<?php

namespace App\Libraries;

/**
 * Code 128 (set B) barcode drawn as a PNG with GD — the same encoding as public/Assets/js/code128.js,
 * readable by any ordinary wired barcode scanner.
 */
class Code128
{
    private const PATTERNS = ['212222','222122','222221','121223','121322','131222','122213','122312','132212','221213','221312','231212','112232','122132','122231','113222','123122','123221','223211','221132','221231','213212','223112','312131','311222','321122','321221','312212','322112','322211','212123','212321','232121','111323','131123','131321','112313','132113','132311','211313','231113','231311','112133','112331','132131','113123','113321','133121','313121','211331','231131','213113','213311','213131','311123','311321','331121','312113','312311','332111','314111','221411','431111','111224','111422','121124','121421','141122','141221','112214','112412','122114','122411','142112','142211','241211','221114','413111','241112','134111','111242','121142','121241','114212','124112','124211','411212','421112','421211','212141','214121','412121','111143','111341','131141','114113','114311','411113','411311','113141','114131','311141','411131','211412','211214','211232','2331112'];

    /** PNG bytes of the barcode for $text. */
    public static function png(string $text, int $module = 3, int $height = 110): string
    {
        $codes = [104]; // start B
        $sum = 104;
        foreach (str_split($text) as $i => $ch) {
            $v = ord($ch) - 32;
            if ($v < 0 || $v > 94) $v = 0;
            $codes[] = $v;
            $sum += $v * ($i + 1);
        }
        $codes[] = $sum % 103; // check digit
        $codes[] = 106;        // stop

        $quiet = 10 * $module;
        $width = $quiet * 2;
        foreach ($codes as $c) $width += array_sum(str_split(self::PATTERNS[$c])) * $module;

        $im = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefill($im, 0, 0, $white);

        $x = $quiet;
        foreach ($codes as $c) {
            foreach (str_split(self::PATTERNS[$c]) as $i => $w) {
                $w = (int) $w * $module;
                if ($i % 2 === 0) imagefilledrectangle($im, $x, 0, $x + $w - 1, $height - 1, $black);
                $x += $w;
            }
        }

        ob_start();
        imagepng($im);
        $png = (string) ob_get_clean();
        imagedestroy($im);

        return $png;
    }

    public static function dataUri(string $text, int $module = 3, int $height = 110): string
    {
        return 'data:image/png;base64,' . base64_encode(self::png($text, $module, $height));
    }
}
