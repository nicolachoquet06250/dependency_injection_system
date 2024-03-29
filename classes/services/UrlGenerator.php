<?php


namespace mvc_router\services;


use mvc_router\dependencies\Dependency;
use mvc_router\mvc\Controller;
use mvc_router\router\Router;

/**
 * Génère des urls en fonctions des routes configurés dans les controlleurs
 * et du chemins des fichiers statics
 *
 * @package mvc_router\services
 */
class UrlGenerator extends Service {
	/** @var \mvc_router\router\Router $router */
	public $router;

	/**
	 * @return string
	 */
	public function get_base_url() {
		return $this->router->get_base_url();
	}

	/**
	 * @return string
	 */
	public function get_api_base_url() {
		return $this->get_base_url().'/api';
	}

	/**
	 * @return string
	 */
	public function get_backoffice_base_url() {
		return $this->get_base_url().'/backoffice';
	}

	public function get_current_protocol() {
		return 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '').'://';
	}

	/**
	 * @param Controller $ctrl
	 * @param string     $method
	 * @param array      $for_regex_url
	 * @return string|null
	 */
	public function get_url_from_ctrl_and_method(Controller $ctrl, string $method, ...$for_regex_url) {
		$ctrl_class = get_class($ctrl);
		$base = $this->get_current_protocol().$this->get_base_url();
		foreach ($this->router->routes() as $route => $route_detail) {
			$current_ctrl_class = Dependency::get_class_from_name($route_detail['controller']);
			if($ctrl_class === $current_ctrl_class && $method === $route_detail['method']) {
				if($route_detail['type'] === Router::STRING) {
					return trim($base.$route);
				}
				preg_match_all('/((\/\?)?\([^\)\(]+\)\??)/', $route, $matches);
				array_shift($matches);
				$matches = $this->inject->get_helpers()->array_flatten($matches);
				foreach ($matches as $i => $match) {
					$route = str_replace($match, (isset($for_regex_url[$i]) ? $for_regex_url[$i] : ''), $route);
				}
				$route = str_replace(['\/', '//'], '/', $route);
				if(substr($route, strlen($route) - 1, 1) === '\\') {
					$route = substr($route, 0, strlen($route) - 1);
				}
				return trim($base.$route);
			}
		}
		return null;
	}
	
	public function get_static_url($directory, $name, $with_base = true) {
		$base = $with_base ? $this->get_base_url() : '';
		return "{$base}/static/{$directory}/{$name}";
	}
}