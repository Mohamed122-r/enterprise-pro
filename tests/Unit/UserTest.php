<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created()
    {
        $role = Role::factory()->create();
        $department = Department::factory()->create();

        $user = User::factory()->create([
            'role_id' => $role->id,
            'department_id' => $department->id,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function test_user_has_role()
    {
        $role = Role::factory()->create();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertEquals($role->id, $user->role->id);
    }

    public function test_user_has_department()
    {
        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);

        $this->assertEquals($department->id, $user->department->id);
    }

    public function test_user_has_permissions()
    {
        $role = Role::factory()->create([
            'permissions' => ['users.read', 'contacts.create']
        ]);
        
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->hasPermissionTo('users.read'));
        $this->assertTrue($user->hasPermissionTo('contacts.create'));
        $this->assertFalse($user->hasPermissionTo('users.delete'));
    }
}