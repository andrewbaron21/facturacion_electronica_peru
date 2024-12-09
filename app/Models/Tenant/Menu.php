<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Menu extends ModelTenant
{
    protected $fillable = ['name', 'price', 'status', 'branch_id', 'description', 'image'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}