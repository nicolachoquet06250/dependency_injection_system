<?php


namespace mvc_router\commands;


use Exception;
use mvc_router\dependencies\Dependency;
use mvc_router\services\FileSystem;
use mvc_router\services\Logger;
use ReflectionException;

class InstallCommand extends Command {
	
	/**
	 * @param Logger $logger
	 */
	private function init_logger(Logger &$logger) {
		$logger ->types(Logger::CONSOLE)
				->separator('---------------------------------------------------------------------------');
	}
	
	/**
	 * @param array $cmds
	 * @param Logger $logger
	 * @param Commands $commands
	 * @throws Exception
	 */
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
	
	/**
	 * @param string $cmd
	 * @param Logger $logger
	 * @param Commands $commands
	 * @throws Exception
	 */
	private function run_system_command(string $cmd, Logger $logger, Commands $commands) {
		$logger->log('command: '.$cmd);
		$result = $commands->run($cmd);
		$logger->log_if($result['output'], !empty($result['output']))->log_if($result['return'], !empty($result['return']))->log_separator();
	}
	
	/**
	 * @param array $cmds
	 * @param Logger $logger
	 * @param Commands $commands
	 * @throws Exception
	 */
	private function run_system_commands(array $cmds, Logger $logger, Commands $commands) {
		foreach ($cmds as $cmd) $this->run_system_command($cmd, $logger, $commands);
	}
	
	/**
	 * @syntax [custom-dir-name] install:install -p [repo=<value>?https://github.com/nicolachoquet06250/mvc_router_demo.git]
	 *
	 * @param Commands $commands
	 * @param Logger $logger
	 * @throws Exception
	 */
	public function install(Commands $commands, Logger $logger) {
		$default_dir = 'demo';
		$default_repo = 'https://github.com/nicolachoquet06250/mvc_router_demo.git';
		$this->init_logger($logger);
		$dir = defined('__SITE_NAME__') ? __SITE_NAME__ : $default_dir;
		$repo = $this->param('repo') ? $this->param('repo') : $default_repo;

		$this->run_framework_commands(
			[
				$dir.' clone:repo -p repo='.$repo,
				'composer '.(is_file(__DIR__.'/../../composer.lock') ? 'update' : 'install'),
				$dir.' generate:base_files',
				$dir.' generate:dependencies',
				$dir.' generate:translations',
				$dir.' install:databases',
				$dir.' install:update',
			],
			$logger, $commands);
	}
	
	/**
	 * @syntax install:update
	 *
	 * @param Commands $commands
	 * @param Logger $logger
	 * @param FileSystem $fs
	 * @throws Exception
	 */
	public function update(Commands $commands, Logger $logger, FileSystem $fs) {
		$custom_dir = defined('__SITE_NAME__') ? __SITE_NAME__ : 'demo';
		$this->init_logger($logger);
		$pulls = [];
		$generates = [];
		$root_dir = explode('/', realpath(__DIR__.'/../..'))[count(explode('/', realpath(__DIR__.'/../..'))) - 1];
//		$fs->browse_root(function ($file) use (&$pulls, &$generates, $root_dir) {
//			$path = realpath(dirname($file).'/.git');
//			if($path && !in_array(realpath(dirname($file)), $pulls)) {
//				$dir_name = explode('/', dirname($path))[count(explode('/', dirname($path))) - 1];
//				if($dir_name !== $root_dir) {
//					$generates[] = $dir_name.' generate:base_files';
//					$generates[] = $dir_name.' generate:dependencies -p custom-file='.$dir_name.'/update_dependencies.php';
//				}
//				$pulls[] = realpath(dirname($file));
//			}
//		}, true);
		$generates[] = $custom_dir.' generate:base_files';
		$generates[] = $custom_dir.' generate:dependencies -p custom-file='.$custom_dir.'/update_dependencies.php';
		
		$pulls = array_merge(['composer '.(is_file(__DIR__.'/../../composer.lock') ? 'update' : 'install')], array_map(function ($_dir) use ($root_dir) {
			$base_dir = explode('/', $_dir)[count(explode('/', $_dir)) - 1];
			return $base_dir === $root_dir ? 'git pull' : "git -C {$_dir} pull";
		}, $pulls));
		$generates[] = $custom_dir.' generate:translations';
		$generates[] = $custom_dir.' install:databases';
		$generates[] = $custom_dir.' generate:base_files';
		$generates[] = $custom_dir.' generate:dependencies';

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