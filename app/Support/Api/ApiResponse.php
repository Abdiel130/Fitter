<?php

namespace App\Support\Api;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Http\JsonResponse;
use JsonSerializable;

class ApiResponse {
    /**
     * Respuesta exitosa estándar (HTTP 200 por defecto).
     */
    public function success(
        string $message = '',
        array|Arrayable|JsonSerializable|null $data = null,
        HttpStatus|int $code = HttpStatus::OK
    ): JsonResponse {
        $payload = [
            'success' => true,
        ];

        if ($message !== '') {
            $payload['message'] = $message;
        }

        if ($data !== null) {
            $payload['data'] = $this->resolveData($data);
        }

        return response()->json($payload, $this->resolveCode($code));
    }

    /**
     * Respuesta de recurso creado (HTTP 201).
     */
    public function created(
        string $message = '',
        array|Arrayable|JsonSerializable|null $data = null
    ): JsonResponse {
        return $this->success(
            message: $message,
            data: $data,
            code: HttpStatus::CREATED
        );
    }

    /**
     * Respuesta sin contenido (HTTP 204).
     */
    public function noContent(): JsonResponse {
        return response()->json(null, HttpStatus::NO_CONTENT->value);
    }

    /**
     * Respuesta de error genérica.
     */
    public function error(
        string $message = 'Ha ocurrido un error.',
        array|MessageBag|null $errors = null,
        HttpStatus|int $code = HttpStatus::BAD_REQUEST
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        $resolvedErrors = $this->resolveErrors($errors);
        if ($resolvedErrors !== null && !empty($resolvedErrors)) {
            $payload['errors'] = $resolvedErrors;
        }

        return response()->json($payload, $this->resolveCode($code));
    }

    /**
     * Respuesta de error de cliente (HTTP 400 Bad Request).
     */
    public function badRequest(
        string $message = 'Solicitud incorrecta.',
        array|MessageBag|null $errors = null
    ): JsonResponse {
        return $this->error($message, $errors, HttpStatus::BAD_REQUEST);
    }

    /**
     * Respuesta de no autenticado / credenciales inválidas (HTTP 401 Unauthorized).
     */
    public function unauthorized(
        string $message = 'No autenticado.',
        array|MessageBag|null $errors = null
    ): JsonResponse {
        return $this->error($message, $errors, HttpStatus::UNAUTHORIZED);
    }

    /**
     * Respuesta de acceso prohibido / falta de permisos (HTTP 403 Forbidden).
     */
    public function forbidden(
        string $message = 'Acceso denegado.',
        array|MessageBag|null $errors = null
    ): JsonResponse {
        return $this->error($message, $errors, HttpStatus::FORBIDDEN);
    }

    /**
     * Respuesta de recurso no encontrado (HTTP 404 Not Found).
     */
    public function notFound(string $message = 'Recurso no encontrado.'): JsonResponse
    {
        return $this->error($message, null, HttpStatus::NOT_FOUND);
    }

    /**
     * Respuesta de validación de datos fallida (HTTP 422 Unprocessable Entity).
     */
    public function validation(
        array|MessageBag $errors,
        string $message = 'Los datos proporcionados no son válidos.'
    ): JsonResponse {
        return $this->error($message, $errors, HttpStatus::UNPROCESSABLE);
    }

    /**
     * Respuesta de error interno del servidor (HTTP 500 Internal Server Error).
     */
    public function internalError(
        string $message = 'Ha ocurrido un error inesperado en el servidor.'
    ): JsonResponse {
        return $this->error($message, null, HttpStatus::INTERNAL_ERROR);
    }

    /**
     * Respuesta con paginación estandarizada para listados y sincronizaciones masivas.
     */
    public function paginated(
        LengthAwarePaginator $paginator,
        string $message = ''
    ): JsonResponse {
        return $this->success(
            message: $message,
            data: [
                'items' => $paginator->items(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'has_more_pages' => $paginator->hasMorePages(),
                ],
            ],
            code: HttpStatus::OK
        );
    }

    /**
     * Resuelve el código numérico a partir de un HttpStatus o un entero.
     */
    private function resolveCode(HttpStatus|int $code): int
    {
        return $code instanceof HttpStatus ? $code->value : $code;
    }

    /**
     * Resuelve estructuras Arrayable o JsonSerializable a arrays asociativos/datos nativos.
     */
    private function resolveData(array|Arrayable|JsonSerializable|null $data): mixed
    {
        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        if ($data instanceof JsonSerializable) {
            return $data->jsonSerialize();
        }

        return $data;
    }

    /**
     * Resuelve MessageBag de validación de Laravel a un array de mensajes de error.
     */
    private function resolveErrors(array|MessageBag|null $errors): ?array
    {
        if ($errors instanceof MessageBag) {
            return $errors->toArray();
        }

        return $errors;
    }
}
