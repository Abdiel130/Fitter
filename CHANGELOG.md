# Changelog

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/), y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---
## [Unreleased]
### Added
- **Perfil de Usuario:**
  - Consulta de los datos básicos del perfil del usuario autenticado.
  - Seguimiento del asistente de configuración inicial de 5 pasos: el servidor registra cuándo el usuario lo completó, y el cliente puede saber desde el inicio de sesión si debe mostrarlo o no.
  - El cliente puede marcar la configuración como completada (o reiniciarla para volver a mostrar el asistente). Repetir la acción es seguro y conserva la fecha original, lo que permite reintentarla tras sincronizaciones sin conexión.
- **Alta de Cuentas Privada:**
  - Creación de cuentas exclusivamente desde el servidor, sin registro público abierto a internet, acorde al uso personal de la aplicación.
  - Recuperación de contraseña desde el servidor, sin flujo público de restablecimiento. Por seguridad, al restablecerla se cierran las sesiones en todos los dispositivos.
  - Contraseñas robustas obligatorias (mínimo 12 caracteres con mayúsculas, minúsculas y números) con la misma política para alta y recuperación, y correos únicos sin distinción de mayúsculas.
- **Documentación Técnica:**
  - Guía del perfil y la configuración inicial, con flujo recomendado de integración en Android bajo el enfoque Offline-First.
  - Guía de creación de cuentas y recuperación de contraseñas.

### Changed
- Los datos del usuario se devuelven con la misma estructura en inicio de sesión, consulta de sesión y perfil.
- Las fechas del usuario ahora usan el mismo formato ISO 8601 que las fechas de expiración de los tokens.

### Fixed
- Usar un token sin los permisos necesarios (por ejemplo, un token de renovación en rutas operativas) devolvía un error fuera del formato estándar y exponía detalles internos del servidor en modo depuración. Ahora responde con el contrato de error habitual.

## [1.2.0] - 11-09-2026
### Security
- **Actualización Mayor a Laravel 13.x:**
  - Mitigación de vulnerabilidad de inyección CRLF en la validación de correos electrónicos (`CVE-2026-48019`).
  - Mitigación de vulnerabilidad de confusión de rutas en URLs temporales firmadas (`Temporary Signed URL Path Confusion`).
  - Actualización de paquetes del framework a sus versiones parcheadas y seguras (`laravel/framework: ^13.0`).
  - Endurecimiento de seguridad en caché desactivando la deserialización arbitraria de clases no autorizadas (`serializable_classes: false` en `config/cache.php`).

### Changed
- **Entorno y Dependencias:**
  - Elevado el requerimiento base de PHP a `^8.3` compatible con el runtime `sail-8.3/app`.
  - Actualizadas las dependencias oficiales de soporte: `laravel/tinker: ^3.0`, `laravel/pail: ^1.2.5`, `nunomaduro/collision: ^8.6`, `phpunit/phpunit: ^12.0`.
  - Verificado y asegurado el levantamiento del entorno de desarrollo completo bajo Docker Sail con PostgreSQL 16.
### Added
- **Sistema de Autenticación de Doble Token (Offline-First):**
  - Implementación de flujo de sesión desacoplado para clientes móviles sin dependencia de conexión continua.
  - Emisión de tokens de acceso (vigencia de 30 días) para sincronizaciones habituales de rutinas y entrenamientos.
  - Emisión de tokens de renovación de larga duración (vigencia de 180 días) para desbloqueo mediante biometría local en el dispositivo.
  - Mecanismo de rotación estricta de credenciales en cada renovación para mitigar ataques de repetición y sesiones comprometidas.
  - Soporte nativo para identificadores UUID en la persistencia de tokens de acceso personal en base de datos.
  - Endpoints dedicados para inicio de sesión, renovación de credenciales, cierre de sesión selectivo por dispositivo y consulta del perfil activo.
- **Estandarización Global de Respuestas API:**
  - Unificación de todas las respuestas JSON bajo un contrato booleano predecible (`success: true|false`), acompañadas de mensaje descriptivo y carga útil o desglose de errores.
  - Tipado estricto en respuestas y eliminación de códigos de estado numéricos mágicos mediante catálogos semánticos.
  - Normalización automática de errores del framework (validación de formularios, accesos no autorizados y rutas no encontradas) para que siempre respeten el mismo formato ante el cliente móvil.
- **Documentación Técnica de Sesión y Biometría Móvil:**
  - Guía integral de integración para Android que cubre el almacenamiento seguro de credenciales en hardware (*Keystore*), integración con el sensor biométrico y recuperación transparente de solicitudes de sincronización interrumpidas.

## [1.0.0] - 06-09-2026
### Added
- **Pivote Completo de Arquitectura:** Transición del repositorio a Servidor API de Autenticación y Sincronización (**Sync & Backup Server**) en **Laravel 11** + **PostgreSQL 16**.
- **Entorno Docker Sail:** Configuración de `docker-compose.yml` y script wrapper `./sail` para desarrollo local en Docker con soporte nativo de PostgreSQL (`pgsql`).
- **Migraciones de Base de Datos PostgreSQL (Modular):** Migración completa de las 27 tablas definidas en `Fitter.sql`, dividida en 29 archivos individuales bajo `database/migrations/` (una migración por tabla/relación) en reemplazo del esquema monolítico inicial:
  - `users`, `routine`, `workout`, `routine_workouts`
  - `exercises`, `workout_exercises`, `workout_sets`, `exercise_substitutes`
  - `body_parts`, `muscles`, `equipments`, `exercise_body_parts`, `exercise_equipments`, `exercise_muscles`
  - `workout_logs`, `workout_log_sets`
  - `body_measurement`, `progress_photos`
  - `nutrition_targets`, `food_items`, `recipes`, `recipe_items`, `daily_food_logs`
  - `daily_habit_logs`, `supplements`, `daily_supplement_logs`, `joint_discomfort_logs`
- **Modelos Eloquent Completos:** Creación de los 24 modelos correspondientes al esquema completo, con soporte nativo para UUIDs (`HasUuids`), `$fillable`/`$casts` y relaciones (`belongsTo`, `hasMany`, `belongsToMany`) totalmente mapeadas entre rutinas, ejercicios, registros de entrenamiento, nutrición y hábitos.
- **Documentación:** Redacción del nuevo `README.md` detallando la arquitectura **Offline-First**, guías de instalación con Sail y especificaciones del dominio del proyecto.

### Removed
- Eliminación de la estructura heredada `client/` (React/Vite) y `server/` (Node/Express/Mongo).
