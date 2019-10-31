<?php


namespace mvc_router\services;

use ReflectionException;

/**
 * Gère les erreurs HTTP en donnant des pages personnalisés
 *
 * @package mvc_router\services
 */
class Error extends Service {

	const JSON = 1;
	const HTML = 2;
	
	/**
	 * @param $type
	 * @return \mvc_router\mvc\controllers\errors\Errors
	 * @throws ReflectionException
	 */
	protected function get_error_controller($type) {
		$error_controller = $this->inject->get_errors_controller();
		switch ($type) {
			case self::HTML:
				header('Content-Type: text/html');
				break;
			case self::JSON:
				header('Content-Type: application/json');
				break;
			default:
				exit('');
		}
		$error_controller->return_type = $type;
		return $error_controller;
	}
	
	public function error400($message = 'Bad request', $type = self::HTML) {
		header('HTTP/1.0 400 '.$message);
		$error_controller = $this->get_error_controller($type);
		return $error_controller->error400($message);
	}

	public function error401($message = 'Login failed !', $type = self::HTML) {
		header('HTTP/1.0 401 '.$message);
		$error_controller = $this->get_error_controller($type);
		return $error_controller->error401($message);
	}

	public function error404($message = 'Page not found !', $type = self::HTML) {
		header('HTTP/1.0 404 '.$message);
		$error_controller = $this->get_error_controller($type);
		return $error_controller->error404($message);
	}

	public function error500($message = 'Internal error !', $type = self::HTML) {
		header('HTTP/1.0 500 '.$message);
		$error_controller = $this->get_error_controller($type);
		return $error_controller->error500($message);
	}

	public function redirect301($url) {
		header("Status: 301 Moved Permanently", false, 301);
		return $this->redirect302($url);
	}

	public function redirect302($url) {
		header("Location: {$url}");
		return;
	}
}