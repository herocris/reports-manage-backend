<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Informe',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'expediente', type: 'string', example: 'EXP-AMB001'),
        new OA\Property(property: 'titulo', type: 'string', example: 'Informe Ambiental 2024'),
        new OA\Property(property: 'producto_id', type: 'integer', example: 1),
        new OA\Property(property: 'fecha_inicio', type: 'string', format: 'date', example: '2024-01-01'),
        new OA\Property(property: 'fecha_final', type: 'string', format: 'date', example: '2024-12-31'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'creador', ref: '#/components/schemas/AuthUser'),
        new OA\Property(property: 'producto', ref: '#/components/schemas/Producto'),
        new OA\Property(property: 'personas', type: 'array', items: new OA\Items(ref: '#/components/schemas/Persona')),
        new OA\Property(property: 'bienes', type: 'array', items: new OA\Items(ref: '#/components/schemas/Bien')),
        new OA\Property(property: 'ambitos', type: 'array', items: new OA\Items(ref: '#/components/schemas/Ambito')),
    ]
)]
#[OA\Schema(
    schema: 'StoreInformeRequest',
    type: 'object',
    required: ['expediente', 'titulo', 'producto_id', 'fecha_inicio', 'fecha_final'],
    properties: [
        new OA\Property(property: 'expediente', type: 'string', example: 'EXP-AMB001', description: 'Formato: EXP-[A-Z]{3}[0-9]{3}'),
        new OA\Property(property: 'titulo', type: 'string', example: 'Informe Ambiental 2024'),
        new OA\Property(property: 'producto_id', type: 'integer', example: 1),
        new OA\Property(property: 'fecha_inicio', type: 'string', format: 'date', example: '2024-01-01'),
        new OA\Property(property: 'fecha_final', type: 'string', format: 'date', example: '2024-12-31'),
        new OA\Property(property: 'ambitos', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2]),
    ]
)]
#[OA\Schema(
    schema: 'AttachPersonaRequest',
    type: 'object',
    required: ['persona_id'],
    properties: [
        new OA\Property(property: 'persona_id', type: 'integer', example: 1),
    ]
)]
#[OA\Schema(
    schema: 'AttachBienRequest',
    type: 'object',
    required: ['bien_id'],
    properties: [
        new OA\Property(property: 'bien_id', type: 'integer', example: 1),
    ]
)]
class InformeDocs
{
    #[OA\Get(
        path: '/informes',
        tags: ['Informes'],
        summary: 'Listar informes',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: '-created_at')),
            new OA\Parameter(name: 'filter[titulo]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'include', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'producto,creador')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Listado exitoso'),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function index(): void
    {
    }

    #[OA\Post(
        path: '/informes',
        tags: ['Informes'],
        summary: 'Crear informe',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreInformeRequest')
        ),
        responses: [
            new OA\Response(response: 201, description: 'Informe creado', content: new OA\JsonContent(ref: '#/components/schemas/Informe')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 422, description: 'Validacion fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function store(): void
    {
    }

    #[OA\Get(
        path: '/informes/{informe}',
        tags: ['Informes'],
        summary: 'Obtener informe',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'informe', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(ref: '#/components/schemas/Informe')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 404, description: 'No encontrado', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function show(): void
    {
    }

    #[OA\Get(
        path: '/informes/{informe}/edit',
        tags: ['Informes'],
        summary: 'Obtener informe con todas sus relaciones (para edicion)',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'informe', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(ref: '#/components/schemas/Informe')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 404, description: 'No encontrado', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function edit(): void
    {
    }

    #[OA\Put(
        path: '/informes/{informe}',
        tags: ['Informes'],
        summary: 'Actualizar informe',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'informe', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreInformeRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Informe')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 404, description: 'No encontrado', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
            new OA\Response(response: 422, description: 'Validacion fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function update(): void
    {
    }

    #[OA\Delete(
        path: '/informes/{informe}',
        tags: ['Informes'],
        summary: 'Eliminar informe (soft delete)',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'informe', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Eliminado', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 404, description: 'No encontrado', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function destroy(): void
    {
    }

    #[OA\Post(
        path: '/informes/{informe}/restore',
        tags: ['Informes'],
        summary: 'Restaurar informe eliminado',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'informe', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Restaurado', content: new OA\JsonContent(ref: '#/components/schemas/Informe')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 404, description: 'No encontrado', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function restore(): void
    {
    }

    #[OA\Post(
        path: '/informes/{informe}/personas',
        tags: ['Informes'],
        summary: 'Adjuntar persona a informe',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'informe', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AttachPersonaRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Persona adjuntada', content: new OA\JsonContent(ref: '#/components/schemas/Informe')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 404, description: 'No encontrado', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
            new OA\Response(response: 422, description: 'Validacion fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function attachPersona(): void
    {
    }

    #[OA\Delete(
        path: '/informes/{informe}/personas/{persona}',
        tags: ['Informes'],
        summary: 'Desasociar persona de informe',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'informe', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'persona', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Persona desasociada', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 404, description: 'No encontrado', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function detachPersona(): void
    {
    }

    #[OA\Post(
        path: '/informes/{informe}/bienes',
        tags: ['Informes'],
        summary: 'Adjuntar bien a informe',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'informe', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AttachBienRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Bien adjuntado', content: new OA\JsonContent(ref: '#/components/schemas/Informe')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 404, description: 'No encontrado', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
            new OA\Response(response: 422, description: 'Validacion fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function attachBien(): void
    {
    }

    #[OA\Delete(
        path: '/informes/{informe}/bienes/{bien}',
        tags: ['Informes'],
        summary: 'Desasociar bien de informe',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'informe', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'bien', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Bien desasociado', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 404, description: 'No encontrado', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function detachBien(): void
    {
    }
}
