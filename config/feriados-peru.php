<?php

use Carbon\CarbonInterface;

// Opciones del paquete. Se publican con:
//   php artisan vendor:publish --tag=feriados-peru-config

return [

    // Días de la semana que no son hábiles, además de los feriados. Una entidad
    // que atiende los sábados deja solo CarbonInterface::SUNDAY.
    'fin_de_semana' => [CarbonInterface::SATURDAY, CarbonInterface::SUNDAY],

];
