<?php

use mvc_router\commands\Commands;
use mvc_router\services\Logger;

function run_system_command(string $cmd, Logger $logger, Commands $commands) {
	$logger->log('command: '.$cmd);
	$result = $commands->run($cmd);
	$logger->log_if($result['output'], !empty($result['output']))->log_if($result['return'], !empty($result['return']))->log_separator();
}
function run_system_commands(array $cmds, Logger $logger, Commands $commands) {
	foreach ($cmds as $cmd) run_system_command($cmd, $logger, $commands);
}
function run_framework_commands(array $cmds, Logger $logger, Commands $commands) {
	foreach ($cmds as $command_to_run) $logger->log('command: '.$command_to_run)->log($commands->run($command_to_run))->log_separator();
}