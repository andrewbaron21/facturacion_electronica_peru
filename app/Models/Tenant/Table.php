<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Table extends ModelTenant
{
    protected $fillable = ['number', 'branch_id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function orders()
    {
        return $this->hasMany(NewOrder::class);
    }
}
