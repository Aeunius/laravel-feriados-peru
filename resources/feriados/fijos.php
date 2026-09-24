<?php

// Feriados nacionales de fecha fija: art. 6 del D. Leg. 713 y sus modificaciones.
//
// "desde" es el primer año en que el feriado se aplicó; sin "desde", el feriado
// ya figuraba en el texto original del D. Leg. 713 (1991).

return [
    ['mes' => 1, 'dia' => 1, 'nombre' => 'Año Nuevo'],
    ['mes' => 5, 'dia' => 1, 'nombre' => 'Día del Trabajo'],
    // Ley 31788, publicada el 15-06-2023: ya no alcanzó al 7 de junio de ese año.
    ['mes' => 6, 'dia' => 7, 'nombre' => 'Batalla de Arica y Día de la Bandera', 'desde' => 2024],
    ['mes' => 6, 'dia' => 29, 'nombre' => 'San Pedro y San Pablo'],
    // Ley 31822, publicada el 08-07-2023.
    ['mes' => 7, 'dia' => 23, 'nombre' => 'Día de la Fuerza Aérea del Perú', 'desde' => 2023],
    ['mes' => 7, 'dia' => 28, 'nombre' => 'Fiestas Patrias'],
    ['mes' => 7, 'dia' => 29, 'nombre' => 'Fiestas Patrias'],
    // Ley 31530, promulgada el 25-07-2022.
    ['mes' => 8, 'dia' => 6, 'nombre' => 'Batalla de Junín', 'desde' => 2022],
    ['mes' => 8, 'dia' => 30, 'nombre' => 'Santa Rosa de Lima'],
    ['mes' => 10, 'dia' => 8, 'nombre' => 'Combate de Angamos'],
    ['mes' => 11, 'dia' => 1, 'nombre' => 'Día de Todos los Santos'],
    ['mes' => 12, 'dia' => 8, 'nombre' => 'Inmaculada Concepción'],
    // Ley 31381, publicada el 31-12-2021.
    ['mes' => 12, 'dia' => 9, 'nombre' => 'Batalla de Ayacucho', 'desde' => 2022],
    ['mes' => 12, 'dia' => 25, 'nombre' => 'Navidad'],
];
