<?php

namespace Aeunius\FeriadosPeru;

use Aeunius\FeriadosPeru\Support\Calendario;
use Carbon\CarbonInterface;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FeriadosPeruServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('feriados-peru')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Calendario::class, function (): Calendario {
            /** @var array<int> $finDeSemana */
            $finDeSemana = config('feriados-peru.fin_de_semana', [CarbonInterface::SATURDAY, CarbonInterface::SUNDAY]);

            return Calendario::peru(array_values($finDeSemana));
        });
    }
}
