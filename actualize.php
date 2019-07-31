<?php

use mvc_router\dependencies\Dependency;
use mvc_router\services\Logger;

require_once __DIR__.'/autoload.php';

$dw = Dependency::get_wrapper_factory()->get_dependency_wrapper();

//exec('git pull');
$commands = $dw->get_commands();
$service_logger = $dw->get_service_logger()->types(Logger::CONSOLE);

$service_logger->separator('---------------------------------------------------------------------------');

$service_logger->log('command: git pull');
$pull = $commands->run('git pull');
if(!empty($pull['output']))
	$service_logger->log($pull['output']);
if(!empty($pull['return']))
	$service_logger->log($pull['return']);

$service_logger->log_separator()
			->log('command: generate:dependencies -p custom-file=demo/update_dependencies.php')
			->log($commands->run('generate:dependencies -p custom-file=demo/update_dependencies.php'))
			->log_separator()->log('command: generate:translations')
			->log($commands->run('generate:translations'))
			->log_separator();

$composer_cmd = 'composer '.(is_dir(__DIR__.'/vendor') ? 'update' : 'install');
$service_logger->log('command: '.$composer_cmd);
$composer = $commands->run($composer_cmd);
if (!empty($pull['output']))
	$service_logger->log($pull['output']);
if (!empty($pull['return']))
	$service_logger->log($pull['return']);
