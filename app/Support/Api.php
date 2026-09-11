<?php

namespace App\Support;

use App\Support\Api\ApiResponse;

/**
 * Gateway central de utilidades API para Fitter.
 * 
 * Acceso explícito a submódulos:
 * - Api::response(): Respuestas JSON estandarizadas.
 * - Api::file(): Gestión de archivos y fotos (futuro).
 * - Api::sync(): Lógica de sincronización offline (futuro).
 */
class Api {
    protected static ?ApiResponse $responseInstance = null;

    /**
     * Acceso al submódulo de respuestas JSON estandarizadas.
     */
    public static function response(): ApiResponse {
        if (static::$responseInstance === null) {
            static::$responseInstance = new ApiResponse();
        }

        return static::$responseInstance;
    }
}

