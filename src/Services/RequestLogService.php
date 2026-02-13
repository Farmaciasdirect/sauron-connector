<?php

declare(strict_types=1);

namespace FarmaciasDirect\Sauron\Services;

use FarmaciasDirect\Sauron\SauronClient;
use Illuminate\Http\Request;

final class RequestLogService
{
    /**
     * Registra una petición HTTP.
     */
    public static function log(
        string $user,
        string $method,
        string $uri,
        ?string $ipAddress = null,
        ?array $payload = null,
        ?array $response = null,
        ?int $statusCode = null
    ): void {
        SauronClient::createRequestLog(array_filter([
            'user' => $user,
            'method' => strtoupper($method),
            'uri' => $uri,
            'ip_address' => $ipAddress,
            'payload' => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
            'response' => $response ? json_encode($response, JSON_UNESCAPED_UNICODE) : null,
            'status_code' => $statusCode,
        ], fn ($value) => $value !== null));
    }

    /**
     * Registra desde un objeto Request de Laravel.
     */
    public static function fromRequest(
        Request $request,
        ?array $response = null,
        ?int $statusCode = null
    ): void {
        self::log(
            user: $request->user()?->email ?? 'anonymous',
            method: $request->method(),
            uri: $request->getRequestUri(),
            ipAddress: $request->ip(),
            payload: $request->all(),
            response: $response,
            statusCode: $statusCode,
        );
    }
}
