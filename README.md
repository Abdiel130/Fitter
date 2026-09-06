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
   - **Autenticación:** Gestión segura de usuarios y sesiones vía Laravel Sanctum.
   - **Sincronización & Respaldo CRUD:** Endpoint API unificado para respaldar la información del usuario en PostgreSQL y sincronizar cambios entre múltiples dispositivos sin pérdida de datos.

---

## 💡 Dominio del Proyecto y Funcionalidades Respaldadas

El modelo de base de datos en PostgreSQL ([Fitter.sql](file:///home/abdiel/projects/personal/Fitter/Fitter.sql)) y el servidor API respaldan las siguientes áreas funcionales:

### 🏋️ Semanals Workouts (Rutinas & Secuencias Rotativas)
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
- **Base de Datos:** PostgreSQL 16 (con extensión `uuid-ossp`)
- **Autenticación:** Laravel Sanctum
- **Entorno de Desarrollo:** Docker & Laravel Sail (`./sail`)

---

## 🚀 Instalación y Arranque (Entorno de Desarrollo)

### 📋 Requisitos Previos

1. **Docker Desktop:** Instalado y activo (recomendado con integración WSL2 en Windows).
2. **Git:** Para clonar el repositorio.

---

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/Fitter.git
cd Fitter
```

---

### 2. Configurar Variables de Entorno

Copia el archivo de ejemplo `.env.example` a `.env`:

```bash
cp .env.example .env
```

Las variables principales de conexión a PostgreSQL ya vienen preconfiguradas para Sail:

```env
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=fitter
DB_USERNAME=sail
DB_PASSWORD=password
```

---

### 3. Levantar los Contenedores con Laravel Sail

Desde la raíz del proyecto, ejecuta el script wrapper `./sail`:

```bash
# Levantar servicios en segundo plano
./sail up -d
```

> **💡 Nota:** En proyectos con Laravel Sail, nunca ejecutes `php artisan` en el host local. Utiliza siempre `./sail artisan <comando>`.

---

### 4. Ejecutar las Migraciones de Base de Datos

Una vez que el contenedor de PostgreSQL esté listo, ejecuta las migraciones para crear las 27 tablas del modelo de datos:

```bash
./sail artisan migrate
```

---

## 📡 Endpoints Principales (API)

El servidor expone rutas bajo `/api/`:

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/` | Estado general de la API y versión del servidor |
| `GET` | `/api/health` | Healthcheck de conectividad |
| `POST` | `/api/login` | Autenticación y emisión de Token Sanctum |
| `GET` | `/api/user` | Datos del usuario autenticado |
| `POST` | `/api/sync` | Endpoint de sincronización masiva para clientes offline-first |

---

## 🗄️ Modelo de Base de Datos (`Fitter.sql`)

El esquema de la base de datos PostgreSQL se encuentra documentado e impulsado por las migraciones en [database/migrations/2026_09_06_000001_create_fitter_schema.php](file:///home/abdiel/projects/personal/Fitter/database/migrations/2026_09_06_000001_create_fitter_schema.php) y el archivo de referencia [Fitter.sql](file:///home/abdiel/projects/personal/Fitter/Fitter.sql).

---

## 📝 Registro de Cambios

Consulta el archivo [CHANGELOG.md](file:///home/abdiel/projects/personal/Fitter/CHANGELOG.md) para ver la evolución del proyecto.