<?php

namespace App\Service;

/**
 * @author Remy BETUS
 */
class ColorManipulation
{
    static function hslToRgb($h, $s = 1, $l = 0.5)
    {
        $c = $s*(1 - abs(2*$l - 1));

        $x = $c*(1 - abs(fmod($h/60, 2) - 1));

        $m = $l - ($c/2);

        $toColor = function($color) use ($m) {
            return intval(($color+$m)*255);
        };

        if ($h < 60) {
            return array_map($toColor, [$c, $x, 0]);
        }
        if ($h < 120) {
            return array_map($toColor, [$x, $c, 0]);
        }
        if ($h < 180) {
            return array_map($toColor, [0, $c, $x]);
        }
        if ($h < 240) {
            return array_map($toColor, [0, $x, $c]);
        }
        if ($h < 300) {
            return array_map($toColor, [$x, 0, $c]);
        }
        if ($h < 360) {
            return array_map($toColor, [$c, 0, $x]);
        }
    }
}
