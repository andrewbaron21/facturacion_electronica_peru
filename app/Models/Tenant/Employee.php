<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Employee extends ModelTenant
{
    protected $fillable = ['name', 'email', 'phone'];
    
    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_employee_roles')
                    ->withPivot('role_id')
                    ->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'branch_employee_roles')
                    ->withPivot('branch_id')
                    ->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(NewOrder::class);
    }
}
