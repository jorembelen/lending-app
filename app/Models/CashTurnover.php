<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTurnover extends Model
{
    use HasFactory;

    protected $fillable = [
        'collector_user_id',
        'turnover_date',
        'system_total',
        'cash_remitted',
        'variance',
        'reconciled_by_user_id',
        'reconciled_at',
        'note',
    ];

    protected $casts = [
        'turnover_date' => 'date',
        'system_total' => 'decimal:2',
        'cash_remitted' => 'decimal:2',
        'variance' => 'decimal:2',
        'reconciled_at' => 'datetime',
    ];

    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_user_id');
    }

    public function reconciledBy()
    {
        return $this->belongsTo(User::class, 'reconciled_by_user_id');
    }
}
