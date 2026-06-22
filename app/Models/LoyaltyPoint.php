<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrower_id',
        'points',
        'reason',
        'loan_id',
        'awarded_at',
    ];

    protected $casts = [
        'awarded_at' => 'datetime',
    ];

    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
