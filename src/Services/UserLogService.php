<?php

declare(strict_types=1);

namespace FarmaciasDirect\Sauron\Services;

use FarmaciasDirect\Sauron\SauronClient;
use Illuminate\Database\Eloquent\Model;

final class UserLogService
{
    /**
     * Registra una acción de usuario sobre un modelo.
     */
    public static function log(
        string $user,
        string $action,
        string $model,
        ?array $originalData = null,
        ?array $newData = null
    ): void {
        SauronClient::createUserLog(array_filter([
            'user' => $user,
            'action' => $action,
            'model' => $model,
            'original_data' => $originalData ? json_encode($originalData, JSON_UNESCAPED_UNICODE) : null,
            'new_data' => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
        ], fn ($value) => $value !== null));
    }

    /**
     * Registra la creación de un modelo.
     */
    public static function created(string $user, Model $model): void
    {
        self::log(
            user: $user,
            action: 'created',
            model: get_class($model).':'.$model->getKey(),
            newData: $model->toArray(),
        );
    }

    /**
     * Registra la actualización de un modelo.
     */
    public static function updated(string $user, Model $model, array $originalData): void
    {
        self::log(
            user: $user,
            action: 'updated',
            model: get_class($model).':'.$model->getKey(),
            originalData: $originalData,
            newData: $model->toArray(),
        );
    }

    /**
     * Registra la eliminación de un modelo.
     */
    public static function deleted(string $user, Model $model): void
    {
        self::log(
            user: $user,
            action: 'deleted',
            model: get_class($model).':'.$model->getKey(),
            originalData: $model->toArray(),
        );
    }
}
