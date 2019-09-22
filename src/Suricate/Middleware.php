<?php

namespace Suricate;

abstract class Middleware implements \Suricate\Interfaces\IMiddleware
{
    abstract public function call(Request &$request, Request &$reponse);
}
