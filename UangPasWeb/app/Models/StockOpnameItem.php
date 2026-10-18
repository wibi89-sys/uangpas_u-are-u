<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'stok_sistem' => 'integer',
        'stok_fisik' => 'integer',
        'selisih_qty' => 'integer',
        'hpp' => 'decimal:2',
        'selisih_nominal' => 'decimal:2',
    ];

    public function opname()
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
