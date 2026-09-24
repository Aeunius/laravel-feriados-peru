# Laravel Feriados Perú

Feriados nacionales del Perú para Laravel: fijos y de Semana Santa, con el año
desde el que rige cada uno. Sin conectarse a ningún servicio externo.

> En desarrollo: todavía no hay una versión publicada.

[![tests](https://github.com/Aeunius/laravel-feriados-peru/actions/workflows/tests.yml/badge.svg)](https://github.com/Aeunius/laravel-feriados-peru/actions/workflows/tests.yml)
[![Licencia](https://img.shields.io/badge/licencia-MIT-blue.svg)](LICENSE.md)

## Requisitos

- PHP 8.2 o superior
- Laravel 12 o 13

## Instalación

```bash
composer require aeunius/laravel-feriados-peru
```

El service provider y el facade `Feriados` se registran solos.

## Uso

```php
use Aeunius\FeriadosPeru\Facades\Feriados;

Feriados::esFeriado('2026-07-28');   // true
Feriados::esFeriado('2023-06-07');   // false: el 7 de junio rige desde 2024

Feriados::delAnio(2026);             // Collection de 16 Feriado, en orden

$navidad = Feriados::proximo('2026-12-24');
$navidad->fecha;                     // CarbonImmutable 2026-12-25
$navidad->nombre;                    // 'Navidad'
$navidad->tipo;                      // TipoFeriado::Nacional
$navidad->esMovil();                 // false
```

Las fechas se aceptan como texto (`'2026-07-28'`) o como cualquier
`DateTimeInterface`. Solo cuenta el día, no la hora, y en la zona horaria de la
propia fecha. `proximo()` devuelve el primer feriado **después** de la fecha, sin
contar la fecha misma.

### Sin Laravel

El motor no necesita la aplicación:

```php
use Aeunius\FeriadosPeru\Support\Calendario;
use Aeunius\FeriadosPeru\Support\Pascua;

Calendario::peru()->esFeriado('2026-04-03');   // true (Viernes Santo)
Pascua::domingo(2026);                         // CarbonImmutable 2026-04-05
```

## Qué feriados incluye

Los del art. 6 del D. Leg. 713 y sus modificaciones: 16 en 2026.

| Fecha | Feriado | Desde |
|---|---|---|
| 1 ene | Año Nuevo | |
| Jueves y Viernes Santo | Según la Pascua | |
| 1 may | Día del Trabajo | |
| 7 jun | Batalla de Arica y Día de la Bandera | 2024 (Ley 31788) |
| 29 jun | San Pedro y San Pablo | |
| 23 jul | Día de la Fuerza Aérea del Perú | 2023 (Ley 31822) |
| 28 y 29 jul | Fiestas Patrias | |
| 6 ago | Batalla de Junín | 2022 (Ley 31530) |
| 30 ago | Santa Rosa de Lima | |
| 8 oct | Combate de Angamos | |
| 1 nov | Día de Todos los Santos | |
| 8 dic | Inmaculada Concepción | |
| 9 dic | Batalla de Ayacucho | 2022 (Ley 31381) |
| 25 dic | Navidad | |

"Desde" es el primer año en que se aplicó el feriado. La Ley 31788 se publicó el
15 de junio de 2023, después del 7 de junio de ese año, así que el primer feriado
fue en 2024.

Todavía no incluye los feriados y días no laborables que el Gobierno declara
cada año por decreto supremo.

La Pascua se calcula con el algoritmo de Butcher, válido para cualquier año del
calendario gregoriano (desde 1583).

## Desarrollo

Todo corre en Docker con la imagen oficial `composer:2`, así que no hace falta
tener PHP instalado:

```bash
make install   # dependencias
make test      # Pest
make analyse   # PHPStan
make lint      # Pint, sin cambiar archivos
make help      # todos los comandos
```

El CI prueba con Laravel 12 y 13, con PHP 8.2 a 8.5, y también con las versiones
mínimas de las dependencias.

Los cambios de cada versión están en el [CHANGELOG](CHANGELOG.md).

## Licencia

MIT. Ver [LICENSE.md](LICENSE.md).
