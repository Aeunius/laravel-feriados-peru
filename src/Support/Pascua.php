<?php

namespace Aeunius\FeriadosPeru\Support;

use Carbon\CarbonImmutable;

final class Pascua
{
    /**
     * Domingo de Pascua de un año del calendario gregoriano.
     *
     * Algoritmo de Butcher (Meeus/Jones/Butcher): solo aritmética entera, válido
     * para cualquier año desde 1583.
     */
    public static function domingo(int $anio): CarbonImmutable
    {
        if ($anio < 1583) {
            throw new \InvalidArgumentException("El año {$anio} es anterior al calendario gregoriano (1583).");
        }

        $a = $anio % 19;
        $b = intdiv($anio, 100);
        $c = $anio % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);

        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;

        return CarbonImmutable::createStrict($anio, $mes, $dia);
    }
}
