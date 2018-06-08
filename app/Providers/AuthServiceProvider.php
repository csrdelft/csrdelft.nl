<?php

namespace App\Providers;

use App\Models\Account;
use App\Auth\MHashHasher;
use App\Auth\CsrSessionGuard;
use App\Policies\AccountPolicy;
use App\Auth\CsrUserProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Account::class => AccountPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPolicies();

        // Registreer mhash als hashing functie.
        $this->app->singleton('hash', function () {
            return new MHashHasher();
        });

        // Registreer CsrUserProvider en CsrSessionGuard
        $this->app->make('auth')->extend(
            'csrSession',
            function (Application $app) {
                $userProvider = new CsrUserProvider($app->make('hash'), config('auth.providers.users.model'));
                $guard = new CsrSessionGuard('csrSession', $userProvider, $app->make('session.store'), request());
                $guard->setCookieJar($app->make('cookie'));
                $guard->setDispatcher($app->make('events'));

                return $guard;
            }
        );
    }
}
