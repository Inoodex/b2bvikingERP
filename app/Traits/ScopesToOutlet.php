<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait ScopesToOutlet
{
    /**
     * Boot the trait to automatically scope queries by outlet if user is tied to an outlet.
     */
    protected static function bootScopesToOutlet(): void
    {
        static::addGlobalScope('outlet_scope', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();

                // If user is not superadmin and is assigned to a specific outlet
                if ($user->outlet_id && !$user->hasRole('Admin') && $user->role_id !== 1) {
                    $table = $builder->getModel()->getTable();
                    $builder->where("{$table}.outlet_id", $user->outlet_id);
                }
            }
        });
    }

    /**
     * Scope query to all outlets bypassing global scope (for central reports/admins).
     */
    public function scopeWithoutOutletScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('outlet_scope');
    }
}
