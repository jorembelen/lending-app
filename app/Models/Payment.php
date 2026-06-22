<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'collector_user_id',
        'amount',
        'collected_at',
        'recorded_at',
        'latitude',
        'longitude',
        'device_identifier',
        'idempotency_key',
        'is_voided',
        'voided_by_user_id',
        'voided_reason',
        'voided_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'collected_at' => 'datetime',
        'recorded_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_voided' => 'boolean',
        'voided_at' => 'datetime',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_user_id');
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by_user_id');
    }
}
