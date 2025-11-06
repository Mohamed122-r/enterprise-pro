<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Conversation extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'type',
        'created_by'
    ];

    public function participants()
    {
        return $this->belongsToMany(User::class, 'participants')
                    ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
}