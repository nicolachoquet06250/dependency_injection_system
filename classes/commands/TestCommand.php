<?php


namespace mvc_router\commands;


use Exception;
use mvc_router\data\gesture\pizzygo\managers\User;
use mvc_router\services\Trigger;

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

	/**
	 * @param Trigger $triggers
	 */
	public function helper_is_cli(Trigger $triggers) {
		$triggers->trig('trigger_test', 'test');
	}

	/**
	 * @return array|string
	 */
	public function mysql() {
		$mysql = $this->confs->get_mysql();
		if(!$mysql->is_connected()) {
			return 'ERROR : Mysql not connected';
		}
		$mysql->query('SELECT * FROM `user` WHERE email = $1 OR email = $2', [
			'nicolachoquet06250@gmail.com',
			'yannchoquet@gmail.com'
		]);
		$users = [];
		while ($data = $mysql->fetch_assoc()) {
			$users[] = $data;
		}
		return $users;
	}

	/**
	 * @param User $userManager
	 */
	public function managers(User $userManager) {
		$user = $userManager->get_all_from_id_lastname(1, 'Choquet');
		$user->save();
	}
}