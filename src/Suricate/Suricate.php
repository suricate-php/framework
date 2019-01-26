<?php declare(strict_types=1);
/**
 * Suricate - Another micro PHP framework
 *
 * @author      Mathieu LESNIAK <mathieu@lesniak.fr>
 * @copyright   2013-2019 Mathieu LESNIAK
 * @version     0.1.16
 * @package     Suricate
 *
 * @method static \Suricate\App      App()      Get instance of App service
 * @method static \Suricate\Database Database() Get instance of Database service
 * @method static \Suricate\Error    Error()    Get instance of Error service
 * @method static \Suricate\I18n     I18n()     Get instance of I18n service
 * @method static \Suricate\Request  Request()  Get instance of Request service
 * @method static \Suricate\Logger   Logger()   Get instance of Logger service
 */
namespace Suricate;

class Suricate
{

    const VERSION = '0.2.0';

    const CONF_DIR = '/conf/';

    private $config  = [];
    private $configFile = [];

    private $useAutoloader = false;

    private static $servicesContainer;
    private static $servicesRepository;

    private $servicesList = [
        'Logger'            => '\Suricate\Logger',
        'App'               => '\Suricate\App',
        'I18n'              => '\Suricate\I18n',
        'Error'             => '\Suricate\Error',
        'Router'            => '\Suricate\Router',
        'Request'           => '\Suricate\Request',
        'Database'          => '\Suricate\Database',
        'Cache'             => '\Suricate\Cache',
        'CacheMemcache'     => '\Suricate\Cache\Memcache',
        'CacheMemcached'    => '\Suricate\Cache\Memcached',
        'CacheApc'          => '\Suricate\Cache\Apc',
        'CacheFile'         => '\Suricate\Cache\File',
        'Curl'              => '\Suricate\Curl',
        'Response'          => '\Suricate\Request',
        'Session'           => '\Suricate\Session',
        'SessionNative'     => '\Suricate\Session\Native',
        'SessionCookie'     => '\Suricate\Session\Cookie',
        'SessionMemcache'   => '\Suricate\Session\Memcache',
    ];


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

    public function getConfig()
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
     * @return null
     */
    private function initServices()
    {
        self::$servicesRepository->setWarehouse($this->servicesList);

        self::$servicesRepository['Request']->parse();
        if (isset($this->config['App']['locale'])) {
            $this->config['I18n'] = ['locale' => $this->config['App']['locale']];
        }
        // first sync, && init, dependency to Suricate::request
        self::$servicesContainer = clone self::$servicesRepository;

        foreach (array_keys($this->servicesList) as $serviceName) {
            if (isset($this->config[$serviceName])) {
                self::$servicesRepository[$serviceName]->configure($this->config[$serviceName]);

                /**
                 TODO : remove sync in service creation
                */
                self::$servicesContainer = clone self::$servicesRepository;
            }
        }

        if (isset($this->config['Constants'])) {
            foreach ($this->config['Constants'] as $constantName => $constantValue) {
                $constantName = strtoupper($constantName);
                define($constantName, $constantValue);
            }
        }

        // final sync, repository is complete
        self::$servicesContainer = clone self::$servicesRepository;
    }

    public function hasService(string $serviceName): bool
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
    /**
     * Load framework configuration from ini file
     * @return null
     */
    private function loadConfig()
    {
        $userConfig = [];
        if (count($this->configFile)) {
            $userConfig = [];
            foreach ($this->configFile as $configFile) {
                $userConfig = array_merge_recursive($userConfig, parse_ini_file($configFile, true, INI_SCANNER_TYPED));
            }

            // Advanced ini parsing, split key with '.' into subarrays
            foreach ($userConfig as $section => $configData) {
                foreach (array_keys($configData) as $name) {
                    if (stripos($name, '.') !== false) {
                        $subkeys = explode('.', $name);
                        unset($userConfig[$section][$name]);
                        $str = "['" . implode("']['", $subkeys) . "'] = \$value;";
                        eval("\$userConfig[\$section]" . $str);
                    }
                }
            }
        }

        foreach ($this->getDefaultConfig() as $context => $directives) {
            if (isset($userConfig[$context])) {
                $this->config[$context] = array_merge($directives, $userConfig[$context]);
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
        $errorReporting     = true;
        $errorDumpContext   = true;
        $logLevel           = Logger::LOGLEVEL_WARN;
        $logFile            = 'php://stdout';
        
        if (isset($this->config['App']['mode'])) {
            switch ($this->config['App']['mode']) {
                case App::DEVELOPMENT_MODE:
                    $errorReporting     = true;
                    $errorDumpContext   = true;
                    $logLevel           = Logger::LOGLEVEL_INFO;
                    $logFile            = 'php://stdout';
                    break;
                case App::DEBUG_MODE:
                    $errorReporting     = true;
                    $errorDumpContext   = true;
                    $logLevel           = Logger::LOGLEVEL_DEBUG;
                    $logFile            = 'php://stdout';
                    break;
                case App::PRELIVE_MODE:
                    $errorReporting     = true;
                    $errorDumpContext   = false;
                    $logLevel           = Logger::LOGLEVEL_WARN;
                    $logFile            = 'php://stderr';
                    break;
                case App::PRODUCTION_MODE:
                    $errorReporting     = false;
                    $errorDumpContext   = false;
                    $logLevel           = Logger::LOGLEVEL_WARN;
                    $logFile            = 'php://stderr';
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

        $this->config['Logger']['level']        = $logLevel;
        $this->config['Logger']['logfile']      = $logFile;
        $this->config['Error']['report']        = $errorReporting;
        $this->config['Error']['dumpContext']   =  $errorDumpContext;
    }
    /**
     * Default setup template
     * @return array setup
     */
    private function getDefaultConfig()
    {
        return [
            'Router'    => [],
            'Logger'    => [
                'enabled'   => true,
            ],
            'App'       => ['base_uri' => '/'],
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
}
