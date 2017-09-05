<?php
namespace Suricate\Session;

class Native extends \Suricate\Session
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

    public function getId()
    {
        return session_id();
    }

    public function regenerate()
    {
        return session_regenerate_id();
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
