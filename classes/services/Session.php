<?php


namespace mvc_router\services;


use mvc_router\interfaces\Singleton;

class Session extends Service implements Singleton {
	private static $instance = null;

	public function __construct() {
		parent::__construct();
		session_start();
	}

	public static function create() {
		if(is_null(self::$instance)) {
			self::$instance = new Session();
		}
		return self::$instance;
	}

	public function get($key) {
		return $this->has($key) ? $_SESSION[$key] : null;
	}

	public function set($key, $value) {
		$_SESSION[$key] = $value;
	}

	public function has($key) {
		return isset($_SESSION[$key]);
	}

	public function get_all() {
		return $_SESSION;
	}
}