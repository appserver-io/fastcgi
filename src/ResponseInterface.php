<?php
namespace Crunch\FastCGI;

interface ResponseInterface
{
    public function getContent();
    public function getError();
}
