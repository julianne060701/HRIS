<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$modules): Response
    {
        if (!Auth::check()) {
            return redirect('/login'); // Redirect to login if not authenticated
        }

        $userModules = Session::get('user_modules', []);

        foreach ($modules as $module) {
            if (in_array($module, $userModules)) {
                return $next($request);
            }
        }

        // If none of the required modules are found
        abort(403, 'Unauthorized access to this module.'); // Or redirect to a forbidden page
    }
}