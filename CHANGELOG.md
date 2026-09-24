# Changelog

Todos los cambios importantes de este paquete se registran aquí.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y el
proyecto usa [versionado semántico](https://semver.org/lang/es/).

## [Sin publicar]

## [1.0.0] - 2026-09-23

### Agregado

- Días no laborables del sector público (`TipoFeriado::NoLaborable`) y feriados
  extraordinarios (`TipoFeriado::Extraordinario`), con la norma que los declara
  en `Feriado::$norma`.
- Catálogo de días no laborables nacionales desde 2025 (D.S. 042-2025-PCM y
  D.S. 075-2026-PCM).
- Opción `extraordinarios` en la configuración, para agregar los que se
  declaren sin esperar una versión nueva.
- `esNoLaborable()`, `delAnio($anio, conNoLaborables: true)` y
  `conNoLaborablesInhabiles()`; opción `no_laborables_inhabiles`.
- Feriados regionales o locales que se repiten cada año (opción `regionales`,
  `TipoFeriado::Regional`), que cortan los plazos como los nacionales.
- Opción `omitir`, para no considerar un feriado del paquete por su clave o una
  fecha puntual. Cada feriado del catálogo trae su clave en `Feriado::$clave`.

### Cambiado

- Los días no laborables cortan los plazos por defecto (TUO de la Ley 27444,
  art. 145.1): el 27 de julio de 2026 ya no es día hábil. Para volver al
  comportamiento anterior, `'no_laborables_inhabiles' => false`.

## [0.2.0] - 2026-09-23

Primera versión.

### Agregado

- Catálogo de feriados nacionales fijos (D. Leg. 713 y modificaciones), con el
  primer año en que rige cada uno.
- Jueves y Viernes Santo, calculados desde la Pascua.
- `Support\Pascua::domingo()`: algoritmo de Butcher.
- `Support\Calendario` y el facade `Feriados`: `esFeriado()`, `delAnio()` y
  `proximo()`.
- Value object `Feriado` y enum `TipoFeriado`.
- Días hábiles según la Ley 27444: `esDiaHabil()`, `sumarDiasHabiles()`,
  `diasHabilesEntre()` y `esVencido()`.
- Opción `fin_de_semana` en la configuración, para entidades que atienden los
  sábados.

[Sin publicar]: https://github.com/Aeunius/laravel-feriados-peru/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/Aeunius/laravel-feriados-peru/compare/v0.2.0...v1.0.0
[0.2.0]: https://github.com/Aeunius/laravel-feriados-peru/releases/tag/v0.2.0
