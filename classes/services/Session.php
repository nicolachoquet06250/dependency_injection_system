<?php


namespace mvc_router\services;


use mvc_router\interfaces\Singleton;

/**
 * @package mvc_router\services
 */
class Session extends Service implements Singleton {
	private static $instance = null;
	protected $session_id;

	public function after_construct() {
		parent::after_construct();
		@session_start();
		$this->session_id = session_id();
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

	public function unset($key) {
		if($this->has($key)) {
			unset($_SESSION[$key]);
		}
		return !$this->has($key);
	}

	public function destroy() {
		return session_destroy();
	}

	public function has($key) {
		return isset($_SESSION[$key]);
	}

	public function get_all() {
		return $_SESSION;
	}
}