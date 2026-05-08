<?php

namespace App\Helpers;

use App\Models\ActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActionLogger
{
    public static function log(string $action, string $description, ?Request $request = null): void
    {
        ActionLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => $request?->ip(),
        ]);

        Log::info($action, [
            'user_id' => Auth::id(),
            'description' => $description,
            'ip_address' => $request?->ip(),
        ]);
    }
}