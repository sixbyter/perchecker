<?php

namespace Sixbyte\Perchecker;

use Illuminate\Support\Facades\Facade;

class PercheckerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Perchecker';
    }
}
