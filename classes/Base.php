<?php


namespace mvc_router;


use Exception;
use mvc_router\confs\Conf;
use mvc_router\confs\ConfWrapper;
use mvc_router\dependencies\Dependency;
use mvc_router\dependencies\DependencyWrapper;
use mvc_router\services\Logger;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;

class Base {
	/** @var DependencyWrapper $inject */
	protected $inject;
	/** @var ConfWrapper $confs */
	protected $confs;
	private $dependencies_injection_enabled = false;
	protected $dependency_injection_method = false;

	/**
	 * Base constructor.
	 *
	 * @throws ReflectionException
	 */
	public function __construct() {
		$this->after_construct();
	}

	/**
	 * @throws ReflectionException
	 */
	protected function after_construct() {
		$this->inject = WrapperFactory::dependencies();
		$this->confs = WrapperFactory::confs();
		$this->enable_dependencies_injection();
	}

	/**
	 * @throws ReflectionException
	 */
	protected function enable_dependencies_injection() {
		$this->dependencies_injection_enabled = true;
		$this->init_dependencies_injection_on_properties();
	}

	/**
	 * @return bool
	 */
	protected function is_dependencies_injection_enabled() {
		return $this->dependencies_injection_enabled;
	}

	/**
	 * @throws ReflectionException
	 */
	protected function init_dependencies_injection_on_properties() {
		if($this->is_dependencies_injection_enabled()) {
			$props = (new ReflectionClass(get_class($this)))->getProperties(ReflectionProperty::IS_PUBLIC);
			$_props = [];
			foreach ($props as $prop) {
				if($prop->class !== Base::class) {
					$_props[] = $prop;
				}
			}
			$props = $_props;
			foreach ($props as $prop) {
				$doc = $prop->getDocComment();
				$class = str_replace(['/**', '*/', "\t", " * ", "@var ", ' $'.$prop->getName(), "\n", ' '], '', $doc);
				$method = 'get_'.(Dependency::is_in($class) ? Dependency::get_name_from_class($class) : Conf::get_name_from_class($class));
				$obj = Dependency::is_in($class) ? 'inject' : 'confs';
				$prop->setValue($this, $this->$obj->$method());
			}
		}
	}

	/**
	 * @param callable $callback
	 * @param array    $methods
	 * @return array
	 */
	private function clean_methods(callable $callback, array $methods) {
		$_methods = [];
		foreach ($methods as $method) {
			$r = $callback($method);
			if(!is_null($r)) {
				$_methods[] = $r;
			}
		}
		return $_methods;
	}

	/**
	 * @param string $name
	 * @param array ...$arguments
	 * @return null|mixed
	 * @throws Exception
	 */
	protected function call($name, ...$arguments) {
		if(!$this->is_dependencies_injection_enabled()) {
			$this->dependency_injection_method = false;
			return $this->$name(...$arguments);
		}
		$ref_obj = new ReflectionObject($this);
		$parent_class = $ref_obj->getParentClass();
		$methods = $ref_obj->getMethods(ReflectionMethod::IS_PUBLIC);
		$methods = $this->clean_methods(function(ReflectionMethod $method) use ($parent_class) {
			if(substr($method->getName(), 0, 2) !== '__'
			   && ($method->class === get_class($this)
				   || (!is_null($parent_class) && $method->class === $parent_class->getName()))) {
				return $method;
			}
			return null;
		}, $methods);
		foreach ($methods as $method) {
			if($method->getName() === $name) {
				$parameters = [];
				foreach ($method->getParameters() as $parameter) {
					$parameters[] = Dependency::get_from_classname($parameter->getClass());
				}
				$method_return = null;
				eval('$method_return = $this->'.$name.'(...$parameters);');
				$this->dependency_injection_method = true;
				return $method_return;
			}
		}
		$this->dependency_injection_method = null;
		return null;
	}

	/**
	 * @param string $name
	 * @param array  ...$arguments
	 * @return mixed|null
	 * @throws Exception
	 */
	protected function call_with_stats_time($name, ...$arguments) {
		$logger = $this->inject->get_service_logger();
		$logger->types(Logger::FILE);
		$logger->file(__DIR__.'/logs/stats/', date('Y-m-d').'.log');
		$logger->log('START: '.__CLASS__.'::'.$name);
		$result = $this->call($name, ...$arguments);
		$logger->log('END: '.__CLASS__.'::'.$name);
		return $result;
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return null
	 * @throws Exception
	 */
	public function __call($name, $arguments) {
		if(strstr($name, '_stat_time') || strstr($name, 'stat_time_')) {
			$name = str_replace(['_stat_time', 'stat_time_'], '', $name);
			return $this->call_with_stats_time($name, ...$arguments);
		}
		return $this->call($name, ...$arguments);
	}

	/**
	 * @param $method_name
	 * @return array
	 * @throws Exception
	 */
	public function get_parameters_table($method_name) {
		if($this->is_dependencies_injection_enabled()) {
			$ref_obj      = new ReflectionObject($this);
			$parent_class = $ref_obj->getParentClass();
			$methods      = $ref_obj->getMethods(ReflectionMethod::IS_PUBLIC);
			$methods      = $this->clean_methods(function (ReflectionMethod $method) use ($parent_class) {
				if (substr($method->getName(), 0, 2) !== '__'
					&& ($method->class === get_class($this)
						|| (!is_null($parent_class) && $method->class === $parent_class->getName()))) {
					return $method;
				}
				return null;
			}, $methods);
			foreach ($methods as $method) {
				if ($method->getName() === $method_name) {
					$parameters = [];
					foreach ($method->getParameters() as $parameter) {
						$parameter_class = $parameter->getClass();
						if ($parameter_class) {
							$parameters[] = Dependency::get_from_classname($parameter->getClass());
						}
					}
					return $parameters;
				}
			}
		}
		return [];
	}
}