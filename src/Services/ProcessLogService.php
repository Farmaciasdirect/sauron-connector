<?php

declare(strict_types=1);

namespace FarmaciasDirect\Sauron\Services;

use Carbon\Carbon;
use FarmaciasDirect\Sauron\Enums\ProcessStatus;
use FarmaciasDirect\Sauron\LogContext;
use FarmaciasDirect\Sauron\SauronClient;
use Throwable;

final class ProcessLogService
{
    /**
     * Inicia un nuevo proceso de log.
     *
     * @param  string  $code  Código único del proceso (ej: 'shopify:update-stock')
     * @param  string  $name  Nombre descriptivo del proceso
     * @param  string|null  $uuid  UUID opcional (se genera si no se proporciona)
     * @param  bool  $critical  Indica si es un proceso crítico
     * @return string UUID del proceso
     */
    public static function start(
        string $code,
        string $name,
        ?string $uuid = null,
        bool $critical = false
    ): string {
        $uuid = LogContext::start($name, $uuid);

        SauronClient::createProcessLog([
            'uuid' => $uuid,
            'code' => $code,
            'name' => $name,
            'status' => ProcessStatus::PROCESSING->value,
            'critical' => $critical,
            'start_date' => Carbon::now()->toDateTimeString(),
        ]);

        return $uuid;
    }

    /**
     * Marca el proceso actual como exitoso.
     */
    public static function success(array $data = []): void
    {
        if (! LogContext::uuid()) {
            return;
        }

        SauronClient::updateProcessLog(LogContext::uuid(), array_filter([
            'status' => ProcessStatus::SUCCESS->value,
            'message' => ! empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
            'end_date' => Carbon::now()->toDateTimeString(),
        ], fn ($value) => $value !== null));
    }

    /**
     * Marca el proceso actual como fallido.
     */
    public static function failed(Throwable $e): void
    {
        if (! LogContext::uuid()) {
            return;
        }

        SauronClient::updateProcessLog(LogContext::uuid(), [
            'status' => ProcessStatus::FAILED->value,
            'message' => $e->getMessage(),
            'exception' => sprintf(
                '%s: %s in %s:%d',
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ),
            'end_date' => Carbon::now()->toDateTimeString(),
        ]);
    }

    /**
     * Actualiza el mensaje del proceso actual sin cambiar el estado.
     */
    public static function updateMessage(string $message): void
    {
        if (! LogContext::uuid()) {
            return;
        }

        SauronClient::updateProcessLog(LogContext::uuid(), [
            'message' => $message,
        ]);
    }
}
