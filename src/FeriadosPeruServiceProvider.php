<?php

namespace Aeunius\FeriadosPeru;

use Aeunius\FeriadosPeru\Support\Calendario;
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
        $this->app->singleton(Calendario::class, fn (): Calendario => Calendario::peru());
    }
}
