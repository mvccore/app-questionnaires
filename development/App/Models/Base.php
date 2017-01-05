<?php

class App_Models_Base
{
	const TABLE_ANSWERS = 'Answers';
	const TABLE_EXECUTED = 'Executed';
	const TABLE_PERSONS = 'Persons';
	
	/**
	 * Default database connection index, in config ini defined in section db.defaultDbIndex = 0.
	 * In extended classes - use this for connection index of current model if different.
	 * @var int
	 */
	protected static $connectionIndex = 0;

	/**
	 * PDO connections array, keyed by connection indexes from system config
	 * @var array
	 */
	protected static $connections = array();

	/**
	 * Instance of current class if there is necessary to use it as singleton
	 * @var MvcCore_Model
	 */
	protected static $instances = array();

	/**
	 * System config sections array with stdClass objects, keyed by connection indexes
	 * @var array
	 */
	protected static $configs = array();

    /**
     * PDO instance
	 * 
     * @var PDO
     */
    protected $db;

	/**
	 * System config section for database under called connection index in constructor
	 * @var stdClass
	 */
	protected $cfg;

	/**
	 * Resource model class with SQL statements
	 * @var MvcCore_Model
	 */
	protected $resource;

	/**
	 * Creates an instance and inits cfg, db and resource properties
	 * @param mixed $connectionIndex 
	 */
	public function __construct ($connectionIndex = -1) {
		if ($connectionIndex == -1) $connectionIndex = static::$connectionIndex;
		$this->cfg = static::getCfg($connectionIndex);
		$this->db = static::getDb($connectionIndex);
		$this->resource = static::getResource(array(), get_class($this));
    }

	/**
	 * Collect all model class public and inherit field values into array
	 * @param boolean $includeInheritProperties if true, include only fields from current model class and from parent classes
	 * @param boolean $publicOnly               if true, include only public model fields
	 * @return array
	 */
	protected function getValues ($includeInheritProperties = TRUE, $publicOnly = TRUE) {
		$data = array();
		$modelClassName = get_class($this);
		$classReflector = new ReflectionClass($modelClassName);
		$properties = $publicOnly ? $classReflector->getProperties(ReflectionProperty::IS_PUBLIC) : $classReflector->getProperties();
		foreach ($properties as $property) {
			if (!$includeInheritProperties && $property->class != $modelClassName) continue;
			$propertyName = $property->name;
			$data[$propertyName] = $this->$propertyName;
		}
		return $data;
	}

	/**
	 * Collect all model class public and inherit field values into array
	 * @param array   $data                     collection with data to set up
	 * @param boolean $includeInheritProperties if true, include only fields from current model class and from parent classes
	 * @param boolean $publicOnly               if true, include only public model fields
	 * @return MvcCore_Model
	 */
	protected function setUp ($data = array(), $includeInheritProperties = TRUE, $publicOnly = TRUE) {
		$modelClassName = get_class($this);
		$classReflector = new ReflectionClass($modelClassName);
		$properties = $publicOnly ? $classReflector->getProperties(ReflectionProperty::IS_PUBLIC) : $classReflector->getProperties();
		foreach ($properties as $property) {
			if (!$includeInheritProperties && $property->class != $modelClassName) continue;
			$propertyName = $property->name;
			if (preg_match('/@var\s+([^\s]+)/', $property->getDocComment(), $matches)) {
				list(, $type) = $matches;
				settype($data[$propertyName], $type);
			}
			if (isset($data[$propertyName])) {
				$this->$propertyName =  $data[$propertyName];
			}
		}
		return $this;
	}

	/**
	 * Returns (or creates and holds) instance from local store
	 * @param mixed $arg,... unlimited OPTIONAL variables to pass into __construct() method
	 * @return App_Models_Base
	 */
	public static function GetInstance (/* $arg1, $arg2, $arg, ... */) {
		// get 'ClassName' string from this call: ClassName::GetInstance();
		$className = get_called_class();
		$args = func_get_args();
		$instanceIndex = md5($className . '_' . serialize($args));
		if (!isset(self::$instances[$instanceIndex])) {
			$reflectionClass = new ReflectionClass($className);
			$instance = $reflectionClass->newInstanceArgs($args);
			self::$instances[$instanceIndex] = $instance;
		}
		return self::$instances[$instanceIndex];
	}

	/**
	 * Returns database connection by connection index (cached by local store)
	 * @param mixed $connectionIndex 
	 * @return PDO
	 */
	protected static function getDb ($connectionIndex = -1) {
		if ($connectionIndex == -1) $connectionIndex = static::$connectionIndex;
		if (!isset(static::$connections[$connectionIndex])) {
			$cfg = static::getCfg($connectionIndex);
			$connection = NULL;
			if ($cfg->driver == 'mysql') {
				$options = array();
				if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) $options[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = TRUE;
				if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'UTF8'";
				$connection = new PDO("mysql:host={$cfg->server};dbname={$cfg->dbname}", $cfg->username, $cfg->password, $options);
			} else if ($cfg->driver == 'mssql') {
				$connection = new PDO("sqlsrv:Server={$cfg->server};Database={$cfg->dbname}", $cfg->username, $cfg->password);
			} else if ($cfg->driver == 'sqllite') {
				$appRoot = MvcCore::GetRequest()->appRoot;
				if (strpos($appRoot, 'phar://') !== FALSE) {
					$lastSlashPos = strrpos($appRoot, '/');
					$appRoot = substr($appRoot, 7, $lastSlashPos - 7);
				}
				$fullPath = realpath($appRoot . $cfg->path);
				$connection = new PDO("sqlite:$fullPath");
			}
			static::$connections[$connectionIndex] = $connection;
        }
		return static::$connections[$connectionIndex];
	}

	/**
	 * Returns database config by connection index as stdClass (cached by local store)
	 * @param int $connectionIndex 
	 * @return object
	 */
	protected static function getCfg ($connectionIndex = -1) {
		if ($connectionIndex == -1) $connectionIndex = self::$connectionIndex;
		if (!isset(self::$configs[$connectionIndex])) {
			$cfg = App_Bootstrap::GetConfig();
			self::$configs[$connectionIndex] = (object) $cfg->db[$connectionIndex];
        }
		return self::$configs[$connectionIndex];
	}

	/**
	 * Returns (or creates if necessary) model resource instance
	 * @param array $args values array with variables to pass into __construct() method
	 * @param string $modelClassPath
	 * @param string $resourceClassPath
	 * @return App_Models_Base(_Resource)
	 */
	protected static function getResource ($args = array(), $modelClassName = '', $resourceClassPath = '_Resource') {
		$result = NULL;
		if (!$modelClassName) $modelClassName = get_called_class();
		// do not create resource instance in resource class (if current class name doesn't end with '_Resource' substring):
		if (strpos($modelClassName, '_Resource') === FALSE) {
			$resourceClassName = $modelClassName . $resourceClassPath;
			// do not create resource instance if resource class doesn't exist:
			if (class_exists($resourceClassName)) {
				$result = call_user_func_array(array($resourceClassName, 'GetInstance'), $args);
			}
		}
		return $result;
	}

	public function __set ($name, $value) {
		$this->$name = $value;
	}

	public function __get ($name) {
		return (isset($this->$name)) ? $this->$name : null;
	}
}