<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Adjunto',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'url', type: 'string', format: 'uri', example: 'https://example.com/storage/adjuntos/foto.jpg'),
        new OA\Property(property: 'descripcion', type: 'string', example: 'Foto del predio'),
        new OA\Property(property: 'adjuntable_id', type: 'integer', example: 1),
        new OA\Property(property: 'adjuntable_type', type: 'string', example: 'App\\Models\\Informe'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'StoreAdjuntoRequest',
    type: 'object',
    required: ['tipo', 'modelo_id', 'archivos'],
    properties: [
        new OA\Property(property: 'tipo', type: 'string', enum: ['persona', 'bien', 'informe'], example: 'informe'),
        new OA\Property(property: 'modelo_id', type: 'integer', example: 1),
        new OA\Property(
            property: 'archivos',
            type: 'array',
            items: new OA\Items(type: 'string', format: 'binary'),
            description: 'Imagenes (max 10MB c/u)'
        ),
    ]
)]
class AdjuntoDocs
{
    #[OA\Get(
        path: '/adjuntos',
        tags: ['Adjuntos'],
        summary: 'Listar adjuntos',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[adjuntable_type]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[adjuntable_id]', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
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
        path: '/adjuntos',
        tags: ['Adjuntos'],
        summary: 'Subir adjuntos (multipart/form-data)',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/StoreAdjuntoRequest')
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Adjuntos subidos', content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/Adjunto')
            )),
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
        path: '/adjuntos/{adjunto}',
        tags: ['Adjuntos'],
        summary: 'Obtener adjunto',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'adjunto', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(ref: '#/components/schemas/Adjunto')),
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

    #[OA\Delete(
        path: '/adjuntos/{adjunto}',
        tags: ['Adjuntos'],
        summary: 'Eliminar adjunto',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'adjunto', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
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

    #[OA\Get(
        path: '/adjuntos/{adjunto}/download',
        tags: ['Adjuntos'],
        summary: 'Descargar archivo adjunto',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'adjunto', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Archivo descargado',
                content: new OA\MediaType(mediaType: 'application/octet-stream', schema: new OA\Schema(type: 'string', format: 'binary'))
            ),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 404, description: 'No encontrado', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function download(): void
    {
    }
}
