<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? 'password'),
                'phone' => $data['phone'] ?? null,
                'department_id' => $data['department_id'],
                'role_id' => $data['role_id'],
                'status' => $data['status'] ?? true,
            ]);

            return $user->load(['role', 'department']);
        });
    }

    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if (isset($data['password']) && $data['password']) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            return $user->load(['role', 'department']);
        });
    }

    public function deleteUser(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            // Prevent self-deletion
            if ($user->id === auth()->id()) {
                throw new \Exception('Cannot delete your own account');
            }

            return $user->delete();
        });
    }

    public function getUsersWithFilters(array $filters = [])
    {
        $query = User::with(['role', 'department']);

        if (isset($filters['search']) && $filters['search']) {
            $query->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['role_id']) && $filters['role_id']) {
            $query->where('role_id', $filters['role_id']);
        }

        if (isset($filters['department_id']) && $filters['department_id']) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }
}