<?php


namespace mvc_router;


use mvc_router\dependencies\DependencyWrapper;
use mvc_router\interfaces\Singleton;

class WrapperFactory implements Singleton {
	private static $dependency_wrapper = null;
	private static $instance = null;

	private function __construct() {}

	/** @return DependencyWrapper */
	public function get_dependency_wrapper() {
		if(is_null(WrapperFactory::$dependency_wrapper)) {
			WrapperFactory::$dependency_wrapper = new DependencyWrapper();
		}
		return WrapperFactory::$dependency_wrapper;
	}

	public static function create() {
		if(is_null(self::$instance)) {
			self::$instance = new WrapperFactory();
		}
		return self::$instance;
	}
}