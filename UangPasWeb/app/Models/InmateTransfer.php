<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InmateTransfer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'saldo_dibawa' => 'decimal:2',
        'tanggal_mutasi' => 'date',
    ];

    public function inmate()
    {
        return $this->belongsTo(Inmate::class);
    }
}
