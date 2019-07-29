<?php

namespace mvc_router\mvc;

use mvc_router\router\Router;
use mvc_router\services\Route;
use mvc_router\services\Translate;

class Routes extends Controller {

	/** @var \mvc_router\services\Translate $translation */
	public $translation;

	/**
	 * @route \/routes\/?(stats)?
	 * @param Router      $router
	 * @param Route       $service_route
	 * @param Translate   $service_translation
	 * @param string|null $stats
	 */
	public function index(Router $router, Route $service_route, $stats = null) {
		echo '<meta charset="utf-8" />';
		echo '<title>'.$this->translation->__('Liste des routes').'</title>';
		echo '<form id="change-lang" method="get" action="">
<select name="lang" onchange="document.querySelector(\'#change-lang\').submit()">
	<option value="" disabled '.(!$router->get('lang') ? 'selected="selected"' : '').'>'.$this->translation->__('Choisir').'</option>';
		foreach ($this->translation->get_languages() as $language => $name) {
			echo '<option value="'.$language.'" '.($router->get('lang') && $router->get('lang') === $language ? 'selected="selected"' : '').'>'.$name.'</option>';
		}
		echo '</select>
</form>';

		if($lang = $router->get('lang')) {
			$this->translation->set_default_language($lang);
		}

		echo '<table style="width: 100%;">';
		$service_route->write_array_header(
			$this->translation->__('Type'),
			$this->translation->__('Controlleur'),
			$this->translation->__('Méthode'),
			$this->translation->__('Route')
		);
		$service_route->write_array_lines($router);
		if($router->get('stats') === true || $router->get('stats') === 1 || !is_null($stats)) {
			$stats = $this->translation->__('Stats');
			$service_route->write_array_header($stats, '', '', '');
			$service_route->write_stats_controllers();
			$service_route->write_stats_types();
			echo '<tr>
	<td colspan="4" style="height: 15px;"></td>
</tr>
<tr>
	<th colspan="4">
		<button onclick="window.location.href = \'/routes'.($router->get('lang') ? '?lang='.$router->get('lang') : '').'\';">'.$this->translation->__('Cacher les stats').'</button>
	</th>
</tr>';
		}
		else {
			echo '<tr>
	<td colspan="4" style="height: 15px;"></td>
</tr>
<tr>
	<th colspan="4">
		<button onclick="window.location.href = \'/routes/stats'.($router->get('lang') ? '?lang='.$router->get('lang') : '').'\';">'.$this->translation->__('Voir les stats').'</button>
	</th>
</tr>';
		}
		echo '</table>';
	}
}