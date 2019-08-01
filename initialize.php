<?php

use mvc_router\dependencies\Dependency;
use mvc_router\services\Logger;

require_once __DIR__.'/autoload.php';
require_once __DIR__.'/classes/basic_functions.php';

$default_dir = 'demo2';
array_shift($argv);

$repository = array_shift($argv);
$dir = empty($argv) ? $default_dir : array_shift($argv);

$dw = Dependency::get_wrapper_factory()->get_dependency_wrapper();
$commands       = $dw->get_commands();
$service_logger = $dw->get_service_logger()
					 ->types(Logger::CONSOLE)
					 ->separator('---------------------------------------------------------------------------');

run_framework_commands(['clone:repo -p repo='.$repository.' dest='.$dir,
						'generate:dependencies -p custom-file='.$dir.'/update_dependencies.php',
						'generate:base_files -p custom-dir='.$dir,
						'generate:translations'],
					   $service_logger, $commands);
