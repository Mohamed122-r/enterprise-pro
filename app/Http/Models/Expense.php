<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use App\Traits\LogsActivity;

class Expense extends Model
{
    use HasFactory, HasUuid, LogsActivity;

    protected $fillable = [
        'category',
        'amount',
        'description',
        'date',
        'receipt',
        'user_id',
        'approved_by',
        'status',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}