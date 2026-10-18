<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'tanggal_opname' => 'date',
        'total_item' => 'integer',
        'total_selisih_qty' => 'integer',
        'total_selisih_nominal' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
    }
}
