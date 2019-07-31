<?php

use mvc_router\dependencies\Dependency;
use mvc_router\services\Logger;

require_once __DIR__.'/autoload.php';

try {
	array_shift($argv);
	$argv = str_replace('\ ', '%20%', implode(' ', $argv));
	$dw = Dependency::get_wrapper_factory()->get_dependency_wrapper();
	$result = $dw->get_commands()->run($argv);
	switch (gettype($result)) {
		case 'string':
			$dw->get_service_logger()->types(Logger::CONSOLE)->log($result);
			break;
		case 'object':
		case 'array':
			var_dump($result);
			break;
		case 'integer':
			$dw->get_service_logger()->types(Logger::CONSOLE)->log('program was exited with code '.$result);
			break;
		default:
			break;
	}
} catch (Exception $e) {
	echo $e->getMessage()."\n";
	var_dump($e->getTrace());
	exit();
}