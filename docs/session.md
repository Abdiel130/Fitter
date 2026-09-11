# 🔐 Documentación de Autenticación, Sesión y Renovación Biométrica (Fitter API)

Este documento detalla la arquitectura de autenticación, el ciclo de vida de los tokens de acceso y la integración con el cliente móvil Android bajo el paradigma **Offline-First**.

---

## 1. Visión General y Filosofía

Fitter opera como un ecosistema **Offline-First**:
* La aplicación móvil Android ejecuta entrenamientos, temporizadores, descansos y registros de peso en su base de datos local (Room / SQLite) **sin requerir conexión a internet**.
* La API del servidor (`fitter-backend`) actúa como nodo de respaldo y sincronización entre dispositivos.
* La autenticación está construida sobre **Laravel Sanctum**, adaptada para claves primarias en formato **UUID** y desacoplada de sesiones por cookies.

### El Reto de la Seguridad Móvil vs Experiencia de Usuario
Expirar la sesión cada pocas horas arruinaría la experiencia de un usuario entrenando en un sótano o gimnasio sin señal. Sin embargo, tener un token perpetuo e infinito sin posibilidad de renovación segura representaría un riesgo de seguridad.

Para resolver esto sin requerir que el usuario ingrese su contraseña constantemente, se diseñó un **esquema de doble token (Access Token + Refresh Token rotativo)** complementado con la seguridad del **Android Keystore y Biometría**.

---

## 2. Ciclo de Vida de los Tokens

| Propiedad | Access Token | Refresh Token |
|---|---|---|
| **Vigencia** | **30 días** | **180 días (6 meses)** |
| **Habilidad (*Ability*)** | `['access-api']` | `['issue-token']` |
| **Uso Permitido** | Rutas operativas (`/api/routines`, `/api/workouts`, etc.) | **Exclusivamente** `POST /api/auth/refresh` |
| **Almacenamiento Android** | `EncryptedSharedPreferences` / DataStore | Protegido en hardware por **Android Keystore** |
| **Factor de Desbloqueo** | Automático en requests con red | **Biometría obligatoria** (`BiometricPrompt`) |
| **Mecanismo de Rotación** | Se reexpide al renovar | **Rotación estricta**: se destruye en BD al usarse |

### ¿Por qué este modelo es seguro ante tokens vencidos?
1. **La biometría nunca viaja por la red**: El sensor de huella o rostro de Android valida la identidad de forma local en el hardware del teléfono (*TEE / Secure Enclave*).
2. **El candado físico**: El chip de Android se niega físicamente a liberar el `refresh_token` a menos que el usuario verifique su huella digital (`setUserAuthenticationRequired(true)`).
3. **Rotación obligatoria en servidor**: Cuando el móvil presenta el `refresh_token` al endpoint `POST /api/auth/refresh`, el backend lo **destruye inmediatamente de la base de datos** y genera un nuevo par de tokens. Si un token usado se vuelve a presentar, es rechazado inmediatamente.

---

## 2.1. Contrato Estándar de Respuestas JSON (`Api::response()`)

Todas las respuestas de la API siguen un formato predecible y fuertemente tipado:

### Formato de Éxito (`success: true`):
```json
{
  "success": true,
  "message": "Operación exitosa (opcional)",
  "data": { ... } // Objeto o array de datos, o null
}
```

### Formato de Error (`success: false`):
```json
{
  "success": false,
  "message": "Descripción clara del motivo del fallo",
  "errors": { ... } // Mapa de errores de validación (opcional)
}
```

En Android (Kotlin), puedes mapear todas las respuestas a un DTO genérico:
```kotlin
data class ApiResponse<T>(
    val success: Boolean,
    val message: String? = null,
    val data: T? = null,
    val errors: Map<String, List<String>>? = null
)
```

---

## 3. Especificación de Endpoints

### 3.1. Iniciar Sesión (`POST /api/auth/login`)
Autentica al usuario mediante correo y contraseña. Emite el par de tokens (`access_token` y `refresh_token`) vinculados al dispositivo.

* **Método:** `POST`
* **URL:** `/api/auth/login`
* **Acceso:** Público
* **Headers Requeridos:**
  * `Content-Type: application/json`
  * `Accept: application/json`

#### Body de la Solicitud (JSON):
```json
{
  "email": "usuario@ejemplo.com",
  "password": "miPasswordSeguro123",
  "device_name": "Google Pixel 8 - Android"
}
```

| Campo | Tipo | Obligatorio | Descripción |
|---|---|---|---|
| `email` | String | Sí | Correo electrónico registrado |
| `password` | String | Sí | Contraseña de la cuenta |
| `device_name` | String | No | Nombre del dispositivo para identificar la sesión (default: `Android Device`) |

#### Respuestas:

##### ✅ HTTP 200 OK (Éxito):
```json
{
  "success": true,
  "message": "Inicio de sesión exitoso.",
  "data": {
    "user": {
      "id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
      "name": "Juan Pérez",
      "email": "usuario@ejemplo.com",
      "current_routine": null,
      "active_sequence_index": 1,
      "created_at": "2026-09-06T12:00:00.000000Z"
    },
    "tokens": {
      "token_type": "Bearer",
      "access_token": "1|qW89d...access_token_plain_text",
      "access_token_expires_at": "2026-10-10T20:30:00.000000Z",
      "refresh_token": "2|zK41a...refresh_token_plain_text",
      "refresh_token_expires_at": "2027-03-09T20:30:00.000000Z"
    }
  }
}
```

##### ❌ HTTP 401 Unauthorized (Credenciales incorrectas):
```json
{
  "success": false,
  "message": "Las credenciales proporcionadas son incorrectas.",
  "errors": {
    "email": [
      "Credenciales inválidas. Verifica tu correo y contraseña."
    ]
  }
}
```

##### ❌ HTTP 422 Unprocessable Entity (Validación fallida):
```json
{
  "success": false,
  "message": "Los datos proporcionados no son válidos.",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password field is required."
    ]
  }
}
```

---

### 3.2. Renovación de Tokens con Biometría (`POST /api/auth/refresh`)
Renueva el `access_token` cuando éste ha expirado, utilizando el `refresh_token` desbloqueado por la huella/rostro en Android. 

* **Método:** `POST`
* **URL:** `/api/auth/refresh`
* **Acceso:** Protegido (Requiere `refresh_token` con habilidad `issue-token`)
* **Headers Requeridos:**
  * `Authorization: Bearer <refresh_token>`
  * `Accept: application/json`

#### Body de la Solicitud:
* *Vacío* (no requiere parámetros en el cuerpo).

#### Respuestas:

##### ✅ HTTP 200 OK (Renovación exitosa):
```json
{
  "success": true,
  "message": "Tokens renovados exitosamente.",
  "data": {
    "tokens": {
      "token_type": "Bearer",
      "access_token": "3|aX92k...nuevo_access_token",
      "access_token_expires_at": "2026-10-10T20:45:00.000000Z",
      "refresh_token": "4|mP33y...nuevo_refresh_token",
      "refresh_token_expires_at": "2027-03-09T20:45:00.000000Z"
    }
  }
}
```

##### ❌ HTTP 401 Unauthorized (Refresh token vencido, inválido o inexistente):
```json
{
  "success": false,
  "message": "No autenticado o token inválido/expirado."
}
```
> **Acción en Android:** Si el refresh token falla con `401`, significa que el token de 180 días caducó o fue revocado. Se debe redirigir al usuario al formulario de login con contraseña.

##### ❌ HTTP 403 Forbidden (Se envió un Access Token en lugar de un Refresh Token):
```json
{
  "success": false,
  "message": "No autorizado. Se requiere un Refresh Token válido para esta operación."
}
```

---

### 3.3. Cerrar Sesión (`POST /api/auth/logout`)
Revoca los tokens del dispositivo activo (o de todos los dispositivos si se especifica).

* **Método:** `POST`
* **URL:** `/api/auth/logout`
* **Acceso:** Protegido (Requiere `access_token` o `refresh_token`)
* **Headers Requeridos:**
  * `Authorization: Bearer <token>`
  * `Accept: application/json`

#### Body de la Solicitud (Opcional):
```json
{
  "all_devices": false
}
```

| Campo | Tipo | Obligatorio | Descripción |
|---|---|---|---|
| `all_devices` | Boolean | No | Si es `true`, revoca todas las sesiones y dispositivos del usuario en la base de datos. Por defecto es `false` (solo revoca los tokens de este dispositivo). |

#### Respuestas:

##### ✅ HTTP 200 OK:
```json
{
  "success": true,
  "message": "Sesión cerrada correctamente en este dispositivo."
}
```

---

### 3.4. Obtener Perfil de Usuario (`GET /api/auth/me` o `GET /api/user`)
Retorna los datos del usuario autenticado y los detalles de su rutina activa.

* **Método:** `GET`
* **URL:** `/api/auth/me` (alias: `/api/user`)
* **Acceso:** Protegido (Requiere `access_token` con habilidad `access-api`)
* **Headers Requeridos:**
  * `Authorization: Bearer <access_token>`
  * `Accept: application/json`

#### Respuestas:

##### ✅ HTTP 200 OK:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
      "name": "Juan Pérez",
      "email": "usuario@ejemplo.com",
      "current_routine": "6a3feb12-2c5d-4f1e-9134-11e2f9d50123",
      "active_sequence_index": 2,
      "created_at": "2026-09-06T12:00:00.000000Z",
      "current_routine_details": {
        "id": "6a3feb12-2c5d-4f1e-9134-11e2f9d50123",
        "name": "PPL Hipertrofia 4 Días",
        "description": "Rutina Push Pull Legs con enfoque dorsal"
      }
    }
  }
}
```

---

## 4. Guía de Implementación para Android (Kotlin)

### 4.1. Almacenamiento Seguro de Tokens
1. **`access_token`**: Almacenar en `EncryptedSharedPreferences` (de la librería `androidx.security:security-crypto`).
2. **`refresh_token`**: Cifrarlo usando una clave de **Android Keystore** generada con:
   ```kotlin
   val keyGenerator = KeyGenerator.getInstance(KeyProperties.KEY_ALGORITHM_AES, "AndroidKeyStore")
   val keyGenParameterSpec = KeyGenParameterSpec.Builder(
       "fitter_refresh_token_key",
       KeyProperties.PURPOSE_ENCRYPT or KeyProperties.PURPOSE_DECRYPT
   )
   .setBlockModes(KeyProperties.BLOCK_MODE_GCM)
   .setEncryptionPaddings(KeyProperties.ENCRYPTION_PADDING_NONE)
   .setUserAuthenticationRequired(true) // 🔒 Requiere huella/rostro para desencriptar
   .setUserAuthenticationParameters(
       0, // 0 segundos = requiere autenticación biométrica en cada uso
       KeyProperties.AUTH_BIOMETRIC_STRONG
   )
   .build()
   
   keyGenerator.init(keyGenParameterSpec)
   keyGenerator.generateKey()
   ```

---

### 4.2. Flujo del Interceptor de Red (OkHttp / Retrofit)

Para que el usuario no sienta interrupciones al sincronizar, se implementa un `Authenticator` en el cliente OkHttp:

```kotlin
class TokenAuthenticator(
    private val tokenManager: TokenManager,
    private val biometricAuthManager: BiometricAuthManager,
    private val authApiService: AuthApiService
) : Authenticator {

    override fun authenticate(route: Route?, response: Response): Request? {
        // 1. Si ya se intentó renovar y volvió a dar 401, desistir
        if (responseCount(response) >= 2) {
            return null
        }

        // 2. Comprobar si hay conexión antes de solicitar biometría
        if (!isNetworkAvailable()) {
            return null // Deja los datos en cola local (Offline-First)
        }

        // 3. Solicitar huella/rostro al usuario mediante BiometricPrompt
        val biometricSuccess = biometricAuthManager.promptBiometricSync(
            title = "Sesión Expirada",
            subtitle = "Confirma con tu huella para sincronizar tus entrenamientos"
        )

        if (!biometricSuccess) {
            return null // El usuario canceló la biometría
        }

        // 4. Desencriptar el refresh_token desde el Keystore
        val refreshToken = tokenManager.getSecuredRefreshToken() ?: return null

        // 5. Llamar al endpoint POST /api/auth/refresh
        val refreshResponse = authApiService.refreshToken("Bearer $refreshToken").execute()

        if (refreshResponse.isSuccessful && refreshResponse.body() != null) {
            val newTokens = refreshResponse.body()!!.data.tokens
            
            // 6. Guardar los nuevos tokens (rotación aplicada)
            tokenManager.saveTokens(
                newTokens.accessToken,
                newTokens.refreshToken
            )

            // 7. Reintentar la petición original que había fallado de forma transparente
            return response.request.newBuilder()
                .header("Authorization", "Bearer ${newTokens.accessToken}")
                .build()
        }

        // 8. Si el refresh token fue rechazado (401), invalidar sesión local y pedir login
        if (refreshResponse.code() == 401) {
            tokenManager.clearSession()
            // Notificar a la UI para ir a pantalla de login (sin borrar base de datos Room)
        }

        return null
    }

    private fun responseCount(response: Response): Int {
        var count = 1
        var prior = response.priorResponse
        while (prior != null) {
            count++
            prior = prior.priorResponse
        }
        return count
    }
}
```

---

### 4.3. Regla de Oro Offline-First en Android
* **Jamás borres la base de datos local (Room) ante un error 401**. Los entrenamientos, sets, marcas personales y notas tomadas sin conexión deben permanecer resguardados.
* Si el usuario se queda sin sesión, los datos se mantienen a salvo en SQLite local hasta que inicie sesión nuevamente; en ese momento, el proceso de sincronización transferirá los datos pendientes hacia el servidor.
