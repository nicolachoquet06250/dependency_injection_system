<?php


namespace mvc_router;


use Exception;
use mvc_router\confs\Conf;
use mvc_router\confs\ConfWrapper;
use mvc_router\dependencies\Dependency;
use mvc_router\dependencies\DependencyWrapper;
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
	 * @param $name
	 * @param $arguments
	 * @return null
	 * @throws Exception
	 */
	public function __call($name, $arguments) {
		if(!$this->is_dependencies_injection_enabled()) {
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
				return $method_return;
			}
		}
		return null;
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