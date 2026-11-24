<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

/**
 * Schemas de respuestas comunes centralizados para toda la API.
 * Basados en el formato estandar de App\Support\Http\ApiResponse.
 */
#[OA\Schema(
    schema: 'SuccessResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Request successful.'),
        new OA\Property(property: 'data', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'UnauthorizedResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
        new OA\Property(property: 'errors', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'ForbiddenResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.'),
        new OA\Property(property: 'errors', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'NotFoundResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Resource not found.'),
        new OA\Property(property: 'errors', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'MethodNotAllowedResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Method not allowed.'),
        new OA\Property(property: 'errors', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'ValidationResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            example: ['email' => ['The email field is required.']]
        ),
    ]
)]
#[OA\Schema(
    schema: 'TooManyRequestsResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Too many requests. Please try again later.'),
        new OA\Property(property: 'errors', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'ServerErrorResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Server error.'),
        new OA\Property(property: 'errors', nullable: true),
    ]
)]
class CommonSchemas {}
