<?php
namespace Fwk\Interfaces;

interface ISession
{
    public function getInstance();

    public function read($key);

    public function write($key, $data);

    public function destroy($key);

    public function close();
}
