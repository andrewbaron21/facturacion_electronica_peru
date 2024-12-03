<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Table extends ModelTenant
{
    protected $fillable = ['number', 'restaurant_id'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orders()
    {
        return $this->hasMany(NewOrder::class);
    }
}
