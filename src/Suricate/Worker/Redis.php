<?php

declare(strict_types=1);

namespace Suricate\Worker;

use Suricate\Suricate;
use Exception;
use Predis\Client;

/**
 * Redis worker class
 */
class Redis
{
    /** @var string $redisHost Redis host name */
    protected $redisHost;

    /** @var string $redisPort Redis port number */
    protected $redisPort;

    /** @var int $maxChildren Number of children to launch */
    protected $maxChildren = 1;

    /** @var string $redisFifoName Redis key to listen to */
    protected $redisFifoName;

    /** @var int $redisFifoTimeout Redis blpop timeout value */
    protected $redisFifoTimeout = 2;

    private $childSlots = [];
    private $includedFiles = [];

    /** @var \Suricate\Logger $logger */
    private $logger;
    private $logPrefix = 'FATHER';

    public function __construct()
    {
        if ($this->redisHost === '') {
            throw new Exception("Redis host is not set");
        }

        if ($this->redisPort === '') {
            throw new Exception("Redis port is not set");
        }

        if ($this->redisFifoName === '') {
            throw new Exception("Redis fifo name is not set");
        }

        $this->logger = Suricate::Logger();
    }

    /**
     * Worker log helper
     *
     * @param string $message
     * @return void
     */
    protected function log($message)
    {
        $this->logger->info('[' . $this->logPrefix . '] ' . $message);
    }

    /**
     * dummy function to hande an incoming job
     * must be overriden in inherited class
     *
     * @param array|null $payload
     * @return void
     */
    public function handleJob(?array $payload)
    {
        $this->logger->fatal(
            'received ' . json_encode($payload) . 'but no handle job defined'
        );
        exit();
    }

    /**
     * Enqueue a job into fifo
     *
     * @param mixed $payload
     * @return void
     */
    public function enqueue($payload)
    {
        $redisSrv = new Client([
            'scheme' => 'tcp',
            'host' => $this->redisHost,
            'port' => $this->redisPort,
            'read_write_timeout' => $this->redisFifoTimeout
        ]);

        $redisSrv->rpush($this->redisFifoName, json_encode($payload));
    }
    /**
     * Worker main run function
     *
     * @return void
     */
    public function run()
    {
        set_time_limit(0);
        posix_setsid();
        set_error_handler([$this, "errorHandler"]);

        $this->log(
            sprintf(
                'Starting worker, redis: %s:%s, queue name %s',
                $this->redisHost,
                $this->redisPort,
                $this->redisFifoName
            )
        );
        $this->log('Launching childrens');
        $father = true;
        // Forking children
        for ($i = 0; $i < $this->maxChildren; $i++) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                die('Impossible to fork');
            } elseif ($pid > 0) {
                $father = true;
                $this->childSlots[$i]['pid'] = $pid;
                $this->childSlots[$i]['start_time'] = time();
                $this->log("Launching child " . ($i + 1) . " with PID " . $pid);
            } elseif ($pid == 0) {
                $childNum = $i + 1;
                $this->logPrefix = 'CHILD_' . $childNum;
                $father = false;
                $this->log("Launched");
                break;
            }
        }
        if ($father) {
            $this->startFather();
        }
        if (!$father) {
            $this->listen();
        }
    }

    public function shutdown()
    {
        $this->signalHandler(SIGTERM);
    }

    /**
     * Father process main loop
     * declare sig handler and enter in an infinite loop
     *
     * @return void
     */
    private function startFather()
    {
        register_shutdown_function([$this, 'shutdown']);

        declare(ticks=1);
        pcntl_signal(SIGINT, [$this, 'signalHandler']);
        pcntl_signal(SIGTERM, [$this, 'signalHandler']);
        pcntl_signal(SIGHUP, [$this, 'signalHandler']);
        pcntl_signal(SIGUSR1, [$this, 'signalHandler']);
        pcntl_signal(SIGCHLD, [$this, 'signalHandler']);
        while (true) {
            sleep(2);
            if (!$this->checkIncludedFiles()) {
                exit();
            }
        }
    }

    /**
     * Children listening function
     * connect to redis and wait for a job
     *
     * @return void
     */
    private function listen()
    {
        $this->log('Connecting to redis');

        $redisSrv = new Client([
            'scheme' => 'tcp',
            'host' => $this->redisHost,
            'port' => $this->redisPort,
            'read_write_timeout' => 0
        ]);
        $this->setIncludedFiles();

        while (true) {
            $result = $redisSrv->blpop(
                $this->redisFifoName,
                $this->redisFifoTimeout
            );
            if ($result) {
                $this->log('Received job ' . json_encode($result[1]));
                $this->handleJob($result[1]);
            }

            if (!$this->checkIncludedFiles()) {
                exit();
            }
        }
    }

    /**
     * Store modification time of all included files of the worker
     * to detect modification and do restart
     *
     * @return void
     */
    private function setIncludedFiles()
    {
        $this->includedFiles = [];
        $allIncludedFiles = get_included_files();

        foreach ($allIncludedFiles as $filename) {
            $includeVersion = filemtime($filename);
            $this->includedFiles[$filename] = $includeVersion;
        }
    }

    /**
     * Compare included files modification times with original ones
     *
     * @return bool
     */
    private function checkIncludedFiles()
    {
        clearstatcache();
        $allIncludedFiles = get_included_files();
        foreach ($allIncludedFiles as $filename) {
            $includeVersion = filemtime($filename);
            if (isset($this->includedFiles[$filename])) {
                $version = $this->includedFiles[$filename];

                if ($includeVersion > $version) {
                    return false;
                }
            } else {
                $this->includedFiles[$filename] = $includeVersion;
            }
        }
        return true;
    }
    /**
     * signal handler, kill children when father receive signal
     *
     * @param int $signalNumber
     * @return void
     */
    private function signalHandler($signalNumber)
    {
        $this->log('received signal ' . $signalNumber);
        foreach ($this->childSlots as $index => $child) {
            $this->log(
                "Child[" . $index . "] Sending SIGTERM TO " . $child['pid']
            );
            posix_kill($child['pid'], SIGTERM);
            pcntl_wait($status);
            $this->log(
                "Child[" . $index . "] returned with status[" . $status . "]"
            );
        }

        exit();
    }

    private function errorHandler(
        $errNumber,
        $errMessage,
        $filename,
        $lineNumber,
        $vars
    ) {
        $this->log(
            'Error occured: ' .
                $errMessage .
                '/' .
                $errNumber .
                ', filename: ' .
                $filename .
                ' on line: ' .
                $lineNumber .
                '. vars : ' .
                json_encode($vars)
        );
    }
}
