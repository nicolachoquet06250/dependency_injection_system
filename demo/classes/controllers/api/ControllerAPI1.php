<?php


namespace mvc_router\mvc\api;


use mvc_router\mvc\Controller;
use mvc_router\router\Router;

class ControllerAPI1 extends Controller {
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