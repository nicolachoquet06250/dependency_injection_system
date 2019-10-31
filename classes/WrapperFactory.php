<?php


namespace mvc_router;


use mvc_router\confs\ConfWrapper;
use mvc_router\dependencies\DependencyWrapper;
use mvc_router\interfaces\Singleton;

class WrapperFactory implements Singleton {
	private static $dependency_wrapper = null;
	private static $conf_wrapper = null;
	private static $instance = null;

	private function __construct() {}

	public function get_dependency_wrapper() {
		return self::dependencies();
	}

	public function get_conf_wrapper() {
		return self::confs();
	}

	public static function create() {
		if(is_null(self::$instance)) {
			self::$instance = new WrapperFactory();
		}
		return self::$instance;
	}

	public static function dependencies() {
		if(is_null(WrapperFactory::$dependency_wrapper)) {
			$class = '\mvc_router\\'.__SITE_NAME__.'\dependencies\DependencyWrapper';
			WrapperFactory::$dependency_wrapper = new $class();
		}
		return WrapperFactory::$dependency_wrapper;
	}

	public static function confs() {
		if(is_null(self::$conf_wrapper)) {
			$class = '\mvc_router\\'.__SITE_NAME__.'\confs\ConfWrapper';
			self::$conf_wrapper = new $class();
		}
		return self::$conf_wrapper;
	}
}