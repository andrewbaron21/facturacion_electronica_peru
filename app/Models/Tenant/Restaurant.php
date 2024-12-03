<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends ModelTenant
{
    protected $fillable = ['name', 'address'];

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }
}
