<?php

use mvc_router\dependencies\Dependency;

require_once __DIR__.'/classes/Dependency.php';

try {
	Dependency::load_base_dependencies();
	Dependency::require_dependency_wrapper();

	array_shift($argv);
	Dependency::get_wrapper_factory()->get_dependency_wrapper()->get_commands()->run(implode(' ', $argv));
} catch (Exception $e) {
	exit($e->getMessage()."\n");
}