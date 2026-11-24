# Reports Manage API

API REST para gestión de informes, construida con **Laravel 13 / PHP 8.5** y autenticación híbrida via **Laravel Sanctum**.

Documentación interactiva (Swagger UI): [`/api/documentation`](http://localhost/api/documentation)

---

## Tabla de contenidos

1. [Requisitos](#requisitos)
2. [Instalación y arranque](#instalación-y-arranque)
3. [Autenticación](#autenticación)
4. [Formato de respuesta](#formato-de-respuesta)
5. [Códigos de error](#códigos-de-error)
6. [Paginación](#paginación)
7. [Ordenamiento](#ordenamiento)
8. [Búsqueda](#búsqueda)
9. [Filtros](#filtros)
10. [Includes (relaciones)](#includes-relaciones)
11. [Soft deletes](#soft-deletes)
12. [Rate limiting](#rate-limiting)
13. [Permisos](#permisos)
14. [Recursos disponibles](#recursos-disponibles)

---

## Requisitos

- Docker + Docker Compose (Laravel Sail)
- PHP 8.5
- Node.js (para assets front si aplica)

---

## Instalación y arranque

```bash
cp .env.example .env
docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/var/www/html" -w /var/www/html laravelsail/php85-composer:latest composer install --no-interaction
vendor/bin/sail up -d --wait
vendor/bin/sail artisan key:generate --no-interaction
vendor/bin/sail artisan migrate --seed --no-interaction
```

El entorno de Sail y los tests utilizan MySQL 8.4. Para crear el administrador inicial, configurar `ADMIN_EMAIL` y `ADMIN_PASSWORD` en `.env` antes del seed; la contraseña debe tener al menos 16 caracteres. Si ambas variables están vacías, no se crea ningún administrador. Los datos de demostración se generan únicamente en `local` y `testing`.

Al actualizar una instalación existente, ejecutar las migraciones, sincronizar el catálogo de permisos y trasladar los adjuntos durante una ventana de mantenimiento:

```bash
vendor/bin/sail artisan down --no-interaction
vendor/bin/sail artisan migrate --no-interaction
vendor/bin/sail artisan db:seed --class=RolesAndPermissionsSeeder --no-interaction
vendor/bin/sail artisan adjuntos:privatize --no-interaction
vendor/bin/sail artisan up --no-interaction
```

El traslado conserva las rutas, verifica que el contenido de la copia privada coincida y solo entonces elimina la copia pública. Es reejecutable; si hay un conflicto entre copias, conserva ambas y termina con error. Corregir cualquier error antes de reabrir la aplicación. Las contraseñas preexistentes no se cambian al ejecutar el seeder.

El seeder del administrador rechaza un email que ya pertenezca a una cuenta no administradora o eliminada; no promueve cuentas preexistentes. Si una instalación conserva cuentas antiguas con contraseñas de demostración, es necesario cambiarlas expresamente.

---

## Autenticación

La API soporta dos modalidades de autenticación que pueden usarse en paralelo.

### Modalidad Mobile / Terceros — Bearer Token

Indicada para apps móviles, integraciones de terceros o cualquier cliente no-browser.

**1. Login → obtener token**

```http
POST /api/v1/auth/mobile/login
Content-Type: application/json

{
	"email": "usuario@example.com",
	"password": "password"
}
```

Respuesta `200 OK`:

```json
{
	"success": true,
	"message": "Logged in successfully.",
	"data": {
		"token": "1|exampleToken...",
		"user": { "id": 1, "name": "Juan Pérez", "email": "usuario@example.com" }
	}
}
```

**2. Usar el token en cada petición**

```http
Authorization: Bearer 1|exampleToken...
```

**3. Logout — revoca únicamente el token usado en esta petición**

```http
POST /api/v1/auth/mobile/logout
Authorization: Bearer 1|exampleToken...
```

---

### Modalidad Web SPA — Sesión / Cookie (Sanctum)

Indicada para frontend SPA en el mismo dominio. Usa `httpOnly` cookies y CSRF, eliminando el riesgo de robo de token via XSS.

**1. Obtener cookie CSRF**

```http
GET /sanctum/csrf-cookie
```

El browser recibirá la cookie `XSRF-TOKEN` automáticamente.

**2. Login**

```http
POST /api/v1/auth/web/login
Content-Type: application/json
X-XSRF-TOKEN: {valor de XSRF-TOKEN cookie}

{
	"email": "usuario@example.com",
	"password": "password"
}
```

Respuesta `200 OK` (no devuelve token, la sesión es la credencial):

```json
{
	"success": true,
	"message": "Logged in successfully.",
	"data": {
		"user": { "id": 1, "name": "Juan Pérez", "email": "usuario@example.com" }
	}
}
```

**3. Peticiones autenticadas**

El browser envía la cookie de sesión automáticamente. Para mutaciones (POST/PUT/PATCH/DELETE) incluir:

```http
X-XSRF-TOKEN: {valor de XSRF-TOKEN cookie}
```

**4. Logout — invalida sesión**

```http
POST /api/v1/auth/web/logout
X-XSRF-TOKEN: {valor de XSRF-TOKEN cookie}
```

---

### Perfil del usuario autenticado

```http
GET    /api/v1/me          → obtener perfil
PUT    /api/v1/me          → actualizar contraseña
PATCH  /api/v1/me          → actualizar contraseña (alias)
```

Solo se permite actualizar `password`. Nombre, email, roles e institución no son modificables desde este endpoint.

---

### Endpoints legacy (compatibilidad)

Los siguientes endpoints se mantienen por compatibilidad con clientes anteriores y son equivalentes a los mobile:

```
POST /api/v1/login   → alias de /api/v1/auth/mobile/login
POST /api/v1/logout  → alias de /api/v1/auth/mobile/logout
```

> ⚠️ Estos endpoints están marcados como deprecated en la documentación Swagger y serán removidos en versiones futuras.

---

## Formato de respuesta

Todas las respuestas siguen un sobre JSON uniforme.

**Éxito:**

```json
{
	"success": true,
	"message": "Request successful.",
	"data": { ... }
}
```

**Error:**

```json
{
	"success": false,
	"message": "Descripción del error.",
	"errors": null
}
```

**Error de validación (422):**

```json
{
	"success": false,
	"message": "The given data was invalid.",
	"errors": {
		"email": ["The email field is required."],
		"password": ["The password field is required."]
	}
}
```

---

## Códigos de error

| Código | Significado                          | Mensaje por defecto                            |
|--------|--------------------------------------|------------------------------------------------|
| `401`  | No autenticado                       | `Unauthenticated.`                             |
| `403`  | Sin permiso                          | `This action is unauthorized.`                 |
| `404`  | Recurso no encontrado                | `Resource not found.`                          |
| `405`  | Método HTTP no permitido             | `Method not allowed.`                          |
| `422`  | Error de validación                  | `The given data was invalid.`                  |
| `429`  | Rate limit excedido                  | `Too many requests. Please try again later.`   |
| `500`  | Error interno del servidor           | `Server error.`                                |

---

## Paginación

Todos los endpoints de listado (`GET /api/v1/{recurso}`) devuelven resultados paginados.

| Parámetro  | Tipo    | Por defecto | Máximo | Descripción              |
|------------|---------|-------------|--------|--------------------------|
| `per_page` | integer | 15          | 100    | Resultados por página    |
| `page`     | integer | 1           | —      | Número de página         |

**Ejemplo:**

```http
GET /api/v1/informes?per_page=25&page=2
```

**Estructura de respuesta paginada:**

```json
{
	"success": true,
	"message": "...",
  "data": [ ... ],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
	}
}
```

---

## Ordenamiento

Usar el parámetro `sort`. Los campos disponibles varían por recurso (ver sección [Recursos disponibles](#recursos-disponibles)).

Usar `direction=asc` o `direction=desc` para indicar el sentido. El orden por defecto es descendente y los empates se resuelven por `id`:

| Valor       | Equivale a          |
|-------------|---------------------|
| `sort=id&direction=asc`   | ORDER BY id ASC     |
| `sort=id&direction=desc`  | ORDER BY id DESC    |

**Ejemplo:**

```http
GET /api/v1/informes?sort=created_at&direction=desc
GET /api/v1/users?sort=name&direction=asc
```

---

## Búsqueda

El parámetro `search` realiza búsqueda por texto libre sobre los campos principales del recurso (título, nombre, descripción, email, etc.).

```http
GET /api/v1/informes?search=expediente+2024
GET /api/v1/users?search=juan
```

---

## Filtros

El parámetro `filter` acepta filtros específicos por campo. Los campos disponibles varían por recurso.

**Formato array (recomendado):**

```http
GET /api/v1/informes?filter[producto_id]=3&filter[fecha_inicio]=2024-01-01
```

**Ejemplo con múltiples filtros:**

```http
GET /api/v1/bienes?filter[tipobien_id]=2&filter[informe_id]=10
```

Los filtros y campos de `include` o `fields` desconocidos devuelven `422`. Las reglas y consultas se declaran juntas en `app/Support/Query/Definitions`; los Requests de listado comparten la validación y normalización de `IndexRequest`.

`fields` admite una lista separada por comas o un array. La clave primaria y las claves necesarias para resolver los includes se conservan automáticamente.

---

## Includes (relaciones)

El parámetro `include` carga relaciones asociadas al recurso. Se pueden enviar como array o como string separado por comas.

**Formato array:**

```http
GET /api/v1/informes?include[]=producto&include[]=personas&include[]=bienes
```

**Formato string (equivalente):**

```http
GET /api/v1/informes?include=producto,personas,bienes
```

### Includes disponibles por recurso

| Recurso         | Includes disponibles                                                                                                    |
|-----------------|-------------------------------------------------------------------------------------------------------------------------|
| `users`         | `roles`, `permissions`, `institucion`                                                                                   |
| `roles`         | `permissions`                                                                                                           |
| `permissions`   | _(ninguno)_                                                                                                             |
| `instituciones` | _(ninguno)_                                                                                                             |
| `personas`      | `adjuntos`, `informes`                                                                                                  |
| `ambitos`       | `informes`                                                                                                              |
| `tipos-bien`    | `bienes`                                                                                                                |
| `bienes`        | `tipoBien`, `adjuntos`                                                                                                  |
| `productos`     | _(ninguno)_                                                                                                             |
| `informes`      | `producto`, `personas`, `bienes`, `adjuntos`, `ubicaciones`, `observaciones`, `bitacoras`, `ambitos`, `usuarioCreador` |
| `observaciones` | `informe`, `usuario`, `cerradaPor`, `comentarios`                                                                          |
| `bitacoras`     | `informe`                                                                                                               |
| `ubicaciones`   | `informe`                                                                                                               |
| `adjuntos`      | _(ninguno)_                                                                                                             |

---

## Soft deletes

Los recursos que soportan eliminación lógica permiten los siguientes parámetros en los listados:

| Parámetro      | Valor  | Comportamiento                     |
|----------------|--------|------------------------------------|
| `only_trashed` | `true` | Devuelve solo registros eliminados |
| _(ninguno)_    | —      | Devuelve solo registros activos    |

Para restaurar un registro eliminado:

```http
POST /api/v1/{recurso}/{id}/restore
```

Requiere el permiso `{recurso}.restore`.

Al eliminar un informe, sus hijos activos se identifican con un lote de borrado. Restaurar ese informe recupera únicamente los hijos de ese lote; conserva eliminados los borrados previamente de forma independiente. Los informes eliminados antes de incorporar los lotes se restauran sin recuperar automáticamente sus hijos, cuyo origen de borrado requiere revisión.

Desvincular una persona elimina su registro de forma lógica únicamente si se quitó un vínculo existente y ya no tiene informes activos.

---

## Rate limiting

| Contexto       | Límite             | Clave de throttling                  |
|----------------|--------------------|--------------------------------------|
| Login          | 5 peticiones/min   | Por email (o IP si no hay email)     |
| API protegida  | 60 peticiones/min  | Por usuario autenticado o IP         |

Al exceder el límite, la API devuelve `429 Too Many Requests`.

---

## Permisos

Los endpoints protegidos requieren permisos específicos asignados al usuario. El patrón de naming es `{recurso}.{accion}`.

La asignación de roles requiere adicionalmente `users.assign-roles`. Un usuario solo puede delegar permisos que ya posee y administrar usuarios o roles cuyos permisos están dentro de los suyos. El nombre `super-admin` no concede privilegios por sí solo: el seeder asigna los permisos efectivos a ese rol.

El catálogo integrado se define en `App\Support\Authorization\PermissionCatalog`. Sus permisos no pueden renombrarse ni eliminarse por API. Los endpoints de permisos personalizados mantienen controles de delegación. Los cambios de usuarios/roles y sus asociaciones son transaccionales.

Al añadir capacidades en aplicaciones derivadas, extender el catálogo junto a sus rutas y volver a ejecutar `RolesAndPermissionsSeeder`. La protección de cuentas y roles contempla también sus privilegios eliminados para impedir apropiarse de una cuenta antes de que estos se restauren.

Los adjuntos se guardan en el disco privado `attachments` y se sirven mediante los endpoints autorizados `download` y `stream`. Su ruta `url` es de solo lectura; solo la descripción se modifica por API. Cada carga admite hasta 20 imágenes de 10MB cada una y revierte registros y archivos ante fallos parciales.

Para colecciones grandes, utilizar los listados paginados de observaciones, bitácoras, ubicaciones y adjuntos con `filter[informe_id]`. El endpoint de edición de informes conserva su contrato de carga completa para los clientes existentes.

**Acciones estándar:**

| Acción    | Descripción                    |
|-----------|--------------------------------|
| `view`    | Listar y ver detalle           |
| `create`  | Crear nuevo registro           |
| `update`  | Actualizar registro existente  |
| `delete`  | Eliminar registro (soft)       |
| `restore` | Restaurar registro eliminado   |

**Ejemplo de permisos para `informes`:**

```
informes.view
informes.create
informes.update
informes.delete
informes.restore
```

---

## Recursos disponibles

Todos los recursos siguen el prefijo `/api/v1/`. Los campos de `sort` y `filter` disponibles se detallan a continuación.

### Users — `/api/v1/users`

| Parámetro               | Valores                                                              |
|-------------------------|----------------------------------------------------------------------|
| `sort`                  | `id`, `name`, `email`, `email_verified_at`, `created_at`, `updated_at` |
| `filter[name]`          | string                                                               |
| `filter[email]`         | string                                                               |
| `filter[institucion_id]` | integer                                                             |
| `filter[role]`          | string                                                               |
| `filter[permission]`    | string                                                               |

### Roles — `/api/v1/roles`

| Parámetro      | Valores                                  |
|----------------|------------------------------------------|
| `sort`         | `id`, `name`, `created_at`, `updated_at` |
| `filter[name]` | string                                   |

### Permissions — `/api/v1/permissions`

| Parámetro      | Valores                                  |
|----------------|------------------------------------------|
| `sort`         | `id`, `name`, `created_at`, `updated_at` |
| `filter[name]` | string                                   |

### Instituciones — `/api/v1/instituciones`

| Parámetro        | Valores                                  |
|------------------|------------------------------------------|
| `sort`           | `id`, `nombre`, `created_at`, `updated_at` |
| `filter[nombre]` | string                                   |

### Personas — `/api/v1/personas`

| Parámetro           | Valores                                                         |
|---------------------|-----------------------------------------------------------------|
| `sort`              | `id`, `nombre`, `apellido`, `ci`, `created_at`, `updated_at`   |
| `filter[nombre]`    | string                                                          |
| `filter[apellido]`  | string                                                          |
| `filter[ci]`        | string                                                          |
| `filter[informe_id]` | integer                                                        |

### Ambitos — `/api/v1/ambitos`

| Parámetro        | Valores                                                    |
|------------------|------------------------------------------------------------|
| `sort`           | `id`, `nombre`, `descripcion`, `created_at`, `updated_at`  |
| `filter[nombre]` | string                                                     |

### Tipos de Bien — `/api/v1/tipos-bien`

| Parámetro             | Valores                                                    |
|-----------------------|------------------------------------------------------------|
| `sort`                | `id`, `nombre`, `descripcion`, `created_at`, `updated_at`  |
| `filter[nombre]`      | string                                                     |
| `filter[descripcion]` | string                                                     |

### Bienes — `/api/v1/bienes`

| Parámetro              | Valores                                                          |
|------------------------|------------------------------------------------------------------|
| `sort`                 | `id`, `descripcion`, `tipobien_id`, `created_at`, `updated_at`   |
| `filter[descripcion]`  | string                                                           |
| `filter[tipoBien]`     | string                                                           |
| `filter[tipobien_id]`  | integer                                                          |
| `filter[informe_id]`   | integer                                                          |

### Productos — `/api/v1/productos`

| Parámetro        | Valores                                                    |
|------------------|------------------------------------------------------------|
| `sort`           | `id`, `nombre`, `descripcion`, `created_at`, `updated_at`  |
| `filter[nombre]` | string                                                     |

### Informes — `/api/v1/informes`

| Parámetro               | Valores                                                                              |
|-------------------------|--------------------------------------------------------------------------------------|
| `sort`                  | `id`, `expediente`, `titulo`, `producto_id`, `fecha_inicio`, `fecha_final`, `created_at`, `updated_at` |
| `filter[expediente]`    | string                                                                               |
| `filter[titulo]`        | string                                                                               |
| `filter[producto]`      | string                                                                               |
| `filter[producto_id]`   | integer                                                                              |
| `filter[fecha_inicio]`  | date (`Y-m-d`)                                                                       |
| `filter[fecha_final]`   | date (`Y-m-d`)                                                                       |

Endpoints adicionales:

```http
GET    /api/v1/informes/{id}/edit             → datos completos para edición
POST   /api/v1/informes/{id}/personas         → asociar persona
DELETE /api/v1/informes/{id}/personas/{id}    → desasociar persona
POST   /api/v1/informes/{id}/bienes           → asociar bien
DELETE /api/v1/informes/{id}/bienes/{id}      → desasociar bien
```

### Observaciones — `/api/v1/observaciones`

| Parámetro             | Valores                                                       |
|-----------------------|---------------------------------------------------------------|
| `sort`                | `id`, `descripcion`, `informe_id`, `created_at`, `updated_at` |
| `filter[descripcion]` | string                                                        |
| `filter[informe_id]`  | integer                                                       |

### Bitácoras — `/api/v1/bitacoras`

| Parámetro             | Valores                                                              |
|-----------------------|----------------------------------------------------------------------|
| `sort`                | `id`, `descripcion`, `fecha`, `informe_id`, `created_at`, `updated_at` |
| `filter[descripcion]` | string                                                               |
| `filter[informe_id]`  | integer                                                              |
| `filter[fecha]`       | date (`Y-m-d`)                                                       |

### Ubicaciones — `/api/v1/ubicaciones`

| Parámetro             | Valores                                                       |
|-----------------------|---------------------------------------------------------------|
| `sort`                | `id`, `descripcion`, `informe_id`, `created_at`, `updated_at` |
| `filter[descripcion]` | string                                                        |
| `filter[informe_id]`  | integer                                                       |

### Adjuntos — `/api/v1/adjuntos`

| Parámetro             | Valores                                                              |
|-----------------------|----------------------------------------------------------------------|
| `sort`                | `id`, `descripcion`, `adjuntable_id`, `created_at`, `updated_at`     |
| `filter[descripcion]` | string                                                               |
| `filter[informe_id]`  | integer                                                              |
| `filter[persona_id]`  | integer                                                              |
| `filter[bien_id]`     | integer                                                              |

Endpoints adicionales:

```http
GET /api/v1/adjuntos/{id}/download    → descarga el archivo adjunto (Content-Disposition: attachment)
GET /api/v1/adjuntos/{id}/stream      → transmite el archivo en línea (Content-Disposition: inline, ideal para preview)
```

### Activity Log — `/api/v1/activity-logs`

Solo lectura. Requiere permiso `activity-log.view`.

```http
GET /api/v1/activity-logs
```

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
