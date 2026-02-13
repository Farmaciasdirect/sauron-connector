<?php

declare(strict_types=1);

namespace FarmaciasDirect\Sauron;

use Illuminate\Support\Str;

final class LogContext
{
    private static ?string $uuid = null;
    private static ?string $task = null;

    public static function start(string $task, ?string $uuid = null): string
    {
        self::$uuid = $uuid ?? (string) Str::uuid();
        self::$task = $task;

        return self::$uuid;
    }

    public static function uuid(): ?string
    {
        return self::$uuid;
    }

    public static function task(): ?string
    {
        return self::$task;
    }
}
