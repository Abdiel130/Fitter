# Changelog

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/), y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---
## [unrelease]

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
