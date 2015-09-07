<?php

/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 15.04.15
 * Time: 22:34.
 */
namespace Crunch\FastCGI\Protocol;

use Crunch\FastCGI\ReaderWriter\ReaderInterface;

interface ResponseInterface
{
    /**
     * @return ReaderInterface
     */
    public function getContent();

    /**
     * @return ReaderInterface
     */
    public function getError();
}
