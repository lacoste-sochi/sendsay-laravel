<?php

namespace Rutrue\Sendsay\Facades;

use Illuminate\Support\Facades\Facade;

class Sendsay extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sendsay';
    }
}