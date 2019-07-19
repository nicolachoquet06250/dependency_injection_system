<?php


namespace mvc_router\mvc;


use mvc_router\Base;
use mvc_router\WrapperFactory;

class Controller extends Base {
	public static function run($route) {
		$dw = WrapperFactory::create()->get_dependency_wrapper();
		$dw->get_router()->execute($route);
	}
}
