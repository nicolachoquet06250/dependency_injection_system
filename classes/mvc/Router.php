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
	 * @return Router
	 * @throws Exception
	 */
	public function route($route, $ctrl, $method = self::DEFAULT_ROUTE_METHOD, $type = self::STRING) {
		if(Dependency::exists($ctrl) && Dependency::is_controller($ctrl))
			self::$routes[$route] = [
				'type' => $type,
				'controller' => $ctrl,
				'method' => $method
			];
		else throw new Exception('controller '.$ctrl.' not found !');
		return $this;
	}

	public function root_route($ctrl, $method = self::DEFAULT_ROUTE_METHOD) {
		return $this->route('/', $ctrl, $method);
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

	/**
	 * @param $route
	 * @return mixed|string
	 * @throws ReflectionException
	 */
	public function execute($route) {
		if(strstr($route, '?')) {
			$route = explode('?', $route);
			$this->set_gets_from_string($route[1]);
			$route = $route[0];
		}
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
				$regex = '/'.$route_str.'$/AD';
				preg_match($regex, $route, $matches, PREG_OFFSET_CAPTURE, 0);
				if(!empty($matches)) {
					array_shift($matches);
					self::$CURRENT_ROUTE = [$route => $_route];
					return $this->run_controller(self::REGEX, $_route['controller'], $_route['method'], ...$matches);
				}
			}
		}
		return $this->inject->get_service_error()->error404();
	}

	/**
	 * @param        $type
	 * @param string $ctrl
	 * @param string $method
	 * @param array  ...$regex_parameter
	 * @return mixed
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function run_controller($type, $ctrl, $method, ...$regex_parameter) {
		$get_ctrl_method = 'get_'.$ctrl;
		/** @var Controller $controller */
		$controller = $this->inject->$get_ctrl_method();
		$method_ref = new ReflectionMethod(get_class($controller), $method);
		$nb_parameters = count($method_ref->getParameters());
		if($type === self::REGEX) {
			if(count($regex_parameter) > $nb_parameters) {
				$this->inject->get_service_error()->error404();
			}
			elseif (count($regex_parameter) < $nb_parameters) {
				for($i = 0; $i < $nb_parameters; $i++) {
					$regex_parameter[] = null;
				}
			}
			$tmp = [];
			foreach ($regex_parameter as $item) {
				if($item !== '') {
					$tmp[] = $item;
				}
			}
			$regex_parameter = $tmp;
			$regex_parameter = $this->inject->get_helpers()->array_flatten($regex_parameter);
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

	public function set_gets_from_string(string $gets) {
		$gets = explode('&', $gets);
		foreach ($gets as $get) {
			if(strstr($get, '=')) {
				$get = explode('=', $get);
//				if(ctype_digit($get[1])) {
//					$get[1] = intval($get[1]);
//				}
			}
			else {
				$get = [
					$get,
					true
				];
			}
			$this->set_get(...$get);
		}
	}

	public function set_get($key, $value) {
		if(ctype_digit($value)) {
			$value = intval($value);
		}
		$_GET[$key] = $value;
	}

	public function get($key) {
		if(isset($_GET[$key]) && ctype_digit($_GET[$key])) {
			$_GET[$key] = intval($_GET[$key]);
		}
		return $_GET[$key] ?? false;
	}

	public function post($key = null) {
		if(is_null($key)) {
			return $_POST;
		}
		return $_POST[$key] ?? false;
	}
}