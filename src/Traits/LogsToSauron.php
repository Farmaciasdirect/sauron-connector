<?php

declare(strict_types=1);

namespace FarmaciasDirect\Sauron\Traits;

use FarmaciasDirect\Sauron\Services\ProcessLogService;
use Illuminate\Support\Str;
use Throwable;

trait LogsToSauron
{
    /**
     * Código del proceso para Sauron.
     * Debe ser sobrescrito en cada Job que use el trait.
     */
    protected function sauronCode(): string
    {
        return strtolower(str_replace('\\', ':', class_basename(static::class)));
    }

    /**
     * Nombre descriptivo del proceso.
     * Por defecto usa la propiedad $task si existe.
     */
    protected function sauronName(): string
    {
        return $this->task ?? class_basename(static::class);
    }

    protected function sauronCritical(): bool
    {
        return false;
    }

    /**
     * Genera el UUID del proceso.
     * Puede ser sobrescrito para usar UUID determinista.
     */
    protected function sauronUuid(): string
    {
        return (string) Str::uuid();
    }

    protected function shouldLogToSauron(): bool
    {
        return $this->sauronCritical() || config('sauron.debug', false);
    }

    /**
     * Inicia el log del proceso en Sauron.
     */
    protected function sauronStart(): string
    {
        return ProcessLogService::start(
            code: $this->sauronCode(),
            name: $this->sauronName(),
            uuid: $this->sauronUuid(),
            critical: $this->sauronCritical(),
        );
    }

    /**
     * Marca el proceso como exitoso en Sauron.
     */
    protected function sauronSuccess(array $data = []): void
    {
        ProcessLogService::success($data);
    }

    /**
     * Marca el proceso como fallido en Sauron.
     */
    protected function sauronFailed(Throwable $e): void
    {
        ProcessLogService::failed($e);
    }

    /**
     * Ejecuta el contenido del job con logging automático a Sauron.
     *
     * Uso:
     * public function handle(): void
     * {
     *     $this->withSauronLogging(function() {
     *         // ... lógica del job ...
     *         return ['items_processed' => 100];
     *     });
     * }
     */
    protected function withSauronLogging(callable $callback): void
    {
        if (!$this->shouldLogToSauron()) {
            $callback();

            return;
        }

        $this->sauronStart();

        try {
            $result = $callback();
            $this->sauronSuccess(is_array($result) ? $result : []);
        } catch (Throwable $e) {
            $this->sauronFailed($e);
            throw $e;
        }
    }
}
