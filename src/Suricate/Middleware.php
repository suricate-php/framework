<?php
namespace Suricate;

abstract class Middleware implements \Suricate\Interfaces\IMiddleware
{
    abstract function call(Request &$request, Request &$reponse);
}
