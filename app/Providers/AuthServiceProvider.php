<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot()
    {
        $this->registerPolicies();

        Gate::define('access-hr', function ($user) {
            return in_array('HR', session('user_modules', []));
        });

        Gate::define('access-payroll', function ($user) {
            return in_array('Payroll', session('user_modules', []));
        });

        Gate::define('access-attendance', function ($user) {
            return in_array('Payroll', session('user_modules', []));
        });

        Gate::define('access-holidays', function ($user) {
            return in_array('HR', session('user_modules', [])) || in_array('Payroll', session('user_modules', []));
        });
    }
}
