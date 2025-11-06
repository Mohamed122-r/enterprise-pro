<?php

namespace App\Traits;

trait HasPermissions
{
    public function hasPermissionTo($permission)
    {
        if ($this->role) {
            return in_array($permission, $this->role->permissions);
        }
        return false;
    }

    public function hasAnyPermission($permissions)
    {
        if ($this->role) {
            return !empty(array_intersect($permissions, $this->role->permissions));
        }
        return false;
    }

    public function hasAllPermissions($permissions)
    {
        if ($this->role) {
            return empty(array_diff($permissions, $this->role->permissions));
        }
        return false;
    }
}