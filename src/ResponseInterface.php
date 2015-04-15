<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 15.04.15
 * Time: 22:34
 */
namespace Crunch\FastCGI;

interface ResponseInterface
{
    /**
     * @return string
     */
    public function getContent();

    /**
     * @return string
     */
    public function getError();
}
