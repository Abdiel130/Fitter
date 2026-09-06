# 🏋️ Fitter - Sync & Backup Server API (Laravel + PostgreSQL)

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=for-the-badge&logo=postgresql)](https://www.postgresql.org)
[![Docker Sail](https://img.shields.io/badge/Docker_Sail-Enabled-2496ED?style=for-the-badge&logo=docker)](https://laravel.com/docs/sail)
[![SemVer](https://img.shields.io/badge/Version-1.0.0-blue?style=for-the-badge)](CHANGELOG.md)

**Fitter Server** es la API backend de autenticación, respaldo y sincronización para el ecosistema **Offline-First** de seguimiento de gimnasio, nutrición y hábitos.

---

## 📐 Arquitectura del Sistema (Offline-First)

El ecosistema de Fitter opera con una arquitectura **Offline-First**:

1. **Cliente Móvil (Android / App):**
   - Ejecuta el 100% de la experiencia de usuario sin necesidad de conexión a internet.
   - Procesa la lógica interactiva en tiempo real: temporizadores de descanso, calculadora de 1RM, calculadora de discos y precarga de pesos históricos.
   - Persiste los datos localmente en el dispositivo.

2. **Servidor API Backend (Este Repositorio):**
   - **Autenticación:** Gestión segura de usuarios y tokens vía Laravel Sanctum.
   - **Sincronización & Respaldo CRUD:** Endpoint API unificado para respaldar la información del usuario en PostgreSQL y sincronizar cambios entre múltiples dispositivos sin pérdida de datos.

---

## 💡 Dominio del Proyecto y Funcionalidades Respaldadas

El modelo de base de datos en PostgreSQL ([Fitter.sql](Fitter.sql)) y el servidor API respaldan las siguientes áreas funcionales:

### 🏋️ Semanal Workouts (Rutinas & Secuencias Rotativas)
- **Secuencias Cíclicas:** Creación de rutinas compuestas por secuencias de días de entrenamiento (ej. PPL 4 Días con Enfoque Dorsal).
- **Flexibilidad de Enfoques:** Alternancia entre secuencias semanales (ej. *Semana Enfoque Empuje* vs *Semana Enfoque Jale*).
- **Puntero de Secuencia:** Seguimiento del día que corresponde en el ciclo (`active_sequence_index`).

### 📐 Antropometría y Progreso Físico
- **Historial de Peso Corporal:** Registro diario de peso en ayunas (`weight_kg`).
- **Medidas Corporales Completas:** Seguimiento de cuello, pecho, cintura/ombligo, caderas, bíceps, muslos y pantorrillas.
- **Fotos de Progreso:** Almacenamiento organizado por pose (`FRONT`, `BACK`, `LEFT`, `RIGHT`) vinculadas a cada registro antropométrico.
- **% Grasa Corporal:** Estimación manual o calculada.

### ⚡ Training & Ejercicios
- **Historial Precargado & 1RM:** Almacenamiento de sets completados (`weight_kg`, `reps_completed`, `rpe`, `calculated_1rm`, `is_personal_record`).
- **Tipos de Series:** Soporte para series `NORMAL`, `WARMUP`, `DROPSET` y `FAILURE`.
- **Ejercicios Sustitutos / Variantes:** Mapeo de alternativas cuando una máquina está ocupada (`exercise_substitutes`).
- **Catalogación Completa:** Clasificación por grupo muscular objetivo/secundario, equipamiento utilizado y partes del cuerpo.

### 🥗 Nutrición y Hábitos
- **Consumo de Agua:** Registro de hidratación diaria y meta personal (`water_intake_ml`, `water_target_ml`).
- **Checklist de Suplementación:** Seguimiento diario de toma de suplementos (`daily_supplement_logs`).
- **Banco de Recetas:** Combinación fija de alimentos/ingredientes para registro en un solo bloque.
- **Metas Nutricionales:** Objetivos de calorías y macronutrientes por tipo de día.

### 💤 Recuperación y Fatiga
- **Sueño y Energía:** Registro al despertar en escala 1 a 5 de calidad de descanso y fatiga acumulada.
- **Dolores Articulares:** Bitácora de molestias por zona (`shoulder_left`, `knee_right`, `lumbar`) e intensidad de dolor (1-10).

---

## 🛠️ Stack Tecnológico

- **Framework:** Laravel 11 (PHP 8.3)
- **Base de Datos:** PostgreSQL 16
- **Autenticación:** Laravel Sanctum (tokens API)
- **Entorno de Desarrollo:** Docker & Laravel Sail (`./sail`)

---

## 🚀 Instalación desde cero (Entorno de Desarrollo)

### 📋 Requisitos Previos

1. **Docker Desktop** instalado y activo.
   - En **Windows + WSL2**: activa la integración WSL en *Docker Desktop → Settings → Resources → WSL Integration* y marca tu distro (ej. Ubuntu). Sin esto, el comando `docker` no existe dentro de la distro y `./sail` fallará.
2. **Git** para clonar el repositorio.
3. No necesitas tener PHP, Composer ni PostgreSQL instalados en tu máquina: todo corre dentro de los contenedores.

---

### 1. Clonar el repositorio

```bash
git clone git@github.com:Abdiel130/Fitter.git
cd Fitter
```

---

### 2. Configurar variables de entorno

Copia el archivo de ejemplo `.env.example` a `.env`. **Este archivo nunca se sube a git** (ver [Seguridad](#-seguridad-y-variables-de-entorno)):

```bash
cp .env.example .env
```

Las variables de conexión a PostgreSQL ya vienen preconfiguradas para Sail:

```env
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=fitter
DB_USERNAME=sail
DB_PASSWORD=password
```

> `DB_PASSWORD=password` es válido solo para desarrollo local (el contenedor de Postgres no está expuesto a internet). Nunca reutilices este valor en un entorno real.

---

### 3. Instalar las dependencias de Composer

El repositorio aún no tiene `vendor/` (no se versiona). El script `./sail` incluido detecta esto automáticamente y levanta un contenedor temporal de Composer para instalar todo sin que necesites PHP en tu host:

```bash
./sail composer install
```

---

### 4. Levantar los contenedores

Una vez existe `vendor/`, el wrapper `./sail` pasa a delegar en `vendor/bin/sail` (el binario real de Laravel Sail):

```bash
./sail up -d
```

> **💡 Nota:** En proyectos con Laravel Sail, nunca ejecutes `php artisan` ni `composer` directo en el host. Usa siempre `./sail artisan <comando>` y `./sail composer <comando>`.

---

### 5. Generar la clave de la aplicación

```bash
./sail artisan key:generate
```

---

### 6. Ejecutar las migraciones de base de datos

```bash
./sail artisan migrate
```

---

### 7. Verificar que todo funciona

```bash
curl http://localhost/api/health
# {"status":"ok","timestamp":"..."}
```

---

## 🧯 Comandos útiles

| Comando | Descripción |
|---|---|
| `./sail up -d` | Levanta los contenedores en segundo plano |
| `./sail down` | Detiene y elimina los contenedores |
| `./sail artisan migrate:fresh` | Reinicia la base de datos desde cero |
| `./sail artisan tinker` | Abre una consola interactiva de Laravel |
| `./sail test` | Ejecuta la suite de tests |
| `./sail logs -f` | Sigue los logs de los contenedores |

---

## 🩺 Mi IDE marca todo como error (Illuminate\... not found, etc.)

Esto ocurre porque el autoload de PHP (`vendor/autoload.php`) y las clases del framework solo existen después de instalar las dependencias. Solución:

1. Corre `./sail composer install` (paso 3 de arriba). Esto crea la carpeta `vendor/`.
2. Recarga la ventana/proyecto de tu IDE (en VS Code: `Developer: Reload Window`) para que el servidor de PHP (Intelephense/PHP Intelephense/PHPStorm) reindexe.
3. Si usas VS Code, instala la extensión **PHP Intelephense** y asegúrate de que la carpeta abierta sea la raíz del repo (donde está `composer.json`).

Si después de esto persisten errores, corre `./sail composer dump-autoload` y vuelve a recargar el IDE.

---

## 🔒 Seguridad y variables de entorno

- El `.env` **nunca** se versiona (está en `.gitignore`). Solo `.env.example` se sube al repositorio, con valores de ejemplo/no sensibles.
- Si alguna vez un `.env` real llega a subirse por error a un repositorio público, considera **todas** las credenciales que contenía como comprometidas: regenera `APP_KEY` (`./sail artisan key:generate`), rota contraseñas de base de datos, tokens de terceros (AWS, mail, etc.) y **reescribe el historial de git** para eliminar el commit afectado antes de forzar el push.

---

## 📡 Endpoints Principales (API)

El servidor expone rutas bajo `/api/`:

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/health` | Healthcheck de conectividad |
| `GET` | `/api/user` | Datos del usuario autenticado (requiere token Sanctum) |

---

## 🗄️ Modelo de Base de Datos

El esquema de la base de datos PostgreSQL se encuentra documentado e impulsado por las migraciones en [database/migrations](database/migrations) y el archivo de referencia [Fitter.sql](Fitter.sql).

---

## 📝 Registro de Cambios

Consulta el archivo [CHANGELOG.md](CHANGELOG.md) para ver la evolución del proyecto.
