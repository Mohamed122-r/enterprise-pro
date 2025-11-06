<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'System Administrator with full access',
                'permissions' => [
                    'users.create', 'users.read', 'users.update', 'users.delete',
                    'contacts.create', 'contacts.read', 'contacts.update', 'contacts.delete',
                    'invoices.create', 'invoices.read', 'invoices.update', 'invoices.delete',
                    'reports.view', 'settings.manage', 'attendance.create', 'attendance.read'
                ]
            ],
            [
                'name' => 'manager',
                'description' => 'Department Manager with limited administrative access',
                'permissions' => [
                    'users.read', 'contacts.create', 'contacts.read', 'contacts.update',
                    'invoices.create', 'invoices.read', 'invoices.update', 'reports.view',
                    'attendance.create', 'attendance.read'
                ]
            ],
            [
                'name' => 'user',
                'description' => 'Regular system user',
                'permissions' => [
                    'contacts.read', 'invoices.read', 'attendance.create', 'attendance.read'
                ]
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}