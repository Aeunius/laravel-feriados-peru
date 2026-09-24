<?php

use Aeunius\FeriadosPeru\Enums\TipoFeriado;
use Aeunius\FeriadosPeru\Feriado;
use Aeunius\FeriadosPeru\Support\Calendario;

beforeEach(function () {
    $this->calendario = Calendario::peru();
});

it('trae los días no laborables declarados', function (string $fecha, string $norma) {
    $dia = $this->calendario->delAnio((int) substr($fecha, 0, 4), conNoLaborables: true)
        ->first(fn (Feriado $f) => $f->fecha->toDateString() === $fecha);

    expect($dia)
        ->tipo->toBe(TipoFeriado::NoLaborable)
        ->norma->toBe($norma)
        ->and($this->calendario->esNoLaborable($fecha))->toBeTrue();
})->with([
    ['2025-05-02', 'D.S. 042-2025-PCM'],
    ['2025-12-26', 'D.S. 042-2025-PCM'],
    ['2026-01-02', 'D.S. 042-2025-PCM'],
    ['2026-07-27', 'D.S. 075-2026-PCM'],
]);

it('no cuenta un día no laborable como feriado', function () {
    expect($this->calendario)
        ->esFeriado('2026-07-27')->toBeFalse()
        ->esNoLaborable('2026-07-28')->toBeFalse()
        ->and($this->calendario->delAnio(2026))->toHaveCount(16)
        ->and($this->calendario->delAnio(2026, conNoLaborables: true))->toHaveCount(18)
        ->and($this->calendario->proximo('2026-07-26')->fecha->toDateString())->toBe('2026-07-28');
});

it('corta los plazos con los días no laborables', function () {
    // Del viernes 24 de julio de 2026: 27 no laborable, 28 y 29 feriados.
    expect($this->calendario->esDiaHabil('2026-07-27'))->toBeFalse()
        ->and($this->calendario->sumarDiasHabiles('2026-07-24', 1)->toDateString())->toBe('2026-07-30');
});

it('puede contar los días no laborables como hábiles', function () {
    $tributario = $this->calendario->conNoLaborablesInhabiles(false);

    expect($tributario->esDiaHabil('2026-07-27'))->toBeTrue()
        ->and($tributario->sumarDiasHabiles('2026-07-24', 1)->toDateString())->toBe('2026-07-27')
        ->and($tributario->esDiaHabil('2026-07-28'))->toBeFalse()
        // El calendario original no cambia.
        ->and($this->calendario->esDiaHabil('2026-07-27'))->toBeFalse()
        ->and($this->calendario->conNoLaborablesInhabiles())->toBe($this->calendario);
});

it('suma feriados extraordinarios propios', function () {
    $calendario = Calendario::peru(extraordinarios: [
        ['fecha' => '2026-09-24', 'nombre' => 'Feriado de prueba', 'tipo' => 'extraordinario', 'norma' => 'Ley 99999'],
    ]);

    expect($calendario->esFeriado('2026-09-24'))->toBeTrue()
        // Un feriado extraordinario es inhábil, cuenten o no los no laborables.
        ->and($calendario->esDiaHabil('2026-09-24'))->toBeFalse()
        ->and($calendario->conNoLaborablesInhabiles(false)->esDiaHabil('2026-09-24'))->toBeFalse()
        ->and($calendario->delAnio(2026))->toHaveCount(17)
        ->and($calendario->proximo('2026-09-23'))
        ->nombre->toBe('Feriado de prueba')
        ->tipo->toBe(TipoFeriado::Extraordinario)
        ->norma->toBe('Ley 99999');
});

it('da prioridad al feriado si coincide con un día no laborable', function () {
    $calendario = Calendario::peru(extraordinarios: [
        ['fecha' => '2026-07-28', 'nombre' => 'Día no laborable', 'tipo' => 'no_laborable'],
        ['fecha' => '2026-07-27', 'nombre' => 'Feriado extraordinario', 'tipo' => 'extraordinario'],
    ]);

    expect($calendario)
        ->esFeriado('2026-07-28')->toBeTrue()
        ->esNoLaborable('2026-07-28')->toBeFalse()
        ->esFeriado('2026-07-27')->toBeTrue()
        ->esNoLaborable('2026-07-27')->toBeFalse();
});

it('rechaza un extraordinario mal escrito', function (array $definicion, string $mensaje) {
    expect(fn () => Calendario::peru(extraordinarios: [$definicion]))
        ->toThrow(InvalidArgumentException::class, $mensaje);
})->with([
    'fecha inexistente' => [['fecha' => '2026-02-30', 'nombre' => 'X', 'tipo' => 'no_laborable'], '2026-02-30'],
    'otro formato' => [['fecha' => '24/09/2026', 'nombre' => 'X', 'tipo' => 'no_laborable'], 'AAAA-MM-DD'],
    'tipo desconocido' => [['fecha' => '2026-09-24', 'nombre' => 'X', 'tipo' => 'puente'], "'extraordinario', 'regional' o 'no_laborable'"],
    'nacional' => [['fecha' => '2026-09-24', 'nombre' => 'X', 'tipo' => 'nacional'], "'extraordinario', 'regional' o 'no_laborable'"],
]);
