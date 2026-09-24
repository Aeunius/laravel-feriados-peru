<?php

namespace Aeunius\FeriadosPeru\Support;

use Aeunius\FeriadosPeru\Feriado;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
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
     * @param  list<int>  $finDeSemana  días inhábiles de la semana: 0 domingo … 6 sábado
     */
    public function __construct(
        private readonly array $fijos,
        private readonly array $moviles,
        private readonly array $finDeSemana = [CarbonInterface::SATURDAY, CarbonInterface::SUNDAY],
    ) {
        foreach ($finDeSemana as $dia) {
            if ($dia < 0 || $dia > 6) {
                throw new \InvalidArgumentException("El día de la semana {$dia} no existe; usa 0 (domingo) a 6 (sábado).");
            }
        }

        if (count(array_unique($finDeSemana)) === 7) {
            throw new \InvalidArgumentException('El fin de semana no puede abarcar los 7 días: no quedaría ningún día hábil.');
        }
    }

    /**
     * Calendario con el catálogo de feriados nacionales que trae el paquete.
     *
     * @param  list<int>  $finDeSemana  días inhábiles de la semana: 0 domingo … 6 sábado
     */
    public static function peru(array $finDeSemana = [CarbonInterface::SATURDAY, CarbonInterface::SUNDAY]): self
    {
        $directorio = dirname(__DIR__, 2).'/resources/feriados';

        /** @var list<Fijo> $fijos */
        $fijos = require $directorio.'/fijos.php';

        /** @var list<Movil> $moviles */
        $moviles = require $directorio.'/moviles.php';

        return new self($fijos, $moviles, $finDeSemana);
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

    /** Ni feriado ni fin de semana. */
    public function esDiaHabil(\DateTimeInterface|string $fecha): bool
    {
        return $this->esHabil(self::fecha($fecha));
    }

    /**
     * El día en que termina un plazo de $dias días hábiles.
     *
     * Como en la Ley 27444 (TUO, arts. 144 y 145), se cuenta desde el día
     * hábil siguiente a la fecha: 5 días desde un lunes sin feriados terminan
     * el lunes siguiente. Con días negativos se cuenta hacia atrás y con 0 se
     * devuelve la misma fecha.
     */
    public function sumarDiasHabiles(\DateTimeInterface|string $fecha, int $dias): CarbonImmutable
    {
        $fecha = self::fecha($fecha);
        $paso = $dias < 0 ? -1 : 1;

        for ($restantes = abs($dias); $restantes > 0;) {
            $fecha = $fecha->addDays($paso);

            if ($this->esHabil($fecha)) {
                $restantes--;
            }
        }

        return $fecha;
    }

    /**
     * Días hábiles después de $desde, hasta $hasta inclusive: la inversa de
     * sumarDiasHabiles(). Si $hasta es anterior, el resultado es negativo.
     */
    public function diasHabilesEntre(\DateTimeInterface|string $desde, \DateTimeInterface|string $hasta): int
    {
        $desde = self::fecha($desde);
        $hasta = self::fecha($hasta);

        if ($hasta->toDateString() < $desde->toDateString()) {
            return -$this->diasHabilesEntre($hasta, $desde);
        }

        $dias = 0;
        $fin = $hasta->toDateString();

        for ($fecha = $desde->addDay(); $fecha->toDateString() <= $fin; $fecha = $fecha->addDay()) {
            if ($this->esHabil($fecha)) {
                $dias++;
            }
        }

        return $dias;
    }

    /**
     * Si un plazo de $dias días hábiles contado desde $fecha ya venció a $hoy.
     * El último día del plazo todavía está dentro de él.
     */
    public function esVencido(\DateTimeInterface|string $fecha, int $dias, \DateTimeInterface|string|null $hoy = null): bool
    {
        $hoy = $hoy === null ? CarbonImmutable::today() : self::fecha($hoy);

        return $hoy->toDateString() > $this->sumarDiasHabiles($fecha, $dias)->toDateString();
    }

    private function esHabil(CarbonImmutable $fecha): bool
    {
        return ! in_array($fecha->dayOfWeek, $this->finDeSemana, true)
            && ! isset($this->feriados($fecha->year)[$fecha->toDateString()]);
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
