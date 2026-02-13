<?php

declare(strict_types=1);

namespace FarmaciasDirect\Sauron;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

final class SauronClient
{
    private const TIMEOUT_SECONDS = 10;

    private static function client(): PendingRequest
    {
        return Http::withToken(config('sauron.token'))
            ->baseUrl(config('sauron.url'))
            ->acceptJson()
            ->timeout(self::TIMEOUT_SECONDS);
    }

    public static function createProcessLog(array $payload): void
    {
        self::safeRequest(fn () => self::client()->post('/api/v1/process-logs', $payload));
    }

    public static function updateProcessLog(string $uuid, array $payload): void
    {
        self::safeRequest(fn () => self::client()->patch("/api/v1/process-logs/{$uuid}", $payload));
    }

    public static function getProcessLog(string $uuid): ?array
    {
        return self::safeRequest(fn () => self::client()->get("/api/v1/process-logs/{$uuid}")->json());
    }

    public static function createUserLog(array $payload): void
    {
        self::safeRequest(fn () => self::client()->post('/api/v1/user-logs', $payload));
    }

    public static function createRequestLog(array $payload): void
    {
        self::safeRequest(fn () => self::client()->post('/api/v1/request-logs', $payload));
    }

    private static function safeRequest(callable $request): mixed
    {
        try {
            return $request();
        } catch (Throwable $e) {
            logger()->warning('SauronClient error', [
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
