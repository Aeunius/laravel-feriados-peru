# Changelog

Todos los cambios importantes de este paquete se registran aquí.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y el
proyecto usa [versionado semántico](https://semver.org/lang/es/).

## [Sin publicar]

### Agregado

- Catálogo de feriados nacionales fijos (D. Leg. 713 y modificaciones), con el
  primer año en que rige cada uno.
- Jueves y Viernes Santo, calculados desde la Pascua.
- `Support\Pascua::domingo()`: algoritmo de Butcher.
- `Support\Calendario` y el facade `Feriados`: `esFeriado()`, `delAnio()` y
  `proximo()`.
- Value object `Feriado` y enum `TipoFeriado`.
