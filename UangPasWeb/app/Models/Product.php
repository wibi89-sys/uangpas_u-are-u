<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'harga' => 'decimal:2',
        'harga_beli' => 'decimal:2',
        'stok' => 'integer',
        'min_stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function stockMutations()
    {
        return $this->hasMany(StockMutation::class)->latest();
    }

    public function purchaseItems()
    {
        return $this->hasMany(StockPurchaseItem::class);
    }

    public function opnameItems()
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    // Hitung margin kotor (Rp)
    public function getMarginNominalAttribute()
    {
        return max(0, $this->harga - $this->harga_beli);
    }

    // Hitung persentase margin laba kotor (%)
    public function getMarginPercentAttribute()
    {
        if ($this->harga <= 0) return 0;
        return round((($this->harga - $this->harga_beli) / $this->harga) * 100, 1);
    }

    // Status stok untuk retail
    public function getStockStatusAttribute()
    {
        if ($this->stok <= 0) return 'Habis';
        if ($this->stok <= $this->min_stock) return 'Menipis';
        return 'Aman';
    }

    public function getStockStatusBadgeAttribute()
    {
        if ($this->stok <= 0) return 'pill-danger';
        if ($this->stok <= $this->min_stock) return 'pill-warning';
        return 'pill-success';
    }

    /**
     * Helper to adjust stock and log mutation
     */
    public function adjustStock($qtyChange, $type, $referenceNo = null, $petugas = 'Admin Koperasi', $keterangan = null)
    {
        $stokAwal = $this->stok;
        $stokAkhir = max(0, $stokAwal + $qtyChange);
        
        $qtyIn = $qtyChange > 0 ? $qtyChange : 0;
        $qtyOut = $qtyChange < 0 ? abs($qtyChange) : 0;

        $this->stok = $stokAkhir;
        $this->save();

        return StockMutation::record(
            $this->id,
            $type,
            $qtyIn,
            $qtyOut,
            $stokAwal,
            $stokAkhir,
            $referenceNo,
            $petugas,
            $keterangan
        );
    }
}
