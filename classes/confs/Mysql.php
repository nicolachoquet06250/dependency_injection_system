<?php


namespace mvc_router\confs;


use Exception;
use mvc_router\Base;
use mysqli;
use mysqli_result;

class Mysql extends Base {
	protected $host;
	protected $user_prefix = '';
	protected  $user;
	protected  $pass;
	protected  $db_prefix = 'mvc_router_';
	protected  $db_name;
	protected  $port = 3306;

	protected $connector;
	protected $last_result;
	/** @var string $last_query */
	protected $last_query;
	/** @var string $last_complete_query */
	protected $last_complete_query;

	public function after_construct() {
		parent::after_construct();
		if(!in_array('\mysqli', get_declared_classes()) && !in_array('mysqli', get_declared_classes())) {
			throw new Exception(
				$this->inject->get_service_translation()
							 ->__("L'extension php-mysql doit être installée et activée pour pouvoir utiliser les configurations Mysql !")
			);
		}
		try {
			/** @var mysqli connector */
			if($this->host && $this->user && $this->pass) {
				$user = $this->user_prefix !== '' ? $this->user_prefix.'_'.$this->user : $this->user;
				$db_name = $this->db_name
					? ($this->db_prefix !== '' ? $this->db_prefix.'_'.$this->db_name : $this->db_name) : null;
				$this->connector = new mysqli($this->host, $user, $this->pass, $db_name, $this->port);
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
		if($this->is_connected()) {
			if (!$this->connector) {
				return false;
			}
			$this->last_query = $query;
			foreach ($vars as $num => $var) {
				$query = str_replace('$'.($num + 1), is_string($var) ? "\"{$var}\"" : $var, $query);
			}
			$this->last_complete_query = $query;
			if (substr($query, 0, strlen('SELECT '))) {
				$this->last_result = $this->connector->query($query);
				return $this;
			}
			return $this->connector->query($query);
		}
		return null;
	}

	/**
	 * @return mixed
	 */
	public function fetch_row() {
		return $this->is_connected() && !is_null($this->get_last_result()) ? $this->get_last_result()->fetch_row() : [];
	}

	/**
	 * @return array|null
	 */
	public function fetch_assoc() {
		return $this->is_connected() && !is_null($this->get_last_result()) ? $this->get_last_result()->fetch_assoc() : [];
	}

	/**
	 * @return mixed
	 */
	public function fetch_array() {
		return $this->is_connected() && !is_null($this->get_last_result()) ? $this->get_last_result()->fetch_array() : [];
	}

	/**
	 * @return mixed
	 */
	public function fetch_all() {
		return $this->is_connected() && !is_null($this->get_last_result()) ? $this->get_last_result()->fetch_all() : [];
	}

	/**
	 * @return string
	 */
	public function get_last_query() {
		return $this->last_query;
	}

	/**
	 * @return string
	 */
	public function get_last_complete_query() {
		return $this->last_complete_query;
	}

	/**
	 * @return mysqli_result
	 */
	public function get_last_result() {
		return $this->last_result;
	}

	/**
	 * @return bool
	 */
	public function is_connected() {
		return $this->connector !== false && !is_null($this->connector) && !$this->connector->connect_error;
	}
}