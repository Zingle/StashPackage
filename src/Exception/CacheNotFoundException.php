<?php

namespace ZingleCom\Stash\Exception;

/**
 * Class CacheNotFoundException
 */
class CacheNotFoundException extends \Exception
{
    /**
     * CacheNotFoundException constructor.
     * @param string $cacheName
     */
    public function __construct($cacheName)
    {
        parent::__construct(sprintf('No cache with name "%s" found.', $cacheName));
    }
}
