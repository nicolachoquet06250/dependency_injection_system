<?php


use mvc_router\mvc\Controller;
use mvc_router\router\Router;
use my_app\services\Service;

class MyController extends Controller {

	public function index() {
		var_dump('index');
	}

	public function test(Service $my_service) {
		$my_service->hello();
	}

	public function test2(Router $router, $param1, $param2) {
		echo '<pre>';
		var_dump($router->get_current_route());
		var_dump($param1, $param2);
		echo '</pre>';
	}
}