<?php


namespace mvc_router\services;


use Ratchet\ComponentInterface;

class Websocket extends Service {
	protected $host = 'localhost';
	protected $port = 2108;

	protected $routes = [];

	public function route($path, ComponentInterface $controller, $allowed_origins = ['*'], $http_host = null) {
		$this->routes[] = [
			'path'            => $path,
			'controller'      => $controller,
			'allowed_origins' => $allowed_origins,
			'http_host'       => $http_host,
		];
		return $this;
	}

	public function run() {
		$app = $this->inject->get_ratchet_app_ws('localhost', 2108);
		foreach ($this->routes as $route) {
			$app->route($route['path'], $route['controller'], $route['allowed_origins'], $route['http_host']);
		}
		$app->run();
	}
}