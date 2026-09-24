<?php

namespace Aeunius\FeriadosPeru;

use Aeunius\FeriadosPeru\Enums\TipoFeriado;
use Carbon\CarbonImmutable;

final readonly class Feriado
{
    public function __construct(
        public CarbonImmutable $fecha,
        public string $nombre,
        public TipoFeriado $tipo = TipoFeriado::Nacional,
        private bool $movil = false,
    ) {}

    /** Si la fecha cambia cada año (depende de la Pascua). */
    public function esMovil(): bool
    {
        return $this->movil;
    }
}
