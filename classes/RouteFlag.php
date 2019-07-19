<?php


namespace mvc_router\router;


use mvc_router\Base;
use mvc_router\interfaces\Singleton;

class RouteFlag extends Base implements Singleton {
	private $flag;

	private function __construct() {
		$this->after_construct();
	}

	public static function create() {
		return new RouteFlag();
	}

	public function flag($flag = null): RouteFlag {
		if(is_null($flag)) {
			return $this->flag;
		}
		$this->flag = $flag;
		return $this;
	}
}