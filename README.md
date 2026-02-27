# Sauron Connector

Laravel SDK para integración con la API de Sauron — sistema centralizado de logging para procesos, auditoría de usuarios y trazabilidad de peticiones HTTP.

[![Packagist Version](https://img.shields.io/packagist/v/farmaciasdirect/sauron-connector)](https://packagist.org/packages/farmaciasdirect/sauron-connector)
[![PHP](https://img.shields.io/badge/PHP-%5E8.2-blue)](https://packagist.org/packages/farmaciasdirect/sauron-connector)
[![Laravel](https://img.shields.io/badge/Laravel-10%2B-red)](https://packagist.org/packages/farmaciasdirect/sauron-connector)

## Requisitos

- PHP ^8.2
- Laravel 10, 11 o 12

## Instalación

```bash
composer require farmaciasdirect/sauron-connector
```

El paquete se registra automáticamente mediante el autodiscovery de Laravel.

### Publicar configuración

```bash
php artisan vendor:publish --tag=sauron-config
```

### Variables de entorno

Añade las siguientes variables a tu `.env`:

```env
SAURON_API_URL=https://sauron.example.com
SAURON_API_TOKEN=your-api-token

# Opcional: activa el logging para todos los procesos (no solo los críticos)
SAURON_DEBUG=false
```

---

## Funcionalidades

### 1. Process Logs — Seguimiento de procesos

Registra el ciclo de vida de un proceso (inicio, éxito o fallo) con trazas intermedias opcionales.

#### Uso directo con `ProcessLogService`

```php
use FarmaciasDirect\Sauron\Services\ProcessLogService;

// Iniciar proceso
$uuid = ProcessLogService::start(
    code: 'shopify:sync-stock',
    name: 'Sincronización de stock con Shopify',
    critical: true,
);

try {
    // ... lógica del proceso ...

    // Traza intermedia
    ProcessLogService::trace('Procesados 50 productos');

    // Marcar como exitoso
    ProcessLogService::success(['items_processed' => 100]);
} catch (Throwable $e) {
    // Marcar como fallido
    ProcessLogService::failed($e);
}
```

#### Uso con el trait `LogsToSauron` en Jobs

El trait `LogsToSauron` simplifica el logging en Laravel Jobs mediante el método `withSauronLogging`:

```php
use FarmaciasDirect\Sauron\Traits\LogsToSauron;

class SyncStockJob implements ShouldQueue
{
    use LogsToSauron;

    protected function sauronCode(): string
    {
        return 'shopify:sync-stock';
    }

    protected function sauronName(): string
    {
        return 'Sincronización de stock con Shopify';
    }

    protected function sauronCritical(): bool
    {
        return true; // Siempre se loguea, independientemente de SAURON_DEBUG
    }

    public function handle(): void
    {
        $this->withSauronLogging(function () {
            // ... lógica del job ...
            $this->sauronTrace('Chunk 1 procesado');

            return ['items_processed' => 100]; // Datos opcionales en el log de éxito
        });
    }
}
```

**Comportamiento del trait:**
- Si `sauronCritical()` devuelve `true` o `SAURON_DEBUG=true`, el logging se activa.
- En caso contrario, el callback se ejecuta sin enviar nada a Sauron.
- Si se lanza una excepción, el proceso se marca como `failed` y la excepción se re-lanza.

**Métodos sobreescribibles:**

| Método | Descripción | Por defecto |
|---|---|---|
| `sauronCode()` | Código identificador del proceso | Nombre de la clase en minúsculas |
| `sauronName()` | Nombre descriptivo | Propiedad `$task` o nombre de la clase |
| `sauronCritical()` | Si siempre debe loguearse | `false` |
| `sauronUuid()` | UUID del proceso | UUID aleatorio |

---

### 2. User Logs — Auditoría de acciones de usuario

Registra acciones CRUD realizadas por usuarios sobre modelos Eloquent.

```php
use FarmaciasDirect\Sauron\Services\UserLogService;

// Genérico
UserLogService::log(
    user: 'user@example.com',
    action: 'approved',
    model: 'Order:42',
    originalData: ['status' => 'pending'],
    newData: ['status' => 'approved'],
);

// Helpers para operaciones CRUD
UserLogService::created($user->email, $model);
UserLogService::updated($user->email, $model, $model->getOriginal());
UserLogService::deleted($user->email, $model);
```

---

### 3. Request Logs — Trazabilidad de peticiones HTTP

Registra peticiones HTTP entrantes con payload y respuesta.

```php
use FarmaciasDirect\Sauron\Services\RequestLogService;

// Desde un objeto Request de Laravel
RequestLogService::fromRequest($request, response: $responseData, statusCode: 200);

// Manual
RequestLogService::log(
    user: 'user@example.com',
    method: 'POST',
    uri: '/api/orders',
    ipAddress: '192.168.1.1',
    payload: $request->all(),
    response: ['id' => 99],
    statusCode: 201,
);
```

---

## Referencia de clases

| Clase | Descripción |
|---|---|
| `ProcessLogService` | Gestión del ciclo de vida de procesos |
| `UserLogService` | Auditoría de acciones de usuario sobre modelos |
| `RequestLogService` | Logging de peticiones HTTP |
| `LogsToSauron` (trait) | Integración automática de logging en Jobs |
| `LogContext` | Contexto compartido del proceso activo (UUID, nombre) |
| `SauronClient` | Cliente HTTP hacia la API de Sauron |

---

## Enlace en Packagist

[https://packagist.org/packages/farmaciasdirect/sauron-connector](https://packagist.org/packages/farmaciasdirect/sauron-connector)
