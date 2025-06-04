<?php

if (!function_exists('easter_date')) {
    /**
     * @see http://php.net/manual/en/function.easter-date.php
     *
     * Source from http://php.net/manual/en/function.easter-date.php#47249
     */
    function easter_date($year = null)
    {
        // G is the Golden Number-1
        // H is 23-Epact (modulo 30)
        // I is the number of days from 21 March to the Paschal full moon
        // J is the weekday for the Paschal full moon (0=Sunday, 1=Monday, etc.)
        // L is the number of days from 21 March to the Sunday on or before the Paschal full moon (a number between -6 and 28)

        if (null === $year) {
            $year = date('Y');
        }

        $G = $year % 19;
        $C = (int)($year / 100);
        $H = (int)($C - (int)($C / 4) - (int)((8*$C+13) / 25) + 19*$G + 15) % 30;
        $I = (int)$H - (int)($H / 28)*(1 - (int)($H / 28)*(int)(29 / ($H + 1))*((int)(21 - $G) / 11));
        $J = ($year + (int)($year/4) + $I + 2 - $C + (int)($C/4)) % 7;
        $L = $I - $J;
        $m = 3 + (int)(($L + 40) / 44);
        $d = $L + 28 - 31 * ((int)($m / 4));
        $y = $year;

        return mktime(0,0,0, $m, $d, $y);
    }
}
