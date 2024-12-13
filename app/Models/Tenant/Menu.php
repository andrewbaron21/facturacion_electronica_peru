<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Menu extends ModelTenant
{
    protected $fillable = ['branch_id', 'item_id',];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // En el modelo Menu
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

}