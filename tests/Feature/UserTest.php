<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_list()
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $admin->role()->associate($role);
        $admin->save();

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/users');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'name', 'email', 'role', 'department'
                        ]
                    ]
                ]);
    }

    public function test_user_cannot_view_users_list_without_permission()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'user']);
        $user->role()->associate($role);
        $user->save();

        $response = $this->actingAs($user)->getJson('/api/v1/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_user()
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $admin->role()->associate($role);
        $admin->save();

        $department = Department::factory()->create();
        $userRole = Role::factory()->create(['name' => 'user']);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'department_id' => $department->id,
            'role_id' => $userRole->id,
            'phone' => '+1234567890'
        ];

        $response = $this->actingAs($admin)->postJson('/api/v1/users', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id', 'name', 'email', 'role', 'department'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);
    }

    public function test_user_creation_requires_valid_data()
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $admin->role()->associate($role);
        $admin->save();

        $response = $this->actingAs($admin)->postJson('/api/v1/users', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password', 'department_id', 'role_id']);
    }

    public function test_admin_can_update_user()
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $admin->role()->associate($role);
        $admin->save();

        $user = User::factory()->create();
        $newDepartment = Department::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'department_id' => $newDepartment->id
        ];

        $response = $this->actingAs($admin)->putJson("/api/v1/users/{$user->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'User updated successfully']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_admin_can_delete_user()
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $admin->role()->associate($role);
        $admin->save();

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'User deleted successfully']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_cannot_delete_their_own_account()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $user->role()->associate($role);
        $user->save();

        $response = $this->actingAs($user)->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(500); // Or whatever status your app returns for this case
    }

    public function test_user_status_can_be_updated()
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $admin->role()->associate($role);
        $admin->save();

        $user = User::factory()->create(['status' => true]);

        $response = $this->actingAs($admin)->patchJson("/api/v1/users/{$user->id}/status", [
            'status' => false
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'User status updated successfully']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => false
        ]);
    }

    public function test_users_can_be_filtered_by_role()
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $admin->role()->associate($role);
        $admin->save();

        $userRole = Role::factory()->create(['name' => 'user']);
        $user = User::factory()->create();
        $user->role()->associate($userRole);
        $user->save();

        $response = $this->actingAs($admin)->getJson("/api/v1/users?role_id={$userRole->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_users_can_be_searched()
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $admin->role()->associate($role);
        $admin->save();

        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->actingAs($admin)->getJson('/api/v1/users?search=John');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('John Doe', $response->json('data.0.name'));
    }
}