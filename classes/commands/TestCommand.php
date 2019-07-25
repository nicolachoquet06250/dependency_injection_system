<?php


namespace mvc_router\commands;


class TestCommand extends Command {
	protected function lol() {
		var_dump($this->param('test'));
		return 'lol';
	}
}