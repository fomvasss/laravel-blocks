<?php

namespace Fomvasss\Blocks\Facades;

class Block extends \Illuminate\Support\Facades\Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Fomvasss\Blocks\BlockService::class;
    }
}
