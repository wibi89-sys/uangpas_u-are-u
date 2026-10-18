<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inmate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'saldo' => 'decimal:2',
        'limit_harian' => 'decimal:2',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function transfers()
    {
        return $this->hasMany(InmateTransfer::class)->latest();
    }

    // Helper: Hitung belanja hari ini untuk validasi limit harian
    public function getTodaySpentAttribute()
    {
        return $this->transactions()
            ->where('type', 'purchase')
            ->where('status', 'success')
            ->whereDate('created_at', today())
            ->sum('amount');
    }

    public function getRemainingDailyLimitAttribute()
    {
        $remaining = $this->limit_harian - $this->today_spent;
        return max(0, $remaining);
    }
}
