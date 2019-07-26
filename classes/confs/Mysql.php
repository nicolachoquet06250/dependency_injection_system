<?php


namespace mvc_router\confs;


use mysqli;
use mysqli_result;

class Mysql {
	public $host;
	public $user;
	public $pass;
	public $db_name;
	public $port = 3306;

	/** @var mysqli $connector */
	protected $connector;
	/** @var mysqli_result $last_result */
	protected $last_result;
	/** @var string $last_query */
	protected $last_query;

	public function __construct() {
		$this->connector = new mysqli($this->host, $this->user, $this->pass, $this->db_name, $this->port);
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