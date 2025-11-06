<?php

return [
    'status' => [
        'active' => 1,
        'inactive' => 0,
    ],
    
    'attendance_status' => [
        'present' => 'present',
        'absent' => 'absent',
        'late' => 'late',
        'half_day' => 'half_day',
    ],
    
    'invoice_status' => [
        'draft' => 'draft',
        'sent' => 'sent',
        'paid' => 'paid',
        'partial' => 'partial',
        'overdue' => 'overdue',
    ],
    
    'contact_status' => [
        'lead' => 'lead',
        'prospect' => 'prospect',
        'customer' => 'customer',
        'partner' => 'partner',
    ],
    
    'activity_types' => [
        'call' => 'Phone Call',
        'meeting' => 'Meeting',
        'email' => 'Email',
        'task' => 'Task',
        'note' => 'Note',
    ],
    
    'priority_levels' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
    ],
    
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],
];