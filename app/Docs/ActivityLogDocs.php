<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ActivityLog',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'log_name', type: 'string', example: 'default'),
        new OA\Property(property: 'description', type: 'string', example: 'created'),
        new OA\Property(property: 'event', type: 'string', example: 'created'),
        new OA\Property(property: 'subject_type', type: 'string', example: 'Informe'),
        new OA\Property(property: 'subject_id', type: 'integer', example: 1),
        new OA\Property(
            property: 'causer',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
            ]
        ),
        new OA\Property(property: 'properties', type: 'object', example: ['attributes' => [], 'old' => []]),
        new OA\Property(property: 'attribute_changes', description: 'Cambios de atributos con contraseñas y tokens de sesión omitidos, incluidos registros históricos.', type: 'object', example: ['attributes' => [], 'old' => []]),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class ActivityLogDocs
{
    #[OA\Get(
        path: '/activity-logs',
        tags: ['ActivityLog'],
        summary: 'Listar registro de actividad',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[subject_type]', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'User')),
            new OA\Parameter(name: 'filter[subject_id]', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[event]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: '-created_at')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Listado exitoso'),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function index(): void {}
}
