<?php
namespace Crunch\FastCGI;

use Traversable;

/**
 * Representing the request parameters
 */
interface RequestParametersInterface
{
    /**
     * @param int $requestId
     * @return Record[]|Traversable
     */
    public function encode($requestId);
}
