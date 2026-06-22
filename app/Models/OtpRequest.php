<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrower_account_id',
        'purpose',
        'code_hash',
        'expires_at',
        'consumed_at',
        'requested_ip',
    ];

    protected $hidden = ['code_hash'];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];

    public function borrowerAccount()
    {
        return $this->belongsTo(BorrowerAccount::class);
    }
}
