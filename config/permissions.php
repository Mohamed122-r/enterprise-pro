<?php

return [
    'roles' => [
        'admin' => [
            'users.create',
            'users.read',
            'users.update',
            'users.delete',
            'contacts.create',
            'contacts.read',
            'contacts.update',
            'contacts.delete',
            'invoices.create',
            'invoices.read',
            'invoices.update',
            'invoices.delete',
            'reports.view',
            'settings.manage',
        ],
        'manager' => [
            'users.read',
            'contacts.create',
            'contacts.read',
            'contacts.update',
            'invoices.create',
            'invoices.read',
            'invoices.update',
            'reports.view',
        ],
        'user' => [
            'contacts.read',
            'invoices.read',
            'attendance.create',
            'attendance.read',
        ],
    ],

    'permissions' => [
        'users' => [
            'create' => 'Create Users',
            'read' => 'View Users',
            'update' => 'Edit Users',
            'delete' => 'Delete Users',
        ],
        'contacts' => [
            'create' => 'Create Contacts',
            'read' => 'View Contacts',
            'update' => 'Edit Contacts',
            'delete' => 'Delete Contacts',
        ],
        'invoices' => [
            'create' => 'Create Invoices',
            'read' => 'View Invoices',
            'update' => 'Edit Invoices',
            'delete' => 'Delete Invoices',
        ],
        'reports' => [
            'view' => 'View Reports',
        ],
        'settings' => [
            'manage' => 'Manage Settings',
        ],
        'attendance' => [
            'create' => 'Record Attendance',
            'read' => 'View Attendance',
        ],
    ],
];