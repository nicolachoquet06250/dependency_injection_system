<?php


namespace mvc_router\commands;


use mvc_router\parser\PHPDocParser;

class HelpCommand extends Command {
	/**
	 * @param PHPDocParser $PHPDocParser
	 * @return array|mixed
	 */
	public function home(PHPDocParser $PHPDocParser) {
		return $this->var_dump($PHPDocParser->get_method_doc($this->get_class(), 'home'));
	}
}