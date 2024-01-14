<?php

declare(strict_types=1);

namespace Suricate;

use InvalidArgumentException;
use Suricate\Cache\Apc as CacheApc;
use Suricate\Cache\File as CacheFile;
use Suricate\Cache\Memcache as CacheMemcache;
use Suricate\Cache\Memcached as CacheMemcached;
use Suricate\Cache\Redis as CacheRedis;
use Suricate\Event\EventDispatcher;
use Suricate\Session\Native as SessionNative;

/**
 * Suricate - Another micro PHP framework
 *
 * @author      Mathieu LESNIAK <mathieu@lesniak.fr>
 * @copyright   2013-2024 Mathieu LESNIAK
 * @version     0.5.8
 * @package     Suricate
 *
 * @method static \Suricate\App                     App($newInstance = false)             Get instance of App service
 * @method static \Suricate\Cache                   Cache($newInstance = false)           Get instance of Cache service
 * @method static \Suricate\CacheMemcache           CacheMemcache($newInstance = false)   Get instance of CacheMemcache service
 * @method static \Suricate\CacheMemcached          CacheMemcached($newInstance = false)  Get instance of CacheMemcached service
 * @method static \Suricate\CacheRedis              CacheRedis($newInstance = false)      Get instance of CacheRedis service
 * @method static \Suricate\CacheApc                CacheApc($newInstance = false)        Get instance of CacheApc service
 * @method static \Suricate\CacheFile               CacheFile($newInstance = false)       Get instance of CacheFile service
 * @method static \Suricate\Curl                    Curl($newInstance = false)            Get instance of Curl service
 * @method static \Suricate\Database                Database($newInstance = false)        Get instance of Database service
 * @method static \Suricate\Error                   Error($newInstance = false)           Get instance of Error service
 * @method static \Suricate\Event\EventDispatcher   EventDispatcher($newInstance = false) Get instance of EventDispatcher service
 * @method static \Suricate\I18n                    I18n($newInstance = false)            Get instance of I18n service
 * @method static \Suricate\Logger                  Logger($newInstance = false)          Get instance of Logger service
 * @method static \Suricate\Request                 Request($newInstance = false)         Get instance of Request service
 * @method static \Suricate\Request                 Response($newInstance = false)        Get instance of Request/Response service
 * @method static \Suricate\Router                  Router($newInstance = false)          Get instance of Router service
 * @method static \Suricate\Session                 Session($newInstance = false)         Get instance of Session service
 * @method static \Suricate\SessionNative           SessionNative($newInstance = false)   Get instance of Session service
 * @method static \Suricate\SessionCookie           SessionCookie($newInstance = false)   Get instance of Session service
 * @method static \Suricate\SessionMemcache         SessionMemcache($newInstance = false) Get instance of Session service
 */

class Suricate
{
    const VERSION = '0.5.6';

    const CONF_DIR = '/conf/';

    private $config = [];
    private $configFile = [];

    private $useAutoloader = false;

    private static $servicesContainer;
    private static $servicesRepository;

    private $servicesList = [
        'App' => App::class,
        'Cache' => Cache::class,
        'CacheMemcache' => CacheMemcache::class,
        'CacheMemcached' => CacheMemcached::class,
        'CacheRedis' => CacheRedis::class,
        'CacheApc' => CacheApc::class,
        'CacheFile' => CacheFile::class,
        'Curl' => Curl::class,
        'Database' => Database::class,
        'Error' => Error::class,
        'EventDispatcher' => EventDispatcher::class,
        'I18n' => I18n::class,
        'Logger' => Logger::class,
        'Request' => Request::class,
        'Response' => Request::class,
        'Router' => Router::class,
        'Session' => Session::class,
        'SessionNative' => SessionNative::class,
        'SessionCookie' => '\Suricate\Session\Cookie',
        'SessionMemcache' => '\Suricate\Session\Memcache'
    ];

    /**
     * Suricate contructor
     *
     * @param array $paths Application paths
     * @param string|array|null $configFile path of configuration file(s)
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct($paths = [], $configFile = null)
    {
        if ($configFile !== null) {
            $this->setConfigFile($configFile);
        }

        // Load helpers
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'Helper.php';

        $this->loadConfig();
        $this->setAppPaths($paths);

        if ($this->useAutoloader) {
            // Configure autoloader
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'AutoLoader.php';
            AutoLoader::register();
        }

        // Define error handler
        set_exception_handler(['\Suricate\Error', 'handleException']);
        set_error_handler(['\Suricate\Error', 'handleError']);
        register_shutdown_function(['\Suricate\Error', 'handleShutdownError']);

        self::$servicesRepository = new Container();

        $this->initServices();
    }

    /**
     * Get app configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    private function setAppPaths($paths = [])
    {
        foreach ($paths as $key => $value) {
            $this->config['App']['path.' . $key] = realpath($value);
        }

        return $this;
    }
    /**
     * Initialize Framework services
     * @return void
     */
    private function initServices()
    {
        self::$servicesRepository->setWarehouse($this->servicesList);

        self::$servicesRepository['Request']->parse();
        if (isset($this->config['App']['locale'])) {
            $this->config['I18n'] = [
                'locale' => $this->config['App']['locale']
            ];
        }

        // Define constants
        if (isset($this->config['Constants'])) {
            foreach (
                $this->config['Constants']
                as $constantName => $constantValue
            ) {
                $constantName = strtoupper($constantName);
                define($constantName, $constantValue);
            }
        }

        // first sync, && init, dependency to Suricate::request
        self::$servicesContainer = clone self::$servicesRepository;

        foreach (array_keys($this->servicesList) as $serviceName) {
            if (isset($this->config[$serviceName])) {
                self::$servicesRepository[$serviceName]->configure(
                    $this->config[$serviceName]
                );

                /**
                 TODO : remove sync in service creation
                 */
                self::$servicesContainer = clone self::$servicesRepository;
            }
        }

        // final sync, repository is complete
        self::$servicesContainer = clone self::$servicesRepository;
    }

    public static function hasService(string $serviceName): bool
    {
        return isset(self::$servicesContainer[$serviceName]);
    }

    private function setConfigFile($configFile)
    {
        foreach ((array) $configFile as $file) {
            if (is_file($file)) {
                $this->configFile[] = $file;
            }
        }

        return $this;
    }

    private function parseYamlConfig($filename) {
        return yaml_parse_file($filename, 0, $ndocs,
        [
            '!include' => function($value, $tag, $flags) use($filename)
            {
                $directory = dirname($filename);
                return $this->parseYamlConfig("$directory/$value");
            }
        ]);
    }
    /**
     * Load framework configuration from ini file
     * @return void
     */
    private function loadConfig()
    {
        $userConfig = [];
        if (count($this->configFile)) {
            $userConfig = [];
            foreach ($this->configFile as $configFile) {
                if (stripos($configFile, 'yml') !== false) {
                    $userConfig = array_merge_recursive($userConfig, $this->parseYamlConfig($configFile));
                } else {
                    $userConfig = array_merge_recursive(
                        $userConfig,
                        (array) parse_ini_file($configFile, true, INI_SCANNER_TYPED)
                    );
                }
            }

            // Advanced ini parsing, split key with '.' into subarrays
            foreach ($userConfig as $section => $configData) {
                foreach ($configData as $name => $value) {
                    if (stripos($name, '.') !== false) {
                        $subkeys = explode('.', $name);
                        unset($userConfig[$section][$name]);
                        $str =
                            "['" . implode("']['", $subkeys) . "'] = \$value;";
                        eval("\$userConfig[\$section]" . $str);
                    }
                }
            }
        }

        foreach ($this->getDefaultConfig() as $context => $directives) {
            if (isset($userConfig[$context])) {
                $this->config[$context] = array_merge(
                    $directives,
                    $userConfig[$context]
                );
                unset($userConfig[$context]);
            } else {
                $this->config[$context] = $directives;
            }
        }

        $this->config = array_merge($this->config, $userConfig);
        $this->configureAppMode();
    }

    private function configureAppMode()
    {
        $errorReporting = true;
        $errorDumpContext = true;
        $logLevel = Logger::LOGLEVEL_WARN;
        $logFile = 'php://stdout';

        if (isset($this->config['App']['mode'])) {
            switch ($this->config['App']['mode']) {
                case App::DEVELOPMENT_MODE:
                    $errorReporting = true;
                    $errorDumpContext = true;
                    $logLevel = Logger::LOGLEVEL_INFO;
                    $logFile = 'php://stdout';
                    break;
                case App::DEBUG_MODE:
                    $errorReporting = true;
                    $errorDumpContext = true;
                    $logLevel = Logger::LOGLEVEL_DEBUG;
                    $logFile = 'php://stdout';
                    break;
                case App::PRELIVE_MODE:
                    $errorReporting = true;
                    $errorDumpContext = false;
                    $logLevel = Logger::LOGLEVEL_WARN;
                    $logFile = 'php://stderr';
                    break;
                case App::PRODUCTION_MODE:
                    $errorReporting = false;
                    $errorDumpContext = false;
                    $logLevel = Logger::LOGLEVEL_WARN;
                    $logFile = 'php://stderr';
                    break;
            }
        }
        if (isset($this->config['Logger']['level'])) {
            $logLevel = $this->config['Logger']['level'];
        }
        if (isset($this->config['Logger']['logfile'])) {
            $logFile = $this->config['Logger']['logfile'];
        }
        if (isset($this->config['Error']['report'])) {
            $errorReporting = $this->config['Error']['report'];
        }
        if (isset($this->config['Error']['dumpContext'])) {
            $errorDumpContext = $this->config['Error']['dumpContext'];
        }

        $this->config['Logger']['level'] = $logLevel;
        $this->config['Logger']['logfile'] = $logFile;
        $this->config['Error']['report'] = $errorReporting;
        $this->config['Error']['dumpContext'] = $errorDumpContext;
    }
    /**
     * Default setup template
     * @return array setup
     */
    private function getDefaultConfig()
    {
        return [
            'Router' => [],
            'Logger' => [
                'enabled' => true
            ],
            'App' => ['base_uri' => '/']
        ];
    }

    public function run()
    {
        self::$servicesContainer['Router']->doRouting();
    }

    public static function __callStatic($name, $arguments)
    {
        if (isset($arguments[0]) && $arguments[0] === true) {
            return clone self::$servicesRepository[$name];
        }

        return self::$servicesContainer[$name];
    }

    public function registerService(string $serviceName, string $serviceClass): self
    {
        if (isset(self::$servicesContainer[$serviceName])) {
            throw new InvalidArgumentException('Service ' . $serviceName . ' already registered');
        }

        self::$servicesContainer->addToWarehouse($serviceName, $serviceClass);
        self::$servicesRepository->addToWarehouse($serviceName, $serviceClass);
        if (isset($this->config[$serviceName])) {
            self::$servicesContainer[$serviceName]->configure(
                $this->config[$serviceName]
            );
        }
        return $this;
    }
}
