<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowerAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrower_id',
        'email',
        'pin_hash',
        'failed_attempts',
        'locked_at',
        'email_verified_at',
        'created_by_user_id',
    ];

    protected $hidden = ['pin_hash'];

    protected $casts = [
        'locked_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function otpRequests()
    {
        return $this->hasMany(OtpRequest::class);
    }
}
