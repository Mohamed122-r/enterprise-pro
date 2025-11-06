<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasPermissions;
use App\Traits\LogsActivity;
use App\Traits\HasUuid;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasPermissions, LogsActivity, HasUuid;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'department_id',
        'role_id',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'status' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'participants')
                    ->withTimestamps();
    }

    public function hasPermission($permission)
    {
        return $this->role->permissions()->where('name', $permission)->exists();
    }
}