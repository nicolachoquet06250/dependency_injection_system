<?php


namespace mvc_router\confs;


use Exception;
use mvc_router\Base;
use mvc_router\data\gesture\Entity;
use mvc_router\dependencies\Dependency;
use PDO;
use PDOStatement;
use ReflectionException;

class Mysql extends Base {
	protected 	$host;
	protected 	$user_prefix = '';
	protected  	$user;
	protected  	$pass;
	protected  	$db_prefix = '';
	protected  	$db_name;
	protected  	$port = 3306;

	const FETCH_ARRAY = 30;

	/** @var PDO $connector */
	protected $connector;
	/** @var bool $is_connected */
	private $is_connected = false;
	/** @var mixed $last_insert_id */
	private $last_insert_id;
	/** @var array $last_result */
	protected $last_result;
	/** @var string $last_query */
	protected $last_query;
	/** @var int $num_rows */
	protected $num_rows = 0;

	/**
	 * @throws Exception
	 */
	public function after_construct() {
		parent::after_construct();
		$this->connect();
	}
	
	/**
	 * @throws Exception
	 */
	public function connect() {
		if(!in_array('\PDO', get_declared_classes()) && !in_array('PDO', get_declared_classes())) {
			throw new Exception(
				$this->inject->get_service_translation()
					->__("L'extension php-mysql doit être installée et activée pour pouvoir utiliser les configurations Mysql !")
			);
		}
		try {
			/** @var PDO connector */
			if($this->host && $this->user && $this->pass) {
				$this->connector = new PDO('mysql:host='.$this->host.';'
				                           .($this->port ? 'port='.$this->port.';' : '')
				                           .'dbname='.($this->db_prefix ? $this->db_prefix.'_' : '').$this->db_name.'',
				                           ($this->user_prefix ? $this->user_prefix.'_' : '').$this->user,
				                           $this->pass);
				$this->is_connected = $this->connector && $this->connector->errorCode() ? false : true;
			}
		}
		catch (Exception $e) {
			throw new Exception('bad mysql credentials');
		}
	}

	/**
	 * @param string $query
	 * @param array $vars
	 * @return bool|array|Mysql
	 */
	public function query($query, $vars = []) {
		if($this->is_connected()) {
			if (!$this->connector) {
				return false;
			}
			$this->last_query = $query;
			if (substr($query, 0, strlen('SELECT ')) !== 'SELECT ' && substr($query, 0, strlen('SHOW ')) !== 'SHOW ') {
				$_query = $this->connector->prepare($query);
				$this->last_result = $_query->execute($vars);
				return $this;
			}
			$_query = $this->connector->prepare($query);
			$_query->execute($vars);
			$this->last_result = $_query;
			return $this;
		}
		return null;
	}

	/**
	 * @param null $fetch_style
	 * @return array|mixed
	 */
	private function count_rows($fetch_style = null) {
		$fetch_result = $fetch_style === self::FETCH_ARRAY ? $this->last_result()->fetch() : (!is_bool($this->last_result()) ? $this->last_result()->fetchAll($fetch_style) : []);
		$this->num_rows = count($fetch_result);
		return $fetch_result;
	}

	/**
	 * @return bool
	 */
	private function is_in_error() {
		$error_code = $this->last_result() && !is_bool($this->last_result()) ? $this->last_result()->errorCode() : null;
		return !is_null($error_code) && $error_code !== '00000';
	}

	/**
	 * @return mixed
	 */
	public function fetch_row() {
		$fetch_result = $this->count_rows();
		return $this->is_connected() && !$this->is_in_error()
			? $fetch_result[array_keys($fetch_result)[0]] : [];
	}

	/**
	 * @return array|null
	 */
	public function fetch_assoc() {
		return $this->is_connected() && !$this->is_in_error() ? $this->count_rows(PDO::FETCH_ASSOC) : [];
	}

	/**
	 * @return mixed
	 */
	public function fetch_array() {
		return $this->is_connected() && !$this->is_in_error() ? $this->count_rows(self::FETCH_ARRAY) : [];
	}

	/**
	 * @return mixed
	 */
	public function fetch_all() {
		return $this->is_connected() && !$this->is_in_error() ? $this->count_rows() : [];
	}

	/**
	 * @param string $class_dependency_name
	 * @return Base[]|Base
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function fetch_object($class_dependency_name) {
		$result = $this->fetch_assoc();
		$results = [];
		foreach ($result as $item) {
			$obj = Dependency::get_from_name($class_dependency_name);
			if($obj) {
				foreach ($item as $key => $value) {
					$obj instanceof Entity ? $obj->set($key, $value, true) : $obj->set($key, $value);
				}
				$results[] = $obj;
			}
		}
		if(count($results) === 1) {
			$results = $results[0];
		}
		return $results;
	}

	/**
	 * @return string
	 */
	public function last_query() {
		return $this->last_query;
	}

	/**
	 * @return array|PDOStatement
	 */
	public function last_result() {
		return $this->last_result;
	}

	/**
	 * @return bool
	 */
	public function is_connected() {
		return $this->is_connected;
	}

	/**
	 * @return mixed
	 */
	public function last_insert_id() {
		return $this->last_insert_id;
	}

	/**
	 * @return int
	 */
	public function get_num_rows() {
		return $this->num_rows;
	}
	
	public function close() {
		$this->connector = null;
		$this->is_connected = false;
	}
}