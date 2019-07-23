<?php


namespace mvc_router\mvc\api;


use mvc_router\mvc\Controller;

class ControllerAPI1 extends Controller {
	/**
	 * @route \/api\/user\/([a-zA-Z0-9\-\_\+\@]+)
	 * @param string $pseudo
	 */
	public function user(string $pseudo) {
		var_dump($pseudo);
	}
}