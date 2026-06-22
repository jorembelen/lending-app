<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RebateGrant extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'borrower_id',
        'rebate_rule_id',
        'interest_amount',
        'rebate_amount',
        'status',
        'approved_by_user_id',
        'approved_at',
        'applied_to_loan_id',
        'applied_at',
    ];

    protected $casts = [
        'interest_amount' => 'decimal:2',
        'rebate_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }

    public function rebateRule()
    {
        return $this->belongsTo(RebateRule::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function appliedToLoan()
    {
        return $this->belongsTo(Loan::class, 'applied_to_loan_id');
    }
}
