<?php


namespace mvc_router\commands;


use mvc_router\services\Logger;

class TestCommand extends Command {
	public function lol(Logger $logger) {
		$logger->types(Logger::CONSOLE, Logger::FILE)->log('lol');
		var_dump($this->param('test'));
	}
}