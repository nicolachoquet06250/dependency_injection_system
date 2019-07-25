<?php


namespace mvc_router\commands;


use Exception;
use mvc_router\Base;
use mvc_router\dependencies\Dependency;
use mvc_router\interfaces\Singleton;

class Commands extends Base implements Singleton {
	private static $instance = null;

	public static function create() {
		if(is_null(self::$instance)) {
			self::$instance = new Commands();
		}
		return self::$instance;
	}

	/**
	 * @param array $params
	 * @return array
	 */
	protected function clean_params($params) {
		$_params = [];
		foreach ($params as $param) {
			if(strstr($param, '=')) {
				$param = explode('=', $param);
				if(ctype_digit($param[1])) {
					$param[1] = intval($param[1]);
				}
				if (gettype($param[1]) === 'string') {
					$param[1] = str_replace('%20%', ' ', $param[1]);
				}
				$_params[$param[0]] = $param[1];
			}
			else {
				$_params[$param] = true;
			}
		}
		return $_params;
	}

	/**
	 * @param string $command
	 * @return mixed
	 * @throws Exception
	 */
	public function run(string $command) {
		$command = explode(' ', $command);

		$command_base = array_shift($command);
		$command_base = explode(':', $command_base);

		$command_class = array_shift($command_base);
		$command_method = array_shift($command_base);

		if(in_array('-p', $command)) {
			array_shift($command);
		}

		$params = $command;

		if(Dependency::exists('command_'.$command_class) && Dependency::is_command('command_'.$command_class)) {
			$get_command = 'get_command_'.$command_class;
			/** @var Command $_command */
			$_command = $this->inject->$get_command();
			$params = $this->clean_params($params);
			foreach ($params as $key => $value) {
				$_command->add_param($key, $value);
			}
			$_command->clean_params();
			return $_command->execute($command_method);
		}
		throw new Exception('command '.$command_class.':'.$command_method.' not found !');
	}
}