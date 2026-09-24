<?php

use Aeunius\FeriadosPeru\Facades\Feriados;

it('consulta los feriados con el facade', function () {
    expect(Feriados::esFeriado('2026-07-28'))->toBeTrue()
        ->and(Feriados::delAnio(2026))->toHaveCount(16)
        ->and(Feriados::proximo('2026-12-24')?->nombre)->toBe('Navidad');
});
