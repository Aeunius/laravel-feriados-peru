<?php

namespace Aeunius\FeriadosPeru\Support;

use Aeunius\FeriadosPeru\Enums\TipoFeriado;
use Aeunius\FeriadosPeru\Feriado;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Motor de feriados y días hábiles, sin dependencia de Laravel: recibe el
 * catálogo de feriados y responde sobre fechas concretas.
 *
 * @phpstan-type Fijo array{clave?: string, mes: int, dia: int, nombre: string, desde?: int, tipo?: TipoFeriado}
 * @phpstan-type Movil array{clave?: string, dias: int, nombre: string}
 * @phpstan-type Regional array{mes: int, dia: int, nombre: string, desde?: int}
 * @phpstan-type Extraordinario array{fecha: string, nombre: string, tipo: string, norma?: string}
 */
final class Calendario
{
    /** @var array<int, array<string, Feriado>> feriados y no laborables de cada año, por fecha Y-m-d */
    private array $porAnio = [];

    /** @var array<int, list<Feriado>> los extraordinarios, por año */
    private array $extraordinariosPorAnio = [];

    /** @var array<string, true> claves y fechas Y-m-d que no se consideran */
    private array $omitidos = [];

    /**
     * @param  list<Fijo>  $fijos
     * @param  list<Movil>  $moviles  días contados desde el domingo de Pascua
     * @param  list<int>  $finDeSemana  días inhábiles de la semana: 0 domingo … 6 sábado
     * @param  list<Extraordinario>  $extraordinarios  tipo 'extraordinario', 'regional' o 'no_laborable'
     * @param  bool  $noLaborablesInhabiles  si los días no laborables cortan los plazos
     * @param  list<string>  $omitir  claves del catálogo o fechas AAAA-MM-DD que no se consideran
     */
    public function __construct(
        private readonly array $fijos,
        private readonly array $moviles,
        private readonly array $finDeSemana = [CarbonInterface::SATURDAY, CarbonInterface::SUNDAY],
        private readonly array $extraordinarios = [],
        private readonly bool $noLaborablesInhabiles = true,
        private readonly array $omitir = [],
    ) {
        foreach ($finDeSemana as $dia) {
            if ($dia < 0 || $dia > 6) {
                throw new \InvalidArgumentException("El día de la semana {$dia} no existe; usa 0 (domingo) a 6 (sábado).");
            }
        }

        if (count(array_unique($finDeSemana)) === 7) {
            throw new \InvalidArgumentException('El fin de semana no puede abarcar los 7 días: no quedaría ningún día hábil.');
        }

        foreach ($fijos as $fijo) {
            if (! checkdate($fijo['mes'], $fijo['dia'], 2001)) {
                throw new \InvalidArgumentException("El feriado [{$fijo['nombre']}] tiene un día que no existe todos los años: {$fijo['dia']}/{$fijo['mes']}.");
            }
        }

        foreach ($extraordinarios as $extraordinario) {
            $feriado = self::extraordinario($extraordinario);
            $this->extraordinariosPorAnio[$feriado->fecha->year][] = $feriado;
        }

        $claves = array_filter(array_column([...$fijos, ...$moviles], 'clave'));

        foreach ($omitir as $omitido) {
            if (! in_array($omitido, $claves, true) && self::fechaValida($omitido) === null) {
                throw new \InvalidArgumentException("No hay un feriado con la clave [{$omitido}] para omitir; usa una clave del catálogo o una fecha AAAA-MM-DD.");
            }

            $this->omitidos[$omitido] = true;
        }
    }

    /**
     * Calendario con el catálogo de feriados nacionales que trae el paquete.
     *
     * @param  list<int>  $finDeSemana  días inhábiles de la semana: 0 domingo … 6 sábado
     * @param  list<Extraordinario>  $extraordinarios  se suman a los del paquete
     * @param  bool  $noLaborablesInhabiles  si los días no laborables cortan los plazos
     * @param  list<Regional>  $regionales  feriados propios que se repiten cada año
     * @param  list<string>  $omitir  claves del catálogo o fechas AAAA-MM-DD que no se consideran
     */
    public static function peru(
        array $finDeSemana = [CarbonInterface::SATURDAY, CarbonInterface::SUNDAY],
        array $extraordinarios = [],
        bool $noLaborablesInhabiles = true,
        array $regionales = [],
        array $omitir = [],
    ): self {
        $directorio = dirname(__DIR__, 2).'/resources/feriados';

        /** @var list<Fijo> $fijos */
        $fijos = require $directorio.'/fijos.php';

        /** @var list<Movil> $moviles */
        $moviles = require $directorio.'/moviles.php';

        /** @var list<Extraordinario> $delPaquete */
        $delPaquete = require $directorio.'/extraordinarios.php';

        foreach ($regionales as $regional) {
            $fijos[] = [...$regional, 'tipo' => TipoFeriado::Regional];
        }

        return new self($fijos, $moviles, $finDeSemana, [...$delPaquete, ...$extraordinarios], $noLaborablesInhabiles, $omitir);
    }

    /**
     * El mismo calendario, pero con los días no laborables como hábiles o no.
     *
     * Por defecto son inhábiles, como manda la Ley 27444 (TUO, art. 145.1) para
     * los plazos del procedimiento administrativo. Para plazos tributarios o
     * del sector privado, esos días son hábiles: conNoLaborablesInhabiles(false).
     */
    public function conNoLaborablesInhabiles(bool $inhabiles = true): self
    {
        return $inhabiles === $this->noLaborablesInhabiles
            ? $this
            : new self($this->fijos, $this->moviles, $this->finDeSemana, $this->extraordinarios, $inhabiles, $this->omitir);
    }

    /** Si es feriado nacional, extraordinario o regional. Un día no laborable no lo es. */
    public function esFeriado(\DateTimeInterface|string $fecha): bool
    {
        return $this->delDia(self::fecha($fecha))?->tipo->esFeriado() ?? false;
    }

    /** Si es un día no laborable del sector público declarado por decreto. */
    public function esNoLaborable(\DateTimeInterface|string $fecha): bool
    {
        return $this->delDia(self::fecha($fecha))?->tipo === TipoFeriado::NoLaborable;
    }

    /**
     * Feriados del año, ordenados por fecha; con $conNoLaborables, también los
     * días no laborables.
     *
     * @return Collection<int, Feriado>
     */
    public function delAnio(int $anio, bool $conNoLaborables = false): Collection
    {
        return (new Collection(array_values($this->feriados($anio))))
            ->filter(fn (Feriado $feriado): bool => $conNoLaborables || $feriado->tipo->esFeriado())
            ->values();
    }

    /** El primer feriado posterior a la fecha, sin contar la fecha misma. */
    public function proximo(\DateTimeInterface|string $fecha): ?Feriado
    {
        $fecha = self::fecha($fecha);
        $dia = $fecha->toDateString();

        foreach ([$fecha->year, $fecha->year + 1] as $anio) {
            foreach ($this->feriados($anio) as $clave => $feriado) {
                if ($clave > $dia && $feriado->tipo->esFeriado()) {
                    return $feriado;
                }
            }
        }

        return null;
    }

    /** Ni feriado ni fin de semana; ni día no laborable, salvo conNoLaborablesInhabiles(false). */
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
        if (in_array($fecha->dayOfWeek, $this->finDeSemana, true)) {
            return false;
        }

        $feriado = $this->delDia($fecha);

        return $feriado === null
            || (! $feriado->tipo->esFeriado() && ! $this->noLaborablesInhabiles);
    }

    private function delDia(CarbonImmutable $fecha): ?Feriado
    {
        return $this->feriados($fecha->year)[$fecha->toDateString()] ?? null;
    }

    /**
     * Si dos caen el mismo día, un feriado gana a un día no laborable; entre
     * iguales, queda el primero: fijos, móviles y luego extraordinarios. Los
     * omitidos no entran.
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

            $this->agregar($feriados, new Feriado(
                CarbonImmutable::createStrict($anio, $fijo['mes'], $fijo['dia']),
                $fijo['nombre'],
                $fijo['tipo'] ?? TipoFeriado::Nacional,
                clave: $fijo['clave'] ?? null,
            ));
        }

        $pascua = Pascua::domingo($anio);

        foreach ($this->moviles as $movil) {
            $this->agregar($feriados, new Feriado($pascua->addDays($movil['dias']), $movil['nombre'], movil: true, clave: $movil['clave'] ?? null));
        }

        foreach ($this->extraordinariosPorAnio[$anio] ?? [] as $feriado) {
            $this->agregar($feriados, $feriado);
        }

        ksort($feriados);

        return $this->porAnio[$anio] = $feriados;
    }

    /** @param  array<string, Feriado>  $feriados */
    private function agregar(array &$feriados, Feriado $feriado): void
    {
        $clave = $feriado->fecha->toDateString();

        if (isset($this->omitidos[$clave]) || isset($this->omitidos[$feriado->clave ?? ''])) {
            return;
        }

        $actual = $feriados[$clave] ?? null;

        if ($actual === null || (! $actual->tipo->esFeriado() && $feriado->tipo->esFeriado())) {
            $feriados[$clave] = $feriado;
        }
    }

    /** @param  Extraordinario  $definicion */
    private static function extraordinario(array $definicion): Feriado
    {
        $fecha = $definicion['fecha'];
        $dia = self::fechaValida($fecha)
            ?? throw new \InvalidArgumentException("La fecha [{$fecha}] del feriado extraordinario no es válida; usa el formato AAAA-MM-DD.");

        $tipo = TipoFeriado::tryFrom($definicion['tipo']);

        if ($tipo === null || $tipo === TipoFeriado::Nacional) {
            throw new \InvalidArgumentException("El tipo [{$definicion['tipo']}] del {$fecha} no es válido; usa 'extraordinario', 'regional' o 'no_laborable'.");
        }

        return new Feriado(
            $dia,
            $definicion['nombre'],
            $tipo,
            norma: $definicion['norma'] ?? null,
        );
    }

    /** La fecha si el texto es una fecha AAAA-MM-DD que existe; si no, null. */
    private static function fechaValida(string $texto): ?CarbonImmutable
    {
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $texto, $partes)
            || ! checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1])) {
            return null;
        }

        return CarbonImmutable::createStrict((int) $partes[1], (int) $partes[2], (int) $partes[3]);
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
