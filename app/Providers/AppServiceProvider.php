<?php

namespace App\Providers;

use Carbon\Carbon;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * The policy mappings for the application.
	 *
	 * @var array
	 */
	protected $policies = [
		'App\Model' => 'App\Policies\ModelPolicy',
	];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
	    $this->registerPolicies();

	    Passport::routes();
	    Passport::tokensExpireIn(Carbon::now()->addYear());
	    Passport::refreshTokensExpireIn(Carbon::now()->addYear());
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
	    if ($this->app->environment() !== 'production') {
		    $this->app->register(IdeHelperServiceProvider::class);
	    }
    }
}
