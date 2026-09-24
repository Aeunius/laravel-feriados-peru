<?php

// Feriados y días no laborables declarados por decreto para fechas concretas.
// Solo los de alcance nacional; los regionales no se incluyen.
//
// Los días no laborables son para el sector público y son compensables. Los
// decretos los declaran hábiles para efectos tributarios.
//
// Los que el Gobierno declare después de la versión instalada se agregan en
// config/feriados-peru.php, con esta misma forma.

return [
    // D.S. 042-2025-PCM, publicado el 03-04-2025.
    ['fecha' => '2025-05-02', 'nombre' => 'Día no laborable', 'tipo' => 'no_laborable', 'norma' => 'D.S. 042-2025-PCM'],
    ['fecha' => '2025-12-26', 'nombre' => 'Día no laborable', 'tipo' => 'no_laborable', 'norma' => 'D.S. 042-2025-PCM'],
    ['fecha' => '2026-01-02', 'nombre' => 'Día no laborable', 'tipo' => 'no_laborable', 'norma' => 'D.S. 042-2025-PCM'],

    // D.S. 075-2026-PCM, publicado el 16-05-2026.
    ['fecha' => '2026-07-27', 'nombre' => 'Día no laborable', 'tipo' => 'no_laborable', 'norma' => 'D.S. 075-2026-PCM'],
];
