<?php


namespace mvc_router\mvc;


use mvc_router\Base;
use mvc_router\WrapperFactory;

class Controller extends Base {
	public function __construct() {
		$this->after_construct();
	}

	public function coucou() {
		var_dump('coucou');
	}

	public static function run($route) {
		$dw = WrapperFactory::create()->get_dependency_wrapper();
		$dw->get_router()->execute($route);
	}
}
