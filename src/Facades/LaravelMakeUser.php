<?php

namespace ITUTUMedia\LaravelMakeUser\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ITUTUMedia\LaravelMakeUser\LaravelMakeUser
 */
class LaravelMakeUser extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \ITUTUMedia\LaravelMakeUser\LaravelMakeUser::class;
    }
}
