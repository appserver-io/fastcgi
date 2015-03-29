<?php
namespace Crunch\FastCGI;

interface RequestInterface
{
    public function getID();
    public function getParameters();
    public function getStdin();
}
