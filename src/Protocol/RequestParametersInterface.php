<?php
namespace Crunch\FastCGI\Protocol;

use Traversable;

/**
 * Representing the request parameters.
 */
interface RequestParametersInterface
{
    /**
     * @param int $requestId
     *
     * @return Record[]|Traversable
     */
    public function encode($requestId);
}
