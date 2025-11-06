<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function ($model) use ($event) {
                $model->logActivity($event);
            });
        }
    }

    protected function logActivity($event)
    {
        $description = $this->getActivityDescription($event);
        
        ActivityLog::create([
            'user_id' => Auth::id(),
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'action' => $event,
            'description' => $description,
            'old_values' => $event === 'updated' ? json_encode($this->getOriginal()) : null,
            'new_values' => $event !== 'deleted' ? json_encode($this->getAttributes()) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected function getActivityDescription($event)
    {
        $modelName = class_basename($this);
        
        return match($event) {
            'created' => "Created new {$modelName}: {$this->getActivityName()}",
            'updated' => "Updated {$modelName}: {$this->getActivityName()}",
            'deleted' => "Deleted {$modelName}: {$this->getActivityName()}",
            default => "Performed action on {$modelName}",
        };
    }

    protected function getActivityName()
    {
        return $this->name ?? $this->title ?? $this->email ?? 'Unknown';
    }
}