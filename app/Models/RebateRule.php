<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RebateRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'loyalty_tier_id',
        'percent_of_interest',
        'max_missed_days_to_qualify',
        'default_application',
        'is_active',
    ];

    protected $casts = [
        'percent_of_interest' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function loyaltyTier()
    {
        return $this->belongsTo(LoyaltyTier::class);
    }

    public function grants()
    {
        return $this->hasMany(RebateGrant::class);
    }
}
