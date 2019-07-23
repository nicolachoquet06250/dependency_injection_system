<?php

namespace mvc_router\mvc;


use mvc_router\router\Router;
use mvc_router\services\Route;

class Routes extends Controller {

	/**
	 * @route_disabled
	 * @param Router $router
	 * @param Route  $service_route
	 */
	public function index(Router $router, Route $service_route) {
		echo '<meta charset="utf-8" />';
		echo '<title>Liste des routes</title>';
		echo '<table style="width: 100%;">';
		$service_route->write_array_header('Type', 'Controlleur', 'Méthode', 'Route');
		$service_route->write_array_lines($router);
		if($router->get('stats') === true || $router->get('stats') === 1) {
			$service_route->write_array_header('Stats', '', '', '');
			$service_route->write_stats_controllers();
			$service_route->write_stats_types();
		}
		echo '</table>';
	}
}