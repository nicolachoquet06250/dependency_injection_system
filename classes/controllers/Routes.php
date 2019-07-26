<?php

namespace mvc_router\mvc;

use mvc_router\router\Router;
use mvc_router\services\Route;
use mvc_router\services\Translate;

class Routes extends Controller {

	/**
	 * @route \/routes\/?(stats)?
	 * @param Router      $router
	 * @param Route       $service_route
	 * @param Translate   $service_translation
	 * @param string|null $stats
	 */
	public function index(Router $router, Route $service_route, Translate $service_translation, $stats = null) {
		$service_translation->set_default_language(Translate::EN);
		echo '<meta charset="utf-8" />';
		echo '<title>'.$service_translation->__('Liste des routes').'</title>';
		echo '<table style="width: 100%;">';
		$service_route->write_array_header(
			$service_translation->__('Type'),
			$service_translation->__('Controlleur'),
			$service_translation->__('Méthode'),
			$service_translation->__('Route')
		);
		$service_route->write_array_lines($router);
		if($router->get('stats') === true || $router->get('stats') === 1 || !is_null($stats)) {
			$service_route->write_array_header($service_translation->__('Stats'), '', '', '');
			$service_route->write_stats_controllers();
			$service_route->write_stats_types();
			echo '<tr>
	<td colspan="4" style="height: 15px;"></td>
</tr>
<tr>
	<th colspan="4">
		<button onclick="window.location.href = \'/routes\';">'.$service_translation->__('Cacher les stats').'</button>
	</th>
</tr>';
		}
		else {
			echo '<tr>
	<td colspan="4" style="height: 15px;"></td>
</tr>
<tr>
	<th colspan="4">
		<button onclick="window.location.href = \'/routes/stats\';">'.$service_translation->__('Voir les stats').'</button>
	</th>
</tr>';
		}
		echo '</table>';
	}
}