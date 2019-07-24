<?php


namespace mvc_router\commands;


use Exception;
use mvc_router\Base;
use mvc_router\dependencies\Dependency;
use ReflectionMethod;
use ReflectionObject;

class Commands extends Base {

	protected function clean_params($params) {
		$_params = [];
		foreach ($params as $param) {
			if(strstr($param, '=')) {
				$param = explode('=', $param);
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

		if(Dependency::exists($command_class) && Dependency::is_command($command_class)) {
			$get_command = 'get_command_'.$command_class;
			$_command = $this->inject->$get_command();
			$class_ref = new ReflectionObject($_command);
			if(in_array($command_method, array_keys($class_ref->getMethods(ReflectionMethod::IS_PUBLIC)))) {
				return $_command->$command_method(...$_command->get_parameters_table($command_method), ...[$this->clean_params($command)]);
			}
		}
		throw new Exception('command '.$command_class.':'.$command_method.' not found !');
	}
}