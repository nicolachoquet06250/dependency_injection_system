<?php

namespace mvc_router\mvc;

class Routes extends Controller {

	/**
	 * @route \/routes\/?(stats)?
	 * @param \mvc_router\router\Router      $router
	 * @param \mvc_router\services\Route       $service_route
	 * @param string|null $stats
	 */
	public function index(\mvc_router\router\Router $router, \mvc_router\services\Route $service_route, $stats = null) {
		echo '<meta charset="utf-8" />';
		echo '<title>Liste des routes</title>';
		echo '<table style="width: 100%;">';
		$service_route->write_array_header('Type', 'Controlleur', 'Méthode', 'Route');
		$service_route->write_array_lines($router);
		if($router->get('stats') === true || $router->get('stats') === 1 || !is_null($stats)) {
			$service_route->write_array_header('Stats', '', '', '');
			$service_route->write_stats_controllers();
			$service_route->write_stats_types();
			echo '<tr>
	<td colspan="4" style="height: 15px;"></td>
</tr>
<tr>
	<th colspan="4">
		<button onclick="window.location.href = \'/routes\';">Cacher les stats</button>
	</th>
</tr>';
		}
		else {
			echo '<tr>
	<td colspan="4" style="height: 15px;"></td>
</tr>
<tr>
	<th colspan="4">
		<button onclick="window.location.href = \'/routes/stats\';">Voir les stats</button>
	</th>
</tr>';
		}
		echo '</table>';
	}
}