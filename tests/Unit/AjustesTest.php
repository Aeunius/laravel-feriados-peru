<?php

use Aeunius\FeriadosPeru\Enums\TipoFeriado;
use Aeunius\FeriadosPeru\Feriado;
use Aeunius\FeriadosPeru\Support\Calendario;

it('identifica cada feriado del catálogo con una clave', function () {
    $claves = Calendario::peru()->delAnio(2026)->map(fn (Feriado $f) => $f->clave)->all();

    expect($claves)->toBe([
        'anio_nuevo', 'jueves_santo', 'viernes_santo', 'dia_del_trabajo',
        'batalla_de_arica', 'san_pedro_y_san_pablo', 'fuerza_aerea',
        'fiestas_patrias_28', 'fiestas_patrias_29', 'batalla_de_junin',
        'santa_rosa_de_lima', 'combate_de_angamos', 'todos_los_santos',
        'inmaculada_concepcion', 'batalla_de_ayacucho', 'navidad',
    ]);
});

it('omite feriados del catálogo por su clave', function () {
    $calendario = Calendario::peru(omitir: ['fuerza_aerea', 'jueves_santo']);

    expect($calendario)
        ->esFeriado('2026-07-23')->toBeFalse()
        ->esFeriado('2027-07-23')->toBeFalse()
        ->esFeriado('2026-04-02')->toBeFalse()
        ->esFeriado('2026-04-03')->toBeTrue()
        ->esDiaHabil('2026-07-23')->toBeTrue()
        ->and($calendario->delAnio(2026))->toHaveCount(14);
});

it('omite un día puntual por su fecha', function () {
    $calendario = Calendario::peru(omitir: ['2026-07-27']);

    expect($calendario)
        ->esNoLaborable('2026-07-27')->toBeFalse()
        ->esDiaHabil('2026-07-27')->toBeTrue()
        ->esNoLaborable('2025-12-26')->toBeTrue();
});

it('agrega feriados regionales que se repiten cada año', function () {
    $calendario = Calendario::peru(regionales: [
        ['mes' => 9, 'dia' => 24, 'nombre' => 'Virgen de las Mercedes'],
        ['mes' => 1, 'dia' => 18, 'nombre' => 'Aniversario de Lima', 'desde' => 2027],
    ]);

    expect($calendario)
        ->esFeriado('2026-09-24')->toBeTrue()
        ->esFeriado('2031-09-24')->toBeTrue()
        ->esDiaHabil('2026-09-24')->toBeFalse()
        ->esFeriado('2026-01-18')->toBeFalse()
        ->esFeriado('2027-01-18')->toBeTrue()
        ->and($calendario->proximo('2026-09-23'))
        ->nombre->toBe('Virgen de las Mercedes')
        ->tipo->toBe(TipoFeriado::Regional)
        ->esMovil()->toBeFalse();
});

it('acepta un feriado regional de una sola fecha', function () {
    $calendario = Calendario::peru(extraordinarios: [
        ['fecha' => '2026-10-15', 'nombre' => 'Feriado regional', 'tipo' => 'regional'],
    ]);

    expect($calendario->proximo('2026-10-14')->tipo)->toBe(TipoFeriado::Regional)
        ->and($calendario->esFeriado('2027-10-15'))->toBeFalse();
});

it('conserva los ajustes al cambiar el trato de los no laborables', function () {
    $calendario = Calendario::peru(
        regionales: [['mes' => 9, 'dia' => 24, 'nombre' => 'Regional']],
        omitir: ['fuerza_aerea'],
    )->conNoLaborablesInhabiles(false);

    expect($calendario)
        ->esFeriado('2026-09-24')->toBeTrue()
        ->esFeriado('2026-07-23')->toBeFalse()
        ->esDiaHabil('2026-07-27')->toBeTrue();
});

it('rechaza ajustes mal escritos', function (array $argumentos, string $mensaje) {
    expect(fn () => Calendario::peru(...$argumentos))->toThrow(InvalidArgumentException::class, $mensaje);
})->with([
    'clave desconocida' => [['omitir' => ['fuerza_aerea_x']], 'fuerza_aerea_x'],
    'fecha inexistente' => [['omitir' => ['2026-02-30']], '2026-02-30'],
    'regional en un día que no existe' => [['regionales' => [['mes' => 2, 'dia' => 30, 'nombre' => 'X']]], 'no existe todos los años'],
    'regional el 29 de febrero' => [['regionales' => [['mes' => 2, 'dia' => 29, 'nombre' => 'X']]], 'no existe todos los años'],
]);
