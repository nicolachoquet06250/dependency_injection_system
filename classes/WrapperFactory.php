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

	/** @return DependencyWrapper */
	public function get_dependency_wrapper() {
		return self::dependencies();
	}

	/** @return ConfWrapper */
	public function get_conf_wrapper() {
		return self::confs();
	}

	public static function create() {
		if(is_null(self::$instance)) {
			self::$instance = new WrapperFactory();
		}
		return self::$instance;
	}

	/** @return DependencyWrapper */
	public static function dependencies() {
		if(is_null(WrapperFactory::$dependency_wrapper)) {
			WrapperFactory::$dependency_wrapper = new DependencyWrapper();
		}
		return WrapperFactory::$dependency_wrapper;
	}

	/** @return ConfWrapper */
	public static function confs() {
		if(is_null(self::$conf_wrapper)) {
			self::$conf_wrapper = new ConfWrapper();
		}
		return self::$conf_wrapper;
	}
}