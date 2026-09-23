<?php

use Aeunius\FeriadosPeru\Facades\Feriados;
use Aeunius\FeriadosPeru\FeriadosPeruServiceProvider;
use Aeunius\FeriadosPeru\Support\Calendario;
use Illuminate\Support\ServiceProvider;

it('registra el service provider', function () {
    expect(app()->getProviders(FeriadosPeruServiceProvider::class))->not->toBeEmpty();
});

it('carga la configuración del paquete', function () {
    expect(config('feriados-peru'))->toBeArray();
});

it('publica la configuración', function () {
    expect(ServiceProvider::publishableGroups())->toContain('feriados-peru-config');
});

it('resuelve el facade a un único calendario', function () {
    expect(Feriados::getFacadeRoot())
        ->toBeInstanceOf(Calendario::class)
        ->toBe(app(Calendario::class));
});
