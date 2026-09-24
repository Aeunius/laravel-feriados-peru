<?php

use Aeunius\FeriadosPeru\Support\Calendario;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

beforeEach(function () {
    $this->calendario = Calendario::peru();
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

it('distingue los días hábiles', function (string $fecha, bool $esperado) {
    expect($this->calendario->esDiaHabil($fecha))->toBe($esperado);
})->with([
    'lunes común' => ['2026-07-20', true],
    'no laborable' => ['2026-07-27', false],
    'feriado en martes' => ['2026-07-28', false],
    'sábado' => ['2026-07-25', false],
    'domingo' => ['2026-07-26', false],
    'Viernes Santo' => ['2026-04-03', false],
]);

it('suma días hábiles desde el día hábil siguiente', function (string $desde, int $dias, string $esperado) {
    expect($this->calendario->sumarDiasHabiles($desde, $dias)->toDateString())->toBe($esperado);
})->with([
    'semana sin feriados' => ['2026-09-07', 5, '2026-09-14'],
    'de viernes a lunes' => ['2026-09-11', 1, '2026-09-14'],
    'desde un sábado' => ['2026-09-12', 1, '2026-09-14'],
    'salta Fiestas Patrias' => ['2026-07-27', 5, '2026-08-05'],
    'salta también Junín' => ['2026-07-27', 6, '2026-08-07'],
    'salta Semana Santa' => ['2026-04-01', 1, '2026-04-06'],
    'cruza el año' => ['2026-12-30', 3, '2027-01-05'],
    'cero días' => ['2026-07-28', 0, '2026-07-28'],
    'hacia atrás, salta el no laborable' => ['2026-08-05', -5, '2026-07-24'],
]);

it('cuenta los días hábiles entre dos fechas', function (string $desde, string $hasta, int $esperado) {
    expect($this->calendario->diasHabilesEntre($desde, $hasta))->toBe($esperado);
})->with([
    'julio 2026, sin el 1 ni el no laborable' => ['2026-07-01', '2026-07-31', 18],
    'la misma fecha' => ['2026-07-27', '2026-07-27', 0],
    'solo feriados y fin de semana' => ['2026-07-27', '2026-07-29', 0],
    'al revés, negativo' => ['2026-08-05', '2026-07-27', -5],
]);

it('cuenta como la inversa de sumar', function () {
    foreach (['2026-01-02', '2026-03-30', '2026-07-24', '2026-12-23'] as $desde) {
        foreach ([1, 7, 20, 250] as $dias) {
            $hasta = $this->calendario->sumarDiasHabiles($desde, $dias);

            expect($this->calendario->diasHabilesEntre($desde, $hasta))->toBe($dias, "{$desde} + {$dias}");
        }
    }
});

it('sabe si un plazo ya venció', function () {
    // 5 días hábiles desde el 27 de julio de 2026 terminan el 5 de agosto.
    expect($this->calendario)
        ->esVencido('2026-07-27', 5, hoy: '2026-08-04')->toBeFalse()
        ->esVencido('2026-07-27', 5, hoy: '2026-08-05')->toBeFalse()
        ->esVencido('2026-07-27', 5, hoy: '2026-08-06')->toBeTrue();
});

it('usa la fecha de hoy si no se indica', function () {
    CarbonImmutable::setTestNow('2026-08-06 09:00');

    expect($this->calendario->esVencido('2026-07-27', 5))->toBeTrue()
        ->and($this->calendario->esVencido('2026-07-27', 6))->toBeFalse();
});

it('permite cambiar el fin de semana', function () {
    $conSabados = Calendario::peru(finDeSemana: [CarbonInterface::SUNDAY]);

    expect($conSabados->esDiaHabil('2026-09-12'))->toBeTrue()
        ->and($conSabados->esDiaHabil('2026-09-13'))->toBeFalse()
        ->and($conSabados->sumarDiasHabiles('2026-09-11', 1)->toDateString())->toBe('2026-09-12');
});

it('rechaza un fin de semana inválido', function (array $finDeSemana, string $mensaje) {
    expect(fn () => Calendario::peru($finDeSemana))->toThrow(InvalidArgumentException::class, $mensaje);
})->with([
    'día inexistente' => [[7], 'no existe'],
    'toda la semana' => [[0, 1, 2, 3, 4, 5, 6], 'los 7 días'],
]);
