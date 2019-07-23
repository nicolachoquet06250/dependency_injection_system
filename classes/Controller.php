<?php


namespace mvc_router\mvc;


use mvc_router\Base;
use mvc_router\WrapperFactory;

class Controller extends Base {
	public static function run($route) {
		$dw = WrapperFactory::create()->get_dependency_wrapper();
		$dw->get_router()->execute($route);
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
}
