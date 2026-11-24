<?php

namespace App\Support\Http;

use Illuminate\Http\JsonResponse;

/**
 * Builds uniform JSON response envelopes for the API.
 *
 * Every endpoint returns either a success envelope
 * `{success: true, message, data}` or an error envelope
 * `{success: false, message, errors}`.
 */
class ApiResponse
{
    /**
     * Build a successful JSON response.
     *
     * @param  mixed  $data  The payload to include in the `data` key.
     * @param  string  $message  Human-readable success message.
     * @param  int  $status  HTTP status code (default 200).
     */
    public static function success(mixed $data = null, string $message = 'Request successful.', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Build an error JSON response.
     *
     * @param  string  $message  Human-readable error description.
     * @param  int  $status  HTTP status code.
     * @param  array<string, string[]>|null  $errors  Per-field validation errors, or null.
     */
    public static function error(string $message, int $status, ?array $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
