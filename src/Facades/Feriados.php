<?php

namespace Aeunius\FeriadosPeru\Facades;

use Aeunius\FeriadosPeru\Support\Calendario;
use Illuminate\Support\Facades\Facade;

/**
 * @see Calendario
 */
class Feriados extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Calendario::class;
    }
}
