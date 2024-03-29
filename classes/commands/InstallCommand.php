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
		foreach ($cmds as $command_to_run) {
			$command_result = $commands->run($command_to_run);
			$logger->log('command: '.$command_to_run);
			if(is_array($command_result)) {
				foreach ($command_result as $i => $k) {
					if(is_int($i)) {
						$message = $k;
					} else {
						$k = is_array($k) ? implode("\n", $k) : $k;
						$message = "{$i} => {$k}";
					}
					$logger->log($message);
				}
			} else $logger->log($command_result);
			$logger->log_separator();

		}
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
	 * @syntax install:install -p [dir=<value>?demo] [repo=<value>?https://github.com/nicolachoquet06250/mvc_router_demo.git]
	 *
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
				'composer '.(is_file(__DIR__.'/../../composer.lock') ? 'update' : 'install'),
				'generate:base_files -p custom-dir='.$dir,
				'generate:dependencies -p custom-file='.$dir.'/update_dependencies.php',
				'generate:translations',
				'install:databases',
				'install:update dir='.$dir,
			],
			$logger, $commands);
	}

	/**
	 * @syntax install:update
	 *
	 * @param Commands   $commands
	 * @param Logger     $logger
	 * @param FileSystem $fs
	 */
	public function update(Commands $commands, Logger $logger, FileSystem $fs) {
		$custom_dir = $this->param('dir');
		$custom_dir = $custom_dir ?? 'demo';
		$this->init_logger($logger);
		$pulls = [];
		$generates = [];
		$root_dir = explode('/', realpath(__DIR__.'/../..'))[count(explode('/', realpath(__DIR__.'/../..'))) - 1];
		$fs->browse_root(function ($file) use (&$pulls, &$generates, $root_dir) {
			$path = realpath(dirname($file).'/.git');
			if($path && !in_array(realpath(dirname($file)), $pulls)) {
				$dir_name = explode('/', dirname($path))[count(explode('/', dirname($path))) - 1];
				if($dir_name !== $root_dir) {
					$generates[] = 'generate:base_files -p custom-dir='.$dir_name;
					$generates[] = 'generate:dependencies -p custom-file='.$dir_name.'/update_dependencies.php';
				}
				$pulls[] = realpath(dirname($file));
			}
		}, true);
		
		$pulls = array_merge(['composer '.(is_file(__DIR__.'/../../composer.lock') ? 'update' : 'install')], array_map(function ($_dir) use ($root_dir) {
			$base_dir = explode('/', $_dir)[count(explode('/', $_dir)) - 1];
			return $base_dir === $root_dir ? 'git pull' : "git -C {$_dir} pull";
		}, $pulls));
		$generates[] = 'generate:translations';
		$generates[] = 'install:databases';
		$generates[] = 'generate:base_files -p custom-dir='.$custom_dir;
		$generates[] = 'generate:dependencies -p custom-file='.$custom_dir.'/update_dependencies.php';

		$this->run_system_commands($pulls, $logger, $commands);
		$this->run_framework_commands($generates, $logger, $commands);
	}

	/**
	 * @throws ReflectionException
	 */
	public function databases() {
		$managers = Dependency::get_managers();
		$result = [];
		foreach ($managers as $manager_name => $manager) {
			if(!is_null($manager)) {
				$result[ $manager_name ] = $manager->create_table();
			}
		}
		return $result;
	}
}