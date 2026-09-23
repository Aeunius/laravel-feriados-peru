<?php

namespace Aeunius\FeriadosPeru\Tests;

use Aeunius\FeriadosPeru\FeriadosPeruServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FeriadosPeruServiceProvider::class,
        ];
    }
}
