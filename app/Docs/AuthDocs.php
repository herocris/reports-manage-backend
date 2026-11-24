<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class AuthDocs
{
    #[OA\Schema(
        schema: 'LoginRequest',
        type: 'object',
        required: ['email', 'password'],
        properties: [
            new OA\Property(property: 'email', type: 'string', format: 'email', example: 'demo@example.com'),
            new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
        ]
    )]
    #[OA\Schema(
        schema: 'UpdateProfileRequest',
        type: 'object',
        required: ['password'],
        properties: [
            new OA\Property(property: 'password', type: 'string', format: 'password', example: 'UpdatedPassword1!'),
        ]
    )]
    #[OA\Schema(
        schema: 'AuthUser',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
            new OA\Property(property: 'email', type: 'string', format: 'email', example: 'demo@example.com'),
        ]
    )]
    #[OA\Post(
        path: '/auth/mobile/login',
        tags: ['Auth'],
        summary: 'Login mobile por token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login exitoso',
                content: new OA\JsonContent(
                    required: ['success', 'message', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Logged in successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'token', type: 'string', example: '1|exampleToken...'),
                                new OA\Property(property: 'user', ref: '#/components/schemas/AuthUser'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Credenciales invalidas', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 422, description: 'Validacion fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function loginMobile(): void {}

    #[OA\Post(
        path: '/auth/web/login',
        tags: ['Auth'],
        summary: 'Login web por sesion Sanctum',
        description: 'Requiere solicitar previamente /sanctum/csrf-cookie desde el frontend SPA.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login exitoso',
                content: new OA\JsonContent(
                    required: ['success', 'message', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Logged in successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'user', ref: '#/components/schemas/AuthUser'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Credenciales invalidas', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 422, description: 'Validacion fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function loginWeb(): void {}

    #[OA\Post(
        path: '/login',
        tags: ['Auth'],
        summary: 'Login legado (compatibilidad)',
        deprecated: true,
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login exitoso (token)'),
            new OA\Response(response: 401, description: 'Credenciales invalidas', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 422, description: 'Validacion fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function loginLegacy(): void {}

    #[OA\Post(
        path: '/auth/mobile/logout',
        tags: ['Auth'],
        summary: 'Logout mobile revocando todos los tokens del usuario',
        security: [['BearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Logout exitoso', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function logoutMobile(): void {}

    #[OA\Post(
        path: '/auth/web/logout',
        tags: ['Auth'],
        summary: 'Logout web invalidando sesion',
        security: [['SanctumCookie' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Logout exitoso', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function logoutWeb(): void {}

    #[OA\Post(
        path: '/logout',
        tags: ['Auth'],
        summary: 'Logout legado (compatibilidad)',
        deprecated: true,
        security: [['BearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Logout exitoso', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function logoutLegacy(): void {}

    #[OA\Get(
        path: '/me',
        tags: ['Auth'],
        summary: 'Obtener perfil autenticado',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Perfil obtenido exitosamente'),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function me(): void {}

    #[OA\Put(
        path: '/me',
        tags: ['Auth'],
        summary: 'Actualizar perfil (solo password)',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateProfileRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Perfil actualizado'),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 422, description: 'Validacion fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function updateProfilePut(): void {}

    #[OA\Patch(
        path: '/me',
        tags: ['Auth'],
        summary: 'Actualizar perfil (solo password, variante PATCH)',
        security: [['BearerAuth' => []], ['SanctumCookie' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateProfileRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Perfil actualizado'),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')),
            new OA\Response(response: 422, description: 'Validacion fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationResponse')),
            new OA\Response(response: 429, description: 'Demasiadas solicitudes', content: new OA\JsonContent(ref: '#/components/schemas/TooManyRequestsResponse')),
            new OA\Response(response: 500, description: 'Error del servidor', content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ]
    )]
    public function updateProfilePatch(): void {}
}
