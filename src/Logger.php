<?php
namespace Fwk;

class Logger extends Service
{
    const LOGLEVEL_FATAL    = 0;
    const LOGLEVEL_ERROR    = 1;
    const LOGLEVEL_WARN     = 2;
    const LOGLEVEL_INFO     = 3;
    const LOGLEVEL_DEBUG    = 4;

    protected $parametersList = array(
        'logfile',
        'enabled',
        'level',
        'timestamp'
    );

    private $resource;

    protected $levels = array(
        self::LOGLEVEL_FATAL    => 'FATAL',
        self::LOGLEVEL_ERROR    => 'ERROR',
        self::LOGLEVEL_WARN     => 'WARN',
        self::LOGLEVEL_INFO     => 'INFO',
        self::LOGLEVEL_DEBUG    => 'DEBUG'
    );

    public function __construct()
    {
        parent::__construct();
    }

    public function log($message, $level)
    {
        if ($this->resource == null && $this->logfile !== null) {
            $this->resource = fopen($this->logfile, 'a+');
        }

        if ($level <= $this->level && $this->enabled) {
            if ($this->timestamp) {
                $message = "[" . date('M d H:i:s') . "] " . $message;
            }
            fputs($this->resource, '[' . $this->levels[$level] . '] '. (string) $message . PHP_EOL);

        }
        
        return $this;
    }

    public function fatal($message)
    {
        return $this->log($message, self::LOGLEVEL_FATAL);
    }

    public function error($message)
    {
        return $this->log($message, self::LOGLEVEL_ERROR);
    }

    public function warn($message)
    {
        return $this->log($message, self::LOGLEVEL_WARN);
    }

    public function info($message)
    {
        return $this->log($message, self::LOGLEVEL_INFO);
    }

    public function debug($message)
    {
        return $this->log($message, self::LOGLEVEL_DEBUG);
    }
}
