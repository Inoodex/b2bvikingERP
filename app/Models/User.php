<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'image',
        'status',
        'role_id',
        'outlet_name',
        'address',
        'discount_type',
        'discount_value',
        'outlet_id',
        'department_id',
        'company_id',
        'credit_limit',
        'customer_segment',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Determine purely dynamically if this user is an internal staff member.
     * ZERO hardcoded role IDs or names: based on Spatie permissions & enterprise assignments.
     */
    public function isStaff(): bool
    {
        // 1. If assigned to an internal enterprise unit (Company, Department, or Outlet)
        if ($this->department_id || $this->company_id || $this->outlet_id) {
            return true;
        }

        // 2. If the user's role has backend management permissions dynamically assigned
        if ($this->userRole && $this->userRole->permissions()->exists()) {
            return true;
        }

        // 3. If direct permissions are granted
        if ($this->permissions()->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Determine purely dynamically if this user is a commercial customer.
     */
    public function isCustomer(): bool
    {
        return !$this->isStaff();
    }

    /**
     * Dynamic Scope: query internal staff members with zero hardcoding.
     */
    public function scopeStaff(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNotNull('department_id')
              ->orWhereNotNull('company_id')
              ->orWhereNotNull('outlet_id')
              ->orWhereHas('userRole.permissions')
              ->orWhereHas('permissions');
        });
    }

    /**
     * Dynamic Scope: query commercial customers with zero hardcoding.
     */
    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('department_id')
              ->whereNull('company_id')
              ->whereNull('outlet_id')
              ->whereDoesntHave('userRole.permissions')
              ->whereDoesntHave('permissions');
        });
    }
}
