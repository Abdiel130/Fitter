<?php

namespace App\Support\Api;

/**
 * Códigos de estado HTTP más comunes para la API de Fitter.
 * Evita el uso de magic numbers y provee tipado estricto en PHP 8.2+.
 */
enum HttpStatus: int {
    // 2xx Success
    case OK = 200;
    case CREATED = 201;
    case ACCEPTED = 202;
    case NO_CONTENT = 204;

    // 4xx Client Errors
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case CONFLICT = 409;
    case UNPROCESSABLE = 422;
    case TOO_MANY_REQUESTS = 429;

    // 5xx Server Errors
    case INTERNAL_ERROR = 500;
    case SERVICE_UNAVAILABLE = 503;
}
