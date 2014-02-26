<?php
namespace Fwk\Session;

class Native extends \Fwk\Session
{
    public function __construct()
    {
        $this->loadSession();
    }

    private function loadSession()
    {
        if (session_id() == '') {
            session_start();
        }
    }

    public function read($key)
    {
        $this->loadSession();
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
    }

    public function write($key, $data)
    {
        $this->loadSession();
        $_SESSION[$key] = $data;
    }

    public function destroy($key)
    {
        $this->loadSession();
        unset($_SESSION[$key]);
    }

    public function close()
    {
        session_destroy();
    }
}