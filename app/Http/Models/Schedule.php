<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Schedule extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'shift_type',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalHoursAttribute()
    {
        return $this->start_time->diffInHours($this->end_time);
    }
}