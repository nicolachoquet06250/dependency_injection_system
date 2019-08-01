<?php


namespace mvc_router\commands;


use Exception;

class CloneCommand extends Command {
	/**
	 * @return string
	 * @throws Exception
	 */
	public function repo() {
		if(!$this->param('repo')) {
			$translation = $this->inject->get_service_translation();
			$exception_message = $translation->__('La commande %1 attends le paramètre `repo` !', ['clone:repo']);
			throw new Exception($exception_message);
		}

		$repo = $this->param('repo');
		$root = realpath(__DIR__.'/../../').'/';
		$command = "git clone {$repo}".(($dest = $this->param('dest')) || ($dest = $this->param('destination'))
			? " {$root}{$dest}" : '');
		$this->inject->get_commands()->run($command);

		$log = "Repository {$repo} has normally been cloned";
		$log .= ($dest = $this->param('dest')) || ($dest = $this->param('destination')) ? " in {$root}{$dest}" : '';
		return $log;
	}

	/**
	 * 4 seconds duration
	 */
	public function test_stats() {
		for($i = 0; $i < 200000; $i++) {
			var_dump('ocucou');
		}
	}
}