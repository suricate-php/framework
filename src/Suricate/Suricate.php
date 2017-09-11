<?php
/**
 * Fwk - Another micro PHP 5 framework
 *
 * @author      Mathieu LESNIAK <mathieu@lesniak.fr>
 * @copyright   2013-2014 Mathieu LESNIAK
 * @version     0.1
 * @package     Suricate
 *
 * @method App          App() App() Get instance of App service
 * @method Database     Database() Database() Get instance of Database service
 * @method Error        Error() Error() Get instance of Error service
 * @method I18n         I18n() I18n() Get instance of I18n service
 * @method Request      Request() Request() Get instance of Request service
 */
namespace Suricate;

class Suricate
{

    const VERSION = '0.1.7';

    const CONF_DIR = '/conf/';

    protected $router;

    private $config;
    private $configFile;

    private $useAutoloader = false;

    private static $servicesContainer;
    private static $servicesRepository;

    private $servicesList = array(
        'Logger'            => '\Suricate\Logger',
        'App'               => '\Suricate\App',
        'I18n'              => '\Suricate\I18n',
        'Error'             => '\Suricate\Error',
        'Router'            => '\Suricate\Router',
        'Request'           => '\Suricate\Request',
        'Database'          => '\Suricate\Database',
        'Cache'             => '\Suricate\Cache',
        'CacheMemcache'     => '\Suricate\Cache\Memcache',
        'CacheApc'          => '\Suricate\Cache\Apc',
        'CacheFile'         => '\Suricate\Cache\File',
        'Curl'              => '\Suricate\Curl',
        'Response'          => '\Suricate\Request',
        'Session'           => '\Suricate\Session',
        'SessionNative'     => '\Suricate\Session\Native',
        'SessionCookie'     => '\Suricate\Session\Cookie',
        'SessionMemcache'   => '\Suricate\Session\Memcache',
    );


    public function __construct($paths = array(), $configFile = null)
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
        set_exception_handler(array('\Suricate\Error', 'handleException'));
        set_error_handler(array('\Suricate\Error', 'handleError'));
        register_shutdown_function(array('\Suricate\Error', 'handleShutdownError'));

        self::$servicesRepository = new Container();

        $this->initServices();
    }

    private function setAppPaths($paths = array())
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
            $this->config['I18n'] = array('locale' => $this->config['App']['locale']);
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
        $userConfig = array();
        if ($this->configFile !== null) {
            $userConfig = array();
            foreach ($this->configFile as $configFile) {
                $userConfig = array_merge($userConfig, parse_ini_file($configFile, true));
            }

            // Advanced ini parsing, split key with '.' into subarrays
            foreach ($userConfig as $section => $configData) {
                foreach ($configData as $name => $value) {
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
        $errorReporting     = false;
        $errorDumpContext   = false;
        $logLevel           = Logger::LOGLEVEL_WARN;
        
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

        $this->config['Error']['report']        = $errorReporting;
        $this->config['Error']['dumpContext']   = $errorDumpContext;
        $this->config['Logger']['level']        = $logLevel;
        $this->config['Logger']['logfile']      = $logFile;
    }

    /**
     * Default setup template
     * @return array setup
     */
    private function getDefaultConfig()
    {
        return array(
                'Router'    => [],
                'Session'   => ['type' => 'native'],
                'Logger'    => [
                    'enabled'   => true,
                    'level'     => Logger::LOGLEVEL_INFO,
                    ],
                'App'       => ['base_uri' => '/'],
                );
    }

    public function run()
    {
        self::$servicesContainer['Router']->doRouting();
    }

    public static function __callStatic($name, $arguments)
    {
        if (isset($arguments[0]) && $arguments[0] === true) {
            return clone self::$servicesRepository[$name];
        } else {
            return self::$servicesContainer[$name];
        }
    }
}
