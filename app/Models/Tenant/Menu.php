<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Menu extends ModelTenant
{
    protected $fillable = ['name', 'price', 'status', 'restaurant_id',  'description', 'image'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}