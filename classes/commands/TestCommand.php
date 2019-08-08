<?php


namespace mvc_router\commands;


use Exception;
use mvc_router\services\Trigger;
use ReflectionException;

class TestCommand extends Command {
	/**
	 * @param Commands $commands
	 * @throws Exception
	 */
	public function server(Commands $commands) {
		$port = '2107';
		$directory = __DIR__.'/../../';
		if($this->param('port')) {
			$port = $this->param('port');
		}

		if($this->param('directory')) {
			$directory .= $this->param('directory');
		}
		$directory = realpath($directory);
		$commands->run('php -S localhost:'.$port.' -t '.$directory);
	}

	public function helper_is_cli(Trigger $triggers) {
		$triggers->trig('trigger_test', 'test');
	}
}