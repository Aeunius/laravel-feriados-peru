<?php

namespace Aeunius\FeriadosPeru\Facades;

use Aeunius\FeriadosPeru\Support\Calendario;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool esFeriado(\DateTimeInterface|string $fecha)
 * @method static \Illuminate\Support\Collection<int, \Aeunius\FeriadosPeru\Feriado> delAnio(int $anio)
 * @method static \Aeunius\FeriadosPeru\Feriado|null proximo(\DateTimeInterface|string $fecha)
 * @method static bool esDiaHabil(\DateTimeInterface|string $fecha)
 * @method static \Carbon\CarbonImmutable sumarDiasHabiles(\DateTimeInterface|string $fecha, int $dias)
 * @method static int diasHabilesEntre(\DateTimeInterface|string $desde, \DateTimeInterface|string $hasta)
 * @method static bool esVencido(\DateTimeInterface|string $fecha, int $dias, \DateTimeInterface|string|null $hoy = null)
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
