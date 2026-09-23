# Laravel Feriados Perú

Feriados nacionales del Perú y cálculo de días hábiles para plazos
administrativos en Laravel, sin conectarse a ningún servicio externo.

> En desarrollo: todavía no hay una versión publicada.

[![tests](https://github.com/Aeunius/laravel-feriados-peru/actions/workflows/tests.yml/badge.svg)](https://github.com/Aeunius/laravel-feriados-peru/actions/workflows/tests.yml)
[![Licencia](https://img.shields.io/badge/licencia-MIT-blue.svg)](LICENSE.md)

## Requisitos

- PHP 8.2 o superior
- Laravel 12 o 13

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
