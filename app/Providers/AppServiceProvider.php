<?php

namespace App\Providers;

use App\Eetplan\Contracts\EetplanContract;
use App\Eetplan\Services\EetplanService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        EetplanContract::class => EetplanService::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Blade::if('mag', function ($permission) {
            return Auth::user()->hasPermission($permission);
        });

        // Cycle tussen de waarden in values, moet in een loop gebruikt worden.
        Blade::directive('cycle', function ($values) {
            return '<?php echo [' . $values . '][$loop->index % 2]; ?>';
        });

        Collection::macro('toAssoc', function () {
            return $this->reduce(function ($assoc, $keyValuePair) {
                list($key, $value) = $keyValuePair;
                $assoc[$key] = $value;
                return $assoc;
            }, new static);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //

    }
}
