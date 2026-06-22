<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Agent\Agent;

class TrustedDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'device_token', 'user_agent', 'ip_address', 'expires_at'
    ];

    protected $dates = ['expires_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     // get platform and browser
    public function getPlatformAttribute()
    {
        $agent = new Agent();
        $agent->setUserAgent($this->user_agent);
        return $agent->platform();
    }

    public function getBrowserAttribute()
    {
        $agent = new Agent();
        $agent->setUserAgent($this->user_agent);
        return $agent->browser();
    }

}
