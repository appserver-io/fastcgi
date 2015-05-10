<?php
namespace Crunch\FastCGI\Strings;

function cut($string, $length)
{
    return [substr($string, 0, $length), substr($string, $length)];
}
