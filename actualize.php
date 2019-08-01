<?php

use mvc_router\dependencies\Dependency;
use mvc_router\services\Logger;

require_once __DIR__.'/autoload.php';
require_once __DIR__.'/classes/basic_functions.php';

$default_dir = 'demo';
array_shift($argv);
$dir = empty($argv) ? $default_dir : array_shift($argv);

$dw = Dependency::get_wrapper_factory()->get_dependency_wrapper();
$commands       = $dw->get_commands();
$service_logger = $dw->get_service_logger()
					 ->types(Logger::CONSOLE)
					 ->separator('---------------------------------------------------------------------------');
$pulls = [];
$directory = __DIR__.'/';
$_dir = opendir($directory);
while (($elem = readdir($_dir)) !== false) {
	if($elem !== '.' && $elem !== '..' && is_dir($directory.$elem.'/.git')) {
		$pulls[] = $directory.$elem;
	}
}
$pulls = array_map(function ($_dir) {
	return 'cd '.$_dir.' & git pull';
}, $pulls);

run_system_commands(array_merge($pulls, ['composer '.(is_dir(__DIR__.'/vendor') ? 'update' : 'install')]),
					$service_logger, $commands);
run_framework_commands(['generate:dependencies -p custom-file='.$dir.'/update_dependencies.php', 'generate:translations',
						   'generate:base_files -p custom-dir='.$dir],
					   $service_logger, $commands);
