<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckModulePermission
{
    public function handle(Request $request, Closure $next, $module)
    {
        if (!in_array($module, session('user_modules', []))) {
            abort(403, 'Unauthorized module access.');
        }

        return $next($request);
    }
}
