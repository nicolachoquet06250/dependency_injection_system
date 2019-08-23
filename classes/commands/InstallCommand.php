<?php


namespace mvc_router\commands;


use mvc_router\dependencies\Dependency;
use mvc_router\services\FileSystem;
use mvc_router\services\Logger;
use ReflectionException;

class InstallCommand extends Command {

	private function init_logger(Logger &$logger) {
		$logger ->types(Logger::CONSOLE)
				->separator('---------------------------------------------------------------------------');
	}

	private function run_framework_commands(array $cmds, Logger $logger, Commands $commands) {
		foreach ($cmds as $command_to_run) $logger->log('command: '.$command_to_run)->log($commands->run($command_to_run))->log_separator();
	}

	private function run_system_command(string $cmd, Logger $logger, Commands $commands) {
		$logger->log('command: '.$cmd);
		$result = $commands->run($cmd);
		$logger->log_if($result['output'], !empty($result['output']))->log_if($result['return'], !empty($result['return']))->log_separator();
	}
	private function run_system_commands(array $cmds, Logger $logger, Commands $commands) {
		foreach ($cmds as $cmd) $this->run_system_command($cmd, $logger, $commands);
	}

	/**
	 * @param Commands $commands
	 * @param Logger   $logger
	 */
	public function install(Commands $commands, Logger $logger) {
		$default_dir = 'demo';
		$default_repo = 'https://github.com/nicolachoquet06250/mvc_router_demo.git';
		$this->init_logger($logger);
		$dir = $this->param('dir') ? $this->param('dir') : $default_dir;
		$repo = $this->param('repo') ? $this->param('repo') : $default_repo;

		$this->run_framework_commands(
			[
				'clone:repo -p repo='.$repo.' dest='.$dir,
				'generate:dependencies -p custom-file='.$dir.'/update_dependencies.php',
				'generate:base_files -p custom-dir='.$dir,
				'generate:translations',
				'install:databases'
			],
			$logger, $commands);
	}

	/**
	 * @param Commands   $commands
	 * @param Logger     $logger
	 * @param FileSystem $fs
	 */
	public function update(Commands $commands, Logger $logger, FileSystem $fs) {
		$this->init_logger($logger);
		$pulls = [];
		$generates = [];
		$root_dir = explode('/', realpath(__DIR__.'/../..'))[count(explode('/', realpath(__DIR__.'/../..'))) - 1];
		$fs->browse_root(function ($file) use (&$pulls, &$generates, $root_dir) {
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
		$pulls = array_map(function ($_dir) use ($root_dir) {
			$base_dir = explode('/', $_dir)[count(explode('/', $_dir)) - 1];
			return $base_dir === $root_dir ? 'git pull' : "git -C {$_dir} pull";
		}, $pulls);
		$pulls[] = 'composer '.(is_dir(__DIR__.'/vendor') ? 'update' : 'install');
		$generates[] = 'generate:translations';
		$generates[] = 'install:databases';

		$this->run_system_commands($pulls, $logger, $commands);
		$this->run_framework_commands($generates, $logger, $commands);
	}

	/**
	 * @throws ReflectionException
	 */
	public function databases() {
		$managers = Dependency::get_managers();
		foreach ($managers as $manager_name => $manager) {
			$manager->create_table();
		}
	}
}