<?php

use Carbon\CarbonInterface;

// Opciones del paquete. Se publican con:
//   php artisan vendor:publish --tag=feriados-peru-config

return [

    // Días de la semana que no son hábiles, además de los feriados. Una entidad
    // que atiende los sábados deja solo CarbonInterface::SUNDAY.
    'fin_de_semana' => [CarbonInterface::SATURDAY, CarbonInterface::SUNDAY],

    // Si los días no laborables del sector público cortan los plazos. La Ley
    // 27444 (TUO, art. 145.1) los excluye del cómputo; para plazos tributarios
    // o del sector privado son hábiles, y conviene ponerlo en false.
    'no_laborables_inhabiles' => true,

    // Feriados y días no laborables declarados después de la versión instalada
    // del paquete. Se suman a los que ya trae. Por ejemplo:
    //
    //   ['fecha' => '2026-12-24', 'nombre' => 'Día no laborable',
    //    'tipo' => 'no_laborable', 'norma' => 'D.S. 999-2026-PCM'],
    //
    // 'tipo' es 'extraordinario' (feriado para todos) o 'no_laborable'
    // (sector público, compensable).
    'extraordinarios' => [],

];
