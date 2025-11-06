<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'phone' => $this->faker->phoneNumber(),
            'avatar' => null,
            'role_id' => Role::factory(),
            'department_id' => Department::factory(),
            'status' => true,
            'last_login_at' => null,
        ];
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role_id' => Role::where('name', 'admin')->first()?->id ?? Role::factory(),
            ];
        });
    }

    public function inactive()
    {
        return $this->state([
            'status' => false,
        ]);
    }
}