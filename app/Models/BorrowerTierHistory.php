<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowerTierHistory extends Model
{
    use HasFactory;

    protected $table = 'borrower_tier_history';

    protected $fillable = [
        'borrower_id',
        'loyalty_tier_id',
        'loan_id',
        'changed_at',
        'note',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }

    public function loyaltyTier()
    {
        return $this->belongsTo(LoyaltyTier::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
