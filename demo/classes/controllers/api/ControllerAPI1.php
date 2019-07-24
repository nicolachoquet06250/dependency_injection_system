<?php


namespace mvc_router\mvc\api;


use mvc_router\mvc\Controller;
use mvc_router\router\Router;

class ControllerAPI1 extends Controller {

	public function error404($message = 'Page not found !') {
		header('HTTP/1.0 404 '.$message);
		exit($this->inject->get_service_json()->encode(
			[
				'error' => true,
				'status' => 404,
				'message' => $message,
			]
		));
	}

	/**
	 * @route \/api\/user\/([a-zA-Z0-9\-\_\+\@]+)
	 * @param Router $router
	 * @param string $pseudo
	 * @return false|string
	 */
	public function user(Router $router, string $pseudo) {
		return $this->render(['name' => $pseudo, 'last_name' => $router->get('nom')], self::JSON);
	}
}