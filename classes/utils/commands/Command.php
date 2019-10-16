<?php


namespace mvc_router\commands;


use Exception;
use mvc_router\Base;
use ReflectionMethod;
use ReflectionObject;

/**
 * Class Command
 *
 * @method void stat_time_clean_params()
 * @method void clean_params_stat_time()
 *
 * @method void stat_time_add_param($key, $value)
 * @method void add_param_stat_time($key, $value)
 *
 * @method void stat_time_param($key)
 * @method void param_stat_time($key)
 *
 * @method void stat_time_params()
 * @method void params_stat_time()
 *
 * @method void stat_time_execute($method, ...$parameters)
 * @method void execute_stat_time($method, ...$parameters)
 *
 * @package mvc_router\commands
 */
class Command extends Base {
	private $params = [];

	public function clean_params() {
		foreach ($this->params as $key => $value) {
			$this->params[$key] = str_replace('%20%', ' ', $value);
			if($this->params[$key] === 'true') {
				$this->params[$key] = true;
			} elseif ($this->params[$key] === 'false') {
				$this->params[$key] = false;
			}
		}
	}

	public function add_param($key, $value) {
		$this->params[$key] = $value;
	}

	protected function param($key) {
		return isset($this->params[$key]) ? $this->params[$key] : null;
	}

	protected function params() {
		return $this->params;
	}

	/**
	 * @param string $method
	 * @param mixed  ...$parameters
	 * @return mixed|null
	 * @throws Exception
	 */
	public function execute($method, ...$parameters) {
		$class_ref = new ReflectionObject($this);
		$_method = $method;
		if($this->param('stat_time')) {
			$_method = 'stat_time_'.$method;
		}
		if(in_array($method, array_keys($class_ref->getMethods(ReflectionMethod::IS_PUBLIC)))) {
			return $this->$_method(...$this->get_parameters_table($method), ...$parameters);
		}
		return null;
	}
}