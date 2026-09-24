<?php

namespace Aeunius\FeriadosPeru\Support;

use Aeunius\FeriadosPeru\Feriado;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Motor de feriados y días hábiles, sin dependencia de Laravel: recibe el
 * catálogo de feriados y responde sobre fechas concretas.
 *
 * @phpstan-type Fijo array{mes: int, dia: int, nombre: string, desde?: int}
 * @phpstan-type Movil array{dias: int, nombre: string}
 */
final class Calendario
{
    /** @var array<int, array<string, Feriado>> feriados de cada año, por fecha Y-m-d */
    private array $porAnio = [];

    /**
     * @param  list<Fijo>  $fijos
     * @param  list<Movil>  $moviles  días contados desde el domingo de Pascua
     */
    public function __construct(
        private readonly array $fijos,
        private readonly array $moviles,
    ) {}

    /** Calendario con el catálogo de feriados nacionales que trae el paquete. */
    public static function peru(): self
    {
        $directorio = dirname(__DIR__, 2).'/resources/feriados';

        /** @var list<Fijo> $fijos */
        $fijos = require $directorio.'/fijos.php';

        /** @var list<Movil> $moviles */
        $moviles = require $directorio.'/moviles.php';

        return new self($fijos, $moviles);
    }

    public function esFeriado(\DateTimeInterface|string $fecha): bool
    {
        $fecha = self::fecha($fecha);

        return isset($this->feriados($fecha->year)[$fecha->toDateString()]);
    }

    /**
     * Feriados del año, ordenados por fecha.
     *
     * @return Collection<int, Feriado>
     */
    public function delAnio(int $anio): Collection
    {
        return new Collection(array_values($this->feriados($anio)));
    }

    /** El primer feriado posterior a la fecha, sin contar la fecha misma. */
    public function proximo(\DateTimeInterface|string $fecha): ?Feriado
    {
        $fecha = self::fecha($fecha);
        $dia = $fecha->toDateString();

        foreach ([$fecha->year, $fecha->year + 1] as $anio) {
            foreach ($this->feriados($anio) as $clave => $feriado) {
                if ($clave > $dia) {
                    return $feriado;
                }
            }
        }

        return null;
    }

    /**
     * Si dos feriados caen el mismo día, queda el primero del catálogo.
     *
     * @return array<string, Feriado>
     */
    private function feriados(int $anio): array
    {
        if (isset($this->porAnio[$anio])) {
            return $this->porAnio[$anio];
        }

        $feriados = [];

        foreach ($this->fijos as $fijo) {
            if ($anio < ($fijo['desde'] ?? PHP_INT_MIN)) {
                continue;
            }

            $feriado = new Feriado(CarbonImmutable::createStrict($anio, $fijo['mes'], $fijo['dia']), $fijo['nombre']);
            $feriados[$feriado->fecha->toDateString()] ??= $feriado;
        }

        $pascua = Pascua::domingo($anio);

        foreach ($this->moviles as $movil) {
            $feriado = new Feriado($pascua->addDays($movil['dias']), $movil['nombre'], movil: true);
            $feriados[$feriado->fecha->toDateString()] ??= $feriado;
        }

        ksort($feriados);

        return $this->porAnio[$anio] = $feriados;
    }

    /** La fecha de calendario, sin la hora; un DateTime conserva su propio día. */
    private static function fecha(\DateTimeInterface|string $fecha): CarbonImmutable
    {
        return ($fecha instanceof \DateTimeInterface
            ? CarbonImmutable::instance($fecha)
            : CarbonImmutable::parse($fecha)
        )->startOfDay();
    }
}
