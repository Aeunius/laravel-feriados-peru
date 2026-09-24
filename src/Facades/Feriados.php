<?php

namespace Aeunius\FeriadosPeru\Facades;

use Aeunius\FeriadosPeru\Support\Calendario;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool esFeriado(\DateTimeInterface|string $fecha)
 * @method static \Illuminate\Support\Collection<int, \Aeunius\FeriadosPeru\Feriado> delAnio(int $anio)
 * @method static \Aeunius\FeriadosPeru\Feriado|null proximo(\DateTimeInterface|string $fecha)
 *
 * @see Calendario
 */
class Feriados extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Calendario::class;
    }
}
