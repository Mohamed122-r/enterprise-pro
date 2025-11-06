<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'role_id' => $data['role_id'] ?? 2, // Default user role
            ]);

            // Trigger registration event
            // event(new UserRegistered($user));

            return $user;
        });
    }

    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return ['success' => false];
        }

        if (!$user->status) {
            throw new \Exception('Account is deactivated');
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'success' => true,
            'user' => $user->load(['role', 'department']),
            'token' => $token
        ];
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }
}