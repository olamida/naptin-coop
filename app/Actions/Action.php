<?php

namespace App\Actions;

abstract class Action
{
    public static function run(...$args): mixed
    {
        return app(static::class)->handle(...$args);
    }
}
