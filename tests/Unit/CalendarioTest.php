<?php

use Aeunius\FeriadosPeru\Enums\TipoFeriado;
use Aeunius\FeriadosPeru\Feriado;
use Aeunius\FeriadosPeru\Support\Calendario;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->calendario = Calendario::peru();
});

it('lista los feriados del año en orden', function (int $anio, array $esperados) {
    $fechas = $this->calendario->delAnio($anio)
        ->map(fn (Feriado $feriado) => $feriado->fecha->toDateString())
        ->all();

    expect($fechas)->toBe($esperados);
})->with('feriados por año');

it('reconoce cada feriado del año', function (int $anio, array $esperados) {
    foreach ($esperados as $fecha) {
        expect($this->calendario->esFeriado($fecha))->toBeTrue($fecha);
    }
})->with('feriados por año');

it('no marca feriado un día común', function (string $fecha) {
    expect($this->calendario->esFeriado($fecha))->toBeFalse();
})->with([
    'día laborable' => '2026-07-27',
    'domingo sin feriado' => '2026-07-26',
    '7 de junio antes de la Ley 31788' => '2023-06-07',
    '23 de julio antes de la Ley 31822' => '2022-07-23',
    'Viernes Santo de otro año' => '2026-04-18',
]);

it('describe cada feriado', function () {
    $feriados = $this->calendario->delAnio(2026)->keyBy(fn (Feriado $f) => $f->fecha->toDateString());

    expect($feriados['2026-07-28'])
        ->nombre->toBe('Fiestas Patrias')
        ->tipo->toBe(TipoFeriado::Nacional)
        ->esMovil()->toBeFalse();

    expect($feriados['2026-04-03'])
        ->nombre->toBe('Viernes Santo')
        ->esMovil()->toBeTrue();
});

it('acepta fechas como texto o como objetos de fecha', function () {
    expect($this->calendario)
        ->esFeriado('2026-07-28')->toBeTrue()
        ->esFeriado('2026-07-28 23:59:59')->toBeTrue()
        ->esFeriado(new DateTimeImmutable('2026-07-28'))->toBeTrue()
        ->esFeriado(CarbonImmutable::create(2026, 7, 28, 18))->toBeTrue();
});

it('toma el día de la fecha en su propia zona horaria', function () {
    // El 27 a las 22:00 en Lima ya es 28 en UTC, pero en Lima sigue siendo 27.
    $lima = new DateTimeZone('America/Lima');

    expect($this->calendario->esFeriado(new DateTimeImmutable('2026-07-27 22:00', $lima)))->toBeFalse();
});

it('encuentra el próximo feriado', function (string $fecha, string $esperado, string $nombre) {
    $feriado = $this->calendario->proximo($fecha);

    expect($feriado->fecha->toDateString())->toBe($esperado)
        ->and($feriado->nombre)->toBe($nombre);
})->with([
    'antes de Navidad' => ['2026-12-24', '2026-12-25', 'Navidad'],
    'en un feriado, el siguiente' => ['2026-07-28', '2026-07-29', 'Fiestas Patrias'],
    'pasa al año siguiente' => ['2026-12-25', '2027-01-01', 'Año Nuevo'],
    'Semana Santa' => ['2026-03-15', '2026-04-02', 'Jueves Santo'],
]);

it('no repite un día si dos feriados coinciden', function () {
    $calendario = new Calendario(
        [['mes' => 4, 'dia' => 3, 'nombre' => 'Fijo']],
        [['dias' => -2, 'nombre' => 'Viernes Santo']],
    );

    expect($calendario->delAnio(2026))
        ->toHaveCount(1)
        ->first()->nombre->toBe('Fijo');
});

it('devuelve null si el catálogo está vacío', function () {
    expect((new Calendario([], []))->proximo('2026-01-01'))->toBeNull();
});
