<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'termin_hari' => 'integer',
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function stockPurchases()
    {
        return $this->hasMany(StockPurchase::class);
    }
}
