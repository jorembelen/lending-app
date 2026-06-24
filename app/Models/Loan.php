<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrower_id',
        'rate_preset_id',
        'principal',
        'rate_per_1000_locked',
        'term_days_locked',
        'daily_installment',
        'total_payable',
        'disbursed_at',
        'disbursed_by_user_id',
        'assigned_collector_id',
        'status',
        'closed_at',
        'missed_days_at_closure',
    ];

    protected $casts = [
        'principal' => 'decimal:2',
        'rate_per_1000_locked' => 'decimal:2',
        'daily_installment' => 'decimal:2',
        'total_payable' => 'decimal:2',
        'disbursed_at' => 'date',
        'closed_at' => 'date',
    ];

    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }

    public function ratePreset()
    {
        return $this->belongsTo(RatePreset::class);
    }

    public function disbursedBy()
    {
        return $this->belongsTo(User::class, 'disbursed_by_user_id');
    }

    public function assignedCollector()
    {
        return $this->belongsTo(User::class, 'assigned_collector_id');
    }

    public function scheduleItems()
    {
        return $this->hasMany(ScheduleItem::class)->orderBy('sequence_number');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function rebateGrants()
    {
        return $this->hasMany(RebateGrant::class);
    }

    public function tierHistory()
    {
        return $this->hasMany(BorrowerTierHistory::class);
    }

    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function getRemainingBalanceAttribute(): float
    {
        $paid = $this->payments()->where('is_voided', false)->sum('amount');
        return max(0, (float) $this->total_payable - (float) $paid);
    }

    public function interestAmount(): float
    {
        return (float) $this->total_payable - (float) $this->principal;
    }
}
