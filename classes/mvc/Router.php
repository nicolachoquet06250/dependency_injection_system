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

	const HTTP_GET = 'get';
	const HTTP_POST = 'post';

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
	 * @param string $http_method
	 * @return Router
	 * @throws Exception
	 */
	public function route($route, $ctrl, $method = self::DEFAULT_ROUTE_METHOD, $type = self::STRING, $http_method = self::HTTP_GET) {
		if(Dependency::exists($ctrl) && Dependency::is_controller($ctrl))
			self::$routes[$route] = [
				'type' => $type,
				'controller' => $ctrl,
				'method' => $method,
				'http_method' => $http_method,
			];
		else throw new Exception('controller '.$ctrl.' not found !');
		return $this;
	}

	/**
	 * @param string $ctrl
	 * @param string $method
	 * @return Router
	 * @throws Exception
	 */
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
			$doc_parser = $this->inject->get_phpdoc_parser();
			foreach ($ref_class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
				$doc_parser->get_method_route($this, $class, $method);
			}
		}
	}

	/**
	 * @param $route
	 * @return mixed|string
	 * @throws Exception
	 */
	public function execute($route) {
		if(strstr($route, '?')) {
			$route = explode('?', $route);
			$this->set_gets_from_string($route[1]);
			$route = $route[0];
		}
		foreach (self::$routes as $route_str => $_route) {
			$route_str = str_replace(["\n", "\r"], '', $route_str);
			if($_route['type'] === self::STRING) {
				if($route === $route_str) {
					if($_route['http_method'] !== 'both' && ($_route['http_method'] === 'get' && $_SERVER['REQUEST_METHOD'] !== 'GET')
					   || ($_route['http_method'] === 'post' && $_SERVER['REQUEST_METHOD'] === 'GET')) {
						$translations = $this->inject->get_service_translation();
						$error_message = $translations->__('Mauvaise méthode de requête http utilisée !');
						$this->inject->get_service_error()->error400($error_message);
					}
					self::$CURRENT_ROUTE = [$route => $_route];
					return $this->run_controller(self::STRING, $_route['controller'], $_route['method']);
				}
			}
		}
		
		foreach (self::$routes as $route_str => $_route) {
			if($_route['type'] === self::REGEX) {
				$regex = '/'.str_replace(["\n", "\r"], '', $route_str).'$/AD';
				preg_match($regex, $route, $matches, PREG_OFFSET_CAPTURE, 0);
				if(!empty($matches)) {
					array_shift($matches);
					self::$CURRENT_ROUTE = [$route => $_route];
					foreach ($matches as $key => $match) {
						if(is_int($key)) {
							unset($matches[$key]);
						} else {
							if(is_array($match)) {
								$matches[$key] = $match[0];
							}
						}
					}
					return $this->run_controller(self::REGEX, $_route['controller'], $_route['method'], $matches);
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
	 * @throws Exception
	 */
	public function run_controller($type, $ctrl, $method, $regex_parameter = []) {
		$get_ctrl_method = 'get_'.$ctrl;
		/** @var Controller $controller */
		$controller = $this->inject->$get_ctrl_method();
		$ref_object = new ReflectionClass($controller->get_class());
		if($ref_object->hasMethod($method)) {
			if ($type === self::REGEX) {
				$controller->add_parameters($regex_parameter);
			}
			return $controller->$method(...$controller->get_parameters_table($method));
		}
		return $this->inject->get_service_error()->error404();
	}

	/**
	 * @return array
	 */
	public function routes() {
		return self::$routes;
	}

	/**
	 * @return bool|array
	 */
	public function get_root_route() {
		return isset(self::$routes['/']) ? self::$routes['/'] : false;
	}

	/**
	 * @param bool $key
	 * @return array|string|null
	 */
	public function get_current_route($key = false) {
		if($key) {
			return array_keys($this->get_current_route())[0];
		}
		return self::$CURRENT_ROUTE;
	}

	/**
	 * @param string $gets
	 */
	public function set_gets_from_string(string $gets) {
		$gets = explode('&', $gets);
		foreach ($gets as $get) {
			if(strstr($get, '=')) {
				$get = explode('=', $get);
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

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set_get(string $key, $value) {
		if(ctype_digit($value)) {
			$value = intval($value);
		}
		$_GET[$key] = $value;
	}

	/**
	 * @param string $key
	 * @param string $default
	 * @param bool   $trim
	 * @return bool|mixed
	 */
	public function get($key, $default = '', $trim = true) {
		if(isset($_GET[$key])) {
			$_GET[$key] = urldecode($_GET[$key]);
			if($trim) $_GET[$key] = trim($_GET[$key]);
			if(ctype_digit($_GET[$key])) $_GET[$key] = intval($_GET[$key]);
		}
		return $_GET[$key] ?? ($default ?? false);
	}

	/**
	 * @param string|null $key
	 * @param string      $default
	 * @param bool        $trim
	 * @return bool|mixed
	 */
	public function post($key = null, $default = '', $trim = true) {
		$json = $this->inject->get_service_json();
		$request_body = file_get_contents('php://input');
		if($request_body) {
			$request_body = $json->decode($request_body, true);
		} else $request_body = [];
		$post = array_merge($_POST, $request_body);
		if(is_null($key)) {
			return $post;
		}
		if(!isset($post[$key])) {
			return $default ?? false;
		}
		if($trim) $post[$key] = trim($post[$key]);
		if(ctype_digit($post[$key])) $post[$key] = intval($post[$key]);
		return $post[$key];
	}

	/**
	 * @return string
	 */
	public function get_base_url() {
		return $_SERVER['HTTP_HOST'];
	}
}