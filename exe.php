<?php

use mvc_router\dependencies\Dependency;

require_once __DIR__.'/autoload.php';

try {
	Dependency::load_base_dependencies();
	Dependency::require_dependency_wrapper();

	array_shift($argv);
	$argv = str_replace('\ ', '%20%', implode(' ', $argv));
	$result = Dependency::get_wrapper_factory()->get_dependency_wrapper()->get_commands()->run($argv);
	switch (gettype($result)) {
		case 'string':
			echo $result."\n";
			break;
		case 'object':
		case 'array':
			var_dump($result);
			break;
		case 'integer':
			echo 'program was exited with code '.$result."\n";
			break;
		default:
			break;
	}
} catch (Exception $e) {
	echo $e->getMessage()."\n";
	var_dump($e->getTrace());
	exit();
}