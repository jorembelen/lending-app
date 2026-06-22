<?php

namespace App\Helpers;

use Jenssegers\Agent\Agent;

if (!function_exists('agent_information')) {
    function agent_information($request)
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());
        $platform = $agent->platform();
        $browser = $agent->browser();
        return $platform . ' | ' . ($browser ?: $platform);
    }
}
