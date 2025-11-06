<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        // Register custom macros
        $this->registerMacros();
    }

    protected function registerMacros()
    {
        // Example macro for collections
        \Illuminate\Support\Collection::macro('toSelectOptions', function ($value = 'id', $label = 'name') {
            return $this->map(function ($item) use ($value, $label) {
                return [
                    'value' => $item->{$value},
                    'label' => $item->{$label}
                ];
            })->toArray();
        });
    }
}