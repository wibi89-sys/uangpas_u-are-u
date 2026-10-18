<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPurchaseItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'qty' => 'integer',
        'harga_beli_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(StockPurchase::class, 'stock_purchase_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
