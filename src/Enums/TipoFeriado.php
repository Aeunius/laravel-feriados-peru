<?php

namespace Aeunius\FeriadosPeru\Enums;

enum TipoFeriado: string
{
    /** Feriado nacional por ley: inhábil en los sectores público y privado. */
    case Nacional = 'nacional';

    /** Feriado declarado para una sola fecha: inhábil como uno nacional. */
    case Extraordinario = 'extraordinario';

    /**
     * Día no laborable compensable del sector público, declarado por decreto
     * supremo. No es feriado: el sector privado trabaja salvo acuerdo, y los
     * decretos lo declaran hábil para efectos tributarios.
     */
    case NoLaborable = 'no_laborable';

    public function esFeriado(): bool
    {
        return $this !== self::NoLaborable;
    }
}
