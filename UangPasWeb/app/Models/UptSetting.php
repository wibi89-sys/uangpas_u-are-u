<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UptSetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'auto_sync' => 'boolean',
        'receipt_auto_print' => 'boolean',
        'receipt_show_inmate_balance' => 'boolean',
        'last_sync_at' => 'datetime',
        'last_backup_at' => 'datetime',
    ];

    /**
     * Singleton helper to get active local UPT setting
     */
    public static function current()
    {
        return static::first() ?? static::create([
            'upt_code' => 'LPS-01',
            'upt_name' => 'Lapas Kelas I Tangerang',
            'server_role' => 'upt_local',
            'central_api_url' => 'http://127.0.0.1:8001/api/v1',
            'central_api_key' => 'uangpas_secret_token_lapas',
            'auto_sync' => true,
        ]);
    }
}
