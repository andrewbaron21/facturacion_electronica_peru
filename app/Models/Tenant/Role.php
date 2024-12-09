<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Role extends ModelTenant
{
    protected $fillable = ['name'];
    
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'branch_employee_roles')
                    ->withPivot('branch_id')
                    ->withTimestamps();
    }    
}
