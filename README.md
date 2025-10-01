<div align="center">
	<h1>📘 Learning English Platform</h1>
	<p>Aplicación web para gestión y seguimiento del aprendizaje de inglés: unidades, lecciones, recursos, ejercicios interactivos y progreso de estudiantes.</p>

	<p>
		<img alt="PHP" src="https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white" />
		<img alt="Laravel" src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white" />
		<img alt="Vue.js" src="https://img.shields.io/badge/Vue.js-3-42b883?logo=vue.js&logoColor=white" />
		<img alt="Inertia.js" src="https://img.shields.io/badge/Inertia.js-2.0-9553e9?logo=inertia&logoColor=white" />
		<img alt="Tailwind CSS" src="https://img.shields.io/badge/TailwindCSS-3-38bdf8?logo=tailwindcss&logoColor=white" />
		<img alt="Vite" src="https://img.shields.io/badge/Vite-7-646CFF?logo=vite&logoColor=white" />
		<img alt="SQLite" src="https://img.shields.io/badge/SQLite-DB-003B57?logo=sqlite&logoColor=white" />
		<img alt="MIT License" src="https://img.shields.io/badge/License-MIT-green?logo=open-source-initiative&logoColor=white" />
	</p>

</div>

---

## 🚀 Descripción General

Learning English es una plataforma tipo LMS ligera enfocada en la práctica guiada (estilo Duolingo) con administración completa de contenidos y seguimiento detallado del avance del estudiante.

La interfaz de estudiante es responsiva y permite resolver ejercicios paso a paso; el administrador gestiona unidades, lecciones, recursos y ejercicios dinámicos con diferentes tipos y soluciones configurables.

## 🧩 Módulos Principales

### 1. Unidades

Contienen: imagen, nombre, descripción, duración estimada y nivel (Básico, Intermedio, Avanzado).

### 2. Niveles

Representan niveles de avance dentro del curso (asociados a las unidades para estructurar el recorrido pedagógico).

### 3. Lecciones

Pertenecen a una unidad. Campos: nombre, referencia a la unidad, descripción. Son el contenedor de los ejercicios.

### 4. Recursos

Archivos asociados a una unidad o lección: PDF, presentaciones, documentos, etc. (gestionados mediante un servicio de archivos).

### 5. Ejercicios

Asociados a una lección. Tipos soportados:
| Tipo | Descripción breve |
|------|-------------------|
| completar diálogo | Se completan partes faltantes de un diálogo |
| completar espacios | Rellenar espacios en blanco |
| emparejar definiciones | Match entre conceptos y definiciones |
| opción múltiple | Seleccionar respuesta correcta |
| ordenar elementos | Rearrangement de secuencias |
| relacionar columnas | Asociación de pares |
| verdadero / falso | Afirmaciones binarias |

El formulario de creación adapta dinámicamente los campos para capturar las soluciones según el tipo elegido.

### 6. Progreso y Seguimiento

El administrador visualiza el avance por estudiante: porcentaje por lección y unidad, estado (En progreso / Completado) y detalle de intentos (correctos, incorrectos, fecha/hora, número de intentos). El estado se marca como completado al resolver correctamente todos los ejercicios de la lección/unidad.

### 7. Vista Estudiante

Flujo:

1. Tarjetas de unidades (resumen y botones de acción).
2. Lista de lecciones de la unidad.
3. Resolución guiada de ejercicios (flujo paso a paso estilo Duolingo).
4. Resumen final + botón Guardar intento.
5. Historial accesible para revisión.

## 👤 Roles y Permisos

Gestionados con `spatie/laravel-permission`.
| Rol | Capacidades |
|-----|-------------|
| Administrador | CRUD completo (unidades, lecciones, recursos, ejercicios, usuarios, revisión de intentos y progreso) |
| Estudiante | Ver unidades/lecciones, resolver ejercicios, consultar su historial y progreso |

Helpers y Traits personalizados: `PermissionHelper`, `PermissionManager`, `HasPermissionCheck` para facilitar validaciones de acceso. Policies específicas para cada modelo (`ExercisePolicy`, `UnitPolicy`, etc.).

## 🛠️ Stack Tecnológico

Backend:

-   PHP 8.2
-   Laravel 12
-   Sanctum (autenticación)
-   Spatie Permission (roles/permisos)
-   Jobs y Queue Listener (proceso background si aplica)

Frontend:

-   Vue 3 (SPA parcial vía Inertia.js)
-   Inertia.js (puente frontend-backend sin API REST explícita inicial)
-   Tailwind CSS + Forms plugin
-   Vite + TypeScript
-   Ziggy (rutas Laravel disponibles en Vue)

Infra / Otros:

-   SQLite (desarrollo rápido) – puede migrarse a MySQL/PostgreSQL.
-   Debugbar (dev)
-   PHPUnit para tests

## 🗃️ Modelos Clave

| Modelo                                | Propósito                                             |
| ------------------------------------- | ----------------------------------------------------- |
| Unit                                  | Agrupa lecciones y recursos, define nivel y metadatos |
| Lesson                                | Contenedor de ejercicios dentro de una unidad         |
| Exercise                              | Define tipo, contenido y soluciones                   |
| UserExerciseAttempt                   | Historial de intentos del estudiante                  |
| UnitUserProgress / LessonUserProgress | Cálculo y caching de avance                           |
| Resource                              | Archivo asociado                                      |
| Level                                 | (Opcional) Clasificación macro del curso              |
| Profile                               | Datos extendidos del usuario                          |

## 🔐 Autenticación

Laravel + Sanctum. Ziggy expone rutas y las vistas Inertia consumen endpoints protegidos. Policies controlan la autorización.

## 📂 Estructura Destacada

```
app/
	Models/ (Unit, Lesson, Exercise, ...)
	Policies/ (UnitPolicy, ExercisePolicy, ...)
	Traits/HasPermissionCheck.php
	Classes/PermissionHelper.php
	Services/FileService.php
resources/
	views/app.blade.php (layout Inertia)
	js/ (componentes Vue + Pages)
database/
	migrations/ (tablas, permisos, progreso)
```

## ⚙️ Instalación (Desarrollo)

### Requisitos

-   PHP ≥ 8.2
-   Composer
-   Node.js ≥ 18
-   SQLite (incluido) o motor alterno

### Pasos Rápidos

```bash
# 1. Clonar
git clone https://github.com/StevenU21/learning-english.git
cd learning-english

# 2. Dependencias backend
composer install

# 3. Copiar variables de entorno
cp .env.example .env   # (En Windows PowerShell: copy .env.example .env)

# 4. Generar key y preparar base
php artisan key:generate
php artisan migrate --seed

# 5. Dependencias frontend
npm install

# 6. Servir (script combinado: PHP + Queue + Vite)
composer run dev
```

Acceso luego en: http://localhost:8000

### Variables .env Relevantes

```
APP_NAME="Learning English"
APP_ENV=local
APP_DEBUG=true
SESSION_DRIVER=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

## 🧪 Tests

Ejecutar:

```bash
composer test
```

Recomendado antes de crear PRs. Añadir pruebas para nuevas reglas de negocio (policies, cálculo de progreso, tipos de ejercicios).

## 📊 Cálculo de Progreso (Resumen Conceptual)

1. Cada intento de ejercicio registra: estado, timestamp, número de intentos.
2. Una lección = completada si todos sus ejercicios están correctos al menos una vez en el último intento guardado.
3. Una unidad = promedio (o 100% si todas las lecciones completas).
4. Se persiste/cached en tablas `LessonUserProgress` y `UnitUserProgress` para evitar recomputar.

## 🧱 Extensibilidad

-   Añadir un nuevo tipo de ejercicio: extender lógica de render en Vue + validación backend + almacenamiento solución.
-   Migrar a API REST: extraer controladores Inertia a controladores API y consumir con Axios.
-   Integrar WebSockets: feedback en tiempo real de progreso.

## 🗺️ Roadmap (Propuesto)

-   [ ] Soporte audio pronunciación por ejercicio
-   [ ] Gamificación (puntos, rachas, logros)
-   [ ] Panel analítico (gráficas de avance global)
-   [ ] Exportación PDF del historial del estudiante
-   [ ] Notificaciones in-app / email por unidad completada
-   [ ] Modo examen (evaluación cronometrada)

## 📄 Licencia

MIT. Ver `LICENSE`

## 🙌 Agradecimientos

-   Laravel & comunidad open source
-   Paquetes: Spatie Permission, Inertia, Tailwind, Ziggy

---

> "Aprender un idioma abre una puerta a un mundo nuevo."

---