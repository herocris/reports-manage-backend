<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Tests\TestCase;

class ApiErrorResponseTest extends TestCase
{
    public function test_conflicts_keep_their_http_status_without_exposing_exception_details(): void
    {
        Route::get('/api/_test/conflict', function (): never {
            abort(409, 'Internal implementation details.');
        });

        $this->getJson('/api/_test/conflict')->assertConflict()->assertExactJson([
            'success' => false,
            'message' => 'Conflict',
            'errors' => null,
        ]);
    }

    public function test_csrf_errors_keep_their_419_status(): void
    {
        Route::post('/api/_test/csrf', function (): never {
            throw new TokenMismatchException;
        });

        $this->postJson('/api/_test/csrf')->assertStatus(419)
            ->assertJsonPath('message', 'CSRF token mismatch.');
    }

    public function test_rate_limit_errors_keep_retry_headers(): void
    {
        Route::get('/api/_test/throttle', function (): never {
            throw new TooManyRequestsHttpException(45);
        });

        $this->getJson('/api/_test/throttle')->assertTooManyRequests()
            ->assertHeader('Retry-After', '45')
            ->assertJsonPath('message', 'Too many requests. Please try again later.');
    }

    public function test_method_not_allowed_keeps_the_allow_header(): void
    {
        Route::post('/api/_test/method', fn (): array => []);

        $this->getJson('/api/_test/method')->assertStatus(405)
            ->assertHeader('Allow', 'POST')
            ->assertJsonPath('message', 'Method not allowed.');
    }

    public function test_unexpected_errors_hide_internal_details(): void
    {
        Route::get('/api/_test/error', function (): never {
            throw new RuntimeException('Internal secret.');
        });

        $this->getJson('/api/_test/error')->assertInternalServerError()->assertExactJson([
            'success' => false,
            'message' => 'Server error.',
            'errors' => null,
        ]);
    }

    public function test_explicit_http_responses_are_preserved(): void
    {
        Route::get('/api/_test/response', function (): never {
            throw new HttpResponseException(response()->json(['reason' => 'business-rule'], 422));
        });

        $this->getJson('/api/_test/response')->assertUnprocessable()
            ->assertExactJson(['reason' => 'business-rule']);
    }
}
