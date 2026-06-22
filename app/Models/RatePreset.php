<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatePreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rate_per_1000',
        'term_days',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'rate_per_1000' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}
