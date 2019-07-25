<?php


namespace mvc_router\parser;


use mvc_router\Base;
use mvc_router\dependencies\Dependency;
use mvc_router\interfaces\Singleton;
use mvc_router\router\Router;
use ReflectionClass;
use ReflectionMethod;

class PHPDocParser extends Base implements Singleton {
	private static $instance = null;

	public static function create() {
		if(is_null(self::$instance)) {
			self::$instance = new PHPDocParser();
		}
		return self::$instance;
	}

	public function get_method_route(Router $router, string $class, ReflectionMethod $method) {
		if($method->getDeclaringClass()->getName() === $class
		   && $method->getName() !== '__construct' && $method->getName() !== '__call'
		   && $method->getName() !== 'run') {
			$method_name = $method->getName();
			$route = '/'.basename(str_replace('\\', '/', $class)).'/'.$method_name;
			$doc = $method->getDocComment();
			if($doc) {
				$doc = str_replace(['/**', ' */', "\t", ' * '], '', $doc);
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
						$route = str_replace('@route ', '', $doc)[0];
						break;
					}
				}
			}
			if(!$this->is_method_route_disabled($method)) {
				$type = strstr($route, '[') || strstr($route, '(')
						|| strstr($route, ']') || strstr($route, ')') ? Router::REGEX : Router::STRING;
				$router->route($route, Dependency::get_name_from_class($class), $method_name, $type);
			}
		}
	}

	public function is_method_route_disabled(ReflectionMethod $method) {
		return strstr($method->getDocComment(), '@route_disabled') ? true : false;
	}
}