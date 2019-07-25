<?php


namespace mvc_router\commands;


use Exception;
use mvc_router\Base;
use ReflectionMethod;
use ReflectionObject;

class Command extends Base {
	private $params = [];

	public function clean_params() {
		foreach ($this->params as $key => $value) {
			$this->params[$key] = str_replace('%20%', ' ', $value);
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
	 * @param string $class_name
	 * @param string $method
	 * @param mixed  ...$parameters
	 * @return mixed|null
	 * @throws Exception
	 */
	public function execute($method, ...$parameters) {
		$class_ref = new ReflectionObject($this);
		if(in_array($method, array_keys($class_ref->getMethods(ReflectionMethod::IS_PUBLIC)))) {
			return $this->$method(...$this->get_parameters_table($method), ...$parameters);
		}
		return null;
	}
}