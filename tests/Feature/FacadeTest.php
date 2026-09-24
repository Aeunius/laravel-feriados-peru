<?php

use Aeunius\FeriadosPeru\Facades\Feriados;
use Aeunius\FeriadosPeru\Support\Calendario;
use Carbon\CarbonInterface;

it('consulta los feriados con el facade', function () {
    expect(Feriados::esFeriado('2026-07-28'))->toBeTrue()
        ->and(Feriados::delAnio(2026))->toHaveCount(16)
        ->and(Feriados::proximo('2026-12-24')?->nombre)->toBe('Navidad');
});

it('calcula días hábiles con el facade', function () {
    expect(Feriados::esDiaHabil('2026-07-28'))->toBeFalse()
        ->and(Feriados::sumarDiasHabiles('2026-07-27', 5)->toDateString())->toBe('2026-08-05')
        ->and(Feriados::diasHabilesEntre('2026-07-27', '2026-08-05'))->toBe(5)
        ->and(Feriados::esVencido('2026-07-27', 5, hoy: '2026-08-06'))->toBeTrue();
});

it('toma el fin de semana de la configuración', function () {
    config(['feriados-peru.fin_de_semana' => [CarbonInterface::SUNDAY]]);
    app()->forgetInstance(Calendario::class);
    Feriados::clearResolvedInstances();

    expect(Feriados::esDiaHabil('2026-09-12'))->toBeTrue();
});

it('toma los extraordinarios y los no laborables de la configuración', function () {
    config([
        'feriados-peru.extraordinarios' => [
            ['fecha' => '2026-09-24', 'nombre' => 'Feriado de prueba', 'tipo' => 'extraordinario'],
        ],
        'feriados-peru.no_laborables_inhabiles' => false,
    ]);
    app()->forgetInstance(Calendario::class);
    Feriados::clearResolvedInstances();

    expect(Feriados::esFeriado('2026-09-24'))->toBeTrue()
        ->and(Feriados::esNoLaborable('2026-07-27'))->toBeTrue()
        ->and(Feriados::esDiaHabil('2026-07-27'))->toBeTrue();
});
