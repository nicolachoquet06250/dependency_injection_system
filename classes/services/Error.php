<?php


namespace mvc_router\services;


class Error extends Service {

	const JSON = 1;
	const HTML = 2;

	/**
	 * @param string $message
	 * @param int    $type
	 */
	public function error400($message = 'Bad request', $type = self::HTML) {
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
	 * @param string $message
	 * @param int    $type
	 */
	public function error404($message = 'Page not found !', $type = self::HTML) {
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

	/**
	 * @param string $message
	 * @param int    $type
	 */
	public function error500($message = 'Internal error !', $type = self::HTML) {
		header('HTTP/1.0 400 '.$message);
		exit('<Doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>Error 500</title>
	</head>
	<body>
		<h1>Error 500</h1>
		<p>'.$message.'</p>
	</body>
</html>');
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