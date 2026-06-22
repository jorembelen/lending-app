<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidayCalendar extends Model
{
    use HasFactory;

    protected $table = 'holiday_calendar';

    protected $fillable = [
        'date',
        'label',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
