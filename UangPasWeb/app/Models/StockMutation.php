<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'qty_in' => 'integer',
        'qty_out' => 'integer',
        'stok_awal' => 'integer',
        'stok_akhir' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Helper to record stock mutation
     */
    public static function record($productId, $type, $qtyIn, $qtyOut, $stokAwal, $stokAkhir, $referenceNo = null, $petugas = 'Sistem', $keterangan = null)
    {
        return self::create([
            'product_id' => $productId,
            'type' => $type,
            'qty_in' => $qtyIn,
            'qty_out' => $qtyOut,
            'stok_awal' => $stokAwal,
            'stok_akhir' => $stokAkhir,
            'reference_no' => $referenceNo,
            'petugas' => $petugas,
            'keterangan' => $keterangan,
        ]);
    }

    public function getTypeLabelAttribute()
    {
        return match ($this->type) {
            'purchase' => 'Restock Masuk',
            'sale' => 'Penjualan POS',
            'opname_adj' => 'Penyesuaian Opname',
            'damage' => 'Barang Rusak/Expired',
            'initial' => 'Stok Awal',
            default => ucfirst($this->type),
        };
    }

    public function getBadgeClassAttribute()
    {
        return match ($this->type) {
            'purchase' => 'pill-success',
            'sale' => 'pill-primary',
            'opname_adj' => 'pill-warning',
            'damage' => 'pill-danger',
            default => 'pill-primary',
        };
    }
}
