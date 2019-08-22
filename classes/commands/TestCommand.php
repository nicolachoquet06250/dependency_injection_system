<?php


namespace mvc_router\commands;


use Exception;
use mvc_router\data\gesture\pizzygo\managers\User;
use mvc_router\services\FileSystem;
use mvc_router\services\Password;
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
	 * @param User        $userManager
	 * @param Password    $passwordService
	 * @return bool|\mvc_router\data\gesture\pizzygo\entities\User|\mvc_router\data\gesture\pizzygo\entities\User[]
	 * @throws Exception
	 */
	public function managers(User $userManager, Password $passwordService) {
		$password = $passwordService->b_crypt('2669NICOLAS2107');
		$user = $userManager->get_all_from_email('nicolachoquet06251@gmail.com');
		if(!$user) {
			$user = $userManager
				->set_address_email_phone_password_description_website_pseudo_firstname_lastname(
					"1102 ch de l'espagnol", 'nicolachoquet06251@gmail.com', '0763207630',
					$password, 'une description',
					'nicolaschoquet.fr', 'nicolachoquet06250',
					'Nicolas', 'Choquet');
		}
		if(!$user->get('active')) $user->set('active', true);
		if(!$user->get('fb_id')) $user->set('fb_id', 20225555);
		$user->save();

		$user = $userManager->get_all_from_pseudo('nicolachoquet06250');
		return $user && $passwordService->is_valid('2669NICOLAS2107', $user->get('password')) ? $user : false;
	}

	public function number_of_lines_in_project(FileSystem $fileSystem) {
		$nb_lines = 0;
		$void = !is_null($this->param('not-void')) ? !$this->param('not-void') : null;
		$fileSystem->browse_root(function ($elem) use ($fileSystem, &$nb_lines, $void) {
			$content = explode("\n", $fileSystem->read_file($elem));
			if(is_null($void)) $nb_lines = $nb_lines + count($content);
			elseif (is_bool($void)) {
				if($void) foreach ($content as $item) {
					if (trim($item) === "") {
						$nb_lines++;
					}
				}
				else foreach ($content as $item) {
					if (trim($item) !== "") {
						$nb_lines++;
					}
				}
			}
		}, true);
		if(is_null($void)) return "L'application comporte {$nb_lines} lignes de code !";
		elseif (is_bool($void)) return $void ? "L'application comporte {$nb_lines} lignes de code vides !"
			: "L'application comporte {$nb_lines} lignes de code non vides !";
		else return null;
	}
}