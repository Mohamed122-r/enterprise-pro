<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use App\Traits\LogsActivity;

class Opportunity extends Model
{
    use HasFactory, HasUuid, LogsActivity;

    protected $fillable = [
        'title',
        'contact_id',
        'value',
        'stage',
        'probability',
        'expected_close_date',
        'assigned_to',
        'description',
        'notes'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expected_close_date' => 'date',
        'probability' => 'integer'
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function getStageColorAttribute()
    {
        return match($this->stage) {
            'prospect' => 'gray',
            'qualification' => 'blue',
            'proposal' => 'yellow',
            'negotiation' => 'orange',
            'closed_won' => 'green',
            'closed_lost' => 'red',
            default => 'gray',
        };
    }
}