<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Reports Manage API',
    description: 'API v1 con autenticacion hibrida: web (sesion/cookie Sanctum) y mobile (Bearer token).'
)]
#[OA\Server(
    url: '/api/v1',
    description: 'API v1'
)]
#[OA\Tag(name: 'Auth', description: 'Autenticacion de usuarios')]
#[OA\Tag(name: 'Users', description: 'Gestion de usuarios')]
#[OA\Tag(name: 'Roles', description: 'Gestion de roles')]
#[OA\Tag(name: 'Permissions', description: 'Gestion de permisos')]
#[OA\Tag(name: 'Instituciones', description: 'Gestion de instituciones')]
#[OA\Tag(name: 'Personas', description: 'Gestion de personas')]
#[OA\Tag(name: 'Ambitos', description: 'Gestion de ambitos')]
#[OA\Tag(name: 'TiposBien', description: 'Gestion de tipos de bien')]
#[OA\Tag(name: 'Bienes', description: 'Gestion de bienes')]
#[OA\Tag(name: 'Productos', description: 'Gestion de productos')]
#[OA\Tag(name: 'Informes', description: 'Gestion de informes')]
#[OA\Tag(name: 'Observaciones', description: 'Gestion de observaciones')]
#[OA\Tag(name: 'Bitacoras', description: 'Gestion de bitacoras')]
#[OA\Tag(name: 'Ubicaciones', description: 'Gestion de ubicaciones')]
#[OA\Tag(name: 'Adjuntos', description: 'Gestion de adjuntos y archivos')]
#[OA\Tag(name: 'ActivityLog', description: 'Registro de actividad del sistema')]
#[OA\SecurityScheme(
    securityScheme: 'BearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Enviar Authorization: Bearer <token> para clientes mobile o terceros.'
)]
#[OA\SecurityScheme(
    securityScheme: 'SanctumCookie',
    type: 'apiKey',
    in: 'cookie',
    name: 'laravel_session',
    description: 'Sesion web de Sanctum. Para mutaciones, incluir cabecera X-XSRF-TOKEN luego de /sanctum/csrf-cookie.'
)]
class OpenApiSpec
{
}
