<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends ModelTenant
{
    protected $fillable = ['order_id', 'menu_id', 'quantity', 'price'];

    public function order()
    {
        return $this->belongsTo(NewOrder::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}