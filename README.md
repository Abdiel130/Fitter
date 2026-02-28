# 🏋️ Fitter - Gym Tracker App (Local-First)

Fitter es una aplicación web progresiva (PWA) diseñada para gestionar rutinas de gimnasio. Construida con una arquitectura **Local-First**, permite registrar ejercicios y rutinas sin conexión a internet utilizando almacenamiento interno del dispositivo, para posteriormente sincronizarse con un servidor en la nube.

---

## 🛠️ Tecnologías (Stack)

### Frontend
- ⚛️ **React + Vite** (TypeScript)
- 🗄️ **Dexie.js** (Base de datos local / IndexedDB)

### Backend
- 🟢 **Node.js v22** (TypeScript)
- 🚂 **Express.js**
- 🍃 **Mongoose**

### Infraestructura & Datos
- 🍃 **MongoDB** (Atlas en Producción / Contenedor en Desarrollo)
- 🐳 **Docker & Docker Compose**
- ☁️ **Render** (Hosting previsto)

---

## 📋 Requisitos Previos

Para correr este proyecto en local de manera óptima, especialmente si estás en Windows, se recomienda usar **WSL2 (Windows Subsystem for Linux)**.

Asegúrate de tener instalado lo siguiente antes de empezar:

1. **Docker Desktop:** Instalado y configurado para integrarse con tu distribución de WSL2.
2. **Git:** Para clonar el repositorio.
3. **NVM (Node Version Manager):** Es crucial instalar Node *dentro* de tu entorno Linux (WSL), no en Windows, para evitar problemas de rutas cruzadas.

```bash
# Instalar NVM (Si no lo tienes)
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
source ~/.bashrc
```

Instala la versión de Node requerida para este proyecto copiando y pegando estos comandos:

```bash
nvm install 22
nvm use 22
nvm alias default 22
```

---

## 🚀 Instalación y Arranque (Entorno de Desarrollo)

### 1. Clonar el repositorio

> **⚠️ IMPORTANTE:** Si usas WSL, asegúrate de clonar el proyecto dentro del sistema de archivos de Linux (ej. `~/projects/`), NO en el disco compartido de Windows (`/mnt/c/...`). Hacerlo en Windows destruirá el rendimiento de los contenedores Docker.

```bash
# Clonar el repositorio
git clone https://github.com/tu-usuario/fitter.git

# Entrar al directorio
cd fitter
```

### 2. Levantar el entorno con Docker Compose

El proyecto incluye un entorno pre-configurado. En la raíz del proyecto, ejecuta:

```bash
docker compose up --build
```

> **💡 Nota:** Usa la bandera `--build` siempre que agregues nuevas dependencias en el `package.json` para que Docker reconstruya las imágenes adecuadamente.

### 3. Acceder a la aplicación

Una vez que los contenedores estén corriendo, los servicios estarán disponibles en:

- 📱 **Frontend (React PWA):** [http://localhost:5173](http://localhost:5173)
- 🔌 **Backend (API Node):** [http://localhost:3000](http://localhost:3000)
- 💽 **Base de datos Local (MongoDB):** `mongodb://127.0.0.1:27017/fitter`

---

## 🧩 Extensiones Recomendadas para VS Code

Para la mejor experiencia de desarrollo manejando WSL y contenedores, se recomienda instalar las siguientes extensiones:

| Extensión | Descripción |
|-----------|-------------|
| **[WSL](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-wsl)** | Permite editar el código alojado en Linux nativamente desde Windows. |
| **[Docker](https://marketplace.visualstudio.com/items?itemName=ms-azuretools.vscode-docker)** | Gestiona los contenedores, imágenes y revisa logs fácilmente. |
| **[MongoDB for VS Code](https://marketplace.visualstudio.com/items?itemName=mongodb.mongodb-vscode)** | Visualiza los datos conectándote a `mongodb://127.0.0.1:27017/`. |
| **[Error Lens](https://marketplace.visualstudio.com/items?itemName=usernamehw.errorlens)** | Muestra errores de tipado de TypeScript directo sobre la línea de código. |
| **[Prettier - Code formatter](https://marketplace.visualstudio.com/items?itemName=esbenp.prettier-vscode)** | Formatea el código automáticamente al guardar. |
| **[ES7+ React/Redux/React-Native snippets](https://marketplace.visualstudio.com/items?itemName=dsznajder.es7-react-js-snippets)** | Acelera la creación de componentes escribiendo comandos rápidos como `rfce`. |

---

## 🔧 Solución de Problemas Frecuentes

<details>
<summary><strong>1. Vite no detecta los cambios al guardar (Frontend)</strong></summary>

<br>

El archivo `docker-compose.yml` ya incluye `CHOKIDAR_USEPOLLING=true` para forzar a Vite a escuchar los cambios en los volúmenes de Docker. Si aún así falla, verifica que el proyecto esté alojado en el sistema de archivos de Linux (`~`).
</details>

<details>
<summary><strong>2. Error en Docker: <code>ENOENT: no such file or directory, open '/app/package.json'</code></strong></summary>

<br>

Este error ocurre si alteras el orden de los `Dockerfile.dev`. Asegúrate de que las instrucciones estén en este estricto orden para aprovechar el caché:

```dockerfile
COPY package*.json ./
RUN npm install
COPY . .
```
</details>

<details>
<summary><strong>3. Conflictos al instalar paquetes desde la terminal de WSL</strong></summary>

<br>

Si comandos como `npm create` o `npm install` lanzan errores extraños de rutas hacia `C:\Users\...`, significa que WSL está usando el ejecutable de Node de tu Windows. Verifica que apunte a un directorio de NVM (`~/.nvm/...`) usando:

```bash
which npm
```
</details>