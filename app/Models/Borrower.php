<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrower extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'phone_number',
        'address',
        'borrower_code',
        'qr_reference',
        'photo_path',
        'current_tier_id',
    ];

    protected static function booted(): void
    {
        static::created(function (Borrower $borrower) {
            $borrower->updateQuietly([
                'borrower_code' => 'BRW-' . str_pad($borrower->id, 6, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function currentTier()
    {
        return $this->belongsTo(LoyaltyTier::class, 'current_tier_id');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function account()
    {
        return $this->hasOne(BorrowerAccount::class);
    }

    public function tierHistory()
    {
        return $this->hasMany(BorrowerTierHistory::class);
    }

    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function rebateGrants()
    {
        return $this->hasMany(RebateGrant::class);
    }

    public function activeLoan()
    {
        return $this->hasOne(Loan::class)->where('status', 'active');
    }
}
