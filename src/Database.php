<?php
namespace Fwk;

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

        $PDOAttributes = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION);
        switch ($params['type']) {
            case 'mysql':
                $PDODsn         = 'mysql:host=' . $params['hostname'] . ';dbname=' . $params['database'];
                $PDOUsername    = isset($params['username']) ? $params['username'] : null;
                $PDOPassword    = isset($params['password']) ? $params['password'] : null;
                if (isset($params['encoding'])) {
                    $PDOAttributes[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES " . $params['encoding'];
                }
                break;
            case 'sqlite':
                $PDODsn         = 'sqlite';

                if (isset($params['memory']) && $params['memory']) {
                    $PDODsn .= '::memory:';
                } else {
                    $PDODsn .= ':' . $params['file'];
                }
                $PDOUsername    = null;
                $PDOPassword    = null;
                break;
            default:
                throw new \Exception('Unsupported PDO DB handler');
                break;
        }

        try {
            $this->handler = new \PDO($PDODsn, $PDOUsername, $PDOPassword);
            foreach ($PDOAttributes as $attributeKey => $attributeValue) {
                $this->handler->setAttribute($attributeKey, $attributeValue);
            }
        } catch ( Exception $e ) {
            throw new Exception("Cannot connect to database");
        }
    }

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

    public function fetchColumn()
    {

    }

    public function fetchObject()
    {

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

    }
}
