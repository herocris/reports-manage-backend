<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Observacion',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'numero', type: 'integer', example: 3),
        new OA\Property(property: 'titulo', type: 'string', example: 'Corregir las conclusiones'),
        new OA\Property(property: 'descripcion', type: 'string', example: 'Observacion relevante sobre el informe'),
        new OA\Property(property: 'estado', type: 'string', enum: ['abierta', 'cerrada'], example: 'abierta'),
        new OA\Property(property: 'usuario_id', type: 'integer', example: 1),
        new OA\Property(property: 'informe_id', type: 'integer', example: 1),
        new OA\Property(property: 'cerrada_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'cerrada_por_id', type: 'integer', nullable: true, example: 2),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'informe', ref: '#/components/schemas/Informe'),
    ]
)]
#[OA\Schema(
    schema: 'StoreObservacionRequest',
    type: 'object',
    required: ['titulo', 'descripcion', 'informe_id'],
    properties: [
        new OA\Property(property: 'titulo', type: 'string', maxLength: 255, example: 'Corregir las conclusiones'),
        new OA\Property(property: 'descripcion', type: 'string', example: 'Observacion relevante'),
        new OA\Property(property: 'informe_id', type: 'integer', example: 1),
    ]
)]
#[OA\Schema(
    schema: 'UpdateObservacionRequest',
    type: 'object',
    properties: [
        new OA\Property(property: 'titulo', type: 'string', maxLength: 255, example: 'Conclusiones corregidas'),
        new OA\Property(property: 'descripcion', type: 'string', example: 'Detalle actualizado de la observacion'),
    ]
)]
#[OA\Schema(
    schema: 'ObservacionComentarioRequest',
    type: 'object',
    required: ['contenido'],
    properties: [
        new OA\Property(property: 'contenido', type: 'string', example: 'Se corrigieron las conclusiones del informe.'),
    ]
)]
class ObservacionDocs
{
    #[OA\Get(
        path: '/observaciones',
        tags: ['Observaciones'],
        summary: 'Listar observaciones',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[informe_id]', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'include', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'informe')),
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

    #[OA\Post(
        path: '/observaciones',
        tags: ['Observaciones'],
        summary: 'Crear observacion',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateObservacionRequest')
        ),
        responses: [
            new OA\Response(response: 201, description: 'Observacion creada', content: new OA\JsonContent(ref: '#/components/schemas/Observacion')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 422, description: 'Validacion fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/observaciones/{observacion}',
        tags: ['Observaciones'],
        summary: 'Obtener observacion',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'observacion', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(ref: '#/components/schemas/Observacion')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 404, description: 'No encontrado', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/observaciones/{observacion}',
        tags: ['Observaciones'],
        summary: 'Actualizar observacion',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'observacion', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreObservacionRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Observacion')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 404, description: 'No encontrado', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
            new OA\Response(response: 422, description: 'Validacion fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function update(): void {}

    #[OA\Post(
        path: '/observaciones/{observacion}/comentarios',
        tags: ['Observaciones'],
        summary: 'Agregar un comentario a la observacion',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'observacion', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ObservacionComentarioRequest')),
        responses: [
            new OA\Response(response: 201, description: 'Comentario creado'),
            new OA\Response(response: 403, description: 'Solo el responsable del informe puede comentar'),
            new OA\Response(response: 422, description: 'Validacion fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationResponse')),
        ]
    )]
    public function storeComentario(): void {}

    #[OA\Post(
        path: '/observaciones/{observacion}/cerrar',
        tags: ['Observaciones'],
        summary: 'Cerrar la observacion con un comentario obligatorio',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'observacion', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ObservacionComentarioRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Observacion cerrada', content: new OA\JsonContent(ref: '#/components/schemas/Observacion')),
            new OA\Response(response: 403, description: 'Solo el responsable del informe puede cerrar'),
            new OA\Response(response: 422, description: 'Validacion fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationResponse')),
        ]
    )]
    public function close(): void {}

    #[OA\Delete(
        path: '/observaciones/{observacion}',
        tags: ['Observaciones'],
        summary: 'Eliminar observacion (soft delete)',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'observacion', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
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
    public function destroy(): void {}

    #[OA\Post(
        path: '/observaciones/{observacion}/restore',
        tags: ['Observaciones'],
        summary: 'Restaurar observacion eliminada',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        parameters: [
            new OA\Parameter(name: 'observacion', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Restaurada', content: new OA\JsonContent(ref: '#/components/schemas/Observacion')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 403, description: 'Sin permiso', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 404, description: 'No encontrado', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function restore(): void {}
}
