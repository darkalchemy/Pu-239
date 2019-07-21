<?php

declare(strict_types = 1);

/**
 * NFO2PNG Online Script.
 *
 * @author  NewEraCracker
 * @license MIT
 *
 * With contributions by Crypt Xor
 *
 * Converted to class for use in Pu-239
 */

namespace Pu239;

/**
 * Class Nfo2Png.
 */
class Nfo2Png
{
    /**
     * @param string $nfo
     * @param int    $nfoName
     * @param string $bgColor
     * @param string $txtColor
     *
     * @return bool|string
     */
    public function nfo2png_ttf(string $nfo, int $nfoName, string $bgColor, string $txtColor)
    {
        if (!file_exists(NFO_DIR . $nfoName . '.png')) {
            $font = INCL_DIR . 'assets/linedraw.ttf';
            $font_height = 10;
            $font_width = 8;
            $line_spacing = 3;
            $nfo_line_height = $font_height + $line_spacing;
            $nfo_side_spacing = 5;

            if (strpos($nfo, "\xEF\xBB\xBF") === 0) {
                $nfo = substr($nfo, 3);
            }
            $nfo = mb_convert_encoding($nfo, 'UTF-8', 'auto');
            if (empty($nfo)) {
                return false;
            }
            $nfo = explode("\n", $nfo);
            $nfo = array_map('rtrim', $nfo);

            $xmax = 0;
            mb_internal_encoding('UTF-8');
            foreach ($nfo as $line) {
                if ($xmax < mb_strlen($line)) {
                    $xmax = mb_strlen($line);
                }
            }

            $xmax = ($nfo_side_spacing * 2) + ($font_width * $xmax);
            $ymax = ($nfo_side_spacing * 2) + ($nfo_line_height * count($nfo));

            if ($xmax * $ymax > 9000000) {
                return false;
            }

            $im = imagecreatetruecolor($xmax, $ymax);
            $bgColor = $this->parse_color($bgColor);
            if (!$bgColor) {
                imagedestroy($im);

                return false;
            }
            $bgColor = imagecolorallocate($im, $bgColor[0], $bgColor[1], $bgColor[2]);
            $txtColor = $this->parse_color($txtColor);
            if (!$txtColor) {
                imagedestroy($im);

                return false;
            }
            $txtColor = imagecolorallocate($im, $txtColor[0], $txtColor[1], $txtColor[2]);
            imagefilledrectangle($im, 0, 0, $xmax, $ymax, $bgColor);
            for ($y = 0, $ycnt = count($nfo), $drawy = ($nfo_side_spacing + $nfo_line_height); $y < $ycnt; $y++, $drawy += $nfo_line_height) {
                $drawx = $nfo_side_spacing;
                imagettftext($im, $font_height, 0, $drawx, $drawy, $txtColor, $font, $nfo[$y]);
            }
            ob_start();
            if (!imagepng($im)) {
                imagedestroy($im);
                ob_end_clean();

                return false;
            }
            $image = ob_get_clean();
            imagedestroy($im);

            $fileName = NFO_DIR . $nfoName . '.png';
            if (!file_put_contents($fileName, $image)) {
                return false;
            }
        }

        return $nfoName . '.png';
    }

    /**
     * @param string $hexStr
     *
     * @return array|bool
     */
    protected function parse_color(string $hexStr)
    {
        if (is_array($hexStr)) {
            return false;
        }

        $hexStr = preg_replace('/[^0-9A-Fa-f]/', '', $hexStr);
        $rgbArray = [];
        if (strlen($hexStr) === 6) {
            $colorVal = hexdec($hexStr);
            $rgbArray[] = 0xFF & ($colorVal >> 0x10);
            $rgbArray[] = 0xFF & ($colorVal >> 0x8);
            $rgbArray[] = 0xFF & $colorVal;
        } elseif (strlen($hexStr) === 3) {
            $rgbArray[] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray[] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray[] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        } else {
            return false;
        }

        return $rgbArray;
    }
}
