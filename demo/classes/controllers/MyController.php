<?php


use mvc_router\mvc\Controller;
use mvc_router\router\Router;
use mvc_router\services\Service;

class MyController extends Controller {
	/**
	 * @route /mon/example/
	 */
	public function index() {
		var_dump('index');
	}

	/**
	 * @route /mon/example/2
	 * @param Service $my_service
	 */
	public function test(Service $my_service) {
		$my_service->hello();
	}

	/**
	 * @route \/([a-zA-Z]+)\/([0-9]+)
	 * @param Router $router
	 * @param        $param1
	 * @param        $param2
	 */
	public function test2(Router $router, $param1, $param2) {
		echo '<pre>';
		var_dump($router->get_current_route());
		var_dump($param1, $param2);
		echo '</pre>';
	}

	/**
	 * @route /test/lol/var
	 */
	public function toto() {
		var_dump('hello 1');
	}

	/**
	 * @route \/([a-zA-Z0-9]+)\/hello-toi
	 * @param Service $service
	 * @param         $param1
	 */
	public function hello_toi(Service $service, $param1) {
		echo '<pre>';
		$service->hello();
		var_dump($param1);
		echo '</pre>';
	}
}