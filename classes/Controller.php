<?php


namespace mvc_router\mvc;


use mvc_router\Base;
use mvc_router\WrapperFactory;

class Controller extends Base {
	const JSON = 1;
	const XML = 2;
	const HTML = 3;
	const TEXT = 4;

	private $content_types = [
		self::JSON => 'application/json',
		self::XML => 'application/xml',
		self::HTML => 'text/html',
		self::TEXT => 'plain/text',
	];

	public static function run($route) {
		$dw = WrapperFactory::create()->get_dependency_wrapper();
		$dw->get_router()->execute($route);
	}

	private function define_content_type($type) {
		header('Content-Type: '.$this->content_types[$type].';charset=utf-8');
	}

	private function render_not_available($type) {
		$this->error400(strtoupper($type).' content type is not available for the moment</b>');
	}

	/**
	 * @route_disabled
	 * @param string $message
	 */
	public function error400($message = 'Bad request') {
		header('HTTP/1.0 400 '.$message);
		exit('<Doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>Error 400</title>
	</head>
	<body>
		<h1>Error 400</h1>
		<p>'.$message.'</p>
	</body>
</html>');
	}

	/**
	 * @route_disabled
	 * @param string $message
	 */
	public function error404($message = 'Page not found !') {
		header('HTTP/1.0 404 '.$message);
		exit('<Doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>404 - '.$message.'</title>
	</head>
	<body>
		<h1>Error 404</h1>
		<p>'.$message.'</p>
	</body>
</html>');
	}

	protected function json($message) {
		$this->define_content_type(self::JSON);
		return $this->inject->get_service_json()->encode($message);
	}

//	protected function xml($message) {
//		$this->define_content_type(self::XML);
//		return $this->inject->get_service_xml()->encode($message);
//	}

	protected function text($message) {
		$this->define_content_type(self::TEXT);
		return $message;
	}

	protected function html($message) {
		$this->define_content_type(self::HTML);
		return $message;
	}

	protected function render($message, $type = self::TEXT) {
		$method = 'text';
		switch ($type) {
			case self::JSON:
				$method = 'json';
				break;
			case self::XML:
				$this->render_not_available('xml');
				break;
			case self::HTML:
				$method = 'html';
				break;
			case self::TEXT:
				$method = 'text';
				break;
			default:
				$method = 'text';
				break;
		}
		return $this->$method($message);
	}
}
