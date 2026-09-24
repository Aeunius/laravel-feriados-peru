<?php

use Aeunius\FeriadosPeru\Support\Pascua;

it('calcula el domingo de Pascua', function (int $anio, string $esperado) {
    expect(Pascua::domingo($anio)->toDateString())->toBe($esperado);
})->with([
    [1583, '1583-04-10'],
    [1818, '1818-03-22'], // la fecha más temprana posible
    [1943, '1943-04-25'], // la más tardía posible
    [2000, '2000-04-23'],
    [2008, '2008-03-23'],
    [2019, '2019-04-21'],
    [2021, '2021-04-04'],
    [2024, '2024-03-31'],
    [2025, '2025-04-20'],
    [2026, '2026-04-05'],
    [2027, '2027-03-28'],
    [2038, '2038-04-25'],
    [2100, '2100-03-28'],
    [2285, '2285-03-22'],
]);

it('cae siempre en domingo', function () {
    foreach (range(1900, 2100) as $anio) {
        expect(Pascua::domingo($anio)->isSunday())->toBeTrue("Pascua de {$anio}");
    }
});

it('rechaza años anteriores al calendario gregoriano', function () {
    Pascua::domingo(1582);
})->throws(InvalidArgumentException::class, '1583');
