<?php


namespace mvc_router\router;


use Exception;
use mvc_router\Base;
use mvc_router\dependencies\Dependency;
use mvc_router\interfaces\Singleton;
use mvc_router\mvc\Controller;
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
				$this->inject->get_phpdoc_parser()->get_method_route($this, $class, $method);
			}
		}
	}

	public function execute($route) {
		foreach (self::$routes as $route_str => $_route) {
			if($_route['type'] === self::STRING) {
				if($route === $route_str) {
					self::$CURRENT_ROUTE = [$route => $_route];
					return $this->run_controller(self::STRING, $_route['controller'], $_route['method']);
				}
			}
		}

		foreach (self::$routes as $route_str => $_route) {
			if($_route['type'] === self::REGEX) {
				preg_match('/'.$route_str.'$/AD', $route, $matches);
				if(!empty($matches)) {
					array_shift($matches);
					self::$CURRENT_ROUTE = [$route => $_route];
					return $this->run_controller(self::REGEX, $_route['controller'], $_route['method'], ...$matches);
				}
			}
		}
		return '';
	}

	/**
	 * @param        $type
	 * @param string $ctrl
	 * @param string $method
	 * @param array  ...$regex_parameter
	 * @return mixed
	 * @throws ReflectionException
	 */
	public function run_controller($type, $ctrl, $method, ...$regex_parameter) {
		$get_ctrl_method = 'get_'.$ctrl;
		/** @var Controller $controller */
		$controller = $this->inject->$get_ctrl_method();
		$method_ref = new ReflectionMethod(get_class($controller), $method);
		$nb_parameters = count($method_ref->getParameters());
		if($regex_parameter !== $nb_parameters && $type === self::REGEX) {
			$controller->error404();
		}
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