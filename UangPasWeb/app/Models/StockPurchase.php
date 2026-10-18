<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPurchase extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'total_nominal' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(StockPurchaseItem::class);
    }
}
