<?php
namespace Suricate\Middleware;
use Suricate\Suricate;

class HttpBasicAuth implements \Suricate\Interfaces\IMiddleware
{
    const AUTHTYPE_ARRAY    = 'array';
    const AUTHTYPE_DB       = 'database';
    protected $realm;

    public function __construct($options = null)
    {
        $this->options = array(
            'users' => array(),
            'type'  => self::AUTHTYPE_ARRAY,
            'path'  => '/',
            'realm' => 'restricted area',
            'db'    => array(),
            );

        if ($options !== null) {
            $this->options = array_merge($this->options, (array)$options);
        }
    }
    
    private function shouldAuthenticate($request)
    {
        $path   = rtrim($this->options["path"], "/");
        $regex  = "#" . $path . "(/.*)?$#";
        
        return preg_match($regex, $request->getRequestUri());
    }

    private function authenticate($user, $password)
    {
        switch ($this->options['type']) {
            case self::AUTHTYPE_ARRAY:
                return $this->authenticateAgainstArray($user, $password);
                break;
            case self::AUTHTYPE_DB:
                return $this->authenticateAgainstDatabase($user, $password);
                break;
        }
    }

    private function authenticateAgainstArray($user, $password)
    {
        if (isset($this->options['users'][$user]) && $this->options['users'][$user] == $password) {
            return true;
        }

        return false;
    }

    private function authenticateAgainstDatabase($user, $password)
    {

    }


    public function call(&$response)
    {
        if ($this->shouldAuthenticate($response)) {
            $user       = dataGet($_SERVER, 'PHP_AUTH_USER');
            $password   = dataGet($_SERVER, 'PHP_AUTH_PW');

            if (!$this->authenticate($user, $password)) {
                app()->abort(
                    '401',
                    'not aut',
                    array(
                        "WWW-Authenticate" => sprintf('Basic realm="%s"', $this->options["realm"])
                    )
                );
                

            }
        }
    }
}