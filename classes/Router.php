<?php


namespace mvc_router\router;


use Exception;
use mvc_router\Base;
use mvc_router\dependencies\Dependency;
use mvc_router\interfaces\Singleton;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Router extends Base implements Singleton {
	private static $instance = null;
	private static $routes = [];

	const REGEX = 0;
	const STRING = 1;

	const DEFAULT_ROUTE_METHOD = 'index';

	private static $CURRENT_ROUTE = null;

	public static function create() {
		if(is_null(self::$instance)) {
			self::$instance = new Router();
		}
		return self::$instance;
	}

	/**
	 * @param        $route
	 * @param        $ctrl
	 * @param string $method
	 * @param int    $type
	 * @param mixed  ...$flags
	 * @throws Exception
	 */
	public function route($route, $ctrl, $method = self::DEFAULT_ROUTE_METHOD, $type = self::STRING, ...$flags) {
		if(Dependency::exists($ctrl) && Dependency::is_controller($ctrl))
			self::$routes[$route] = [
				'type' => $type,
				'controller' => $ctrl,
				'method' => $method,
				'flags' => $flags
			];
		else throw new Exception('controller '.$ctrl.' not found !');
	}

	/**
	 * @throws Exception
	 */
	public function inspect_controllers() {
		foreach (Dependency::controllers() as $class => $controller) {
			Dependency::get_from_classname($class);
			$ref_class = new ReflectionClass($class);
			foreach ($ref_class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
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
					$type = strstr($route, '[') || strstr($route, '(')
							|| strstr($route, ']') || strstr($route, ')') ? self::REGEX : self::STRING;
					self::route($route, Dependency::get_name_from_class($class), $method_name, $type);
				}
			}
		}
	}

	public function execute($route) {
		foreach (self::$routes as $route_str => $_route) {
			if($_route['type'] === self::STRING) {
				if($route === $route_str) {
					self::$CURRENT_ROUTE = [$route => $_route];
					return $this->run_controller($_route['controller'], $_route['method']);
				}
			}
		}

		foreach (self::$routes as $route_str => $_route) {
			if($_route['type'] === self::REGEX) {
				preg_match('/'.$route_str.'$/AD', $route, $matches);
				if(!empty($matches)) {
					array_shift($matches);
					self::$CURRENT_ROUTE = [$route => $_route];
					return $this->run_controller($_route['controller'], $_route['method'], ...$matches);
				}
			}
		}
		return '';
	}

	public function run_controller($ctrl, $method, ...$regex_parameter) {
		$get_ctrl_method = 'get_'.$ctrl;
		$controller = $this->inject->$get_ctrl_method();
		return $controller->$method(...$controller->get_parameters_table($method), ...$regex_parameter);
	}

	public function routes() {
		return self::$routes;
	}

	/**
	 * @return bool|array
	 */
	public function get_root_route() {
		return isset(self::$routes['/']) ? self::$routes['/'] : false;
	}

	public function get_current_route() {
		return self::$CURRENT_ROUTE;
	}
}