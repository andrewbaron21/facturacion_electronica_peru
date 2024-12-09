<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends ModelTenant
{
    protected $fillable = ['name', 'address'];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
}
