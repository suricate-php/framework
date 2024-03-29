<?php

declare(strict_types=1);

namespace Suricate\Session;

use Suricate\Session;

class Native extends Session
{
    public function __construct()
    {
        parent::__construct();
        if (php_sapi_name() === 'cli') {
            return;
        }
        $this->loadSession();
    }

    private function loadSession()
    {
        if (session_id() === '') {
            session_start();
        }
    }

    /**
     * Get current session id
     *
     * @return string
     */
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

    /**
     * Close current session
     *
     * @return void
     */
    public function close()
    {
        session_destroy();
    }
}
