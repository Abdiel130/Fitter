# 🔑 Gestión de Cuentas de Usuario (Fitter)

Fitter es una aplicación **personal y privada**. Por diseño **no existe un endpoint público de registro** (`/api/register`): nadie en internet puede crearse una cuenta.

Las cuentas se crean **únicamente desde la consola del servidor**, con un comando de Artisan. Solo quien tiene acceso al servidor (Docker local, SSH al VPS, etc.) puede ejecutarlo.

---

## 1. ¿Por qué un comando de consola y no un endpoint?

| Opción | Superficie de ataque | Comentario |
|---|---|---|
| Registro público (`POST /api/register`) | Alta | Cualquiera puede crear cuentas; requiere captcha, rate limiting, verificación de correo… |
| Registro con código de invitación | Media | El endpoint sigue expuesto; el código puede filtrarse o atacarse por fuerza bruta |
| Endpoint de administración con rol `admin` | Media | Requiere sistema de roles y proteger otra ruta más |
| **Comando Artisan en el servidor** ✅ | **Nula en HTTP** | No hay nada expuesto a la red. El acceso al servidor ya es la barrera de seguridad |

Para una app de un solo dueño, el comando de consola es la opción más simple y segura.

---

## 2. Crear una cuenta

```bash
./sail artisan fitter:user:create
```

El comando pregunta de forma interactiva:

```
 Nombre:
 > Juan Pérez

 Correo electrónico:
 > usuario@ejemplo.com

 Contraseña (mínimo 12 caracteres, mayúsculas, minúsculas y números):
 >

 Confirmar contraseña:
 >

Usuario creado correctamente: usuario@ejemplo.com (01a0deeb-534d-713b-9550-07403231cc17)
```

También se pueden pasar nombre y correo como opciones (la contraseña **siempre** se pide de forma oculta, para que no quede guardada en el historial del shell):

```bash
./sail artisan fitter:user:create --name="Juan Pérez" --email=usuario@ejemplo.com
```

En producción (sin Sail):

```bash
php artisan fitter:user:create
```

### Reglas de validación

| Campo | Reglas |
|---|---|
| Nombre | Obligatorio, máx. 100 caracteres |
| Correo | Obligatorio, formato de email válido, máx. 150 caracteres, **único** (no puede existir otra cuenta con el mismo correo). Se guarda en minúsculas |
| Contraseña | Obligatoria, **mínimo 12 caracteres**, al menos una mayúscula, una minúscula y un número. Debe coincidir con la confirmación |

Si alguna regla falla, el comando muestra los errores y termina con código de salida `1` sin crear nada.

### Estado inicial de la cuenta creada

* `setup_completed_at = null` (configuración pendiente): al iniciar sesión por primera vez, la app mostrará el asistente de configuración inicial (ver [profile.md](profile.md)).
* Sin rutina activa (`current_routine = null`), `active_sequence_index = 1`.
* La contraseña se guarda hasheada (bcrypt vía `Hash::make`).

---

## 3. Después de crear la cuenta

Inicia sesión desde la app, o prueba con cURL:

```bash
curl -X POST http://localhost/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"usuario@ejemplo.com","password":"<tu contraseña>","device_name":"Pixel 8"}'
```

Detalles del login y los tokens en [session.md](session.md).

---

## 4. Referencia de Código

| Pieza | Ubicación |
|---|---|
| Comando | `app/Console/Commands/CreateUserCommand.php` (`fitter:user:create`) |
| Modelo | `app/Models/User.php` |
