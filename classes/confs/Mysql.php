<?php


namespace mvc_router\confs;


use Exception;
use mvc_router\dependencies\DependencyWrapper;
use mysqli;
use mysqli_result;

class Mysql {
	public $host;
	public $user;
	public $pass;
	public $db_name;
	public $port = 3306;

	protected $connector;
	protected $last_result;
	/** @var string $last_query */
	protected $last_query;

	/**
	 * Mysql constructor.
	 *
	 * @throws Exception
	 */
	public function __construct() {
		if(!in_array('\mysqli', get_declared_classes()) && !in_array('mysqli', get_declared_classes())) {
			throw new Exception(
				DependencyWrapper::get_wrapper_factory()
								 ->get_dependency_wrapper()
								 ->get_service_translation()
								 ->__("L'extension php-mysql doit être installée et activée pour pouvoir utiliser les configurations Mysql !")
			);
		}
		try {
			/** @var mysqli connector */
			if($this->host && $this->user && $this->pass) {
				$this->connector = new mysqli($this->host, $this->user, $this->pass, $this->db_name, $this->port);
			}
		}
		catch (\mysql_xdevapi\Exception $e) {
			throw new Exception('bad mysql credentials');
		}
	}

	/**
	 * @param string $query
	 * @param array $vars
	 * @return bool|mysqli_result|Mysql
	 */
	public function query($query, $vars = []) {
		$this->last_query = $query;
		foreach ($vars as $num => $var) {
			$query = str_replace('$'.$num, $var, $query);
		}
		if(substr($query, 0, strlen('SELECT '))) {
			$this->last_result = $this->connector->query($query);
			return $this;
		}
		return $this->connector->query($query);
	}

	/**
	 * @return mixed
	 */
	public function fetch_row() {
		return $this->get_last_result()->fetch_row();
	}

	/**
	 * @return array|null
	 */
	public function fetch_assoc() {
		return $this->get_last_result()->fetch_assoc();
	}

	/**
	 * @return mixed
	 */
	public function fetch_array() {
		return $this->get_last_result()->fetch_array();
	}

	/**
	 * @return mixed
	 */
	public function fetch_all() {
		return $this->get_last_result()->fetch_all();
	}

	/**
	 * @return string
	 */
	public function get_last_query() {
		return $this->last_query;
	}

	/**
	 * @return mysqli_result
	 */
	public function get_last_result() {
		return $this->last_result;
	}
}