<?php
/**
 * Fwk - Another micro PHP 5 framework
 *
 * @author      Mathieu LESNIAK <mathieu@lesniak.fr>
 * @copyright   2013 Mathieu LESNIAK
 * @version     0.2
 * @package     Fwk
 */
namespace Fwk;

class Fwk
{

    const VERSION = '0.3';

    const CONF_DIR = '/conf/';

    protected $router;

    private $config;
    private $configFile;
    private $baseDir;

    private static $servicesContainer;
    private static $servicesRepository;

    private $servicesList = array(
        'Logger'            => '\Fwk\Logger',
        'App'               => '\Fwk\App',
        'I18n'              => '\Fwk\I18n',
        'Router'            => '\Fwk\Router',
        'Request'           => '\Fwk\Request',
        'Database'          => '\Fwk\Database',
        'Cache'             => '\Fwk\Cache',
        'CacheMemcache'     => '\Fwk\Cache\Memcache',
        'CacheApc'          => '\Fwk\Cache\Apc',
        'Curl'              => '\Fwk\Curl',
        'Response'          => '\Fwk\Request',
        'Error'             => '\Fwk\Error',
        'Session'           => '\Fwk\Session',
        'SessionNative'     => '\Fwk\Session\Native',
        'SessionCookie'     => '\Fwk\Session\Cookie',
        'SessionMemcache'   => '\Fwk\Session\Memcache',
    );


    public function __construct($configFile = null)
    {
        $this->baseDir = dirname(__DIR__);

        if ($configFile !== null) {
            $this->setConfigFile($configFile);
        }

        $this->loadConfig();

        // Load helpers
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'Helper.php';

        // Configure autoloader
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'AutoLoader.php';
        AutoLoader::register();

        // Define error handler
        /*set_exception_handler(array('\Fwk\Error', 'handleException'));
        set_error_handler(array('\Fwk\Error', 'handleError'));
        register_shutdown_function(array('\Fwk\Error', 'handleShutdownError'));
*/
        static::$servicesRepository = new Container();


        $this->initServices();
    }

    /**
     * Initialize Framework services
     * @return null
     */
    private function initServices()
    {
        static::$servicesRepository->setWarehouse($this->servicesList);

        static::$servicesRepository['Request']->parse();
        if (isset($this->config['App']['locale'])) {
            $this->config['I18n'] = array('locale' => $this->config['App']['locale']);
        }
        // first sync, && init, dependency to Fwk::request
        static::$servicesContainer = clone static::$servicesRepository;

        foreach (array_keys($this->servicesList) as $serviceName) {
            if (isset($this->config[$serviceName])) {
                static::$servicesRepository[$serviceName]->configure($this->config[$serviceName]);

                /**
                 TODO : remove sync in service creation
                */
                static::$servicesContainer = clone static::$servicesRepository;
            }
        }

        if (isset($this->config['Constants'])) {
            foreach ($this->config['Constants'] as $constantName => $constantValue) {
                $constantName = strtoupper($constantName);
                define($constantName, $constantValue);
            }
        }



        // final sync, repository is complete
        static::$servicesContainer = clone static::$servicesRepository;
    }

    private function setConfigFile($configFile)
    {
        if (is_file($this->baseDir . self::CONF_DIR . $configFile)) {
            $this->configFile = $configFile;
        }

        return $this;
    }
    /**
     * Load framework configuration from ini file
     * @return null
     */
    private function loadConfig()
    {
        if ($this->configFile !== null) {
            $userConfig = parse_ini_file($this->baseDir . self::CONF_DIR . $this->configFile, true);

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
        } else {
            $userConfig = array();
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
    }

    /**
     * Default setup template
     * @return array setup
     */
    private function getDefaultConfig()
    {
        $errorReporting = false;
        $logLevel       = 4;

        if (isset($this->config['App']['mode'])) {
            if ($this->config['App']['mode'] == 'development') {
                $errorReporting = true;
                $logLevel       = 4;
            }
        }

        return array(
                'Paths' 	=> array(),
                'Router' 	=> array(),
                'Error'     => array('report' => $errorReporting),
                'Session'   => array('type' => 'native'),
                'Logger'	=> array(
                                'logfile'   => 'php://output',
                                'enabled'	=> true,
                                'level'		=> $logLevel
                            ),
                );
    }

    public function boot()
    {
        // Boot application, if available
        if (isset($this->config['App']['root']) 
            && is_file($this->config['App']['root'] . DIRECTORY_SEPARATOR . 'boot.php')) {
            require $this->config['App']['root'] . DIRECTORY_SEPARATOR . 'boot.php';
        }
    }

    public function run()
    {
        $this->boot();
        static::$servicesContainer['Router']->doRouting();
    }



   
    public static function __callStatic($name, $arguments)
    {
        if (isset($arguments[0]) && $arguments[0] === true) {
            return clone static::$servicesRepository[$name];
        } else {
            return static::$servicesContainer[$name];
        }
    }
}
