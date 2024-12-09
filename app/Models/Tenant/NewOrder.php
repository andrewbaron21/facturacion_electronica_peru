<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class NewOrder extends ModelTenant
{
    protected $fillable = ['table_id', 'status', 'employee_id'];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function employee() { 
        return $this->belongsTo(Employee::class); 
    }
}
