<?php


namespace mvc_router\services;


use mvc_router\dependencies\Dependency;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use ReflectionParameter;

/**
 * @package mvc_router\services
 */
class Trigger extends Service {
	protected static $triggers = [];

	/**
	 * @param string $trigger_name
	 * @param array|string  $callback
	 * @return mixed
	 * @throws ReflectionException
	 */
	public function register(string $trigger_name, $callback) {
		if(gettype($callback) === 'array') {
			$function = $callback['function'];
			$type     = 'normal';
			if (isset($callback['class'])) {
				$type  = 'static';
				$class = $callback['class'];
				$callback = $class.'::'.$function;
			} elseif (isset($callback['object'])) {
				$object = $callback['object'];
			}
		}
		else {
			$function = explode('::', $callback)[1];
			$type = 'static';
			$class = explode('::', $callback)[0];
		}

		$closure_ref = ($type === 'normal') ? new ReflectionObject($object) : new ReflectionClass($class);
		$function_ref = $closure_ref->getMethod($function);
		$parameters = $function_ref->getParameters();

		$parameters = array_map(function (ReflectionParameter $parameter) {
			$type = $parameter->getType()->getName();

			$array = [
				'type' => $type,
				'name' => $parameter->getName(),
			];

			if(strstr($type, '\\')) {
				$array['type'] = 'object';
				$array['class'] = $parameter->getClass()->getName();
			}

			return $array;
		}, $parameters);

		self::$triggers[$trigger_name] = [
			'callback' => $callback,
			'parameters' => $parameters
		];

		return self::$triggers[$trigger_name];
	}

	public function unregister(string $trigger_name) {
		if(!$this->exists($trigger_name)) {
			return false;
		}
		unset(self::$triggers[$trigger_name]);
		return true;
	}

	public function exists(string $trigger_name) {
		return isset(self::$triggers[$trigger_name]);
	}

	public function trig(string $trigger_name, ...$arguments) {
		if(!$this->exists($trigger_name)) {
			return false;
		}

		$callback = self::$triggers[$trigger_name]['callback'];
		if(!is_string($callback) && isset($callback['object'])) {
			return $callback['object']->{$callback['function']}(...$arguments);
		}
		return $callback(...$arguments);
	}

	public function flush() {
		self::$triggers = [];
	}

	public function get_trigger_data(string $trigger_name) {
		return $this->exists($trigger_name) ? self::$triggers[$trigger_name] : null;
	}

	public static function trigger_test(string $toto) {
		$helper = Dependency::get_wrapper_factory()->get_dependency_wrapper()->get_helpers();
		if($helper->is_cli_server() || $helper->is_cgi()) {
			echo '<pre>';
		}
		var_dump('class: ', __CLASS__);
		var_dump('function: ', __FUNCTION__);
		var_dump('params: ', $toto);
		if($helper->is_cli_server() || $helper->is_cgi()) {
			echo '</pre>';
		}
	}
}