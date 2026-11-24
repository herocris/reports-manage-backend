<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('auth.initial_admin.email');
        $password = config('auth.initial_admin.password');

        if (blank($email) && blank($password)) {
            return;
        }

        Validator::make(['email' => $email, 'password' => $password], [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:16'],
        ])->validate();

        DB::transaction(function () use ($email, $password): void {
            $existing = User::withTrashed()->where('email', $email)->lockForUpdate()->first();
            if ($existing !== null) {
                if ($existing->trashed() || ! $existing->hasRole('super-admin')) {
                    throw ValidationException::withMessages([
                        'email' => ['The initial administrator email is already in use.'],
                    ]);
                }

                return;
            }

            $adminUser = User::query()->create(['email' => $email, 'name' => 'Administrator', 'password' => $password]);
            $adminUser->syncRoles(['super-admin']);
        });
    }
}
