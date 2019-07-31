<?php


namespace mvc_router\confs\custom;


class Mysql extends \mvc_router\confs\Mysql {
	protected $host = 'mysqli';
	protected $pass = 'root';
	protected $user = 'root';
}