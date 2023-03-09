<?php

namespace ItutuMedia\LaravelMakeUser;

use Illuminate\Support\ServiceProvider;

class CreateUserServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateUser::class,
            ]);
        }
    }
}
