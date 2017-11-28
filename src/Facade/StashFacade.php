<?php

namespace ZingleCom\Stash\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Class StashFacade
 */
class StashFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'stash.cache';
    }
}
