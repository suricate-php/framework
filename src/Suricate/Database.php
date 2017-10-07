<?php
namespace Suricate;

/**
 * Database extension for Suricate
 *
 * @package Suricate
 * @author  Mathieu LESNIAK <mathieu@lesniak.fr>
 *
 * @property array $configs array of predefined DB configurations
 */


class Database extends Service
{
    protected $parametersList = array(
                                    'configs'
                                );

    private $config;
    private $handler;
    private $statement;

    public function __construct()
    {
        $this->configs = array();
        $this->handler = false;
    }

    public function configure($parameters = array())
    {
        $dbConfs = array();
        foreach ($parameters as $name => $value) {
            if (is_array($value)) {
                $dbConfs[$name] = $value;
            } else {
                $dbConfs['default'][$name] = $value;
            }
        }
        $parameters = array('configs' => $dbConfs);
        parent::configure($parameters);
    }

    public function setConfigs($configs)
    {
        $this->configs = $configs;

        return $this;
    }

    public function getConfigs()
    {
        return $this->configs;
    }

    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    private function connect()
    {
        if ($this->handler !== false) {
            return;
        }

        if ($this->config !== null && isset($this->configs[$this->config])) {
            $params = $this->configs[$this->config];
        } else {
            $confs  = array_values($this->configs);
            $params = array_shift($confs);
        }

        $pdoAttributes = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION);
        switch ($params['type']) {
            case 'mysql':
                $this->configurePDOMySQL($params, $pdoDsn, $pdoUsername, $pdoPassword, $pdoAttributes);
                break;
            case 'sqlite':
                $this->configurePDOSQLite($params, $pdoDsn, $pdoUsername, $pdoPassword);
                break;
            default:
                throw new \Exception('Unsupported PDO DB handler');
        }

        try {
            $this->handler = new \PDO($pdoDsn, $pdoUsername, $pdoPassword);
            foreach ($pdoAttributes as $attributeKey => $attributeValue) {
                $this->handler->setAttribute($attributeKey, $attributeValue);
            }
        } catch (\Exception $e) {
            throw new \Exception("Cannot connect to database");
        }
    }

    /**
     * Execute a query against database. Create a connection if not already alvailable
     * @param  string $sql        Query
     * @param  array  $parameters Parameters used in query
     * @return Database
     */
    public function query($sql, $parameters = array())
    {
        $this->connect();

        $this->statement = $this->handler->prepare($sql);
        $this->statement->execute($parameters);

        return $this;
    }

    public function fetchAll($mode = \PDO::FETCH_ASSOC)
    {
        return $this->statement->fetchAll($mode);
    }

    public function fetch($mode = \PDO::FETCH_ASSOC)
    {
        return $this->statement->fetch($mode);
    }

    public function fetchColumn($colNb = 0)
    {
        return $this->statement->fetchColumn($colNb);
    }

    public function fetchObject()
    {
        return $this->statement->fetch(\PDO::FETCH_OBJ);
    }

    public function lastInsertId()
    {
        return $this->handler->lastInsertId();
    }

    public function beginTransaction()
    {

    }

    public function commit()
    {

    }

    public function rollback()
    {

    }

    public function inTransaction()
    {

    }

    public function getColumnCount()
    {
        return $this->statement->columnCount();
    }

    private function configurePDOMySQL($params, &$pdoDsn, &$pdoUsername, &$pdoPassword, &$pdoAttributes)
    {
        $defaultParams = array(
            'hostname' => null,
            'database' => null,
            'username' => null,
            'password' => null,
            'encoding' => null
        );

        $params = array_merge($defaultParams, $params);

        $pdoDsn         = 'mysql:host=' . $params['hostname'] . ';dbname=' . $params['database'];
        $pdoUsername    = $params['username'];
        $pdoPassword    = $params['password'];
        if ($params['encoding'] != null) {
            $pdoAttributes[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES " . $params['encoding'];
        }
    }

    private function configurePDOSQLite($params, &$pdoDsn, &$pdoUsername, &$pdoPassword)
    {
        $defaultParams = array(
            'username'  => null,
            'password'  => null,
            'memory'    => null,
            'file'      => null,
        );

        $params = array_merge($defaultParams, $params);
        
        $pdoDsn         = 'sqlite';

        if ($params['memory']) {
            $pdoDsn .= '::memory:';
        } else {
            $pdoDsn .= ':' . $params['file'];
        }
        
        $pdoUsername    = $params['username'];
        $pdoPassword    = $params['password'];
    }
}
