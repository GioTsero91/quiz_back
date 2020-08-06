<?php

namespace App\Shared;


class SessionHandler
{
    public static function get($key)
    {
        return request()->session()->get($key);
    }

    public static function getId()
    {
        return request()->session()->getId();
    }

    public static function put($key, $value)
    {
        return request()->session()->put($key, $value);
    }

    public static function reset(){
        request()->session()->flush();
        request()->session()->regenerate();
    }
}
