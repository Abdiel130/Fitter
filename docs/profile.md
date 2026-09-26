# 👤 Documentación de Perfil de Usuario y Configuración Inicial (Fitter API)

Este documento cubre la API de **perfil** del usuario autenticado y el mecanismo para que el cliente (Android) sepa si el usuario ya completó el **asistente de configuración inicial de 5 pasos** (*onboarding / setup*), y pueda marcarlo como completado.

> Para login, tokens, renovación biométrica y el contrato general de respuestas (`success`, `message`, `data`, `errors`), consulta [session.md](session.md). Este documento asume que ya lo leíste.

---

## 1. Visión General

| Necesidad del cliente | Cómo se resuelve |
|---|---|
| Obtener los datos básicos del usuario | `GET /api/profile` |
| Saber si el usuario ya completó la configuración inicial | `setup_completed_at` distinto de `null` (o el campo calculado `is_setup_completed`), en el perfil, `login` y `/auth/me` |
| Avisar al servidor que el usuario terminó los 5 pasos | `PATCH /api/profile/setup` con `{"is_setup_completed": true}` |
| Volver a mostrar el asistente (reiniciar configuración) | `PATCH /api/profile/setup` con `{"is_setup_completed": false}` |

### Flujo recomendado en la app

```
            ┌──────────────────────┐
            │ POST /api/auth/login │
            └──────────┬───────────┘
                       │ data.user.is_setup_completed
          ┌────────────┴────────────┐
       false                       true
          │                         │
          ▼                         ▼
 ┌──────────────────┐        ┌─────────────┐
 │ Asistente 5 pasos│        │ Pantalla    │
 │ (Paso 1 … Paso 5)│        │ principal   │
 └────────┬─────────┘        └─────────────┘
          │ al terminar el paso 5
          ▼
 ┌──────────────────────────────────────────────┐
 │ PATCH /api/profile/setup                     │
 │ {"is_setup_completed": true}                 │
 └──────────────────────────────────────────────┘
```

El servidor **solo guarda el estado final** (completado / no completado). El avance paso a paso (en qué paso va el usuario, respuestas parciales) es responsabilidad del cliente y debe persistirse localmente.

---

## 2. Modelo de Datos

Migración: `database/migrations/2026_09_26_000001_add_setup_completed_at_to_users_table.php`

Columna agregada a la tabla `users`:

| Columna | Tipo | Nulo | Default | Descripción |
|---|---|---|---|---|
| `setup_completed_at` | `timestamp` | Sí | `null` | Momento en que el usuario completó el asistente de configuración inicial. **`null` = configuración pendiente** |

**Una sola fuente de verdad:** no existe una columna booleana. El estado se deriva de la fecha:

```
is_setup_completed = (setup_completed_at IS NOT NULL)
```

En el modelo: `User::isSetupCompleted()`. En la API se expone `is_setup_completed` como **campo calculado** (solo de lectura) para que el cliente no tenga que interpretar el `null`.

**Reglas:**
* Todos los usuarios existentes quedan con `setup_completed_at = null` al aplicar la migración (verán el asistente la próxima vez).
* Los usuarios nuevos se crean con `setup_completed_at = null`.
* `setup_completed_at` solo se escribe cuando el estado **cambia** de pendiente a completado. Marcar como completado un usuario que ya lo estaba no modifica la fecha (la operación es idempotente).

---

## 3. Objeto `profile`

Todos los endpoints de este documento (y `login` / `auth/me`) devuelven el mismo objeto base de usuario:

```json
{
  "id": "01a0deeb-534d-713b-9550-07403231cc17",
  "name": "Juan Pérez",
  "email": "usuario@ejemplo.com",
  "current_routine": null,
  "active_sequence_index": 1,
  "is_setup_completed": false,
  "setup_completed_at": null,
  "created_at": "2026-09-26T18:12:39+00:00"
}
```

| Campo | Tipo | Nulo | Descripción |
|---|---|---|---|
| `id` | String (UUID) | No | Identificador único del usuario |
| `name` | String | No | Nombre visible (máx. 100 caracteres) |
| `email` | String | No | Correo usado para iniciar sesión |
| `current_routine` | String (UUID) | Sí | ID de la rutina activa, o `null` si no tiene |
| `active_sequence_index` | Integer | No | Puntero al día de la rutina que toca hoy (1, 2, 3…) |
| `is_setup_completed` | Boolean | No | **Calculado** (no se almacena): `true` si `setup_completed_at` no es `null` |
| `setup_completed_at` | String (ISO 8601) | Sí | Fecha/hora en que se completó la configuración; `null` si está pendiente. Es el dato real guardado en BD |
| `created_at` | String (ISO 8601) | Sí | Fecha de creación de la cuenta |

> Las fechas se devuelven en formato ISO 8601 con zona horaria (`2026-09-26T18:12:39+00:00`), el mismo formato que usan `access_token_expires_at` y `refresh_token_expires_at`.

La serialización vive en `User::toProfileArray()` (`app/Models/User.php`), por lo que login, `/auth/me` y `/profile` siempre devuelven los mismos campos.

---

## 4. Especificación de Endpoints

Todos los endpoints requieren un **access token** (habilidad `access-api`). Un refresh token es rechazado con `403`.

**Headers comunes:**
* `Authorization: Bearer <access_token>`
* `Accept: application/json`
* `Content-Type: application/json` (solo en `PATCH`)

---

### 4.1. Obtener Perfil (`GET /api/profile`)

Devuelve los datos básicos del usuario autenticado, incluido el estado de la configuración inicial.

* **Método:** `GET`
* **URL:** `/api/profile`
* **Acceso:** Protegido (`access_token`, habilidad `access-api`)
* **Body:** Ninguno

#### Ejemplo (cURL)
```bash
curl -X GET http://localhost/api/profile \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <access_token>"
```

#### ✅ HTTP 200 OK
```json
{
  "success": true,
  "data": {
    "profile": {
      "id": "01a0deeb-534d-713b-9550-07403231cc17",
      "name": "Juan Pérez",
      "email": "usuario@ejemplo.com",
      "current_routine": null,
      "active_sequence_index": 1,
      "is_setup_completed": false,
      "setup_completed_at": null,
      "created_at": "2026-09-26T18:12:39+00:00"
    }
  }
}
```

> **`/api/profile` vs `/api/auth/me`:** ambos devuelven el mismo perfil base. `/auth/me` además incluye `current_routine_details` (el objeto completo de la rutina activa) bajo la llave `data.user`. `/profile` devuelve solo los datos básicos bajo `data.profile` y es el endpoint recomendado para pantallas de perfil / configuración.

---

### 4.2. Actualizar Estado de Configuración Inicial (`PATCH /api/profile/setup`)

Marca el asistente de configuración inicial como completado (o lo reinicia). Devuelve el perfil actualizado para que el cliente pueda refrescar su caché local con una sola llamada.

* **Método:** `PATCH`
* **URL:** `/api/profile/setup`
* **Acceso:** Protegido (`access_token`, habilidad `access-api`)
* **Idempotente:** Sí. Repetir la misma petición no cambia el resultado ni `setup_completed_at`.

#### Body de la Solicitud
```json
{
  "is_setup_completed": true
}
```

| Campo | Tipo | Obligatorio | Descripción |
|---|---|---|---|
| `is_setup_completed` | Boolean | Sí | `true` para marcar la configuración como completada (el servidor fija `setup_completed_at` con su propia hora), `false` para reiniciarla (`setup_completed_at` vuelve a `null`) (el usuario volverá a ver el asistente). Acepta `true`/`false`, `1`/`0`, `"1"`/`"0"` |

#### Comportamiento

| `setup_completed_at` actual | Valor enviado | Resultado |
|---|---|---|
| `null` | `true` | Se fija a la hora actual del servidor |
| fecha | `true` | Sin cambios: se conserva la fecha original |
| fecha | `false` | Se limpia (`null`) |
| `null` | `false` | Sin cambios: sigue en `null` |

#### Ejemplo (cURL)
```bash
curl -X PATCH http://localhost/api/profile/setup \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <access_token>" \
  -d '{"is_setup_completed": true}'
```

#### ✅ HTTP 200 OK (marcado como completado)
```json
{
  "success": true,
  "message": "Configuración inicial marcada como completada.",
  "data": {
    "profile": {
      "id": "01a0deeb-534d-713b-9550-07403231cc17",
      "name": "Juan Pérez",
      "email": "usuario@ejemplo.com",
      "current_routine": null,
      "active_sequence_index": 1,
      "is_setup_completed": true,
      "setup_completed_at": "2026-09-26T18:12:47+00:00",
      "created_at": "2026-09-26T18:12:39+00:00"
    }
  }
}
```

#### ✅ HTTP 200 OK (configuración reiniciada, `is_setup_completed: false`)
```json
{
  "success": true,
  "message": "Configuración inicial reiniciada.",
  "data": {
    "profile": {
      "...": "...",
      "is_setup_completed": false,
      "setup_completed_at": null
    }
  }
}
```

#### ❌ HTTP 422 Unprocessable Entity (valor faltante o no booleano)
```json
{
  "success": false,
  "message": "Los datos proporcionados no son válidos.",
  "errors": {
    "is_setup_completed": [
      "The is setup completed field must be true or false."
    ]
  }
}
```

---

### 4.3. Errores Comunes (ambos endpoints)

#### ❌ HTTP 401 Unauthorized (sin token, token inválido o expirado)
```json
{
  "success": false,
  "message": "No autenticado o token inválido/expirado."
}
```
> **Acción en Android:** dejar que el `TokenAuthenticator` intente la renovación biométrica (ver [session.md §4.2](session.md)).

#### ❌ HTTP 403 Forbidden (se envió un refresh token en lugar de un access token)
```json
{
  "success": false,
  "message": "No autorizado. El token no tiene permisos para esta operación."
}
```

---

> **Regla de conflicto:** si local dice `true` y el servidor dice `false`, gana `true` (el usuario ya pasó por el asistente en este dispositivo) y se reenvía el `PATCH`. Solo una acción explícita del usuario ("Repetir configuración inicial") debe enviar `false`.

---

## 6. Referencia de Código

| Pieza | Ubicación |
|---|---|
| Rutas | `routes/api.php` (grupo `profile`) |
| Controlador | `app/Http/Controllers/Profile/ProfileController.php` |
| Serialización del perfil | `User::toProfileArray()` en `app/Models/User.php` |
| Estado derivado | `User::isSetupCompleted()` en `app/Models/User.php` |
| Migración | `database/migrations/2026_09_26_000001_add_setup_completed_at_to_users_table.php` |
| Normalización de 403 por habilidad | `bootstrap/app.php` (`AccessDeniedHttpException`) |
