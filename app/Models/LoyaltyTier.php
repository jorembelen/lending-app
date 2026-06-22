<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rank',
        'max_missed_days_to_qualify',
        'loan_ceiling_multiplier',
        'rate_discount_per_1000',
        'priority_reloan',
    ];

    protected $casts = [
        'loan_ceiling_multiplier' => 'decimal:2',
        'rate_discount_per_1000' => 'decimal:2',
        'priority_reloan' => 'boolean',
    ];

    public function borrowers()
    {
        return $this->hasMany(Borrower::class, 'current_tier_id');
    }

    public function tierHistory()
    {
        return $this->hasMany(BorrowerTierHistory::class);
    }

    public function rebateRules()
    {
        return $this->hasMany(RebateRule::class);
    }
}
