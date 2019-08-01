<?php

declare(strict_types=1);

namespace Suricate\Middleware;

class HttpBasicAuth extends \Suricate\Middleware
{
    const AUTHTYPE_ARRAY = 'array';
    const AUTHTYPE_DB = 'database';
    protected $options;

    public function __construct($options = null)
    {
        $this->options = [
            'users' => [],
            'type' => self::AUTHTYPE_ARRAY,
            'path' => '/',
            'realm' => 'restricted area',
            'db' => []
        ];

        if ($options !== null) {
            $this->options = array_merge($this->options, (array) $options);
        }
    }

    private function shouldAuthenticate($request)
    {
        $path = rtrim($this->options["path"], "/");
        $regex = "#" . $path . "(/.*)?$#";

        return preg_match($regex, $request->getRequestUri());
    }

    /**
     * Authenticate against backend dispatcher
     *
     * @param ?string $user     username
     * @param ?string $password password
     * @return bool
     */
    private function authenticate(?string $user, ?string $password): bool
    {
        switch ($this->options['type']) {
            case self::AUTHTYPE_ARRAY:
                return $this->authenticateAgainstArray($user, $password);
            case self::AUTHTYPE_DB:
                return $this->authenticateAgainstDatabase($user, $password);
        }

        return false;
    }

    /**
     * Authenticate against array of usernames / passwords
     *
     * @param ?string $user     username
     * @param ?string $password password
     * @return bool
     */
    private function authenticateAgainstArray(
        ?string $user,
        ?string $password
    ): bool {
        if (
            isset($this->options['users'][$user]) &&
            $this->options['users'][$user] == $password
        ) {
            return true;
        }

        return false;
    }

    private function authenticateAgainstDatabase(
        ?string $user,
        ?string $password
    ): bool {
        return false;
    }

    public function call(&$request, &$response)
    {
        if ($this->shouldAuthenticate($response)) {
            $user = dataGet($_SERVER, 'PHP_AUTH_USER');
            $password = dataGet($_SERVER, 'PHP_AUTH_PW');

            if (!$this->authenticate($user, $password)) {
                app()->abort('401', 'not aut', [
                    "WWW-Authenticate" => sprintf(
                        'Basic realm="%s"',
                        $this->options["realm"]
                    )
                ]);
            }
        }
    }
}
