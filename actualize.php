<?php

use mvc_router\dependencies\Dependency;
use mvc_router\services\Logger;

require_once __DIR__.'/autoload.php';
require_once __DIR__.'/classes/basic_functions.php';

$dw = Dependency::get_wrapper_factory()->get_dependency_wrapper();
$commands       = $dw->get_commands();
$service_logger = $dw->get_service_logger()
					 ->types(Logger::CONSOLE)
					 ->separator('---------------------------------------------------------------------------');
$pulls = [];
$generates = [];
$root_dir = explode('/', __DIR__)[count(explode('/', __DIR__)) - 1];
$dw->get_service_fs()->browse_root(function ($file) use (&$pulls, &$generates, $root_dir) {
	$path = realpath(dirname($file).'/.git');
	if($path && !in_array(realpath(dirname($file)), $pulls)) {
		$dir_name = explode('/', dirname($path))[count(explode('/', dirname($path))) - 1];
		if($dir_name !== $root_dir) {
			$generates[] = 'generate:dependencies -p custom-file='.$dir_name.'/update_dependencies.php';
			$generates[] = 'generate:base_files -p custom-dir='.$dir_name;
		}
		$pulls[] = realpath(dirname($file));
	}
}, true);
$pulls = array_map(function ($_dir) {
	return 'cd '.$_dir.' & git pull';
}, $pulls);
$pulls[] = 'composer '.(is_dir(__DIR__.'/vendor') ? 'update' : 'install');
$generates[] = 'generate:translations';

run_system_commands($pulls, $service_logger, $commands);
run_framework_commands($generates, $service_logger, $commands);
