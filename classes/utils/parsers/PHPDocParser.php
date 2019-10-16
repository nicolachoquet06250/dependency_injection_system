<?php


namespace mvc_router\parser;


use mvc_router\Base;
use mvc_router\commands\Command;
use mvc_router\data\gesture\Entity;
use mvc_router\data\gesture\Manager;
use mvc_router\dependencies\Dependency;
use mvc_router\interfaces\Singleton;
use mvc_router\mvc\Controller;
use mvc_router\router\Router;
use mvc_router\services\Service;
use ReflectionClass;
use ReflectionMethod;
use ReflectionException;

class PHPDocParser extends Base implements Singleton {
	
	const COMMAND       = 0;
	const SERVICE       = 1;
	const CONTROLLER    = 2;
	const ENTITY        = 3;
	const MANAGER       = 4;
	
	private static $instance = null;

	public static function create() {
		if(is_null(self::$instance)) {
			self::$instance = new PHPDocParser();
		}
		return self::$instance;
	}

	protected function clean_doc($doc) {
		return str_replace(['/**', ' */', "\t", ' * ', ' *'], '', $doc);
	}

	protected function get_route($doc, $class, $method_name) {
		$route = '/'.basename(str_replace('\\', '/', $class)).'/'.$method_name;
		if($doc) {
			$doc = $this->clean_doc($doc);
			$doc = explode("\n", $doc);
			$_doc = [];
			foreach ($doc as $line) {
				if(strlen($line) > 0) {
					$_doc[] = $line;
				}
			}
			$doc = $_doc;
			foreach ($doc as $item) {
				if(substr($item, 0, strlen('@route ')) === '@route ') {
					$route = str_replace(['@route ', ' '], '', $item);
					break;
				}
			}
		}
		return $route;
	}
	
	/**
	 * @param $class
	 * @param $method
	 * @return array|mixed
	 * @throws ReflectionException
	 */
	public function get_method_doc($class, $method) {
		if(is_object($class)) {
			$class = get_class($class);
		}
		$ref = new ReflectionClass($class);
		if($ref->hasMethod($method)) {
			$doc = $this->clean_doc($ref->getMethod($method)->getDocComment());
			$doc = explode("\n", $doc);
			$_doc = [];
			foreach ($doc as $line) {
				if(substr($line, 0, 1) === '@') {
					$line = str_replace('@', '', $line);
					$line = explode(' ', $line);
					$key = array_shift($line);
					if(isset($_doc[$key]) && !is_array($_doc[$key])) {
						$new_value = [
							$_doc[$key],
							implode(' ', $line),
						];
						$_doc[$key] = $new_value;
					} elseif (isset($_doc[$key]) && is_array($_doc[$key])) {
						$_doc[$key][] = implode(' ', $line);
					} else {
						$_doc[$key] = implode(' ', $line);
					}
				} else {
					if(!isset($_doc['description'])) {
						$_doc['description'] = $line;
					} else {
						$_doc['description'] .= "\n".$line;
					}
				}
			}
			$doc = $_doc;
			if(isset($doc['description']) && trim($doc['description']) === '') {
				unset($doc['description']);
			}
			return $doc;
		}
		return [];
	}
	
	/**
	 * @param $class
	 * @return array|mixed
	 * @throws ReflectionException
	 */
	public function get_class_doc($class) {
		if(is_object($class)) {
			$class = get_class($class);
		}
		$doc = $this->clean_doc((new ReflectionClass($class))->getDocComment());
		$doc = explode("\n", $doc);
		$_doc = [];
		foreach ($doc as $line) {
			if(substr($line, 0, 1) === '@') {
				$line = str_replace('@', '', $line);
				$line = explode(' ', $line);
				$key = array_shift($line);
				if(isset($_doc[$key]) && !is_array($_doc[$key])) {
					$new_value = [
						$_doc[$key],
						implode(' ', $line),
					];
					$_doc[$key] = $new_value;
				} elseif (isset($_doc[$key]) && is_array($_doc[$key])) {
					$_doc[$key][] = implode(' ', $line);
				} else {
					$_doc[$key] = implode(' ', $line);
				}
			} else {
				if(!isset($_doc['description'])) {
					$_doc['description'] = $line;
				} else {
					$_doc['description'] .= "\n".$line;
				}
			}
		}
		$doc = $_doc;
		if(isset($doc['description']) && trim($doc['description']) === '') {
			unset($doc['description']);
		}
		return $doc;
	}
	
	/**
	 * @param object|string $class
	 * @param integer $class_type
	 * @return array|ReflectionMethod[]
	 * @throws ReflectionException
	 */
	public function get_class_methods($class, $class_type) {
		if(is_object($class)) {
			$class = get_class($class);
		}
		$base = $class === Base::class;
		$ref = new ReflectionClass($class);
		$methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
		$methods = array_map(function(ReflectionMethod $method) {
			return $method->getName();
		}, $methods);
		
		if(!$base) {
			$base_ref = new ReflectionClass(Base::class);
			$base_methods = $base_ref->getMethods();
			switch($class_type) {
				case self::COMMAND:
					$class_base = Command::class;
					break;
				case self::SERVICE:
					$class_base = Service::class;
					break;
				case self::CONTROLLER:
					$class_base = Controller::class;
					break;
				case self::ENTITY:
					$class_base = Entity::class;
					break;
				case self::MANAGER:
					$class_base = Manager::class;
					break;
				default:
					$class_base = Base::class;
			}
			$class_base_ref = new ReflectionClass($class_base);
			$class_base_methods = $class_base_ref->getMethods();
			foreach($base_methods as $method) {
				if(in_array($method->getName(), $methods)) {
					foreach($methods as $i => $_method) {
						if($method->getName() === $_method) {
							unset($methods[$i]);
						}
					}
				}
			}
			foreach($class_base_methods as $method) {
				if(in_array($method->getName(), $methods)) {
					foreach($methods as $i => $_method) {
						if($method->getName() === $_method) {
							unset($methods[$i]);
						}
					}
				}
			}
		}
		return $methods;
	}

	protected function get_http_method($doc) {
		$http_method = 'get';
		if($doc) {
			$doc = $this->clean_doc($doc);
			$doc = explode("\n", $doc);
			$_doc = [];
			foreach ($doc as $line) {
				if(strlen($line) > 0) {
					$_doc[] = $line;
				}
			}
			$doc = $_doc;
			foreach ($doc as $item) {
				if(substr($item, 0, strlen('@http ')) === '@http '
				   || substr($item, 0, strlen('@http_method ')) === '@http_method ') {
					$http_method = str_replace(['@http ', '@http_method ', ' '], '', $item);
					break;
				}
			}
		}
		return $http_method;
	}

	public function get_method_route(Router $router, string $class, ReflectionMethod $method) {
		if($method->getDeclaringClass()->getName() === $class
		   && $method->getName() !== '__construct' && $method->getName() !== '__call'
		   && $method->getName() !== 'run') {
			$method_name = $method->getName();
			$doc = $method->getDocComment();

			$route = $this->get_route($doc, $class, $method_name);
			$http_method = $this->get_http_method($doc);

			if(!$this->is_method_route_disabled($method)) {
				$type = strstr($route, '[') || strstr($route, '(')
						|| strstr($route, ']') || strstr($route, ')') ? Router::REGEX : Router::STRING;
				$router->route($route, Dependency::get_name_from_class($class), $method_name, $type, $http_method);
			}
		}
	}

	public function is_method_route_disabled(ReflectionMethod $method) {
		return strstr($method->getDocComment(), '@route_disabled') ? true : false;
	}
}