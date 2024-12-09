<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Branch extends ModelTenant
{
    protected $fillable = ['restaurant_id', 'name', 'address', 'phone'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'branch_employee_roles')
                    ->withPivot('role_id')
                    ->withTimestamps();
    }

}
