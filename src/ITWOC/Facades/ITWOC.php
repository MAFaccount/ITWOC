<?php

namespace ITWOC\Facades;

use Illuminate\Support\Facades\Facade;

class ITWOC extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        self::clearResolvedInstance('itwoc');
        return 'itwoc';
    }
}