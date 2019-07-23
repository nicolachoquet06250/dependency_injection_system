<?php


namespace my_app\services;


use mvc_router\Base;

class Service extends Base {
	public function hello() {
		var_dump('hello');
	}
}
